# Task API

Public REST API providing full CRUD on tasks. Every request is authenticated with a **per-user API key**, so actions are attributed to a real user (task creator, notifications, and access rules all work as in the web app).

-   **Base URL (dev):** `https://hi-time.hi-orbit.com/api`
-   **Format:** JSON (request and response). Dates are ISO 8601.

* * *

## Authentication

### Generating a key

Keys are generated per user via artisan:

```bash
php artisan api:key:generate user@example.com
```

-   The key is **shown only once** — store it securely.
-   Generating a new key for a user **replaces** their old key (the old key stops working immediately).
-   There is intentionally no API endpoint to create or view keys.

### Using a key

Send the key with every request, either as the `X-API-Key` header or a Bearer token:

```
X-API-Key: <your-api-key>
# or
Authorization: Bearer <your-api-key>
```

| Status | Meaning |
| --- | --- |
| 401 | Missing or invalid API key |
| 403 | Authenticated, but not allowed to access/modify this resource |
| 422 | Validation error (JSON error bag, see Errors) |
| 404 | Task does not exist |

## Access rules

| Role | Access |
| --- | --- |
| admin, user, contractor | All tasks |
| customer | Only tasks in projects they are assigned to (create, update, move, and delete are restricted to those projects as well) |

Deleting a task additionally requires the user to be an **admin**, the task's **assignee**, or its **creator** (same rule as the web app).

* * *

## The task object

Returned by `show`, `store`, `update`, and (inside `data[]`) by `list`.

| Field | Type | Description |
| --- | --- | --- |
| id | int | Task ID |
| title | string | Task title |
| description | string\|null | Task description |
| status | string | One of the statuses |
| order | int | Ordering position within its status column (managed by the web UI; not settable via the API) |
| project | object\|null | { "id": int, "name": string } |
| assigned_user | object\|null | { "id": int, "name": string } — null if unassigned |
| created_by | object | { "id": int, "name": string } |
| tags | array | [{ "id": int, "name": string, "color": string }] |
| total_time_minutes | int | Total minutes tracked on this task's notes |
| created_at | string | ISO 8601 timestamp |
| updated_at | string | ISO 8601 timestamp |

Example:

```json
{
  "id": 281,
  "title": "Design landing page",
  "description": "Optional description",
  "status": "in_progress",
  "order": 0,
  "project": { "id": 6, "name": "Ownex (OCD)" },
  "assigned_user": { "id": 3, "name": "Dawn Taylor" },
  "created_by": { "id": 2, "name": "Rob Locke" },
  "tags": [{ "id": 3, "name": "frontend", "color": "#3B82F6" }],
  "total_time_minutes": 240,
  "created_at": "2026-09-22T10:00:00+00:00",
  "updated_at": "2026-09-22T12:30:00+00:00"
}
```

### Task statuses

`backlog` · `in_progress` · `in_test` · `failed_testing` · `ready_to_release` · `done` · `general`

`status` is **required** when creating a task; any value outside this list returns `422`.

* * *

## Endpoints

### List tasks — `GET /api/tasks`

Customer users only receive tasks from their assigned projects; other roles receive all tasks.

**Query parameters** (all optional):

| Parameter | Type | Description |
| --- | --- | --- |
| project_id | int | Only tasks in this project (must exist) |
| status | string | Only tasks with this status (must be a valid status) |
| assigned_to | int | Only tasks assigned to this user (must exist) |
| per_page | int | Page size, default 20, max 100 |

```bash
curl -s -H "X-API-Key: <key>" \
  "https://hi-time.hi-orbit.com/api/tasks?project_id=6&status=in_progress&per_page=50"
```

**Response `200`** — Laravel pagination envelope; tasks are ordered by status, then `order`:

```json
{
  "data": [ { "id": 281, "...": "task object" } ],
  "links": [],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 50,
    "total": 142
  }
}
```

> `meta.total` is the number of tasks matching the filters. Invalid filter values (unknown status/project/user, out-of-range `per_page`) return `422`.

### Show a task — `GET /api/tasks/{task}`

```bash
curl -s -H "X-API-Key: <key>" https://hi-time.hi-orbit.com/api/tasks/281
```

**Response `200`**: `{ "data": { task object } }`

### Create a task — `POST /api/tasks`

**Request body:**

| Field | Type | Required | Rules |
| --- | --- | --- | --- |
| title | string | yes | Max 255 characters |
| status | string | yes | One of the statuses |
| project_id | int | yes | Must exist; customer users must be assigned to the project |
| description | string | no | — |
| assigned_to | int\|null | no | Must be an existing user |
| tags | int[] | no | Tag IDs; replaces the task's tags (new tasks start empty) |

```bash
curl -s -X POST -H "X-API-Key: <key>" -H "Content-Type: application/json" \
  -d '{
    "title": "API task",
    "description": "Created via the API",
    "status": "backlog",
    "project_id": 6,
    "assigned_to": 3,
    "tags": [3]
  }' \
  https://hi-time.hi-orbit.com/api/tasks
```

**Response `201`**: `{ "data": { task object } }` — `created_by` is the API key's user. If `assigned_to` is set, the assignee receives the same in-app **task assignment notification** the web UI sends.

### Update a task — `PUT` / `PATCH /api/tasks/{task}`

Partial updates: omit any field to leave it unchanged.

**Request body** (all optional):

| Field | Type | Rules / semantics |
| --- | --- | --- |
| title | string | Max 255 characters |
| description | string\|null | Send null to clear it |
| status | string | One of the statuses |
| assigned_to | int\|null | Send null to unassign |
| project_id | int | Moves the task to another project; customer users must be assigned to the target project |
| tags | int[] | Replaces the task's tags; send [] to clear all |

```bash
curl -s -X PATCH -H "X-API-Key: <key>" -H "Content-Type: application/json" \
  -d '{"status": "in_progress", "assigned_to": 3, "tags": [3, 4]}' \
  https://hi-time.hi-orbit.com/api/tasks/281
```

**Response `200`**: `{ "data": { task object } }`

**Side effects** (mirroring the web app):

-   Reassigning the task to a _new_ user → task assignment notification to that user.
-   Changing the status (while an assignee exists) → task status notification to the assignee.

### Delete a task — `DELETE /api/tasks/{task}`

Deletes the task and its notes/time entries. Requires admin, assignee, or creator (and, for customers, project access).

```bash
curl -s -X DELETE -H "X-API-Key: <key>" https://hi-time.hi-orbit.com/api/tasks/281
```

**Response:** `204 No Content` on success; `403` without permission; `404` if already deleted.

* * *

## Errors

All errors are JSON.

`401` — missing/invalid key:

```json
{ "message": "Invalid or missing API key." }
```

`403` — access denied:

```json
{ "message": "You do not have access to this project." }
```

`422` — validation failed (messages + per-field error bag):

```json
{
  "message": "The title field is required.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

`404` — not found:

```json
{ "message": "Not Found." }
```
