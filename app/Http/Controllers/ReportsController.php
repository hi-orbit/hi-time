<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Models\TaskNote;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Display the main reports dashboard
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * My Time Today report
     */
    public function myTimeToday()
    {
        return view('reports.my-time-today-wrapper');
    }

    /**
     * Time by customer - current month
     */
    public function timeByCustomerThisMonth()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $customerTimeData = $this->getCustomerTimeData($startOfMonth, $endOfMonth);

        return view('reports.time-by-customer-this-month', [
            'customerTimeData' => $customerTimeData,
            'startDate' => $startOfMonth,
            'endDate' => $endOfMonth,
            'periodName' => 'This Month'
        ]);
    }

    /**
     * Time by customer - last month
     */
    public function timeByCustomerLastMonth()
    {
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        $customerTimeData = $this->getCustomerTimeData($startOfLastMonth, $endOfLastMonth);

        return view('reports.time-by-customer-last-month', [
            'customerTimeData' => $customerTimeData,
            'startDate' => $startOfLastMonth,
            'endDate' => $endOfLastMonth,
            'periodName' => 'Last Month'
        ]);
    }

    /**
     * Time by user
     */
    public function timeByUser(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $userTimeData = $this->getUserTimeData($startDate, $endDate);

        return view('reports.time-by-user', [
            'userTimeData' => $userTimeData,
            'startDate' => Carbon::parse($startDate),
            'endDate' => Carbon::parse($endDate),
            'selectedStartDate' => $startDate,
            'selectedEndDate' => $endDate
        ]);
    }

    /**
     * Get time data grouped by customer for a date range
     */
    private function getCustomerTimeData($startDate, $endDate)
    {
        // Get task notes with time logged and related data
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
            ->orderBy('customers.name')
            ->orderBy('projects.name')
            ->get();

        // Group by customer
        $customerData = [];
        $totalHours = 0;

        foreach ($timeEntries as $entry) {
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
            $entry->decimal_hours = $hours; // For compatibility

            if (!isset($customerData[$customerName])) {
                $customerData[$customerName] = [
                    'customer_name' => $customerName,
                    'projects' => [],
                    'total_hours' => 0,
                    'total_task_hours' => 0,
                    'total_general_hours' => 0
                ];
            }

            if (!isset($customerData[$customerName]['projects'][$projectName])) {
                $customerData[$customerName]['projects'][$projectName] = [
                    'project_name' => $projectName,
                    'total_hours' => 0,
                    'total_task_hours' => 0,
                    'total_general_hours' => 0,
                    'entries' => []
                ];
            }

            // Track totals by entry type
            $customerData[$customerName]['total_hours'] += $hours;
            $customerData[$customerName]['projects'][$projectName]['total_hours'] += $hours;
            $customerData[$customerName]['projects'][$projectName]['entries'][] = $entry;

            // Track by entry type
            if ($entryType === 'General Activity') {
                $customerData[$customerName]['total_general_hours'] += $hours;
                $customerData[$customerName]['projects'][$projectName]['total_general_hours'] += $hours;
            } else {
                $customerData[$customerName]['total_task_hours'] += $hours;
                $customerData[$customerName]['projects'][$projectName]['total_task_hours'] += $hours;
            }

            $totalHours += $hours;
        }

        return [
            'customers' => $customerData,
            'total_hours' => $totalHours
        ];
    }

    /**
     * Get time data grouped by user for a date range
     */
    private function getUserTimeData($startDate, $endDate)
    {
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
            $entry->decimal_hours = $hours; // For compatibility

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

        return [
            'users' => $userData,
            'total_hours' => $totalHours
        ];
    }
}
