<div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 relative z-10">
    @if($isEditing)
        <!-- Edit Form -->
        <div class="space-y-4 relative z-20 bg-white border border-gray-300 rounded-lg p-4 shadow-lg">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Duration Input -->
                <div>
                    <label for="duration{{ $timeEntry->id }}" class="block text-sm font-medium text-gray-700">
                        Duration (minutes)
                        @if($timeEntry->is_running)
                            <span class="text-xs text-green-600 font-normal">(currently elapsed)</span>
                        @endif
                    </label>
                    <input type="number"
                           wire:model="duration"
                           id="duration{{ $timeEntry->id }}"
                           min="0.01"
                           step="0.01"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           placeholder="Enter duration in minutes">
                    @if($timeEntry->is_running)
                        <p class="mt-1 text-xs text-gray-500">Editing will adjust the start time to maintain the timer running state.</p>
                    @endif
                    @error('duration') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Date Input -->
                <div>
                    <label for="entryDate{{ $timeEntry->id }}" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date"
                           wire:model="entryDate"
                           id="entryDate{{ $timeEntry->id }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('entryDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Task/Project Info (Read-only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Task/Project</label>
                    <div class="mt-1 px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-900">
                        @if($timeEntry->task)
                            {{ $timeEntry->task->title }} • {{ $timeEntry->task->project->name }}
                        @elseif($timeEntry->activity_type)
                            {{ $timeEntry->activity_type }}
                            @if($timeEntry->project)
                                • {{ $timeEntry->project->name }}
                            @endif
                        @else
                            General Time Entry
                        @endif
                    </div>
                </div>
            </div>

            <!-- Description Input -->
            <div>
                <label for="description{{ $timeEntry->id }}" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea wire:model="description"
                          id="description{{ $timeEntry->id }}"
                          rows="3"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                          placeholder="Enter description (optional)"></textarea>
                @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-3">
                <button wire:click="saveEdit"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save Changes
                </button>
                <button wire:click="cancelEdit"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>
            </div>
        </div>
    @else
        <!-- View Mode -->
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                    <!-- Duration -->
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        @php
                            $minutes = $timeEntry->total_minutes ?? $timeEntry->duration_minutes ?? 0;
                            $hours = floor($minutes / 60);
                            $mins = $minutes % 60;
                        @endphp
                        {{ $hours }}h {{ $mins }}m
                    </span>

                    <!-- Date -->
                    <span class="text-sm text-gray-500">
                        @if($timeEntry->entry_date)
                            {{ $timeEntry->entry_date->format('M j, Y') }}
                        @else
                            {{ $timeEntry->created_at->format('M j, Y') }}
                        @endif
                    </span>

                    <!-- Time -->
                    <span class="text-sm text-gray-500">
                        {{ $timeEntry->created_at->format('H:i') }}
                    </span>

                    <!-- Running indicator -->
                    @if($timeEntry->is_running)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <div class="w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></div>
                            Running
                        </span>
                    @endif
                </div>

                <!-- Project/Task Info -->
                <div class="mb-2">
                    @if($timeEntry->task)
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-900">{{ $timeEntry->task->title }}</span>
                            <span class="text-sm text-gray-500">•</span>
                            <span class="text-sm text-gray-600">{{ $timeEntry->task->project->name }}</span>
                        </div>
                    @elseif($timeEntry->activity_type)
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-900">{{ $timeEntry->activity_type }}</span>
                            @if($timeEntry->project)
                                <span class="text-sm text-gray-500">•</span>
                                <span class="text-sm text-gray-600">{{ $timeEntry->project->name }}</span>
                            @endif
                        </div>
                    @else
                        <span class="text-sm font-medium text-gray-500">General Time Entry</span>
                    @endif
                </div>

                <!-- Description -->
                @if($timeEntry->description)
                    <p class="text-sm text-gray-700">{{ $timeEntry->description }}</p>
                @endif
            </div>

            <!-- Actions -->
            <div class="ml-4 flex items-center space-x-2">
                @if($showViewTaskLink && $timeEntry->task)
                    <a href="{{ route('projects.show', $timeEntry->task->project_id) }}?task={{ $timeEntry->task_id }}"
                       class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                        View Task
                    </a>
                @endif

                @if($timeEntry->user_id === Auth::id())
                    <button wire:click="startEdit"
                            class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                        Edit
                    </button>

                    @if($showDeleteButton)
                        <button wire:click="deleteEntry"
                                onclick="return confirm('Are you sure you want to delete this time entry?')"
                                class="text-red-600 hover:text-red-500 text-sm font-medium">
                            Delete
                        </button>
                    @endif
                @endif
            </div>
        </div>
    @endif
</div>
