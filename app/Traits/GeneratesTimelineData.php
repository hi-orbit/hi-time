<?php

namespace App\Traits;

use App\Models\TaskNote;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Libraries\TimelineLibrary\Services\TimelineChart;

trait GeneratesTimelineData
{
    /**
     * Generate timeline visualization data for a given date
     * This is the single consolidated method used across all components
     */
    public function generateTimelineVisualization(Carbon $date): array
    {
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

        // Generate timeline data using the unified Timeline Library
        $timelineChart = new TimelineChart([
            'show_tooltips' => true,
            'show_legend' => true,
            'layer_offset' => 40,
        ]);

        return [
            'entries' => $entries,
            'timelineData' => $timelineChart->generateTimelineData($entries, $date)
        ];
    }

    /**
     * Generate legacy chart data for backward compatibility
     * This maintains compatibility with existing features that still need chart data format
     */
    protected function generateLegacyChartData($entries, $date): array
    {
        $colors = [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
            '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#14B8A6'
        ];
        $colorIndex = 0;
        $taskColors = [];
        $tempChartData = [];

        // First pass: process all entries and create temporary chart data
        foreach ($entries as $entry) {
            $startTime = null;
            $endTime = null;
            $isRunning = false;

            if ($entry->start_time && $entry->end_time) {
                $startTime = \Carbon\Carbon::parse($entry->start_time);
                $endTime = \Carbon\Carbon::parse($entry->end_time);
            } elseif ($entry->start_time && !$entry->end_time) {
                $startTime = \Carbon\Carbon::parse($entry->start_time);
                $endTime = now();
                $isRunning = true;
            } else {
                // Manual entry - position based on created time
                $startTime = $entry->created_at;
                $duration = $entry->total_minutes ?? $entry->duration_minutes ?? 60;
                $endTime = $startTime->copy()->addMinutes($duration);
            }

            // Determine task/activity info
            $taskTitle = 'General Activity';
            $projectName = 'General';
            $taskId = null;

            if ($entry->task) {
                $taskTitle = $entry->task->title;
                $projectName = $entry->task->project->name ?? 'No Project';
                $taskId = $entry->task->id;
            } elseif ($entry->activity_type) {
                $taskTitle = $entry->activity_type;
                $projectName = 'General Activity';
            }

            // Get consistent color for this task/activity
            $colorKey = $taskId ?? $taskTitle;
            if (!isset($taskColors[$colorKey])) {
                $taskColors[$colorKey] = $colors[$colorIndex % count($colors)];
                $colorIndex++;
            }

            // Calculate positions (percentage of day)
            $startPercent = ($startTime->hour + $startTime->minute / 60) / 24 * 100;
            $endPercent = ($endTime->hour + $endTime->minute / 60) / 24 * 100;

            // Ensure minimum width for visibility
            if ($endPercent - $startPercent < 0.5) {
                $endPercent = $startPercent + 0.5;
            }

            $tempChartData[] = [
                'title' => $taskTitle,
                'project' => $projectName,
                'start' => $startPercent,
                'end' => $endPercent,
                'width' => $endPercent - $startPercent,
                'color' => $taskColors[$colorKey],
                'startTime' => $startTime->format('H:i'),
                'endTime' => $endTime->format('H:i'),
                'duration' => $entry->total_minutes ?? $entry->duration_minutes ?? $startTime->diffInMinutes($endTime),
                'isRunning' => $isRunning,
                'isManual' => !($entry->start_time && $entry->end_time) && !$isRunning,
                'description' => $entry->description ?? $entry->content ?? '',
                'row' => 0, // Will be calculated in next step
            ];
        }

        // Second pass: calculate row positions for overlapping entries
        return $this->calculateStackedRows($tempChartData);
    }

    /**
     * Calculate stacked row positions for overlapping entries
     */
    private function calculateStackedRows($entries): array
    {
        if (empty($entries)) {
            return [];
        }

        // Sort entries by start time, then by duration (longer entries first)
        usort($entries, function($a, $b) {
            $startCompare = $a['start'] <=> $b['start'];
            if ($startCompare !== 0) {
                return $startCompare;
            }
            // If start times are equal, put longer entries first
            return $b['width'] <=> $a['width'];
        });

        $rows = [];

        foreach ($entries as $entry) {
            $placed = false;
            $rowIndex = 0;

            // Try to place in existing rows
            while (!$placed) {
                if (!isset($rows[$rowIndex])) {
                    $rows[$rowIndex] = [];
                }

                $canPlace = true;
                foreach ($rows[$rowIndex] as $existingEntry) {
                    // Check for overlap
                    if ($this->entriesOverlap($entry, $existingEntry)) {
                        $canPlace = false;
                        break;
                    }
                }

                if ($canPlace) {
                    $entry['row'] = $rowIndex;
                    $rows[$rowIndex][] = $entry;
                    $placed = true;
                } else {
                    $rowIndex++;
                }
            }
        }

        // Flatten the rows back into a single array
        $flatEntries = [];
        foreach ($rows as $row) {
            foreach ($row as $entry) {
                $flatEntries[] = $entry;
            }
        }

        return $flatEntries;
    }

    /**
     * Check if two entries overlap in time
     */
    private function entriesOverlap($entry1, $entry2): bool
    {
        $start1 = $entry1['start'];
        $end1 = $entry1['end'];
        $start2 = $entry2['start'];
        $end2 = $entry2['end'];

        // Check if entries overlap
        return max($start1, $start2) < min($end1, $end2);
    }
}
