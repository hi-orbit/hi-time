<div class="py-6" x-data="{
    showMessage: false,
    message: '',
    messageType: 'success'
}"
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

        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <div class="flex items-center">
                    <a href="{{ route('reports.index') }}" class="text-gray-500 hover:text-gray-700 mr-3">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Time by User (Enhanced)
                    </h1>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    {{ \Carbon\Carbon::parse($startDate)->format('M j, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M j, Y') }}
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <div class="bg-purple-100 px-4 py-2 rounded-lg">
                    <span class="text-purple-800 font-medium">Total: {{ number_format($userTimeData['total_hours'] ?? 0, 2) }} hours</span>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <div x-show="showMessage" x-transition class="mb-6">
            <div x-bind:class="messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'"
                 class="px-4 py-3 rounded">
                <span x-text="message"></span>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4">
                <div class="flex items-center space-x-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" wire:model.live="startDate" id="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" wire:model.live="endDate" id="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>
        </div>

        @if(count($userTimeData['users'] ?? []) > 0)
            <div class="space-y-6">
                @foreach($userTimeData['users'] as $userData)
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <!-- User Header -->
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                        <span class="text-purple-600 font-medium text-sm">
                                            {{ substr($userData['user_name'], 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">{{ $userData['user_name'] }}</h3>
                                        <p class="text-sm text-gray-500">{{ count($userData['customers']) }} customer{{ count($userData['customers']) !== 1 ? 's' : '' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-gray-900">{{ number_format($userData['total_hours'], 2) }}h</div>
                                    <div class="text-sm text-gray-500">
                                        @if(($userTimeData['total_hours'] ?? 0) > 0)
                                            {{ number_format(($userData['total_hours'] / $userTimeData['total_hours']) * 100, 1) }}% of total
                                        @else
                                            0% of total
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customers -->
                        <div class="divide-y divide-gray-200">
                            @foreach($userData['customers'] as $customerData)
                                <div class="px-6 py-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-md font-medium text-gray-900">{{ $customerData['customer_name'] }}</h4>
                                        <span class="text-lg font-semibold text-gray-700">{{ number_format($customerData['hours'], 2) }}h</span>
                                    </div>

                                    <!-- Projects -->
                                    <div class="ml-4 space-y-3">
                                        @foreach($customerData['projects'] as $projectData)
                                            <div class="border-l-2 border-gray-200 pl-4">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h5 class="text-sm font-medium text-gray-700">{{ $projectData['project_name'] }}</h5>
                                                    <span class="text-sm font-semibold text-gray-600">{{ number_format($projectData['hours'], 2) }}h</span>
                                                </div>

                                                <!-- Time Entries Table -->
                                                <div class="mt-3">
                                                    <div class="bg-white overflow-hidden shadow-sm rounded border border-gray-200">
                                                        <table class="min-w-full divide-y divide-gray-200">
                                                            <thead class="bg-gray-50">
                                                                <tr>
                                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start</th>
                                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End</th>
                                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-white divide-y divide-gray-200">
                                                                @foreach($projectData['entries'] as $entry)
                                                                    <tr class="hover:bg-gray-50">
                                                                        <!-- Task -->
                                                                        <td class="px-4 py-3 text-xs font-medium text-gray-900">
                                                                            {{ $entry->task ? $entry->task->title : ($entry->activity_type ?? 'General Activity') }}
                                                                        </td>

                                                                        <!-- Description -->
                                                                        <td class="px-4 py-3 text-xs text-gray-500">
                                                                            @if($editingTimeEntry === $entry->id)
                                                                                <input type="text"
                                                                                       wire:model="editDescription"
                                                                                       class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                                                                       placeholder="Description">
                                                                            @else
                                                                                {{ $entry->description ?? '-' }}
                                                                            @endif
                                                                        </td>

                                                                        <!-- Duration -->
                                                                        <td class="px-4 py-3 text-xs text-gray-500">
                                                                            @if($editingTimeEntry === $entry->id)
                                                                                <div class="flex items-center space-x-1">
                                                                                    <input type="number"
                                                                                           wire:model="editDuration"
                                                                                           step="0.25"
                                                                                           min="0.01"
                                                                                           class="w-12 px-1 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
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

                                                                        <!-- Date -->
                                                                        <td class="px-4 py-3 text-xs text-gray-500">
                                                                            {{ \Carbon\Carbon::parse($entry->entry_date ?? $entry->created_at)->format('M j') }}
                                                                        </td>

                                                                        <!-- Start Time -->
                                                                        <td class="px-4 py-3 text-xs text-gray-500">
                                                                            @if($editingTimeEntry === $entry->id)
                                                                                <input type="time"
                                                                                       wire:model="editStartTime"
                                                                                       class="w-20 px-1 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                                                            @else
                                                                                {{ $entry->start_time ? \Carbon\Carbon::parse($entry->start_time)->format('H:i') : '-' }}
                                                                            @endif
                                                                        </td>

                                                                        <!-- End Time -->
                                                                        <td class="px-4 py-3 text-xs text-gray-500">
                                                                            @if($editingTimeEntry === $entry->id)
                                                                                <input type="time"
                                                                                       wire:model="editEndTime"
                                                                                       class="w-20 px-1 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                                                            @else
                                                                                {{ $entry->end_time ? \Carbon\Carbon::parse($entry->end_time)->format('H:i') : '-' }}
                                                                            @endif
                                                                        </td>

                                                                        <!-- Actions -->
                                                                        <td class="px-4 py-3 text-xs font-medium">
                                                                            @if(Auth::id() === $entry->user_id)
                                                                                @if($editingTimeEntry === $entry->id)
                                                                                    <div class="flex space-x-1">
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
                                                                                    <div class="flex space-x-1">
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
                                                                            @else
                                                                                <span class="text-gray-400 text-xs">View only</span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No time entries found</h3>
                <p class="mt-1 text-sm text-gray-500">No time has been tracked by any users in this period.</p>
            </div>
        @endif
    </div>
</div>
