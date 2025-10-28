<div class="py-12 time-tracking-section" x-data="timeTrackingData()" data-time-tracking>
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
                                            <h4 class="font-medium text-gray-900">
                                                @if($entry->task)
                                                    {{ $entry->task->title }}
                                                @else
                                                    {{ $entry->activity_type ?? 'General Activity' }}
                                                @endif
                                            </h4>
                                            <p class="text-sm text-gray-600">
                                                @if($entry->task)
                                                    {{ $entry->task->project->name }}
                                                @else
                                                    General Activity
                                                @endif
                                            </p>
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
                <div class="grid gap-6 md:grid-cols-2 time-entry-form" data-time-tracking>
                    <!-- Start Timer -->
                    <div class="bg-gray-50 rounded-lg p-6 time-controls">
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
                                    @if($generalActivities && count($generalActivities) > 0)
                                        <optgroup label="─── General Activities ───">
                                            @foreach($generalActivities as $activity)
                                                <option value="general_{{ $loop->index }}">{{ $activity }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
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
                    <div class="bg-gray-50 rounded-lg p-6 time-controls">
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
                                    @if($generalActivities && count($generalActivities) > 0)
                                        <optgroup label="─── General Activities ───">
                                            @foreach($generalActivities as $activity)
                                                <option value="general_{{ $loop->index }}">{{ $activity }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
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
                                <div class="grid grid-cols-2 gap-4 mb-3 time-input">
                                    <div>
                                        <label for="hours" class="block text-sm text-gray-600 mb-1">Hours</label>
                                        <input wire:model="hours" type="number" id="hours" min="0" max="23" placeholder="0"
                                               class="time-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('hours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="minutes" class="block text-sm text-gray-600 mb-1">Minutes</label>
                                        <input wire:model="minutes" type="number" id="minutes" min="0" max="59" placeholder="0"
                                               class="time-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
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
                                <div class="grid grid-cols-2 gap-4 time-input">
                                    <div>
                                        <label for="start-time" class="block text-sm text-gray-600 mb-1">Start Time</label>
                                        <input wire:model="startTime" type="text" id="start-time"
                                               class="time-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('startTime') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="end-time" class="block text-sm text-gray-600 mb-1">End Time</label>
                                        <input wire:model="endTime" type="text" id="end-time"
                                               class="time-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
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

                <!-- Enhanced Timeline Visualization -->
                <div class="mt-8 time-tracking-section" data-time-tracking>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Timeline for {{ \Carbon\Carbon::parse($selectedDate)->format('M j, Y') }}</h3>

                    <!-- Date Picker -->
                    <div class="mb-4">
                        <label for="timeline-date" class="block text-sm font-medium text-gray-700 mb-2">Select Date</label>
                        <input wire:model.live="selectedDate"
                               type="date"
                               id="timeline-date"
                               class="time-input px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Timeline Chart using Timeline Library -->
                    @if(count($timelineData['entries'] ?? []) > 0)
                        @include('timeline-library::timeline-chart', [
                            'timelineData' => $timelineData,
                            'date' => \Carbon\Carbon::parse($selectedDate)
                        ])
                    @else
                        <div class="text-center py-12 bg-gray-50 border border-gray-200 rounded-lg">
                            <div class="text-gray-500 text-lg font-medium mb-2">No time entries for {{ \Carbon\Carbon::parse($selectedDate)->format('M j, Y') }}</div>
                            <div class="text-gray-400 text-sm">Start tracking time to see your timeline visualization here.</div>
                        </div>
                    @endif
                </div>

                <!-- Time Entries List -->

                <!-- Recent Time Entries Table -->
                @if($recentEntries->count() > 0)
                    <div class="mt-8 time-tracking-section" data-time-tracking>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Time Entries</h3>

                        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                            <div class="overflow-x-auto">
                                <table class="w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider duration-display">Duration</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start</th>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentEntries as $entry)
                                        <tr class="hover:bg-gray-50">
                                            <!-- Task -->
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                <div class="truncate max-w-48">
                                                    {{ $entry->task ? $entry->task->title : ($entry->activity_type ?? 'General Activity') }}
                                                </div>
                                            </td>

                                            <!-- Project -->
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                <div class="truncate max-w-24">
                                                    @if($entry->task && $entry->task->project)
                                                        {{ $entry->task->project->name }}
                                                    @else
                                                        General Activity
                                                    @endif
                                                </div>
                                            </td>

                                            <!-- Description -->
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                @if($editingTimeEntry === $entry->id)
                                                    <input type="text"
                                                           wire:model="editDescription"
                                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                                           placeholder="Description">
                                                @else
                                                    <div class="truncate max-w-36">{{ $entry->description ?? '-' }}</div>
                                                @endif
                                            </td>

                                            <!-- Duration -->
                                            <td class="px-3 py-3 text-sm text-gray-500 duration-display">
                                                @if($editingTimeEntry === $entry->id)
                                                    <div class="flex items-center">
                                                        <input type="number"
                                                               wire:model="editDuration"
                                                               step="0.25"
                                                               min="0.01"
                                                               class="time-input w-14 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                                        <span class="text-xs text-gray-400 ml-1">min</span>
                                                    </div>
                                                @else
                                                    @php
                                                        $totalMinutes = $entry->total_minutes ?? $entry->duration_minutes ?? 0;
                                                        $hours = floor($totalMinutes / 60);
                                                        $minutes = $totalMinutes % 60;
                                                    @endphp
                                                    {{ $hours > 0 ? $hours . 'h ' : '' }}{{ $minutes > 0 ? $minutes . 'm' : ($hours == 0 ? '0m' : '') }}
                                                @endif
                                            </td>

                                            <!-- Date -->
                                            <td class="px-3 py-3 text-sm text-gray-500">
                                                <div class="truncate">{{ \Carbon\Carbon::parse($entry->entry_date ?? $entry->created_at)->format('M j') }}</div>
                                            </td>

                                            <!-- Start Time -->
                                            <td class="px-3 py-3 text-sm text-gray-500">
                                                @if($editingTimeEntry === $entry->id)
                                                    <input type="time"
                                                           wire:model="editStartTime"
                                                           class="time-input w-16 px-1 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                                @else
                                                    <span class="duration-display">{{ $entry->start_time ? \Carbon\Carbon::parse($entry->start_time)->format('H:i') : '-' }}</span>
                                                @endif
                                            </td>

                                            <!-- End Time -->
                                            <td class="px-3 py-3 text-sm text-gray-500">
                                                @if($editingTimeEntry === $entry->id)
                                                    <input type="time"
                                                           wire:model="editEndTime"
                                                           class="time-input w-16 px-1 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                                @else
                                                    <span class="duration-display">{{ $entry->end_time ? \Carbon\Carbon::parse($entry->end_time)->format('H:i') : '-' }}</span>
                                                @endif
                                            </td>

                                            <!-- Actions -->
                                            <td class="px-4 py-3 text-sm font-medium">
                                                @if($editingTimeEntry === $entry->id)
                                                    <div class="flex space-x-2">
                                                        <button wire:click="saveTimeEntry({{ $entry->id }})"
                                                                class="text-green-600 hover:text-green-800 px-2 py-1 bg-green-50 rounded text-sm">
                                                            Save
                                                        </button>
                                                        <button wire:click="cancelEdit"
                                                                class="text-gray-600 hover:text-gray-800 px-2 py-1 bg-gray-50 rounded text-sm">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                @else
                                                    <div class="flex space-x-2">
                                                        <button wire:click="editTimeEntry({{ $entry->id }})"
                                                                class="text-blue-600 hover:text-blue-800 px-2 py-1 bg-blue-50 rounded text-sm">
                                                            Edit
                                                        </button>
                                                        <button wire:click="deleteTimeEntry({{ $entry->id }})"
                                                                class="text-red-600 hover:text-red-800 px-2 py-1 bg-red-50 rounded text-sm"
                                                                onclick="return confirm('Are you sure you want to delete this time entry?')">
                                                            Delete
                                                        </button>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
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
