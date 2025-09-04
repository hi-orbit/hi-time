<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\TaskNote;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MyTimeToday extends Component
{
    public $totalHours = 0;
    public $totalMinutes = 0;
    public $timeEntries = [];
    public $selectedDate;
    public $chartData = [];

    protected $listeners = ['timeEntryUpdated' => 'loadTimeEntries', 'timeEntryDeleted' => 'loadTimeEntries'];

    public function mount()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->loadTimeEntries();
    }

    public function updatedSelectedDate()
    {
        $this->loadTimeEntries();
        $this->generateChartData();
    }

    public function loadTimeEntries()
    {
        $date = Carbon::parse($this->selectedDate);

        $this->timeEntries = TaskNote::with(['task.project', 'user'])
            ->whereNotNull('total_minutes') // Only get entries with time logged
            ->where('user_id', Auth::id())
            ->where(function($query) use ($date) {
                $query->whereDate('entry_date', $date)
                      ->orWhere(function($subQuery) use ($date) {
                          $subQuery->whereNull('entry_date')
                                   ->whereDate('created_at', $date);
                      });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate total time using total_minutes (primary) or duration_minutes (fallback)
        $totalMinutes = collect($this->timeEntries)->sum(function($entry) {
            return $entry->total_minutes ?? $entry->duration_minutes ?? 0;
        });

        $this->totalHours = intval($totalMinutes / 60);
        $this->totalMinutes = $totalMinutes % 60;

        $this->generateChartData();
    }

    public function generateChartData()
    {
        $date = Carbon::parse($this->selectedDate);

        // Get all time entries for the selected date
        $entries = TaskNote::where('user_id', Auth::id())
            ->whereNotNull('total_minutes')
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
                $entryData['start_time'] = Carbon::parse($entry->start_time);
                $entryData['end_time'] = Carbon::parse($entry->end_time);
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

    public function deleteTimeEntry($entryId)
    {
        $entry = TaskNote::find($entryId);

        if ($entry && $entry->user_id === Auth::id()) {
            $entry->delete();
            $this->loadTimeEntries();
            session()->flash('message', 'Time entry deleted successfully.');
        } else {
            session()->flash('error', 'You can only delete your own time entries.');
        }
    }

    public function render()
    {
        return view('livewire.reports.my-time-today');
    }
}
