<?php

namespace App\Libraries\TimelineLibrary\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TimelineChart
{
    protected array $config;
    protected array $colors;
    protected int $hoursInDay = 24;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'show_tooltips' => true,
            'show_legend' => true,
            'hours_in_day' => 24,
            'time_format' => 'H:i',
            'default_entry_height' => 20,
            'layer_offset' => 25,
            'max_layers' => 10,
            'manual_entry_pattern' => 'striped',
            'running_entry_animation' => true,
        ], $config);

        $this->hoursInDay = $this->config['hours_in_day'];
        $this->initializeColors();
    }

    protected function initializeColors(): void
    {
        $this->colors = [
            'default' => '#6366f1',
            'general' => '#10b981',
            'running' => '#ef4444',
            'manual' => '#f59e0b',
            'background' => '#f3f4f6',
            'text' => '#374151',
            'border' => '#d1d5db',
        ];
    }

    public function generateTimelineData(Collection $timeEntries, ?Carbon $date = null): array
    {
        $date = $date ?? now();
        $processedEntries = $this->processTimeEntries($timeEntries, $date);
        $layers = $this->calculateLayers($processedEntries); // This modifies $processedEntries by reference
        $hourMarkers = $this->generateHourMarkers();
        $legend = $this->generateLegend($processedEntries);

        return [
            'entries' => $processedEntries, // Return the modified entries with row calculations
            'layers' => $layers,
            'hour_markers' => $hourMarkers,
            'legend' => $legend,
            'total_hours' => $this->calculateTotalHours($processedEntries),
            'config' => $this->config,
        ];
    }

    protected function processTimeEntries(Collection $timeEntries, Carbon $date): array
    {
        return $timeEntries->map(function ($entry) use ($date) {
            return $this->processTimeEntry($entry, $date);
        })->filter()->values()->toArray();
    }

    protected function processTimeEntry($entry, Carbon $date): ?array
    {
        // Get entry date or fallback to created_at
        $entryDate = $entry->entry_date ? Carbon::parse($entry->entry_date) : $entry->created_at;

        // Skip entries not from the target date
        if (!$entryDate->isSameDay($date)) {
            return null;
        }

        // Calculate duration and positioning
        $duration = $this->getEntryDuration($entry);
        if ($duration <= 0) {
            return null;
        }

        $positioning = $this->calculateEntryPositioning($entry, $duration, $date);
        if (!$positioning) {
            return null;
        }

        return [
            'id' => $entry->id,
            'title' => $this->getEntryTitle($entry),
            'description' => $this->getEntryDescription($entry),
            'duration' => $duration,
            'duration_formatted' => $this->formatDuration($duration),
            'color' => $this->getEntryColor($entry),
            'is_running' => $entry->is_running ?? false,
            'is_manual' => $this->isManualEntry($entry),
            'start_position' => $positioning['start_position'],
            'width' => $positioning['width'],
            'start_time' => $positioning['start_time'],
            'end_time' => $positioning['end_time'],
            'layer' => 0, // Will be calculated in calculateLayers
            'row' => 0, // Will be calculated in calculateStackedRows
            'entry' => $entry, // Keep reference for additional data
        ];
    }

    protected function getEntryDuration($entry): float
    {
        if ($entry->is_running ?? false) {
            // For running entries, calculate current elapsed time
            $startTime = $entry->start_time ?? $entry->created_at;
            return Carbon::parse($startTime)->diffInMinutes(now());
        }

        // Use total_minutes first, then duration_minutes as fallback
        return $entry->total_minutes ?? $entry->duration_minutes ?? 0;
    }

    protected function calculateEntryPositioning($entry, float $duration, Carbon $date): ?array
    {
        $startTime = null;
        $endTime = null;

        if ($entry->start_time && $entry->end_time) {
            // Timed entries
            $startTime = Carbon::parse($entry->start_time);
            $endTime = Carbon::parse($entry->end_time);
        } elseif ($entry->start_time && ($entry->is_running ?? false)) {
            // Running entries
            $startTime = Carbon::parse($entry->start_time);
            $endTime = now();
        } else {
            // Manual entries - position based on created time and duration
            $createdTime = $entry->created_at;
            $startTime = Carbon::parse($createdTime);
            $endTime = $startTime->copy()->addMinutes($duration);
        }

        // Ensure times are on the correct date
        if (!$startTime->isSameDay($date)) {
            $startTime = $date->copy()->setTimeFrom($startTime);
        }
        if (!$endTime->isSameDay($date)) {
            $endTime = $date->copy()->setTimeFrom($endTime);
        }

        // Calculate positions as percentages of the day
        $startPosition = $this->timeToPercentage($startTime);
        $endPosition = $this->timeToPercentage($endTime);
        $width = max(0.5, $endPosition - $startPosition); // Minimum width for visibility

        return [
            'start_position' => $startPosition,
            'width' => $width,
            'start_time' => $startTime->format($this->config['time_format']),
            'end_time' => $endTime->format($this->config['time_format']),
        ];
    }

    protected function timeToPercentage(Carbon $time): float
    {
        $minutesFromMidnight = $time->hour * 60 + $time->minute;
        $totalMinutesInDay = $this->hoursInDay * 60;
        return ($minutesFromMidnight / $totalMinutesInDay) * 100;
    }

    protected function calculateLayers(array &$entries): array
    {
        // Use improved stacking algorithm from time tracking
        $this->calculateStackedRows($entries);

        $layers = [];
        $maxLayer = 0;

        foreach ($entries as $index => &$entry) {
            $layer = $entry['row'] ?? 0; // Use the calculated row as layer, fallback to 0
            $entry['layer'] = $layer;

            if (!isset($layers[$layer])) {
                $layers[$layer] = [];
            }
            $layers[$layer][] = $entry;

            $maxLayer = max($maxLayer, $layer);
        }

        return [
            'layers' => $layers,
            'max_layer' => min($maxLayer, $this->config['max_layers'] - 1),
            'total_height' => ($maxLayer + 1) * $this->config['layer_offset'],
        ];
    }

    protected function calculateStackedRows(array &$entries): void
    {
        if (empty($entries)) {
            return;
        }

        // Sort entries by start time for consistent processing
        usort($entries, function($a, $b) {
            return $a['start_position'] <=> $b['start_position'];
        });

        // Assign rows based on overlaps
        foreach ($entries as $i => &$currentEntry) {
            $currentEntry['row'] = 0; // Start at row 0

            // Check all previous entries for overlaps
            for ($j = 0; $j < $i; $j++) {
                $previousEntry = $entries[$j];

                // Calculate overlap
                $currentStart = $currentEntry['start_position'];
                $currentEnd = $currentEntry['start_position'] + $currentEntry['width'];
                $previousStart = $previousEntry['start_position'];
                $previousEnd = $previousEntry['start_position'] + $previousEntry['width'];

                // Check if they overlap (current starts before previous ends AND current ends after previous starts)
                $overlaps = ($currentStart < $previousEnd) && ($currentEnd > $previousStart);

                if ($overlaps) {
                    // Move current entry to a row below the previous entry
                    $currentEntry['row'] = max($currentEntry['row'], $previousEntry['row'] + 1);
                }
            }
        }
    }

    protected function entriesOverlap(array $entry1, array $entry2): bool
    {
        $start1 = $entry1['startPercent'];
        $end1 = $entry1['endPercent'];
        $start2 = $entry2['startPercent'];
        $end2 = $entry2['endPercent'];

        // Two entries overlap if one starts before the other ends
        $overlaps = !($end1 <= $start2 || $end2 <= $start1);

        // Debug logging
        Log::debug("Timeline Overlap Check", [
            'entry1_title' => $entry1['title'] ?? 'Unknown',
            'entry1_start' => $start1,
            'entry1_end' => $end1,
            'entry2_title' => $entry2['title'] ?? 'Unknown',
            'entry2_start' => $start2,
            'entry2_end' => $end2,
            'overlaps' => $overlaps
        ]);

        return $overlaps;
    }

    protected function generateHourMarkers(): array
    {
        $markers = [];

        // Generate hour and half-hour markers
        for ($hour = 0; $hour < $this->hoursInDay; $hour++) {
            // Full hour marker
            $position = ($hour / $this->hoursInDay) * 100;
            $markers[] = [
                'hour' => $hour,
                'position' => $position,
                'label' => sprintf('%02d:00', $hour),
                'is_major' => true, // Hour markers are major
                'type' => 'hour'
            ];

            // Half-hour marker (except for the last hour to avoid going past 24:00)
            if ($hour < 23) {
                $halfHourPosition = (($hour + 0.5) / $this->hoursInDay) * 100;
                $markers[] = [
                    'hour' => $hour + 0.5,
                    'position' => $halfHourPosition,
                    'label' => sprintf('%02d:30', $hour),
                    'is_major' => false, // Half-hour markers are minor
                    'type' => 'half-hour'
                ];
            }
        }

        return $markers;
    }

    protected function generateLegend(array $entries): array
    {
        if (!$this->config['show_legend']) {
            return [];
        }

        $legend = [];
        $tasks = [];
        $activities = [];

        foreach ($entries as $entry) {
            $entryData = $entry['entry'];

            if ($entryData->task) {
                $key = $entryData->task->id;
                if (!isset($tasks[$key])) {
                    $tasks[$key] = [
                        'type' => 'task',
                        'title' => $entryData->task->title,
                        'subtitle' => $entryData->task->project->name ?? '',
                        'color' => $entry['color'],
                        'count' => 0,
                        'total_duration' => 0,
                    ];
                }
                $tasks[$key]['count']++;
                $tasks[$key]['total_duration'] += $entry['duration'];
            } elseif ($entryData->activity_type) {
                $key = $entryData->activity_type;
                if (!isset($activities[$key])) {
                    $activities[$key] = [
                        'type' => 'activity',
                        'title' => $entryData->activity_type,
                        'subtitle' => $entryData->project->name ?? 'General Activity',
                        'color' => $entry['color'],
                        'count' => 0,
                        'total_duration' => 0,
                    ];
                }
                $activities[$key]['count']++;
                $activities[$key]['total_duration'] += $entry['duration'];
            }
        }

        return array_merge(array_values($tasks), array_values($activities));
    }

    protected function getEntryTitle($entry): string
    {
        if ($entry->task) {
            return $entry->task->title;
        } elseif ($entry->activity_type) {
            return $entry->activity_type;
        }
        return 'General Time Entry';
    }

    protected function getEntryDescription($entry): string
    {
        $description = $entry->description ?? $entry->content ?? '';

        if ($entry->task && $entry->task->project) {
            $description = $entry->task->project->name . ($description ? " - {$description}" : '');
        } elseif ($entry->project) {
            $description = $entry->project->name . ($description ? " - {$description}" : '');
        }

        return $description ?: 'No description';
    }

    protected function getEntryColor($entry): string
    {
        if ($entry->is_running ?? false) {
            return $this->colors['running'];
        }

        if ($this->isManualEntry($entry)) {
            return $this->colors['manual'];
        }

        if ($entry->task && $entry->task->project) {
            // Generate consistent color based on project ID
            $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#84cc16'];
            return $colors[$entry->task->project->id % count($colors)];
        }

        if ($entry->activity_type) {
            return $this->colors['general'];
        }

        return $this->colors['default'];
    }

    protected function isManualEntry($entry): bool
    {
        return !($entry->start_time && $entry->end_time) && !($entry->is_running ?? false);
    }

    protected function formatDuration(float $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $mins);
        }
        return sprintf('%dm', $mins);
    }

    protected function calculateTotalHours(array $entries): string
    {
        $totalMinutes = array_sum(array_column($entries, 'duration'));
        return $this->formatDuration($totalMinutes);
    }

    public function setConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    public function setColors(array $colors): self
    {
        $this->colors = array_merge($this->colors, $colors);
        return $this;
    }

    public function generateTimelineFromChartData(array $chartData = null, ?Carbon $date = null): array
    {
        $date = $date ?? now();

        // Handle null or empty chart data
        if (empty($chartData)) {
            return [
                'entries' => [],
                'layers' => [
                    'layers' => [],
                    'max_layer' => 0,
                    'total_height' => 60,
                ],
                'hour_markers' => $this->generateHourMarkers(),
                'legend' => [],
                'total_hours' => $this->formatDuration(0),
                'config' => $this->config,
            ];
        }

        // Convert chartData format to timeline entries
        $processedEntries = [];
        foreach ($chartData as $entry) {
            $processedEntries[] = [
                'id' => $entry['id'] ?? null,
                'title' => $entry['title'],
                'description' => $entry['description'] ?? $entry['project'] ?? '',
                'duration' => $entry['duration'],
                'duration_formatted' => $this->formatDuration($entry['duration']),
                'color' => $entry['color'],
                'is_running' => $entry['isRunning'] ?? false,
                'is_manual' => $entry['isManual'] ?? false,
                'start_position' => $entry['start'],
                'width' => $entry['width'],
                'start_time' => $entry['startTime'],
                'end_time' => $entry['endTime'],
                'layer' => 0, // Will be calculated
                'row' => $entry['row'] ?? 0,
                'entry' => (object) $entry, // Convert to object for compatibility
            ];
        }

        $layers = $this->calculateLayers($processedEntries);
        $hourMarkers = $this->generateHourMarkers();
        $legend = $this->generateLegendFromChartData($processedEntries);

        return [
            'entries' => $processedEntries,
            'layers' => $layers,
            'hour_markers' => $hourMarkers,
            'legend' => $legend,
            'total_hours' => $this->calculateTotalHours($processedEntries),
            'config' => $this->config,
        ];
    }

    protected function generateLegendFromChartData(array $entries): array
    {
        if (!$this->config['show_legend']) {
            return [];
        }

        $legend = [];
        $taskGroups = [];

        foreach ($entries as $entry) {
            $key = $entry['title'];
            if (!isset($taskGroups[$key])) {
                $taskGroups[$key] = [
                    'title' => $entry['title'],
                    'subtitle' => $entry['description'],
                    'color' => $entry['color'],
                    'count' => 0,
                    'total_duration' => 0,
                ];
            }
            $taskGroups[$key]['count']++;
            $taskGroups[$key]['total_duration'] += $entry['duration'];
        }

        return array_values($taskGroups);
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getColors(): array
    {
        return $this->colors;
    }
}
