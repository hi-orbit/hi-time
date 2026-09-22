<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskNote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskCommentController extends Controller
{
    /**
     * List the comments (task notes) for a task, newest first.
     */
    public function index(Task $task): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $this->assertTaskAccess($user, $task);

        $comments = $task->notes()
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $comments->map(fn (TaskNote $note) => $this->commentPayload($note))->values()]);
    }

    /**
     * Create a comment on a task, optionally with tracked time.
     *
     * Time can be given as manual hours/minutes or as a start/end time pair
     * (same semantics as the web app's note form, including overnight work).
     */
    public function store(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'hours' => 'nullable|integer|between:0,23',
            'minutes' => 'nullable|integer|between:0,59',
            'start_time' => 'nullable|required_with:end_time|date',
            'end_time' => 'nullable|required_with:start_time|date|after_or_equal:start_time',
        ], [
            'end_time.after_or_equal' => 'The end time must be after the start time.',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $this->assertTaskAccess($user, $task);

        $noteData = [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
            'source' => 'api',
            'is_running' => false,
        ];

        // Manual hours/minutes
        if (isset($validated['hours']) || isset($validated['minutes'])) {
            $hours = $validated['hours'] ?? 0;
            $minutes = $validated['minutes'] ?? 0;

            $noteData['hours'] = $hours > 0 ? $hours : null;
            $noteData['minutes'] = $minutes > 0 ? $minutes : null;
            $noteData['total_minutes'] = $hours * 60 + $minutes;
        }

        // Explicit start/end times
        if (! empty($validated['start_time']) && ! empty($validated['end_time'])) {
            $start = Carbon::parse($validated['start_time']);
            $end = Carbon::parse($validated['end_time']);

            // Handle overnight work (end time next day)
            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $noteData['start_time'] = $start;
            $noteData['end_time'] = $end;
            $noteData['entry_date'] = $start->toDateString();
            $noteData['total_minutes'] = (int) $start->diffInMinutes($end);
        }

        $note = TaskNote::create($noteData);

        return response()->json(['data' => $this->commentPayload($note->load('user'))], 201);
    }

    /**
     * Abort with 403 if the user may not touch tasks in this task's project.
     */
    private function assertTaskAccess(User $user, Task $task): void
    {
        if ($user->isCustomer() && ! $user->assignedProjects()->pluck('projects.id')->contains($task->project_id)) {
            abort(403, 'You do not have access to this project.');
        }
    }

    /**
     * Build the JSON payload for a comment.
     */
    private function commentPayload(TaskNote $note): array
    {
        return [
            'id' => $note->id,
            'task_id' => $note->task_id,
            'content' => $note->content,
            'user' => $note->user ? ['id' => $note->user->id, 'name' => $note->user->name] : null,
            'hours' => $note->hours,
            'minutes' => $note->minutes,
            'total_minutes' => $note->total_minutes,
            'start_time' => $note->start_time?->toIso8601String(),
            'end_time' => $note->end_time?->toIso8601String(),
            'entry_date' => $note->entry_date?->toDateString(),
            'created_at' => $note->created_at?->toIso8601String(),
        ];
    }
}
