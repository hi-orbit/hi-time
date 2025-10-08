<?php

namespace App\Livewire\TimeTracking;

use Livewire\Component;
use App\Models\Task;
use App\Models\TaskNote;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use App\Libraries\TimelineLibrary\Services\TimelineChart;
use App\Traits\GeneratesTimelineData;

class Index extends Component
{
    use GeneratesTimelineData;
    public $selectedProjectId = '';
    public $selectedTaskId = '';
    public $description = '';
    public $note = '';
    public $hours = '';
    public $minutes = '';
    public $startTime = '';
    public $endTime = '';
    public $entryDate;

    // Chart data
    public $chartData = [];
    public $timelineData = [];

    // Timeline Library integration
    public $selectedDate;
    public $timeEntries = [];

    // Edit properties for inline editing
    public $editingTimeEntry = null;
    public $editingEntryId = null;
    public $editEntryDate;
    public $editStartTime;
    public $editEndTime;
    public $editDescription;
    public $editDuration;

    protected $rules = [
        'selectedTaskId' => 'required',
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
        $this->selectedDate = now()->format('Y-m-d');

        // Generate timeline data for today
        $this->generateTimelineData();

        if (request()->has('task')) {
            $this->selectedTaskId = request('task');
        }
    }

    public function updatedSelectedProjectId($value)
    {
        // Reset task selection when project changes
        $this->selectedTaskId = '';
    }

    public function updatedEntryDate()
    {
        // Update selected date to match entry date
        $this->selectedDate = $this->entryDate;
        // Regenerate timeline data when date changes
        $this->generateTimelineData();
    }

    public function updatedSelectedDate()
    {
        // Update entry date to match selected date
        $this->entryDate = $this->selectedDate;
        // Regenerate timeline data when date changes
        $this->generateTimelineData();
    }

    // Method to clear time fields when manual entry is used
    public function updatedHours()
    {
        if ($this->hours || $this->minutes) {
            $this->startTime = '';
            $this->endTime = '';
        }
    }

    public function updatedMinutes()
    {
        if ($this->hours || $this->minutes) {
            $this->startTime = '';
            $this->endTime = '';
        }
    }

    // Method to clear manual entry when start/end times are used
    public function updatedStartTime()
    {
        if ($this->startTime) {
            $this->hours = '';
            $this->minutes = '';
        }
    }

    public function updatedEndTime()
    {
        if ($this->endTime) {
            $this->hours = '';
            $this->minutes = '';
        }
    }

    protected function validateTaskSelection()
    {
        if (!$this->selectedTaskId) {
            throw new \Exception('Please select a task or activity.');
        }

        // Check if it's a general activity
        if (str_starts_with($this->selectedTaskId, 'general_')) {
            return true; // Valid general activity
        }

        // Check if it's a valid task
        if (!Task::find($this->selectedTaskId)) {
            throw new \Exception('Invalid task selected.');
        }

        return true;
    }

    protected function getSelectedActivityInfo()
    {
        if (str_starts_with($this->selectedTaskId, 'general_')) {
            $index = (int) str_replace('general_', '', $this->selectedTaskId);
            $activities = $this->getStandardActivityTypes();

            if (isset($activities[$index])) {
                return [
                    'type' => 'general',
                    'activity_name' => $activities[$index],
                    'task_id' => null,
                ];
            }
        }

        $task = Task::find($this->selectedTaskId);
        if ($task) {
            return [
                'type' => 'task',
                'activity_name' => $task->title,
                'task_id' => $task->id,
            ];
        }

        return null;
    }

    public function startTimer()
    {
        try {
            $this->validateTaskSelection();
        } catch (\Exception $e) {
            $this->addError('selectedTaskId', $e->getMessage());
            return;
        }

        $activityInfo = $this->getSelectedActivityInfo();
        if (!$activityInfo) {
            $this->addError('selectedTaskId', 'Invalid selection.');
            return;
        }

        // Stop any running timers for this user
        TaskNote::where('user_id', Auth::id())
            ->where('is_running', true)
            ->update([
                'is_running' => false,
                'end_time' => now(),
            ]);

        // Create TaskNote data
        $taskNoteData = [
            'user_id' => Auth::id(),
            'content' => $this->description ?: 'Timer started',
            'description' => $this->description,
            'start_time' => now(),
            'is_running' => true,
            'entry_date' => now()->toDate(),
        ];

        if ($activityInfo['type'] === 'task') {
            $taskNoteData['task_id'] = $activityInfo['task_id'];
        } else {
            // For general activities, we need a project but no specific task
            if (!$this->selectedProjectId) {
                $this->addError('selectedTaskId', 'Please select a project for general activities.');
                return;
            }
            $taskNoteData['activity_type'] = $activityInfo['activity_name'];
            // We'll need to create a general task or handle this differently
            // For now, let's create it without a task_id but with activity_type
        }

        TaskNote::create($taskNoteData);

        session()->flash('message', 'Timer started for: ' . $activityInfo['activity_name']);
        $this->reset(['description']);
        $this->generateTimelineData();
    }

