<div class="py-12" x-data="timeTrackingData()">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Time Tracking</h2>

                @if (session()->has('message'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('message') }}
                    </div>
                @endif

                <!-- Running Timers -->
                @if($runningEntries->count() > 0)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">⏱️ Running Timers</h3>
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($runningEntries as $entry)
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-medium text-gray-900">{{ $entry->task->title }}</h4>
                                            <p class="text-sm text-gray-600">{{ $entry->task->project->name }}</p>
                                            <p class="text-sm text-gray-500">Started: {{ $entry->start_time->format('H:i') }}</p>
                                            @if($entry->description)
                                                <p class="text-sm text-gray-600 mt-1">{{ $entry->description }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <span class="text-sm font-medium text-green-600">{{ $entry->formatted_decimal_hours }}</span>
                                            <button wire:click="stopTimer({{ $entry->id }})"
                                                    class="block mt-2 text-xs bg-red-100 text-red-700 px-2 py-1 rounded hover:bg-red-200">
                                                ⏹ Stop
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Time Entry Forms -->
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Start Timer -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Start Timer</h3>
                        <form wire:submit.prevent="startTimer">
                            <div class="mb-4">
                                <label for="project-timer" class="block text-sm font-medium text-gray-700 mb-2">Select Project</label>
                                <select x-model="selectedProjectId"
                                        wire:model="selectedProjectId"
                                        id="project-timer"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Choose a project first...</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="task-timer" class="block text-sm font-medium text-gray-700 mb-2">Select Task</label>
                                <select wire:model="selectedTaskId" id="task-timer"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        x-bind:disabled="!selectedProjectId">
                                    <option value="" x-text="selectedProjectId ? 'Choose a task...' : 'Select a project first'"></option>
                                    @foreach($tasks as $task)
                                        <option value="{{ $task->id }}">{{ $task->title }}</option>
                                    @endforeach
                                </select>
                                @error('selectedTaskId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-4">
                                <label for="timer-description" class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                                <textarea wire:model="description" id="timer-description" rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                          placeholder="What are you working on?"></textarea>
                            </div>
                            <button type="submit"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md font-medium">
                                ▶ Start Timer
                            </button>
                        </form>
                    </div>

                    <!-- Manual Time Entry -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Note & Log Time</h3>
                        <form wire:submit.prevent="logManualTime">
                            <div class="mb-4">
                                <label for="project-manual" class="block text-sm font-medium text-gray-700 mb-2">Select Project</label>
                                <select x-model="selectedProjectId"
                                        wire:model="selectedProjectId"
                                        id="project-manual"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Choose a project first...</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="task-manual" class="block text-sm font-medium text-gray-700 mb-2">Select Task</label>
                                <select wire:model="selectedTaskId" id="task-manual"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        x-bind:disabled="!selectedProjectId">
                                    <option value="" x-text="selectedProjectId ? 'Choose a task...' : 'Select a project first'"></option>
                                    @foreach($tasks as $task)
                                        <option value="{{ $task->id }}">{{ $task->title }}</option>
                                    @endforeach
                                </select>
                                @error('selectedTaskId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-4">
                                <label for="entry-date" class="block text-sm font-medium text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                                <input wire:model.live="entryDate" type="date" id="entry-date" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                @error('entryDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Time Duration</label>

                                <!-- Manual Hours/Minutes Entry -->
                                <div class="grid grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <label for="hours" class="block text-sm text-gray-600 mb-1">Hours</label>
                                        <input wire:model="hours" type="number" id="hours" min="0" max="23" placeholder="0"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('hours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="minutes" class="block text-sm text-gray-600 mb-1">Minutes</label>
                                        <input wire:model="minutes" type="number" id="minutes" min="0" max="59" placeholder="0"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('minutes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- OR divider -->
                                <div class="flex items-center my-3">
                                    <div class="flex-grow border-t border-gray-300"></div>
                                    <span class="mx-3 text-sm text-gray-500 font-medium">or</span>
                                    <div class="flex-grow border-t border-gray-300"></div>
                                </div>

                                <!-- Start/End Time Entry -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="start-time" class="block text-sm text-gray-600 mb-1">Start Time</label>
                                        <input wire:model="startTime" type="text" id="start-time"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('startTime') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="end-time" class="block text-sm text-gray-600 mb-1">End Time</label>
                                        <input wire:model="endTime" type="text" id="end-time"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('endTime') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="manual-note" class="block text-sm font-medium text-gray-700 mb-2">Note <span class="text-red-500">*</span></label>
                                <textarea wire:model="note" id="manual-note" rows="3" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                          placeholder="Add a note about what you worked on..."></textarea>
                                @error('note') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <button type="submit"
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md font-medium">
                                + Add Note & Log Time
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Daily Hours Chart -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Hours Logged on {{ \Carbon\Carbon::parse($entryDate)->format('M j, Y') }}</h3>

                    @php
                        $totalMinutes = collect($chartData)->sum('duration_minutes');
                        $totalHours = floor($totalMinutes / 60);
                        $totalMins = $totalMinutes % 60;
                    @endphp

                <div class="bg-gray-50 rounded-lg p-6">
                    <!-- Timeline Header -->
                    <div class="mb-4">
                        <p class="text-lg font-semibold text-gray-700">
                            Total: {{ $totalHours }}h {{ $totalMins }}m
                        </p>
                        <p class="text-sm text-gray-500">Hover over a task to see details</p>
                        <p class="text-sm text-gray-400">Manual entries are shown with dashed borders and estimated times</p>
                    </div>                        <!-- Timeline Chart -->
                        @php
                            $maxLayer = count($chartData) > 0 ? max(array_column($chartData, 'layer')) : 0;
                            $rowHeight = 50; // Height of each timeline row
                            $chartHeight = max(100, ($maxLayer + 1) * $rowHeight + 50); // Proper padding for bottom entries
                        @endphp

                        <div class="relative" style="height: {{ $chartHeight }}px;">
                            <!-- Hour markers -->
                            <div class="absolute inset-0 flex text-xs text-gray-400 mb-2">
                                @for($hour = 0; $hour < 24; $hour++)
                                    <div class="flex flex-col items-start" style="width: {{ 100/24 }}%; position: relative;">
                                        <span class="text-xs font-medium">{{ sprintf('%02d', $hour) }}</span>
                                        <div class="absolute left-0 top-4 w-px bg-gray-300" style="height: {{ $chartHeight - 20 }}px;"></div>
                                        <!-- 30-minute marker -->
                                        <div class="absolute left-1/2 top-4 w-px bg-gray-200" style="height: {{ $chartHeight - 30 }}px;"></div>
                                        <!-- Time labels for clarity -->
                                        <div class="absolute left-1/2 top-0 text-xs text-gray-300 transform -translate-x-1/2">30</div>
                                    </div>
                                @endfor
                            </div>

                            <!-- Timeline bars -->
                            <div class="absolute inset-0 mt-8">
                                @if(count($chartData) > 0)
                                    @foreach($chartData as $entry)
                                        @php
                                            // Calculate exact decimal hours for positioning
                                            $startHour = $entry['start_time']->hour + ($entry['start_time']->minute / 60);
                                            $endHour = $entry['end_time']->hour + ($entry['end_time']->minute / 60);

                                            // Calculate position and width as percentage of 24-hour day
                                            $left = ($startHour / 24) * 100;
                                            $width = (($endHour - $startHour) / 24) * 100;

                                            // Calculate vertical position based on layer
                                            $topPosition = 15 + ($entry['layer'] * $rowHeight);

                                            $durationHours = floor($entry['duration_minutes'] / 60);
                                            $durationMins = $entry['duration_minutes'] % 60;
                                        @endphp
                                        <div class="absolute group"
                                             style="left: {{ number_format($left, 2) }}%; width: {{ number_format($width, 2) }}%; top: {{ $topPosition }}px; height: 40px;">
                                            <div class="w-full h-full rounded shadow-sm hover:shadow-md transition-shadow duration-200 cursor-pointer border-2 border-white {{ isset($entry['is_manual']) && $entry['is_manual'] ? 'border-dashed opacity-75' : '' }}"
                                                 style="background-color: {{ $entry['color'] }};">

                                                <!-- Tooltip -->
                                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block z-10">
                                                    <div class="bg-gray-800 text-white text-xs rounded-lg py-2 px-3 whitespace-nowrap shadow-lg">
                                                        <div class="font-semibold">{{ $entry['task'] }}</div>
                                                        <div class="text-gray-300">{{ $entry['project'] }}</div>
                                                        <div class="mt-1">
                                                            {{ $entry['start_time']->format('H:i') }} - {{ $entry['end_time']->format('H:i') }}
                                                            @if(isset($entry['is_manual']) && $entry['is_manual'])
                                                                <span class="text-yellow-300">(estimated)</span>
                                                            @endif
                                                        </div>
                                                        <div>Duration: {{ $durationHours }}h {{ $durationMins }}m</div>
                                                        @if(isset($entry['is_manual']) && $entry['is_manual'])
                                                            <div class="text-yellow-300 text-xs">Manual entry</div>
                                                        @endif
                                                        <!-- Arrow -->
                                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex items-center justify-center h-full">
                                        <p class="text-gray-500 text-sm">No time logged for this date</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Legend -->
                        @if(count($chartData) > 0)
                            <div class="mt-1 pt-2 border-t border-gray-200">
                                <h4 class="text-sm font-medium text-gray-700 mb-1">Tasks:</h4>
                                <div class="flex flex-wrap gap-3">
                                    @php
                                        $uniqueTasks = collect($chartData)->unique('task');
                                    @endphp
                                    @foreach($uniqueTasks as $entry)
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 rounded-sm mr-2" style="background-color: {{ $entry['color'] }};"></div>
                                            <span class="text-sm text-gray-600">{{ $entry['task'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Time Entries -->
                @if($recentEntries->count() > 0)
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Time Entries</h3>
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentEntries as $entry)
                                        @if($editingEntryId === $entry->id)
                                            <!-- Edit Form Row -->
                                            <tr class="bg-yellow-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $entry->task->title }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $entry->task->project->name }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    <input wire:model="editDescription" type="text"
                                                           class="w-full text-sm px-2 py-1 border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500"
                                                           placeholder="Description">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $entry->formatted_decimal_hours }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input wire:model="editEntryDate" type="date"
                                                           class="text-sm px-2 py-1 border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input wire:model="editStartTime" type="time"
                                                           class="text-sm px-2 py-1 border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input wire:model="editEndTime" type="time"
                                                           class="text-sm px-2 py-1 border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <div class="flex space-x-2">
                                                        <button wire:click="updateEntry"
                                                                class="text-green-600 hover:text-green-900 font-medium">
                                                            Save
                                                        </button>
                                                        <button wire:click="cancelEdit"
                                                                class="text-gray-600 hover:text-gray-900 font-medium">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @else
                                            <!-- Display Row -->
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $entry->task->title }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $entry->task->project->name }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500">
                                                    {{ $entry->description ?: '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $entry->formatted_decimal_hours }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $entry->entry_date ? $entry->entry_date->format('M j, Y') : $entry->created_at->format('M j, Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $entry->start_time ? $entry->start_time->format('H:i') : '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $entry->end_time ? $entry->end_time->format('H:i') : '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <button wire:click="editEntry({{ $entry->id }})"
                                                            class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                        Edit
                                                    </button>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function timeTrackingData() {
            return {
                selectedProjectId: '',

                init() {
                    // Load from localStorage on page load
                    this.selectedProjectId = localStorage.getItem('time_tracking_project_id') || '';

                    // Set Livewire component property if we have a saved value
                    if (this.selectedProjectId) {
                        this.$wire.set('selectedProjectId', this.selectedProjectId);
                    }

                    // Watch for changes and save to localStorage
                    this.$watch('selectedProjectId', (value) => {
                        if (value) {
                            localStorage.setItem('time_tracking_project_id', value);
                        } else {
                            localStorage.removeItem('time_tracking_project_id');
                        }

                        // Update Livewire component
                        this.$wire.set('selectedProjectId', value);
                    });
                }
            }
        }
    </script>
</div>
