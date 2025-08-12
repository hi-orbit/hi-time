<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h2>

                @if($runningTimeEntries->count() > 0)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">⏱️ Currently Running Timers</h3>
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($runningTimeEntries as $entry)
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-medium text-gray-900">{{ $entry->task->title }}</h4>
                                            <p class="text-sm text-gray-600">{{ $entry->task->project->name }}</p>
                                            <p class="text-sm text-gray-500">Started: {{ $entry->start_time->format('H:i') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-sm font-medium text-green-600">{{ $entry->formatted_duration }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Quick Actions -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="{{ route('time-tracking.index') }}"
                               class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center py-3 px-4 rounded-md font-medium">
                                ⏱️ Start Time Tracking
                            </a>
                            <a href="{{ route('projects.index') }}"
                               class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-center py-3 px-4 rounded-md font-medium">
                                📋 View Projects
                            </a>
                            <a href="{{ route('reports.index') }}"
                               class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-center py-3 px-4 rounded-md font-medium">
                                📊 View Reports
                            </a>
                        </div>
                    </div>

                    <!-- Assigned Tasks -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">My Tasks</h3>
                        @if($assignedTasks->count() > 0)
                            <div class="space-y-3">
                                @foreach($assignedTasks as $task)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h4 class="font-medium text-gray-900">{{ $task->title }}</h4>
                                                <p class="text-sm text-gray-600">{{ $task->project->name }}</p>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @switch($task->status)
                                                        @case('backlog') bg-gray-100 text-gray-800 @break
                                                        @case('in_progress') bg-blue-100 text-blue-800 @break
                                                        @case('in_test') bg-yellow-100 text-yellow-800 @break
                                                        @case('ready_to_release') bg-purple-100 text-purple-800 @break
                                                        @case('done') bg-green-100 text-green-800 @break
                                                    @endswitch">
                                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                </span>
                                            </div>
                                            <div class="text-right">
                                                @if($task->isRunning())
                                                    <span class="text-green-600 text-sm font-medium">⏱️ Running</span>
                                                @else
                                                    <a href="{{ route('time-tracking.index') }}?task={{ $task->id }}"
                                                       class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                        Start Timer
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">No tasks assigned to you yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
