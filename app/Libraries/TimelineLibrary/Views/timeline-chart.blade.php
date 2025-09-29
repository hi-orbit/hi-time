{{-- Timeline Chart Component --}}
<div class="timeline-chart-container" data-config="{{ json_encode($timelineData['config']) }}">
    @if(isset($timelineData['entries']) && count($timelineData['entries']) > 0)
        {{-- Header with total hours --}}
        @if($timelineData['config']['show_legend'])
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Hours Logged on {{ $date->format('M j, Y') }}</h3>
                <span class="text-sm font-medium text-gray-600">Total: {{ $timelineData['total_hours'] }}</span>
            </div>
        @endif

        {{-- Timeline Container --}}
        <div class="timeline-chart bg-white border border-gray-200 rounded-lg p-4 mb-6">
            {{-- Hour markers --}}
            <div class="relative mb-2">
                <div class="flex text-xs text-gray-400 mb-2">
                    @for($i = 0; $i < 24; $i++)
                        <div class="flex-1 text-center">{{ sprintf('%02d:00', $i) }}</div>
                    @endfor
                </div>
            </div>

            {{-- Timeline entries with grid background --}}
            @php
                // Calculate the number of rows needed for stacking
                $maxRow = 0;
                foreach($timelineData['entries'] as $entry) {
                    $entryRow = $entry['row'] ?? 0;
                    $maxRow = max($maxRow, $entryRow);
                }
                $totalRows = $maxRow + 1;
                $rowHeight = $timelineData['config']['layer_offset'] ?? 40;
                $totalHeight = max(60, $totalRows * $rowHeight);
            @endphp

            <div class="relative timeline-entries bg-gray-100 border-2 border-gray-300 rounded-lg overflow-hidden"
                 style="height: {{ $totalHeight }}px; min-height: 48px;">

                {{-- Grid lines for hours and 30-minute marks --}}
                @for($hour = 0; $hour < 24; $hour++)
                    {{-- Hour lines --}}
                    <div class="absolute top-0 bottom-0 w-px bg-gray-400"
                         style="left: {{ ($hour / 24) * 100 }}%"></div>
                    {{-- 30-minute lines --}}
                    @if($hour < 23)
                        <div class="absolute top-0 bottom-0 w-px bg-gray-300"
                             style="left: {{ (($hour + 0.5) / 24) * 100 }}%"></div>
                    @endif
                @endfor

                {{-- Current time indicator - REMOVED (was causing red line bug) --}}
                {{-- @php
                    $now = now();
                @endphp
                @if(isset($date) && $date->isToday())
                    @php
                        $currentPercent = ($now->hour + $now->minute / 60) / 24 * 100;
                    @endphp
                    <div class="absolute top-0 bottom-0 w-1 bg-red-500 z-30"
                         style="left: {{ $currentPercent }}%"
                         title="Current time: {{ $now->format('H:i') }}"></div>
                @endif --}}

                @foreach($timelineData['entries'] as $entry)
                    @php
                        // Use row-based positioning like the main time tracking
                        $rowHeight = $timelineData['config']['layer_offset'] ?? 40;
                        $entryRow = $entry['row'] ?? 0;  // Safe fallback to 0
                        $entryTop = ($entryRow * $rowHeight) + 4; // 4px padding from top
                        $entryHeight = $rowHeight - 8; // 8px total padding (4px top + 4px bottom)
                    @endphp
                    <div class="absolute rounded cursor-pointer transition-all duration-200 hover:shadow-lg hover:z-40 {{ ($entry['is_manual'] ?? false) ? 'border-2 border-dashed border-white' : 'border border-white' }} {{ ($entry['is_running'] ?? false) ? 'animate-pulse' : '' }}"
                         style="left: {{ $entry['start_position'] }}%;
                                width: {{ $entry['width'] }}%;
                                top: {{ $entryTop }}px;
                                height: {{ $entryHeight }}px;
                                background-color: {{ $entry['color'] }};
                                z-index: {{ 20 + $loop->index }};"
                         title="{{ $entry['title'] }} - {{ $entry['description'] ?? '' }}&#10;{{ $entry['start_time'] }} - {{ $entry['end_time'] }}&#10;Duration: {{ $entry['duration_formatted'] }}{{ $entry['description'] ? '&#10;' . $entry['description'] : '' }}"
                         @if($timelineData['config']['show_tooltips'])
                         x-data="{ showTooltip: false }"
                         @mouseenter="showTooltip = true"
                         @mouseleave="showTooltip = false"
                         @endif
                         >

                        {{-- Entry content --}}
                        <div class="px-2 py-1 text-xs text-white font-medium truncate leading-tight h-full flex items-center">
                            {{ $entry['title'] }}
                        </div>

                        {{-- Manual entry pattern --}}
                        @if($entry['is_manual'] && $timelineData['config']['manual_entry_pattern'] === 'striped')
                            <div class="absolute inset-0 opacity-30"
                                 style="background-image: repeating-linear-gradient(45deg, transparent, transparent 2px, rgba(255,255,255,0.3) 2px, rgba(255,255,255,0.3) 4px);"></div>
                        @endif

                        {{-- Running entry animation --}}
                        @if($entry['is_running'] && $timelineData['config']['running_entry_animation'])
                            <div class="absolute inset-0 animate-pulse bg-white opacity-20 rounded"></div>
                        @endif

                        {{-- Tooltip --}}
                        @if($timelineData['config']['show_tooltips'])
                            <div x-show="showTooltip"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 text-xs text-white bg-gray-900 rounded-lg shadow-lg whitespace-nowrap timeline-tooltip"
                                 style="min-width: 200px; z-index: 9999 !important;">
                                <div class="text-center">
                                    <div class="font-medium">{{ $entry['title'] }}</div>
                                    <div class="text-gray-300">{{ $entry['description'] }}</div>
                                    <div class="text-gray-300">{{ $entry['start_time'] }} - {{ $entry['end_time'] }}</div>
                                    <div class="text-gray-300">({{ $entry['duration_formatted'] }})</div>
                                </div>
                                {{-- Tooltip arrow --}}
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Legend --}}
        @if($timelineData['config']['show_legend'] && count($timelineData['legend']) > 0)
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Legend</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($timelineData['legend'] as $item)
                        <div class="flex items-center space-x-3">
                            <div class="w-4 h-4 rounded flex-shrink-0" style="background-color: {{ $item['color'] }}"></div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900 truncate">{{ $item['title'] }}</div>
                                <div class="text-xs text-gray-500 truncate">
                                    {{ $item['subtitle'] }} • {{ $item['count'] }} {{ $item['count'] === 1 ? 'entry' : 'entries' }} • {{ number_format($item['total_duration'] / 60, 1) }}h
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Legend indicators --}}
                <div class="mt-4 pt-3 border-t border-gray-200">
                    <div class="flex flex-wrap gap-6 text-xs text-gray-600">
                        {{-- Manual Entry Legend --}}
                        @if($timelineData['config']['manual_entry_pattern'] === 'dashed')
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-4 border-2 border-dashed border-blue-500 bg-blue-100 rounded"></div>
                                <span class="font-medium">Manual Entry</span>
                            </div>
                        @elseif($timelineData['config']['manual_entry_pattern'] === 'striped')
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-4 bg-blue-500 relative rounded">
                                    <div class="absolute inset-0 opacity-50 rounded"
                                         style="background-image: repeating-linear-gradient(45deg, transparent, transparent 1px, rgba(255,255,255,0.5) 1px, rgba(255,255,255,0.5) 2px);"></div>
                                </div>
                                <span class="font-medium">Manual Entry</span>
                            </div>
                        @endif

                        {{-- Running Entry Legend --}}
                        @if($timelineData['config']['running_entry_animation'])
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-4 bg-red-500 rounded animate-pulse"></div>
                                <span class="font-medium">Currently Running</span>
                            </div>
                        @endif

                        {{-- Stacked Entries Legend --}}
                        @if(count($timelineData['entries']) > 1)
                            <div class="flex items-center space-x-2">
                                <div class="relative w-6 h-4">
                                    <div class="absolute w-4 h-2 bg-blue-400 rounded top-0 left-0"></div>
                                    <div class="absolute w-4 h-2 bg-green-400 rounded bottom-0 right-0"></div>
                                </div>
                                <span class="font-medium">Overlapping Entries</span>
                            </div>
                        @endif

                        {{-- Time Grid Legend --}}
                        <div class="flex items-center space-x-2">
                            <div class="w-6 h-4 border border-gray-300 relative bg-gray-50 rounded">
                                <div class="absolute inset-y-0 left-2 w-px bg-gray-400"></div>
                                <div class="absolute inset-y-0 right-2 w-px bg-gray-300"></div>
                            </div>
                            <span class="font-medium">Hour Grid</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    @else
        {{-- No entries message --}}
        <div class="text-center py-12 bg-gray-50 border border-gray-200 rounded-lg">
            <div class="text-gray-500 text-lg font-medium mb-2">No time entries for {{ $date->format('M j, Y') }}</div>
            <div class="text-gray-400 text-sm">Start tracking time to see your timeline visualization here.</div>
        </div>
    @endif
</div>

{{-- Custom CSS for timeline animations and patterns --}}
<style>
.timeline-entry {
    border-radius: 4px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.2s ease;
    cursor: pointer;
}

.timeline-entry:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-color: rgba(255, 255, 255, 0.4);
}

.timeline-entry-manual {
    border-style: dashed;
}

.timeline-entry-running {
    border-color: rgba(255, 255, 255, 0.6);
    border-width: 2px;
}

@keyframes pulse-glow {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
    }
    50% {
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }
}

.timeline-entry-running {
    animation: pulse-glow 2s infinite;
}

/* Ensure timeline tooltips are always visible */
.timeline-tooltip {
    z-index: 9999 !important;
    position: absolute !important;
}

/* Override any overflow hidden that might clip tooltips */
.timeline-chart-container {
    overflow: visible !important;
}

.timeline-chart {
    overflow: visible !important;
}

.timeline-entries {
    overflow: visible !important;
}
</style>