    public function stopTimer($entryId)
    {
        $entry = TaskNote::where('id', $entryId)
            ->where('user_id', Auth::id())
            ->where('is_running', true)
            ->first();

        if ($entry) {
            $endTime = now();
            $durationMinutes = $entry->start_time->diffInMinutes($endTime);

            $entry->update([
                'is_running' => false,
                'end_time' => $endTime,
                'duration_minutes' => $durationMinutes,
                'total_minutes' => $durationMinutes,
                'hours' => floor($durationMinutes / 60),
                'minutes' => $durationMinutes % 60,
            ]);
            session()->flash('message', 'Timer stopped.');
            // Regenerate timeline data after stopping timer
            $this->generateTimelineData();
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
        try {
            $this->validateTaskSelection();
        } catch (\Exception $e) {
            $this->addError('selectedTaskId', $e->getMessage());
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
            if ($this->hours !== '' && (!is_numeric($this->hours) || $this->hours < 0 || $this->hours > 23)) {
                $this->addError('hours', 'Hours must be between 0 and 23.');
                return;
            }

            if ($this->minutes !== '' && (!is_numeric($this->minutes) || $this->minutes < 0 || $this->minutes > 59)) {
                $this->addError('minutes', 'Minutes must be between 0 and 59.');
                return;
            }

            // Require at least some time input (either hours or minutes)
            if ($this->hours === '' && $this->minutes === '') {
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
            $hours = ($this->hours !== '') ? (int) $this->hours : 0;
            $minutes = ($this->minutes !== '') ? (int) $this->minutes : 0;
            $totalMinutes = ($hours * 60) + $minutes;
        }

        if ($totalMinutes > 0) {
            $activityInfo = $this->getSelectedActivityInfo();
            if (!$activityInfo) {
                $this->addError('selectedTaskId', 'Invalid selection.');
                return;
            }

            // Create TaskNote with time tracking
            $taskNoteData = [
                'user_id' => Auth::id(),
                'content' => $this->note,
                'hours' => $hours > 0 ? $hours : null,
                'minutes' => $minutes > 0 ? $minutes : null,
                'total_minutes' => $totalMinutes,
                'entry_date' => \Carbon\Carbon::parse($this->entryDate),
                'created_at' => \Carbon\Carbon::parse($this->entryDate . ' ' . now()->format('H:i:s')),
            ];

            if ($activityInfo['type'] === 'task') {
                $taskNoteData['task_id'] = $activityInfo['task_id'];
            } else {
                // For general activities
                if (!$this->selectedProjectId) {
                    $this->addError('selectedTaskId', 'Please select a project for general activities.');
                    return;
                }
                $taskNoteData['activity_type'] = $activityInfo['activity_name'];
            }

            // Add start/end times if they were provided
            if ($this->startTime && $this->endTime) {
                $taskNoteData['start_time'] = \Carbon\Carbon::parse($this->entryDate . ' ' . $this->startTime);
                $taskNoteData['end_time'] = \Carbon\Carbon::parse($this->entryDate . ' ' . $this->endTime);
                $taskNoteData['duration_minutes'] = $totalMinutes;
            }

            TaskNote::create($taskNoteData);

            session()->flash('message', 'Note and time logged successfully!');
            $this->reset(['note', 'hours', 'minutes', 'startTime', 'endTime']);
            // Regenerate timeline data after adding new entry
            $this->generateTimelineData();
            // Keep the entry date as is, don't reset it
        }
    }

    public function generateTimelineData()
    {
        $date = \Carbon\Carbon::parse($this->selectedDate);

        // Use the consolidated timeline generation
        $result = $this->generateTimelineVisualization($date);
        $entries = $result['entries'];
        $this->timelineData = $result['timelineData'];

        // Generate legacy chart data for backward compatibility
        $this->chartData = $this->generateLegacyChartData($entries, $date);

        // Keep the Eloquent models for the time entries list
        $this->timeEntries = $entries;
    }

    public function getStandardActivityTypes()
    {
        return [
            'Client Meeting',
            'Project Planning',
            'Research',
            'Documentation',
            'Code Review',
            'Testing',
            'Deployment',
            'Training',
            'Administrative',
            'Travel Time',
            'Bug Investigation',
            'System Maintenance',
            'Client Communication',
            'Team Meeting',
            'Sales',
            'Time Entry',
            'Other'
        ];
    }

    public function render()
    {
        $runningEntries = TaskNote::where('user_id', Auth::id())
            ->where('is_running', true)
            ->with(['task.project'])
            ->get();

        $recentEntries = TaskNote::where('user_id', Auth::id())
            ->where('total_minutes', '>', 0)
            ->with(['task.project']) // Left join so general activities are included
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Get all active projects for the dropdown
        $projects = Project::where('status', 'active')
            ->orderBy('name')
            ->get();

        // Get tasks based on selected project (exclude completed tasks)
        $tasks = collect();
        if ($this->selectedProjectId) {
            $tasks = Task::where('project_id', $this->selectedProjectId)
                ->where('status', '!=', 'done') // Exclude completed tasks
                ->with('project')
                ->orderBy('title')
                ->get();
        }

        return view('livewire.time-tracking.index', [
            'runningEntries' => $runningEntries,
            'recentEntries' => $recentEntries,
            'projects' => $projects,
            'tasks' => $tasks,
            'chartData' => $this->chartData,
            'timelineData' => $this->timelineData,
            'timeEntries' => $this->timeEntries,
            'selectedDate' => $this->selectedDate,
            'generalActivities' => $this->getStandardActivityTypes(),
        ]);
    }

    // Inline Time Entry Editing Methods
    public function editTimeEntry($entryId)
    {
        $entry = TaskNote::findOrFail($entryId);

        // Check if user can edit this entry
        if ($entry->user_id !== Auth::id()) {
            session()->flash('error', 'You can only edit your own time entries.');
            return;
        }

        $this->editingTimeEntry = $entryId;
        $this->editDuration = $entry->total_minutes ?? $entry->duration_minutes ?? 0;
        $this->editStartTime = $entry->start_time ? \Carbon\Carbon::parse($entry->start_time)->format('H:i') : '';
        $this->editEndTime = $entry->end_time ? \Carbon\Carbon::parse($entry->end_time)->format('H:i') : '';
        $this->editDescription = $entry->description ?? $entry->content ?? '';
    }

    public function updatedEditStartTime()
    {
        $this->calculateDurationFromTimes();
    }

    public function updatedEditEndTime()
    {
        $this->calculateDurationFromTimes();
    }

    private function calculateDurationFromTimes()
    {
        if ($this->editStartTime && $this->editEndTime) {
            try {
                $startTime = \Carbon\Carbon::createFromFormat('H:i', $this->editStartTime);
                $endTime = \Carbon\Carbon::createFromFormat('H:i', $this->editEndTime);

                // Handle case where end time is next day (crosses midnight)
                if ($endTime->lessThan($startTime)) {
                    $endTime->addDay();
                }

                $diffInMinutes = $startTime->diffInMinutes($endTime);
                $this->editDuration = $diffInMinutes;
            } catch (\Exception $e) {
                // If parsing fails, don't update duration
            }
        }
    }

    public function saveTimeEntry($entryId)
    {
        $entry = TaskNote::findOrFail($entryId);

        // Check if user can edit this entry
        if ($entry->user_id !== Auth::id()) {
            session()->flash('error', 'You can only edit your own time entries.');
            return;
        }

        $this->validate([
            'editDuration' => 'required|numeric|min:0.01',
            'editDescription' => 'nullable|string|max:1000',
            'editStartTime' => 'nullable|date_format:H:i',
            'editEndTime' => 'nullable|date_format:H:i',
        ]);

        // Recalculate duration from start/end times if both are provided
        if ($this->editStartTime && $this->editEndTime) {
            $this->calculateDurationFromTimes();
        }

        // Update the entry
        $updateData = [
            'total_minutes' => (float) $this->editDuration,
            'duration_minutes' => (float) $this->editDuration,
            'hours' => floor((float) $this->editDuration / 60),
            'minutes' => (float) $this->editDuration % 60,
        ];

        if ($this->editDescription) {
            $updateData['description'] = $this->editDescription;
            $updateData['content'] = $this->editDescription;
        }

        if ($this->editStartTime && $this->editEndTime) {
            $date = $entry->entry_date ?? $entry->created_at->toDateString();

            // Parse the date and time more safely
            try {
                $updateData['start_time'] = \Carbon\Carbon::parse($date . ' ' . $this->editStartTime);
                $updateData['end_time'] = \Carbon\Carbon::parse($date . ' ' . $this->editEndTime);
            } catch (\Exception $e) {
                // Fallback: use current date if parsing fails
                $updateData['start_time'] = \Carbon\Carbon::today()->setTimeFromTimeString($this->editStartTime);
                $updateData['end_time'] = \Carbon\Carbon::today()->setTimeFromTimeString($this->editEndTime);
            }
        }

        $entry->update($updateData);

        $this->cancelEdit();
        $this->generateTimelineData();

        session()->flash('message', 'Time entry updated successfully.');
    }

    public function deleteTimeEntry($entryId)
    {
        $entry = TaskNote::findOrFail($entryId);

        // Check if user can delete this entry
        if ($entry->user_id !== Auth::id()) {
            session()->flash('error', 'You can only delete your own time entries.');
            return;
        }

        $entry->delete();
        $this->generateTimelineData();

        session()->flash('message', 'Time entry deleted successfully.');
    }

    public function cancelEdit()
    {
        $this->editingTimeEntry = null;
        $this->editDuration = '';
        $this->editStartTime = '';
        $this->editEndTime = '';
        $this->editDescription = '';
    }
}
