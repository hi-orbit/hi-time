<?php

namespace App\Livewire\TimeTracking;

use Livewire\Component;
use App\Models\Task;
use App\Models\TaskNote;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
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

        // Generate chart data for today
        $this->generateChartData();

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
        // Regenerate chart data when date changes
        $this->generateChartData();
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

    public function startTimer()
    {
        $this->validate(['selectedTaskId' => 'required|exists:tasks,id']);

        // Stop any running timers for this user
        TaskNote::where('user_id', Auth::id())
            ->where('is_running', true)
            ->update([
                'is_running' => false,
                'end_time' => now(),
            ]);

        // Start new timer by creating a TaskNote with timer fields
        TaskNote::create([
            'task_id' => $this->selectedTaskId,
            'user_id' => Auth::id(),
            'content' => $this->description ?: 'Timer started',
            'description' => $this->description,
            'start_time' => now(),
            'is_running' => true,
            'entry_date' => now()->toDate(),
        ]);

        $task = Task::find($this->selectedTaskId);
        session()->flash('message', 'Timer started for: ' . $task->title);
        $this->reset(['description']);
        // Regenerate chart data after starting timer
        $this->generateChartData();
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
            // Regenerate chart data after stopping timer
            $this->generateChartData();
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
            // Create TaskNote with time tracking
            $taskNoteData = [
                'task_id' => $this->selectedTaskId,
                'user_id' => Auth::id(),
                'content' => $this->note,
                'hours' => $hours > 0 ? $hours : null,
                'minutes' => $minutes > 0 ? $minutes : null,
                'total_minutes' => $totalMinutes,
                'entry_date' => \Carbon\Carbon::parse($this->entryDate),
                'created_at' => \Carbon\Carbon::parse($this->entryDate . ' ' . now()->format('H:i:s')),
            ];

            // Add start/end times if they were provided
            if ($this->startTime && $this->endTime) {
                $taskNoteData['start_time'] = \Carbon\Carbon::parse($this->entryDate . ' ' . $this->startTime);
                $taskNoteData['end_time'] = \Carbon\Carbon::parse($this->entryDate . ' ' . $this->endTime);
                $taskNoteData['duration_minutes'] = $totalMinutes;
            }

            TaskNote::create($taskNoteData);

            session()->flash('message', 'Note and time logged successfully!');
            $this->reset(['note', 'hours', 'minutes', 'startTime', 'endTime']);
            // Regenerate chart data after adding new entry
            $this->generateChartData();
            // Keep the entry date as is, don't reset it
        }
    }

    public function editEntry($entryId)
    {
        $entry = TaskNote::where('id', $entryId)
            ->whereNotNull('total_minutes')
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

        $entry = TaskNote::where('id', $this->editingEntryId)
            ->whereNotNull('total_minutes')
            ->where('user_id', Auth::id())
            ->first();

        if ($entry) {
            $updateData = [
                'entry_date' => \Carbon\Carbon::parse($this->editEntryDate),
                'description' => $this->editDescription,
            ];

            // Handle time updates
            if ($this->editStartTime) {
                $updateData['start_time'] = \Carbon\Carbon::parse($this->editEntryDate . ' ' . $this->editStartTime);
            }

            if ($this->editEndTime && $this->editStartTime) {
                $updateData['end_time'] = \Carbon\Carbon::parse($this->editEntryDate . ' ' . $this->editEndTime);

                // Calculate duration if both times are provided
                $start = \Carbon\Carbon::parse($updateData['start_time']);
                $end = \Carbon\Carbon::parse($updateData['end_time']);

                if ($end->greaterThan($start)) {
                    $durationMinutes = $start->diffInMinutes($end);
                    $updateData['duration_minutes'] = $durationMinutes;
                    $updateData['total_minutes'] = $durationMinutes;
                    $updateData['hours'] = floor($durationMinutes / 60);
                    $updateData['minutes'] = $durationMinutes % 60;
                    $updateData['is_running'] = false;
                } else {
                    // If end time is before start time, don't update duration
                    unset($updateData['end_time']);
                }
            }

            $entry->update($updateData);

            session()->flash('message', 'Time entry updated successfully!');
            $this->cancelEdit();
            // Regenerate chart data after updating entry
            $this->generateChartData();
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

    public function generateChartData()
    {
        $date = \Carbon\Carbon::parse($this->entryDate);

        // Get all time entries for the selected date with total_minutes > 0
        $entries = TaskNote::where('user_id', Auth::id())
            ->where('total_minutes', '>', 0)
            ->where(function($query) use ($date) {
                $query->whereDate('entry_date', $date)
                      ->orWhere(function($subQuery) use ($date) {
                          $subQuery->whereNull('entry_date')
                                   ->whereDate('created_at', $date);
                      });
            })
            ->with(['task.project'])
            ->orderBy('created_at')
            ->get();

        $this->chartData = [];
        $colors = [
            '#8B5CF6', '#06B6D4', '#10B981', '#F59E0B',
            '#EF4444', '#8B5A2B', '#6366F1', '#EC4899',
            '#14B8A6', '#F97316', '#84CC16', '#6B7280'
        ];
        $colorIndex = 0;
        $taskColors = [];

        // Group overlapping entries into layers
        $layers = [];
        $currentStackPosition = 0; // For entries without specific times

        foreach ($entries as $entry) {
            $entryData = [
                'id' => $entry->id,
                'task' => $entry->task->title,
                'project' => $entry->task->project->name,
                'duration_minutes' => $entry->total_minutes ?? $entry->duration_minutes ?? 0,
                'description' => $entry->description ?? $entry->content ?? '',
            ];

            // Handle entries with specific start/end times
            if ($entry->start_time && $entry->end_time) {
                $entryData['start_time'] = \Carbon\Carbon::parse($entry->start_time);
                $entryData['end_time'] = \Carbon\Carbon::parse($entry->end_time);
            } else {
                // For manual entries without specific times, create approximate times
                // Stack them in 1-hour blocks starting from 9 AM
                $baseHour = 9 + $currentStackPosition;
                $startTime = $date->copy()->setHour($baseHour)->setMinute(0);
                $endTime = $startTime->copy()->addMinutes($entryData['duration_minutes']);

                $entryData['start_time'] = $startTime;
                $entryData['end_time'] = $endTime;
                $entryData['is_manual'] = true; // Flag to indicate this is a manual entry

                $currentStackPosition++;
            }

            // Assign color based on task
            $taskKey = $entry->task->id;
            if (!isset($taskColors[$taskKey])) {
                $taskColors[$taskKey] = $colors[$colorIndex % count($colors)];
                $colorIndex++;
            }
            $entryData['color'] = $taskColors[$taskKey];

            // Find appropriate layer (avoid overlaps)
            $placed = false;
            for ($layerIndex = 0; $layerIndex < count($layers); $layerIndex++) {
                $canPlace = true;
                foreach ($layers[$layerIndex] as $existingEntry) {
                    // Check for time overlap
                    if ($entryData['start_time']->lt($existingEntry['end_time']) &&
                        $entryData['end_time']->gt($existingEntry['start_time'])) {
                        $canPlace = false;
                        break;
                    }
                }
                if ($canPlace) {
                    $layers[$layerIndex][] = $entryData;
                    $entryData['layer'] = $layerIndex;
                    $placed = true;
                    break;
                }
            }

            // If no existing layer works, create new layer
            if (!$placed) {
                $layers[] = [$entryData];
                $entryData['layer'] = count($layers) - 1;
            }

            $this->chartData[] = $entryData;
        }
    }

    public function render()
    {
        $runningEntries = TaskNote::where('user_id', Auth::id())
            ->where('is_running', true)
            ->whereNotNull('total_minutes')
            ->with(['task.project'])
            ->get();

        $recentEntries = TaskNote::where('user_id', Auth::id())
            ->where('total_minutes', '>', 0)
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
            'chartData' => $this->chartData,
        ]);
    }
}
