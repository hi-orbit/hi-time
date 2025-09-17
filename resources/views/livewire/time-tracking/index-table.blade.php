<!-- Spreadsheet-style Time Entries Table -->
@if(count($timeEntries) > 0)
    <div class="mt-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">1Time Entries for {{ \Carbon\Carbon::parse($selectedDate)->format('M j, Y') }}</h3>

        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-1 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                            <th class="px-1 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                            <th class="px-1 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-1 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-1 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($timeEntries as $index => $entry)
                        <tr class="hover:bg-gray-50">
                            @if(isset($editingTimeEntry) && $editingTimeEntry === $entry->id)
                                <!-- Edit Mode - Ultra compact -->
                                <td class="px-1 py-1">
                                    <select wire:model="editingData.task_id" class="w-full text-xs border-gray-300 rounded">
                                        <option value="">Task</option>
                                        @foreach($tasks as $task)
                                            <option value="{{ $task->id }}">{{ \Str::limit($task->title, 12) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-1 py-1">
                                    <div class="text-xs text-gray-500 truncate max-w-16">
                                        {{ $entry->task && $entry->task->project ? $entry->task->project->name : 'General' }}
                                    </div>
                                </td>
                                <td class="px-1 py-1">
                                    <input wire:model="editDescription" type="text"
                                           class="w-full px-1 py-1 text-xs border-gray-300 rounded"
                                           placeholder="Description">
                                </td>
                                <td class="px-1 py-1">
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-1">
                                            <input wire:model="editDuration" type="number" step="0.01" min="0"
                                                   class="w-6 px-1 py-1 text-xs border-gray-300 rounded">
                                            <span class="text-xs">m</span>
                                        </div>
                                        <div class="flex items-center">
                                            <input wire:model="editStartTime" type="time"
                                                   class="w-10 px-0 py-1 text-xs border-gray-300 rounded">
                                            <span class="text-xs mx-1">-</span>
                                            <input wire:model="editEndTime" type="time"
                                                   class="w-10 px-0 py-1 text-xs border-gray-300 rounded">
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($entry->entry_date ?? $entry->created_at)->format('M j') }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-1 py-1">
                                    <div class="space-y-1">
                                        <button wire:click="saveTimeEntry({{ $entry->id }})"
                                                class="w-full text-green-600 hover:text-green-800 text-xs px-1 py-1 bg-green-50 rounded">
                                            Save
                                        </button>
                                        <button wire:click="cancelEdit"
                                                class="w-full text-gray-600 hover:text-gray-800 text-xs px-1 py-1 bg-gray-50 rounded">
                                            Cancel
                                        </button>
                                    </div>
                                </td>
                            @else
                                <!-- View Mode - Ultra compact -->
                                <td class="px-1 py-1">
                                    <div class="text-xs font-medium text-gray-900 truncate">
                                        {{ $entry->task ? $entry->task->title : ($entry->activity_type ?? 'General Activity') }}
                                    </div>
                                </td>
                                <td class="px-1 py-1">
                                    <div class="text-xs text-gray-500 truncate">
                                        {{ $entry->task && $entry->task->project ? $entry->task->project->name : 'General' }}
                                    </div>
                                </td>
                                <td class="px-1 py-1">
                                    <div class="text-xs text-gray-900 truncate">
                                        {{ $entry->description ?? $entry->content ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-1 py-1">
                                    <div class="text-xs space-y-1">
                                        <div class="font-medium">{{ floor(($entry->total_minutes ?? $entry->duration_minutes ?? 0) / 60) }}h {{ ($entry->total_minutes ?? $entry->duration_minutes ?? 0) % 60 }}m</div>
                                        <div class="text-gray-500">
                                            {{ $entry->start_time ? \Carbon\Carbon::parse($entry->start_time)->format('H:i') : '-' }} -
                                            {{ $entry->end_time ? \Carbon\Carbon::parse($entry->end_time)->format('H:i') : '-' }}
                                        </div>
                                        <div class="text-gray-400">{{ \Carbon\Carbon::parse($entry->entry_date ?? $entry->created_at)->format('M j') }}</div>
                                    </div>
                                </td>
                                <td class="px-1 py-1">
                                    <div class="space-y-1">
                                        <button wire:click="editTimeEntry({{ $entry->id }})"
                                                class="w-full text-indigo-600 hover:text-indigo-800 text-xs px-1 py-1 bg-indigo-50 rounded">
                                            Edit
                                        </button>
                                        <button wire:click="deleteTimeEntry({{ $entry->id }})"
                                                class="w-full text-red-600 hover:text-red-800 text-xs px-1 py-1 bg-red-50 rounded"
                                                onclick="return confirm('Are you sure you want to delete this time entry?')">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
