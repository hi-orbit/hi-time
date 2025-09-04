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
            $customerName = $entry->customer_name ?? 'No Customer';
            $projectName = $entry->project_name ?? 'Unknown Project';

            // Calculate hours from total_minutes (primary) or duration_minutes (fallback)
            $minutes = $entry->total_minutes ?? $entry->duration_minutes ?? 0;
            $hours = $minutes / 60;

            // Determine activity description for display
            $activityDescription = $entry->task_title ?? 'Unknown Activity';

            // Add computed fields for display
            $entry->activity_description = $activityDescription;
            $entry->entry_type = 'Task Work';
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

    public function render()
    {
        return view('livewire.reports.time-by-user-livewire');
    }
}
