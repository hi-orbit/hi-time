<div class="py-12 time-tracking-section" x-data="{
    showMessage: false,
    message: '',
    messageType: 'success'
}" data-time-tracking>
x-on:success.window="showMessage = true; message = $event.detail; messageType = 'success'; setTimeout(() => showMessage = false, 3000)"
x-on:error.window="showMessage = true; message = $event.detail; messageType = 'error'; setTimeout(() => showMessage = false, 5000)">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Reports Navigation -->
        <div class="mb-6">
            <nav class="bg-white shadow rounded-lg">
                <div class="px-6 py-3">
                    <div class="flex space-x-8">
                        <a href="{{ route('reports.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('reports.index') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            </svg>
                            Reports Dashboard
                        </a>
                        <a href="{{ route('reports.my-time-today') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('reports.my-time-today') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            My Time Today
                        </a>
                        <a href="{{ route('reports.time-by-user') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('reports.time-by-user*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a4 4 0 11-8 0"></path>
                            </svg>
                            Time by User
                        </a>
                        <a href="{{ route('reports.time-by-customer-this-month') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium {{ request()->routeIs('reports.time-by-customer*') ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Customer Reports
                        </a>
                    </div>
                </div>
            </nav>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">My Time Today</h2>
                    <div class="flex items-center space-x-4">
                        <!-- Date Picker -->
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700">Date:</label>
                            <input type="date"
                                   wire:model.live="selectedDate"
                                   id="date"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                </div>

                <!-- Flash Messages -->
                <div x-show="showMessage" x-transition class="mb-4">
                    <div x-bind:class="messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'"
                         class="px-4 py-3 rounded">
                        <span x-text="message"></span>
                    </div>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('message') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Summary -->
                <div class="bg-indigo-50 rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-indigo-900">Total Time</h3>
                            <p class="text-sm text-indigo-600">{{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-indigo-900">
                                {{ $totalHours }}h {{ $totalMinutes }}m
                            </div>
                            <p class="text-sm text-indigo-600">{{ count($timeEntries) }} {{ Str::plural('entry', count($timeEntries)) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Timeline Visualization using Timeline Library -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline for {{ \Carbon\Carbon::parse($selectedDate)->format('M j, Y') }}</h3>

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

                <!-- Time Entries Table -->
                @if(count($timeEntries) > 0)
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Time Entries</h3>

                        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($timeEntries as $entry)
                                        <tr class="hover:bg-gray-50">
                                            <!-- Task -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $entry->task ? $entry->task->title : ($entry->activity_type ?? 'General Activity') }}
                                            </td>

                                            <!-- Project -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($entry->task && $entry->task->project)
                                                    {{ $entry->task->project->name }}
                                                @else
                                                    General Activity
                                                @endif
                                            </td>

                                            <!-- Description -->
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                @if($editingTimeEntry === $entry->id)
                                                    <input type="text"
                                                           wire:model="editDescription"
                                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                                           placeholder="Description">
                                                @else
                                                    {{ $entry->description ?? '-' }}
                                                @endif
                                            </td>

                                            <!-- Duration -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($editingTimeEntry === $entry->id)
                                                    <div class="flex items-center space-x-1">
                                                        <input type="number"
                                                               wire:model="editDuration"
                                                               step="0.25"
                                                               min="0.01"
                                                               class="w-16 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                                        <span class="text-xs text-gray-400">min</span>
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

                                            <!-- Start Time -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($editingTimeEntry === $entry->id)
                                                    <input type="time"
                                                           wire:model="editStartTime"
                                                           class="w-24 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                                @else
                                                    {{ $entry->start_time ? \Carbon\Carbon::parse($entry->start_time)->format('H:i') : '-' }}
                                                @endif
                                            </td>

                                            <!-- End Time -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($editingTimeEntry === $entry->id)
                                                    <input type="time"
                                                           wire:model="editEndTime"
                                                           class="w-24 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                                @else
                                                    {{ $entry->end_time ? \Carbon\Carbon::parse($entry->end_time)->format('H:i') : '-' }}
                                                @endif
                                            </td>

                                            <!-- Actions -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @if($editingTimeEntry === $entry->id)
                                                    <div class="flex space-x-2">
                                                        <button wire:click="saveTimeEntry({{ $entry->id }})"
                                                                class="text-green-600 hover:text-green-800">
                                                            Save
                                                        </button>
                                                        <button wire:click="cancelEdit"
                                                                class="text-gray-600 hover:text-gray-800">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                @else
                                                    <div class="flex space-x-2">
                                                        <button wire:click="editTimeEntry({{ $entry->id }})"
                                                                class="text-blue-600 hover:text-blue-800">
                                                            Edit
                                                        </button>
                                                        <button wire:click="deleteTimeEntry({{ $entry->id }})"
                                                                class="text-red-600 hover:text-red-800"
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
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No time entries</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            You haven't logged any time for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('time-tracking.index') }}"
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Log Time
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
