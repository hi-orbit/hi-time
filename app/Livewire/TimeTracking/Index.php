<?php

namespace App\Livewire\TimeTracking;

use Livewire\Component;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $selectedTaskId = '';
    public $description = '';
    public $hours = 0;
    public $minutes = 0;
    public $entryDate;

    protected $rules = [
        'selectedTaskId' => 'required|exists:tasks,id',
        'hours' => 'nullable|integer|min:0|max:23',
        'minutes' => 'required|integer|min:0|max:59',
        'entryDate' => 'required|date',
    ];

    public function mount()
    {
        // Set default entry date to today
        $this->entryDate = now()->format('Y-m-d');

        if (request()->has('task')) {
            $this->selectedTaskId = request('task');
        }
    }

    public function startTimer()
    {
        $this->validate(['selectedTaskId' => 'required|exists:tasks,id']);

        // Stop any running timers for this user
        TimeEntry::where('user_id', Auth::id())
            ->where('is_running', true)
            ->update([
                'is_running' => false,
                'end_time' => now(),
            ]);

        // Start new timer
        TimeEntry::create([
            'task_id' => $this->selectedTaskId,
            'user_id' => Auth::id(),
            'description' => $this->description,
            'start_time' => now(),
            'is_running' => true,
        ]);

        $task = Task::find($this->selectedTaskId);
        session()->flash('message', 'Timer started for: ' . $task->title);
        $this->reset(['description']);
    }

    public function stopTimer($entryId)
    {
        $entry = TimeEntry::where('id', $entryId)
            ->where('user_id', Auth::id())
            ->where('is_running', true)
            ->first();

        if ($entry) {
            $entry->update([
                'is_running' => false,
                'end_time' => now(),
            ]);
            session()->flash('message', 'Timer stopped.');
        }
    }

    public function logManualTime()
    {
        $this->validate();

        // Treat empty or null hours as 0
        $hours = $this->hours ?: 0;
        $totalMinutes = ($hours * 60) + $this->minutes;

        if ($totalMinutes > 0) {
            TimeEntry::create([
                'task_id' => $this->selectedTaskId,
                'user_id' => Auth::id(),
                'entry_date' => $this->entryDate,
                'description' => $this->description,
                'duration_minutes' => $totalMinutes,
                'is_running' => false,
            ]);

            session()->flash('message', 'Time logged successfully!');
            $this->reset(['description', 'hours', 'minutes']);
            // Keep the entry date as is, don't reset it
        }
    }

    public function render()
    {
        $runningEntries = TimeEntry::where('user_id', Auth::id())
            ->where('is_running', true)
            ->with(['task.project'])
            ->get();

        $recentEntries = TimeEntry::where('user_id', Auth::id())
            ->whereNotNull('end_time')
            ->with(['task.project'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $tasks = Task::whereIn('project_id', function($query) {
            $query->select('id')->from('projects');
        })->with('project')->get();

        return view('livewire.time-tracking.index', [
            'runningEntries' => $runningEntries,
            'recentEntries' => $recentEntries,
            'tasks' => $tasks,
        ]);
    }
}
