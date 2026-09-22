# API

Public REST API for the hi-time application covering **tasks** (full CRUD), **task comments** (task notes, read + create), and **tags** (list + create). Every request is authenticated with a **per-user API key**, so actions are attributed to a real user (task creator, notifications, and access rules all work as in the web app).

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
| 404 | Resource does not exist |

## Access rules

| Role | Access |
| --- | --- |
| admin, user, contractor | All tasks, comments, and tags |
| customer | Only tasks (and their comments) in projects they are assigned to; only tags for customers they have access to (via their assigned projects) |

Deleting a task additionally requires the user to be an **admin**, the task's **assignee**, or its **creator** (same rule as the web app).

Creating a tag as a customer is restricted to customers they have access to; global tags (no `customer_id`) are out of reach.

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

## The comment object

A comment is a note on a task (the web app's time-entry/notes feature). Returned by the comment endpoints.

| Field | Type | Description |
| --- | --- | --- |
| id | int | Comment (note) ID |
| task_id | int | The task the comment belongs to |
| content | string | Comment text (max 1000 characters) |
| user | object\|null | { "id": int, "name": string } — the author |
| hours | int\|null | Tracked hours (manual or derived) |
| minutes | int\|null | Tracked minutes |
| total_minutes | int\|null | Total tracked minutes; null for a plain note without time |
| start_time | string\|null | ISO 8601, when the tracked work started |
| end_time | string\|null | ISO 8601, when it ended |
| entry_date | string\|null | `YYYY-MM-DD` — the day the work belongs to |
| created_at | string | ISO 8601 timestamp |

Example:

```json
{
  "id": 942,
  "task_id": 281,
  "content": "Fixed the flaky test",
  "user": { "id": 2, "name": "Rob Locke" },
  "hours": 1,
  "minutes": 30,
  "total_minutes": 90,
  "start_time": "2026-09-21T09:00:00+00:00",
  "end_time": "2026-09-21T10:30:00+00:00",
  "entry_date": "2026-09-21",
  "created_at": "2026-09-21T11:05:00+00:00"
}
```

## The tag object

Returned by the tag endpoints.

| Field | Type | Description |
| --- | --- | --- |
| id | int | Tag ID |
| name | string | Tag name (unique per customer scope) |
| color | string | Hex color, e.g. `#3B82F6` (default when omitted) |
| description | string\|null | Optional description |
| customer | object\|null | { "id": int, "name": string } — null for global tags |
| created_at | string | ISO 8601 timestamp |
| updated_at | string | ISO 8601 timestamp |

Example:

```json
{
  "id": 3,
  "name": "frontend",
  "color": "#3B82F6",
  "description": null,
  "customer": { "id": 6, "name": "Ownex (OCD)" },
  "created_at": "2026-09-01T09:00:00+00:00",
  "updated_at": "2026-09-01T09:00:00+00:00"
}
```

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

### List task comments — `GET /api/tasks/{task}/comments`

Returns all comments/notes on the task, newest first (no pagination).

```bash
curl -s -H "X-API-Key: <key>" https://hi-time.hi-orbit.com/api/tasks/281/comments
```

**Response `200`**: `{ "data": [ { comment object }, ... ] }`

Customer users may only read comments on tasks in projects they are assigned to (`403` otherwise).

### Create a task comment — `POST /api/tasks/{task}/comments`

A comment is plain text; time is optional and works with either **manual hours/minutes** or a **start/end time pair** (same semantics as the web app's note form, including overnight work). Omit both to create a plain note.

**Request body:**

| Field | Type | Required | Rules |
| --- | --- | --- | --- |
| content | string | yes | Max 1000 characters |
| hours | int | no | 0–23; manual time entry |
| minutes | int | no | 0–59; manual time entry |
| start_time | string | conditional | ISO 8601 date-time; requires `end_time` |
| end_time | string | conditional | ISO 8601 date-time; must be at or after `start_time` — if it is earlier it is treated as the next day (overnight work) |

```bash
# Plain note
curl -s -X POST -H "X-API-Key: <key>" -H "Content-Type: application/json" \
  -d '{"content": "Pushed the fix"}' \
  https://hi-time.hi-orbit.com/api/tasks/281/comments

# Manual time
curl -s -X POST -H "X-API-Key: <key>" -H "Content-Type: application/json" \
  -d '{"content": "Worked on the API", "hours": 2, "minutes": 30}' \
  https://hi-time.hi-orbit.com/api/tasks/281/comments

# Tracked start/end time
curl -s -X POST -H "X-API-Key: <key>" -H "Content-Type: application/json" \
  -d '{"content": "Pair programming", "start_time": "2026-09-21T09:00:00", "end_time": "2026-09-21T10:30:00"}' \
  https://hi-time.hi-orbit.com/api/tasks/281/comments
```

**Response `201`**: `{ "data": { comment object } }` — `user` is the API key's user, and the comment counts toward the task's `total_time_minutes`.

Customer users may only create comments on tasks in projects they are assigned to (`403` otherwise). Comments created via the API are marked with `source: api` (not exposed in the response).

### List tags — `GET /api/tags`

Returns tags ordered by name. Customer users only see tags for customers they have access to.

**Query parameters** (both optional):

| Parameter | Type | Description |
| --- | --- | --- |
| customer_id | int | Only return tags for this customer |
| name | string | Partial match on the tag name |

```bash
curl -s -H "X-API-Key: <key>" "https://hi-time.hi-orbit.com/api/tags?customer_id=6&name=front"
```

**Response `200`**: `{ "data": [ { tag object }, ... ] }`

### Create a tag — `POST /api/tags`

**Request body:**

| Field | Type | Required | Rules |
| --- | --- | --- | --- |
| name | string | yes | Max 255 characters; must be unique per customer scope (same name for the same customer → `422`; the same name under a different customer is fine) |
| color | string | no | Hex `#RRGGBB`, e.g. `#EF4444`; defaults to `#3B82F6` |
| description | string | no | — |
| customer_id | int\|null | no | Must be an existing customer; omit for a global tag |

```bash
curl -s -X POST -H "X-API-Key: <key>" -H "Content-Type: application/json" \
  -d '{"name": "backend", "color": "#10B981", "customer_id": 6}' \
  https://hi-time.hi-orbit.com/api/tags
```

**Response `201`**: `{ "data": { tag object } }`

Customer users may only create tags for customers they have access to — foreign customers and global tags return `403`.

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
