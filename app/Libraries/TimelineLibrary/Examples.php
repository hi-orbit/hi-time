<?php
/**
 * Timeline Library Usage Examples
 *
 * This file contains practical examples of how to use the Timeline Library
 * in different scenarios throughout your application.
 */

namespace App\Examples;

use App\Libraries\TimelineLibrary\Services\TimelineChart;
use App\Libraries\TimelineLibrary\Components\TimelineViewer;
use App\Libraries\TimelineLibrary\Components\TimeEntryEditor;
use Carbon\Carbon;
use App\Models\TaskNote;

class TimelineLibraryExamples
{
    /**
     * Example 1: Basic Timeline Chart Service Usage
     */
    public function basicTimelineExample()
    {
        // Get time entries from database
        $timeEntries = TaskNote::with(['task.project'])
            ->whereDate('entry_date', Carbon::today())
            ->whereNotNull('total_minutes')
            ->get();

        // Create timeline chart with custom config
        $timelineChart = new TimelineChart([
            'show_tooltips' => true,
            'show_legend' => true,
            'manual_entry_pattern' => 'striped',
            'running_entry_animation' => true,
            'layer_offset' => 30,  // More spacing between layers
        ]);

        // Generate timeline data
        $timelineData = $timelineChart->generateTimelineData($timeEntries, Carbon::today());

        return $timelineData;
    }

    /**
     * Example 2: Custom Colors for Specific Projects
     */
    public function customColorExample()
    {
        $timelineChart = new TimelineChart();

        // Set custom colors
        $timelineChart->setColors([
            'running' => '#ff6b6b',     // Red for running timers
            'general' => '#4ecdc4',     // Teal for general activities
            'manual' => '#ffe66d',      // Yellow for manual entries
        ]);

        $timeEntries = TaskNote::whereDate('entry_date', Carbon::today())->get();
        return $timelineChart->generateTimelineData($timeEntries, Carbon::today());
    }

    /**
     * Example 3: Livewire Component Usage in Controller
     */
    public function livewireComponentExample()
    {
        // This would be in a Livewire component class
        /*
        use App\Libraries\TimelineLibrary\Services\TimelineChart;

        class ProjectTimeline extends Component
        {
            public $projectId;
            public $timelineData = [];
            public $selectedDate;

            protected $listeners = [
                'timeEntryUpdated' => 'refreshTimeline',
                'timeEntryDeleted' => 'refreshTimeline',
            ];

            public function mount($projectId)
            {
                $this->projectId = $projectId;
                $this->selectedDate = Carbon::today();
                $this->loadTimeline();
            }

            public function loadTimeline()
            {
                $timeEntries = TaskNote::whereHas('task', function($query) {
                    $query->where('project_id', $this->projectId);
                })
                ->whereDate('entry_date', $this->selectedDate)
                ->with(['task.project', 'user'])
                ->get();

                $timelineChart = new TimelineChart([
                    'show_tooltips' => true,
                    'show_legend' => true,
                ]);

                $this->timelineData = $timelineChart->generateTimelineData(
                    $timeEntries,
                    $this->selectedDate
                );
            }

            public function updatedSelectedDate()
            {
                $this->loadTimeline();
            }

            public function refreshTimeline()
            {
                $this->loadTimeline();
            }

            public function render()
            {
                return view('livewire.project-timeline');
            }
        }
        */
    }

    /**
     * Example 4: Blade Template Usage
     */
    public function bladeTemplateExample()
    {
        /*
        <!-- In your blade template: resources/views/livewire/project-timeline.blade.php -->

        <div>
            <!-- Date Picker -->
            <div class="mb-4">
                <label for="date-picker">Select Date:</label>
                <input type="date"
                       id="date-picker"
                       wire:model.live="selectedDate"
                       class="border rounded px-3 py-2">
            </div>

            <!-- Timeline Chart -->
            @if(count($timelineData['entries'] ?? []) > 0)
                @include('timeline-library::timeline-chart', [
                    'timelineData' => $timelineData,
                    'date' => $selectedDate
                ])
            @else
                <div class="text-center py-8 text-gray-500">
                    No time entries for this date.
                </div>
            @endif

            <!-- Individual Entry Editors -->
            <div class="mt-6 space-y-4">
                <h3 class="text-lg font-medium">Time Entries</h3>
                @foreach($timeEntries as $entry)
                    @livewire('timeline-entry-editor', [
                        'timeEntry' => $entry,
                        'config' => [
                            'compact_mode' => true,
                            'show_delete_button' => true,
                            'custom_actions' => [
                                'duplicate' => [
                                    'label' => 'Duplicate',
                                    'color' => 'blue'
                                ]
                            ]
                        ]
                    ], key($entry->id))
                @endforeach
            </div>
        </div>
        */
    }

