<div class="py-12" x-data="{
    showMessage: false,
    message: '',
    messageType: 'success'
}"
x-on:success.window="showMessage = true; message = $event.detail; messageType = 'success'; setTimeout(() => showMessage = false, 3000)"
x-on:error.window="showMessage = true; message = $event.detail; messageType = 'error'; setTimeout(() => showMessage = false, 5000)">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h2>

                <!-- Flash Messages -->
                <div x-show="showMessage" x-transition class="mb-6">
                    <div x-bind:class="messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'"
                         class="px-4 py-3 rounded">
                        <span x-text="message"></span>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Quick Actions -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                @if(!auth()->user()->isCustomer())
                                    <a href="{{ route('time-tracking.index') }}"
                                       class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center py-3 px-4 rounded-md font-medium">
                                        ⏱️ Start Time Tracking
                                    </a>
                                @endif
                                <a href="{{ route('projects.index') }}"
                                   class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-center py-3 px-4 rounded-md font-medium">
                                    📋 View Projects
                                </a>
                                @if(!auth()->user()->isCustomer())
                                    <a href="{{ route('reports.index') }}"
                                       class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-center py-3 px-4 rounded-md font-medium">
                                        📊 View Reports
                                    </a>
                                @endif
                            </div>
                        </div>

                        @if(!auth()->user()->isCustomer())
                            <!-- Running Timers -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">⏱️ Running Timers</h3>
                                @if($runningTimeEntries->count() > 0)
                                    <div class="border border-gray-200 rounded-lg divide-y divide-gray-100">
                                        @foreach($runningTimeEntries as $entry)
                                            <div class="p-4">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center space-x-2 mb-1">
                                                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                                            <h4 class="text-sm font-medium text-gray-900 truncate">
                                                                @if($entry->task)
                                                                    {{ $entry->task->title }}
                                                                @else
                                                                    {{ $entry->activity_type ?? 'General Activity' }}
                                                                @endif
                                                            </h4>
                                                        </div>
                                                        <div class="text-xs text-gray-500 space-y-1">
                                                            @if($entry->task)
                                                                <p>{{ $entry->task->project->name }}</p>
                                                                @if($entry->task->project->customer)
                                                                    <p>{{ $entry->task->project->customer->name }}</p>
                                                                @endif
                                                            @elseif($entry->project)
                                                                <p>{{ $entry->project->name }}</p>
                                                                @if($entry->project->customer)
                                                                    <p>{{ $entry->project->customer->name }}</p>
                                                                @endif
                                                            @endif
                                                            <p>Started: {{ $entry->start_time->format('H:i') }}</p>
                                                            <p class="font-medium text-green-600">{{ $entry->formatted_decimal_hours }}</p>
                                                        </div>
                                                        @if($entry->description)
                                                            <p class="text-xs text-gray-600 mt-1 truncate">{{ $entry->description }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="ml-3 flex flex-col space-y-2">
                                                        @livewire('timeline-library.time-entry-editor', [
                                                            'timeEntry' => $entry,
                                                            'showProjectName' => false,
                                                            'showTaskTitle' => false,
                                                            'showDescription' => false,
                                                            'showDuration' => true,
                                                            'showTimes' => false,
                                                            'showDate' => false,
                                                            'allowEdit' => true,
                                                            'allowDelete' => false
                                                        ], key('running-' . $entry->id))
                                                        <button wire:click="stopTimer({{ $entry->id }})"
                                                                class="text-xs px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded border border-red-200 transition-colors">
                                                            Stop Timer
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="border border-gray-200 rounded-lg p-6">
                                        <div class="text-center">
                                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <h4 class="mt-2 text-sm font-medium text-gray-900">No active timers</h4>
                                            <p class="mt-1 text-sm text-gray-500">Start tracking time on your tasks.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Right Column - Assigned Tasks -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">My Tasks</h3>
                        @if($assignedTasks->count() > 0)
                            <div class="space-y-4">
                                @foreach($assignedTasks as $customerName => $customerTasks)
                                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                                        <!-- Customer Header -->
                                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                                            <h4 class="text-sm font-medium text-gray-900">{{ $customerName }}</h4>
                                            <span class="text-xs text-gray-500">{{ $customerTasks->count() }} {{ Str::plural('task', $customerTasks->count()) }}</span>
                                        </div>

                                        <!-- Tasks List -->
                                        <div class="divide-y divide-gray-100">
                                            @foreach($customerTasks as $task)
                                                <div class="hover:bg-gray-50 transition-colors">
                                                    <a href="{{ route('projects.show', $task->project_id) }}?task={{ $task->id }}"
                                                       class="block px-4 py-3">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex-1 min-w-0">
                                                                <div class="flex items-center space-x-2">
                                                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $task->title }}</p>
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                                        @switch($task->status)
                                                                            @case('backlog') bg-gray-100 text-gray-700 @break
                                                                            @case('in_progress') bg-blue-100 text-blue-700 @break
                                                                            @case('in_test') bg-yellow-100 text-yellow-700 @break
                                                                            @case('ready_to_release') bg-purple-100 text-purple-700 @break
                                                                            @case('done') bg-green-100 text-green-700 @break
                                                                        @endswitch">
                                                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                                    </span>
                                                                </div>
                                                                <p class="text-xs text-gray-500 truncate">{{ $task->project->name }}</p>
                                                            </div>
                                                            <div class="flex items-center space-x-2">
                                                                @if(!auth()->user()->isCustomer())
                                                                    @if($task->isRunning())
                                                                        <span class="flex items-center text-green-600 text-xs font-medium">
                                                                            <div class="w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></div>
                                                                            Running
                                                                        </span>
                                                                    @else
                                                                        <button onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('time-tracking.index') }}?task={{ $task->id }}'"
                                                                                class="text-indigo-600 hover:text-indigo-900 text-xs font-medium px-2 py-1 rounded border border-indigo-200 hover:border-indigo-300 transition-colors">
                                                                            Start Timer
                                                                        </button>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="border border-gray-200 rounded-lg p-6">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks assigned</h3>
                                    <p class="mt-1 text-sm text-gray-500">You don't have any tasks assigned to you yet.</p>
                                    <div class="mt-6">
                                        <a href="{{ route('projects.index') }}"
                                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                            View Projects
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
