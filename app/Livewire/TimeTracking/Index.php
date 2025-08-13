<?php

namespace App\Livewire\TimeTracking;

use Livewire\Component;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $selectedProjectId = '';
    public $selectedTaskId = '';
    public $description = '';
    public $hours = 0;
    public $minutes = 0;
    public $entryDate;

    // Edit properties
    public $editingEntryId = null;
    public $editEntryDate;
    public $editStartTime;
    public $editEndTime;
    public $editDescription;

    protected $rules = [
        'selectedTaskId' => 'required|exists:tasks,id',
        'hours' => 'nullable|integer|min:0|max:23',
        'minutes' => 'required|integer|min:0|max:59',
        'entryDate' => 'required|date',
        'editEntryDate' => 'required|date',
        'editStartTime' => 'nullable|date_format:H:i',
        'editEndTime' => 'nullable|date_format:H:i',
        'editDescription' => 'nullable|string',
    ];

    public function mount()
    {
        // Set default entry date to today
        $this->entryDate = now()->format('Y-m-d');

        if (request()->has('task')) {
            $this->selectedTaskId = request('task');
        }
    }

    public function updatedSelectedProjectId($value)
    {
        // Reset task selection when project changes
        $this->selectedTaskId = '';
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

    public function editEntry($entryId)
    {
        $entry = TimeEntry::where('id', $entryId)
            ->where('user_id', Auth::id())
            ->first();

        if ($entry) {
            $this->editingEntryId = $entryId;
            $this->editEntryDate = $entry->entry_date ? $entry->entry_date->format('Y-m-d') : $entry->created_at->format('Y-m-d');
            $this->editStartTime = $entry->start_time ? $entry->start_time->format('H:i') : '';
            $this->editEndTime = $entry->end_time ? $entry->end_time->format('H:i') : '';
            $this->editDescription = $entry->description;
        }
    }

    public function updateEntry()
    {
        $this->validate([
            'editEntryDate' => 'required|date',
            'editStartTime' => 'nullable|date_format:H:i',
            'editEndTime' => 'nullable|date_format:H:i',
            'editDescription' => 'nullable|string',
        ]);

        $entry = TimeEntry::where('id', $this->editingEntryId)
            ->where('user_id', Auth::id())
            ->first();

        if ($entry) {
            $updateData = [
                'entry_date' => $this->editEntryDate,
                'description' => $this->editDescription,
            ];

            // Handle time updates
            if ($this->editStartTime) {
                $updateData['start_time'] = $this->editEntryDate . ' ' . $this->editStartTime;
            }

            if ($this->editEndTime && $this->editStartTime) {
                $updateData['end_time'] = $this->editEntryDate . ' ' . $this->editEndTime;
                
                // Calculate duration if both times are provided
                $start = \Carbon\Carbon::parse($updateData['start_time']);
                $end = \Carbon\Carbon::parse($updateData['end_time']);
                
                if ($end->greaterThan($start)) {
                    $updateData['duration_minutes'] = $start->diffInMinutes($end);
                    $updateData['is_running'] = false;
                } else {
                    // If end time is before start time, don't update duration
                    unset($updateData['end_time']);
                }
            }

            $entry->update($updateData);
            
            session()->flash('message', 'Time entry updated successfully!');
            $this->cancelEdit();
        }
    }

    public function cancelEdit()
    {
        $this->editingEntryId = null;
        $this->editEntryDate = '';
        $this->editStartTime = '';
        $this->editEndTime = '';
        $this->editDescription = '';
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

        // Get all active projects for the dropdown
        $projects = Project::where('status', 'active')
            ->orderBy('name')
            ->get();

        // Get tasks based on selected project
        $tasks = collect();
        if ($this->selectedProjectId) {
            $tasks = Task::where('project_id', $this->selectedProjectId)
                ->with('project')
                ->orderBy('title')
                ->get();
        }

        return view('livewire.time-tracking.index', [
            'runningEntries' => $runningEntries,
            'recentEntries' => $recentEntries,
            'projects' => $projects,
            'tasks' => $tasks,
        ]);
    }
}
