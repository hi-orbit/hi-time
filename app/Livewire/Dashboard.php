<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    protected $listeners = ['timeEntryUpdated' => '$refresh', 'timeEntryStopped' => '$refresh'];

    public function stopTimer($timeEntryId)
    {
        $timeEntry = TimeEntry::find($timeEntryId);

        if ($timeEntry && $timeEntry->user_id === Auth::id() && $timeEntry->is_running) {
            $timeEntry->update([
                'is_running' => false,
                'end_time' => now(),
                'duration_minutes' => $timeEntry->start_time->diffInMinutes(now())
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
        $assignedTasks = Task::where('assigned_to', $user->id)
            ->with(['project.customer', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->groupBy(function ($task) {
                return $task->project->customer->name ?? 'No Customer';
            });

        // Get running time entries for current user
        $runningTimeEntries = TimeEntry::where('user_id', $user->id)
            ->where('is_running', true)
            ->with(['task.project.customer'])
            ->orderBy('start_time', 'desc')
            ->get();

        return view('livewire.dashboard', [
            'assignedTasks' => $assignedTasks,
            'runningTimeEntries' => $runningTimeEntries,
        ]);
    }
}
