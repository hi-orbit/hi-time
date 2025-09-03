<?php

namespace App\Livewire\TimeTracking;

use Livewire\Component;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\TaskNote;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $selectedProjectId = '';
    public $selectedTaskId = '';
    public $description = '';
    public $note = '';
    public $hours = 0;
    public $minutes = 0;
    public $startTime = '';
    public $endTime = '';
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
        'minutes' => 'nullable|integer|min:0|max:59',
        'note' => 'required|string|max:1000',
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
        // Validate note content first
        if (empty($this->note)) {
            $this->addError('note', 'Note content is required.');
            return;
        }

        if (strlen($this->note) > 1000) {
            $this->addError('note', 'Note content cannot exceed 1000 characters.');
            return;
        }

        // Validate task selection
        if (!$this->selectedTaskId) {
            $this->addError('selectedTaskId', 'Please select a task.');
            return;
        }

        // Validate entry date
        if (!$this->entryDate) {
            $this->addError('entryDate', 'Entry date is required.');
            return;
        }

        // Normalize time formats BEFORE validation
        if ($this->startTime && strlen($this->startTime) > 5) {
            $this->startTime = substr($this->startTime, 0, 5);
        }
        if ($this->endTime && strlen($this->endTime) > 5) {
            $this->endTime = substr($this->endTime, 0, 5);
        }

        // Custom validation for time fields
        $validTimePattern = '/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/';

        // Validate start/end times if provided
        if ($this->startTime && !preg_match($validTimePattern, $this->startTime)) {
            $this->addError('startTime', 'Please enter a valid time in HH:MM format.');
            return;
        }

        if ($this->endTime && !preg_match($validTimePattern, $this->endTime)) {
            $this->addError('endTime', 'Please enter a valid time in HH:MM format.');
            return;
        }

        // If using start/end times, validate they're both provided
        if ($this->startTime || $this->endTime) {
            if (!$this->startTime) {
                $this->addError('startTime', 'Start time is required when end time is provided.');
                return;
            }
            if (!$this->endTime) {
                $this->addError('endTime', 'End time is required when start time is provided.');
                return;
            }
        } else {
            // Validate manual hours/minutes if provided
            if ($this->hours && (!is_numeric($this->hours) || $this->hours < 0 || $this->hours > 23)) {
                $this->addError('hours', 'Hours must be between 0 and 23.');
                return;
            }

            if ($this->minutes && (!is_numeric($this->minutes) || $this->minutes < 0 || $this->minutes > 59)) {
                $this->addError('minutes', 'Minutes must be between 0 and 59.');
                return;
            }

            // Require at least some time input (either hours or minutes)
            if (!$this->hours && !$this->minutes) {
                $this->addError('minutes', 'Please enter either hours/minutes or start/end times.');
                return;
            }
        }

        $hours = 0;
        $minutes = 0;
        $totalMinutes = 0;

        // Calculate time based on start/end times if provided
        if ($this->startTime && $this->endTime) {
            try {
                $start = \Carbon\Carbon::createFromFormat('H:i', $this->startTime);
                $end = \Carbon\Carbon::createFromFormat('H:i', $this->endTime);

                // Handle overnight work (end time next day)
                if ($end->lessThan($start)) {
                    $end->addDay();
                }

                $totalMinutes = $start->diffInMinutes($end);

                // Validate that the time difference is reasonable (max 24 hours)
                if ($totalMinutes > 1440) { // 24 * 60 = 1440 minutes
                    $this->addError('endTime', 'End time cannot be more than 24 hours after start time.');
                    return;
                }

                $hours = intval($totalMinutes / 60);
                $minutes = $totalMinutes % 60;
            } catch (\Exception $e) {
                $this->addError('startTime', 'Invalid time format. Please use HH:MM format.');
                return;
            }
        } else {
            // Use manual hours/minutes entry
            $hours = (int) $this->hours ?: 0;
            $minutes = (int) $this->minutes ?: 0;
            $totalMinutes = ($hours * 60) + $minutes;
        }

        if ($totalMinutes > 0) {
            // Create TaskNote with time tracking
            $taskNote = TaskNote::create([
                'task_id' => $this->selectedTaskId,
                'user_id' => Auth::id(),
                'content' => $this->note,
                'hours' => $hours > 0 ? $hours : null,
                'minutes' => $minutes > 0 ? $minutes : null,
                'total_minutes' => $totalMinutes,
                'created_at' => $this->entryDate . ' ' . now()->format('H:i:s'),
            ]);

            // Also create a TimeEntry for backwards compatibility
            TimeEntry::create([
                'task_id' => $this->selectedTaskId,
                'user_id' => Auth::id(),
                'entry_date' => $this->entryDate,
                'description' => 'Note: ' . $this->note,
                'duration_minutes' => $totalMinutes,
                'is_running' => false,
            ]);

            session()->flash('message', 'Note and time logged successfully!');
            $this->reset(['note', 'hours', 'minutes', 'startTime', 'endTime']);
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