    /**
     * Example 5: Time Tracking Dashboard Integration
     */
    public function dashboardIntegrationExample()
    {
        /*
        <!-- In resources/views/livewire/time-tracking/index.blade.php -->

        <div class="space-y-6">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium mb-2">Today's Total</h3>
                    <p class="text-2xl font-bold text-indigo-600">
                        {{ $timelineData['total_hours'] ?? '0h 0m' }}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium mb-2">Entries</h3>
                    <p class="text-2xl font-bold text-green-600">
                        {{ count($timelineData['entries'] ?? []) }}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium mb-2">Projects</h3>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ count($timelineData['legend'] ?? []) }}
                    </p>
                </div>
            </div>

            <!-- Timeline Visualization -->
            @include('timeline-library::timeline-chart', [
                'timelineData' => $timelineData,
                'date' => $selectedDate
            ])

            <!-- Time Entry Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <!-- Your existing time entry form -->
            </div>

            <!-- Recent Entries with Inline Editing -->
            <div class="space-y-4">
                @foreach($recentEntries as $entry)
                    @livewire('timeline-entry-editor', [
                        'timeEntry' => $entry,
                        'config' => ['compact_mode' => false]
                    ], key($entry->id))
                @endforeach
            </div>
        </div>
        */
    }

    /**
     * Example 6: Weekly Timeline View
     */
    public function weeklyTimelineExample()
    {
        /*
        class WeeklyTimeline extends Component
        {
            public $weekStart;
            public $weeklyData = [];

            public function mount()
            {
                $this->weekStart = Carbon::today()->startOfWeek();
                $this->loadWeeklyData();
            }

            public function loadWeeklyData()
            {
                $timelineChart = new TimelineChart();

                for ($i = 0; $i < 7; $i++) {
                    $date = $this->weekStart->copy()->addDays($i);

                    $entries = TaskNote::whereDate('entry_date', $date)
                        ->with(['task.project'])
                        ->get();

                    $this->weeklyData[$date->format('Y-m-d')] = [
                        'date' => $date,
                        'timeline' => $timelineChart->generateTimelineData($entries, $date),
                    ];
                }
            }

            public function render()
            {
                return view('livewire.weekly-timeline');
            }
        }

        <!-- In the blade template -->
        <div class="space-y-6">
            @foreach($weeklyData as $dayData)
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b">
                        <h3 class="text-lg font-medium">
                            {{ $dayData['date']->format('l, F j, Y') }}
                        </h3>
                    </div>
                    <div class="p-6">
                        @include('timeline-library::timeline-chart', [
                            'timelineData' => $dayData['timeline'],
                            'date' => $dayData['date']
                        ])
                    </div>
                </div>
            @endforeach
        </div>
        */
    }

    /**
     * Example 7: API Integration for External Systems
     */
    public function apiIntegrationExample()
    {
        /*
        // In routes/api.php
        Route::get('/timeline/{date}', function($date) {
            $carbonDate = Carbon::parse($date);

            $timeEntries = TaskNote::whereDate('entry_date', $carbonDate)
                ->with(['task.project', 'user'])
                ->get();

            $timelineChart = new TimelineChart([
                'show_tooltips' => false,  // Not needed for API
                'show_legend' => true,
            ]);

            $timelineData = $timelineChart->generateTimelineData($timeEntries, $carbonDate);

            return response()->json([
                'date' => $carbonDate->format('Y-m-d'),
                'timeline' => $timelineData,
                'total_entries' => count($timelineData['entries']),
                'total_duration' => $timelineData['total_hours'],
            ]);
        });
        */
    }

    /**
     * Example 8: Custom Event Handling for Time Entry Editor
     */
    public function customEventHandlingExample()
    {
        /*
        class TimeTrackingPage extends Component
        {
            protected $listeners = [
                'timeEntryUpdated' => 'handleEntryUpdate',
                'timeEntryDeleted' => 'handleEntryDelete',
                'customActionExecuted' => 'handleCustomAction',
            ];

            public function handleEntryUpdate($data)
            {
                // Refresh timeline data
                $this->loadTimelineData();

                // Show success message
                $this->dispatch('success', 'Time entry updated successfully!');

                // Log the action
                Log::info('Time entry updated', ['entry_id' => $data['id']]);
            }

            public function handleEntryDelete($data)
            {
                // Refresh timeline data
                $this->loadTimelineData();

                // Show success message
                $this->dispatch('success', 'Time entry deleted successfully!');
            }

            public function handleCustomAction($data)
            {
                $action = $data['action'];
                $entryId = $data['entry_id'];

                switch ($action) {
                    case 'duplicate':
                        $this->duplicateEntry($entryId);
                        break;
                    case 'archive':
                        $this->archiveEntry($entryId);
                        break;
                    default:
                        $this->dispatch('error', 'Unknown action: ' . $action);
                }
            }

            private function duplicateEntry($entryId)
            {
                $original = TaskNote::find($entryId);
                if ($original) {
                    $duplicate = $original->replicate();
                    $duplicate->created_at = now();
                    $duplicate->save();

                    $this->loadTimelineData();
                    $this->dispatch('success', 'Entry duplicated successfully!');
                }
            }
        }
        */
    }
}
