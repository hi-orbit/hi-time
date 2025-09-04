<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use App\Models\TaskNote;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    protected $listeners = ['timeEntryUpdated' => '$refresh', 'timeEntryStopped' => '$refresh'];

    public function stopTimer($timeEntryId)
    {
        $user = Auth::user();

        // Customers cannot stop timers
        if ($user->isCustomer()) {
            $this->dispatch('error', 'Access denied.');
            return;
        }

        $timeEntry = TaskNote::find($timeEntryId);

        if ($timeEntry && $timeEntry->user_id === $user->id && $timeEntry->is_running) {
            $endTime = now();
            $durationMinutes = $timeEntry->start_time->diffInMinutes($endTime);

            $timeEntry->update([
                'is_running' => false,
                'end_time' => $endTime,
                'duration_minutes' => $durationMinutes,
                'total_minutes' => $durationMinutes,
                'hours' => floor($durationMinutes / 60),
                'minutes' => $durationMinutes % 60,
            ]);

            $this->dispatch('success', 'Timer stopped successfully.');
        } else {
            $this->dispatch('error', 'Unable to stop timer.');
        }
    }

    public function render()
    {
        $user = Auth::user();

        // Get tasks assigned to current user, grouped by client
        $assignedTasksQuery = Task::where('assigned_to', $user->id)
            ->with(['project.customer', 'assignedUser']);

        // If user is a customer, only show tasks from projects they're assigned to
        if ($user->isCustomer()) {
            $projectIds = $user->assignedProjects()->pluck('projects.id');
            $assignedTasksQuery->whereIn('project_id', $projectIds);
        }

        $assignedTasks = $assignedTasksQuery
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->groupBy(function ($task) {
                return $task->project->customer->name ?? 'No Customer';
            });

        // Get running time entries for current user (not applicable for customers)
        $runningTimeEntries = collect();
        if (!$user->isCustomer()) {
            $runningTimeEntries = TaskNote::where('user_id', $user->id)
                ->where('is_running', true)
                ->whereNotNull('total_minutes')
                ->with(['task.project.customer'])
                ->orderBy('start_time', 'desc')
                ->get();
        }

        return view('livewire.dashboard', [
            'assignedTasks' => $assignedTasks,
            'runningTimeEntries' => $runningTimeEntries,
        ]);
    }
}
