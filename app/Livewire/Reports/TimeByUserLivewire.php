<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\TimeEntry;
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

        $timeEntries = TimeEntry::select([
                'time_entries.*',
                'tasks.title as task_title',
                'projects.name as project_name',
                'projects.customer_id',
                'customers.name as customer_name',
                'users.name as user_name'
            ])
            ->leftJoin('tasks', 'time_entries.task_id', '=', 'tasks.id')
            ->leftJoin('projects', function($join) {
                $join->on('tasks.project_id', '=', 'projects.id')
                     ->orOn('time_entries.project_id', '=', 'projects.id');
            })
            ->leftJoin('customers', 'projects.customer_id', '=', 'customers.id')
            ->join('users', 'time_entries.user_id', '=', 'users.id')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('time_entries.entry_date', [$startDate, $endDate])
                      ->orWhere(function($subQuery) use ($startDate, $endDate) {
                          $subQuery->whereNull('time_entries.entry_date')
                                   ->whereBetween('time_entries.created_at', [$startDate, $endDate]);
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

            // Calculate hours from duration_minutes
            $minutes = $entry->duration ?? $entry->duration_minutes ?? 0;
            $hours = $minutes / 60;

            // Determine activity description for display
            $activityDescription = $entry->activity_type ?? $entry->task_title ?? 'Unknown Activity';

            // Add computed fields for display
            $entry->activity_description = $activityDescription;
            $entry->entry_type = $entry->activity_type ? 'General Activity' : 'Task Work';
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
