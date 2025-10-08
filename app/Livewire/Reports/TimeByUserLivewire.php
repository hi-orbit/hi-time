<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\TaskNote;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TimeByUserLivewire extends Component
{
    public $startDate;
    public $endDate;
    public $userTimeData = [];

    // Inline editing properties
    public $editingTimeEntry = null;
    public $editDuration;
    public $editStartTime;
    public $editEndTime;
    public $editDescription;

    protected $listeners = ['timeEntryUpdated' => 'loadTimeData', 'timeEntryDeleted' => 'loadTimeData'];

    public function mount($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate ?: Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = $endDate ?: Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->loadTimeData();
    }

    public function updatedStartDate()
    {
        $this->loadTimeData();
    }

    public function updatedEndDate()
    {
        $this->loadTimeData();
    }

    public function loadTimeData()
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        $timeEntries = TaskNote::select([
                'task_notes.*',
                'tasks.title as task_title',
                'projects.name as project_name',
                'projects.customer_id',
                'customers.name as customer_name',
                'users.name as user_name'
            ])
            ->whereNotNull('task_notes.total_minutes') // Only get entries with time logged
            ->leftJoin('tasks', 'task_notes.task_id', '=', 'tasks.id')
            ->leftJoin('projects', 'tasks.project_id', '=', 'projects.id')
            ->leftJoin('customers', 'projects.customer_id', '=', 'customers.id')
            ->join('users', 'task_notes.user_id', '=', 'users.id')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('task_notes.entry_date', [$startDate, $endDate])
                      ->orWhere(function($subQuery) use ($startDate, $endDate) {
                          $subQuery->whereNull('task_notes.entry_date')
                                   ->whereBetween('task_notes.created_at', [$startDate, $endDate]);
                      });
            })
            ->orderBy('users.name')
            ->orderBy('projects.name')
            ->get();

        // Group by user
        $userData = [];
        $totalHours = 0;

        foreach ($timeEntries as $entry) {
            $userName = $entry->user_name;

            // Check if this is a general activity (no task_id)
            if (is_null($entry->task_id)) {
                $customerName = 'General Activities';
                $projectName = 'General Activities';

                // Create a descriptive activity description using activity_type and content
                $activityType = $entry->activity_type ?? 'General Activity';
                $content = $entry->content ?? $entry->description ?? '';

                if (!empty($content)) {
                    $activityDescription = $activityType . ': ' . $content;
                } else {
                    $activityDescription = $activityType;
                }

                $entryType = 'General Activity';
            } else {
                $customerName = $entry->customer_name ?? 'No Customer';
                $projectName = $entry->project_name ?? 'Unknown Project';
                $activityDescription = $entry->task_title ?? 'Unknown Activity';
                $entryType = 'Task Work';
            }

            // Calculate hours from total_minutes (primary) or duration_minutes (fallback)
            $minutes = $entry->total_minutes ?? $entry->duration_minutes ?? 0;
            $hours = $minutes / 60;

            // Add computed fields for display
            $entry->activity_description = $activityDescription;
            $entry->entry_type = $entryType;
            $entry->calculated_hours = $hours;

            if (!isset($userData[$userName])) {
                $userData[$userName] = [
                    'user_name' => $userName,
                    'total_hours' => 0,
                    'customers' => [],
                    'entries' => []
                ];
            }

            if (!isset($userData[$userName]['customers'][$customerName])) {
                $userData[$userName]['customers'][$customerName] = [
                    'customer_name' => $customerName,
                    'hours' => 0,
                    'projects' => []
                ];
            }

            if (!isset($userData[$userName]['customers'][$customerName]['projects'][$projectName])) {
                $userData[$userName]['customers'][$customerName]['projects'][$projectName] = [
                    'project_name' => $projectName,
                    'hours' => 0,
                    'entries' => []
                ];
            }

            $userData[$userName]['total_hours'] += $hours;
            $userData[$userName]['customers'][$customerName]['hours'] += $hours;
            $userData[$userName]['customers'][$customerName]['projects'][$projectName]['hours'] += $hours;
            $userData[$userName]['customers'][$customerName]['projects'][$projectName]['entries'][] = $entry;
            $userData[$userName]['entries'][] = $entry;
            $totalHours += $hours;
        }

        $this->userTimeData = [
            'users' => $userData,
            'total_hours' => $totalHours
        ];
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
        $this->loadTimeData();

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
        $this->loadTimeData();

        session()->flash('message', 'Time entry deleted successfully.');
    }

    public function cancelEdit()
    {
        $this->editingTimeEntry = null;
        $this->editDuration = null;
        $this->editStartTime = null;
        $this->editEndTime = null;
        $this->editDescription = null;
    }

    public function render()
    {
        return view('livewire.reports.time-by-user-livewire');
    }
}
