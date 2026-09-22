<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Valid task statuses (mirrors the tasks.status DB enum).
     */
    public const STATUSES = ['backlog', 'in_progress', 'in_test', 'failed_testing', 'ready_to_release', 'done', 'general'];

    /**
     * List tasks with optional filtering and pagination.
     *
     * Filters: project_id, status, assigned_to, per_page (default 20, max 100).
     * Customer users only see tasks for their assigned projects.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'status' => 'nullable|in:'.implode(',', self::STATUSES),
            'assigned_to' => 'nullable|exists:users,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $query = Task::with(['project', 'assignedUser', 'creator', 'tags']);

        if ($user->isCustomer()) {
            $query->whereIn('project_id', $user->assignedProjects()->pluck('projects.id'));
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $validated['project_id']);
        }

        if ($request->filled('status')) {
            $query->where('status', $validated['status']);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $validated['assigned_to']);
        }

        $tasks = $query
            ->orderBy('status')
            ->orderBy('order')
            ->paginate($validated['per_page'] ?? 20);

        return response()->json([
            'data' => $tasks->getCollection()->map(fn (Task $task) => $this->taskPayload($task))->values(),
            'links' => $tasks->linkCollection()->values(),
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ],
        ]);
    }

    /**
     * Show a single task.
     */
    public function show(Task $task): JsonResponse
    {
        $task->load(['project', 'assignedUser', 'creator', 'tags']);

        $this->assertTaskAccess(Auth::user(), $task);

        return response()->json(['data' => $this->taskPayload($task)]);
    }

    /**
     * Create a task.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:'.implode(',', self::STATUSES),
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $project = Project::findOrFail($validated['project_id']);
        $this->assertProjectAccess($user, $project);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'project_id' => $project->id,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'created_by' => $user->id,
        ]);

        if (isset($validated['tags'])) {
            $task->tags()->sync($validated['tags']);
        }

        if ($task->assigned_to) {
            (new NotificationService)->sendTaskAssignmentNotification(
                $task->assigned_to,
                $task->title,
                $project->name,
                $project->id
            );
        }

        return response()->json(['data' => $this->taskPayload($task->fresh('tags'))], 201);
    }

    /**
     * Update a task (also supports moving it to another project).
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'status' => 'sometimes|in:'.implode(',', self::STATUSES),
            'project_id' => 'sometimes|exists:projects,id',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $this->assertTaskAccess($user, $task);

        $oldAssignedTo = $task->assigned_to;
        $oldStatus = $task->status;

        // Project move: customers must also be assigned to the target project.
        if (isset($validated['project_id']) && (int) $validated['project_id'] !== (int) $task->project_id) {
            $this->assertProjectAccess($user, Project::findOrFail($validated['project_id']));
        }

        $task->update([
            'title' => $validated['title'] ?? $task->title,
            'description' => array_key_exists('description', $validated) ? $validated['description'] : $task->description,
            'status' => $validated['status'] ?? $task->status,
            'assigned_to' => array_key_exists('assigned_to', $validated) ? ($validated['assigned_to'] ?? null) : $task->assigned_to,
            'project_id' => $validated['project_id'] ?? $task->project_id,
        ]);

        if (isset($validated['tags'])) {
            $task->tags()->sync($validated['tags']);
        }

        $project = $task->project;

        // Mirror the web app's notification behavior on reassignment/status change.
        if ($task->assigned_to && $task->assigned_to != $oldAssignedTo) {
            (new NotificationService)->sendTaskAssignmentNotification(
                $task->assigned_to,
                $task->title,
                $project->name,
                $project->id
            );
        }

        if ($task->status != $oldStatus && $task->assigned_to) {
            (new NotificationService)->sendTaskStatusNotification(
                $task->assigned_to,
                $task->title,
                $task->status,
                $project->name,
                $project->id
            );
        }

        return response()->json(['data' => $this->taskPayload($task->fresh('tags'))]);
    }

    /**
     * Delete a task and its notes.
     */
    public function destroy(Task $task): \Illuminate\Http\Response
    {
        $user = Auth::user();

        $this->assertTaskAccess($user, $task);

        // Mirror the web app's delete rule: admin, assignee, or creator.
        if (! $user->isAdmin() && $task->assigned_to !== $user->id && $task->created_by !== $user->id) {
            abort(403, 'You do not have permission to delete this task.');
        }

        $task->timeEntries()->delete();
        $task->notes()->delete();
        $task->delete();

        return response()->noContent();
    }

    /**
     * Abort with 403 if the user may not touch tasks in the project.
     */
    private function assertProjectAccess(User $user, Project $project): void
    {
        if ($user->isCustomer() && ! $project->assignedUsers()->where('user_id', $user->id)->exists()) {
            abort(403, 'You do not have access to this project.');
        }
    }

    /**
     * Abort with 403 if the user may not touch this task.
     */
    private function assertTaskAccess(User $user, Task $task): void
    {
        $this->assertProjectAccess($user, $task->project ?? Project::find($task->project_id));
    }

    /**
     * Build the JSON payload for a task.
     */
    private function taskPayload(Task $task): array
    {
        $task->loadMissing(['project', 'assignedUser', 'creator', 'tags']);

        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'order' => $task->order,
            'project' => $task->project ? ['id' => $task->project->id, 'name' => $task->project->name] : null,
            'assigned_user' => $task->assignedUser ? ['id' => $task->assignedUser->id, 'name' => $task->assignedUser->name] : null,
            'created_by' => $task->creator ? ['id' => $task->creator->id, 'name' => $task->creator->name] : null,
            'tags' => $task->tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'color' => $tag->color,
            ])->values(),
            'total_time_minutes' => $task->total_time_from_notes,
            'created_at' => $task->created_at?->toIso8601String(),
            'updated_at' => $task->updated_at?->toIso8601String(),
        ];
    }
}
