<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        // Get tasks assigned to current user
        $assignedTasks = Task::where('assigned_to', $user->id)
            ->with(['project', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Get running time entries for current user
        $runningTimeEntries = TimeEntry::where('user_id', $user->id)
            ->where('is_running', true)
            ->with(['task.project'])
            ->get();

        return view('livewire.dashboard', [
            'assignedTasks' => $assignedTasks,
            'runningTimeEntries' => $runningTimeEntries,
        ]);
    }
}
