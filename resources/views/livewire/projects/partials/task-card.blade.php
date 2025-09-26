<div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow cursor-move task-card"
     draggable="true"
     data-task-id="{{ $task->id }}"
     wire:click="openTaskDetails({{ $task->id }})"
     ondragstart="event.dataTransfer.setData('text/plain', '{{ $task->id }}')">

    <!-- Drop zones for ordering -->
    <div class="drop-zone drop-zone-before h-2 -mx-4 -mt-4 mb-2 opacity-0 transition-opacity"
         data-position="before" data-task-id="{{ $task->id }}"></div>
    <div class="mb-3">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h4 class="font-medium text-gray-900 text-sm mb-1">{{ $task->title }}</h4>
                @if($task->description)
                    <p class="text-xs text-gray-600 mb-2">{{ Str::limit($task->description, 60) }}</p>
                @endif

                <!-- Tags -->
                @if($task->tags && $task->tags->count() > 0)
                    <div class="flex flex-wrap gap-1 mb-2">
                        @foreach($task->tags as $tag)
                            <button wire:click.stop="toggleTagFilter({{ $tag->id }})"
                                    class="inline-flex items-center px-2 py-0.5 text-xs rounded-full font-medium text-white hover:opacity-80 transition-opacity {{ in_array($tag->id, $this->selectedTagFilters ?? []) ? 'ring-2 ring-white ring-opacity-50' : '' }}"
                                    style="background-color: {{ $tag->color }}"
                                    title="{{ in_array($tag->id, $this->selectedTagFilters ?? []) ? 'Remove from filter' : 'Click to filter by this tag' }}">
                                {{ $tag->name }}
                                @if(in_array($tag->id, $this->selectedTagFilters ?? []))
                                    <svg class="ml-1 w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            @if(auth()->user()->role === 'admin' || auth()->user()->id == $task->assigned_to || auth()->user()->id == $task->created_by)
                <button wire:click.stop="deleteTask({{ $task->id }})"
                        onclick="return confirm('Are you sure you want to delete this task?')"
                        class="text-gray-400 hover:text-red-600 ml-2 p-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            @endif
        </div>
    </div>

    <!-- Assigned User -->
    @if($task->assignedUser)
        <div class="flex items-center mb-3">
            <div class="flex items-center">
                <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-gray-300 text-xs font-medium text-gray-700">
                    {{ substr($task->assignedUser->name, 0, 1) }}
                </span>
                <span class="ml-2 text-xs text-gray-600">{{ $task->assignedUser->name }}</span>
            </div>
        </div>
    @else
        <div class="mb-3">
            <span class="text-xs text-gray-400">Unassigned</span>
        </div>
    @endif

    <!-- Time Tracking & Actions -->
    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
        @if(!auth()->user()->isCustomer())
            <div class="flex space-x-2">
                @if($task->isRunning() && $task->runningTimeEntry && $task->runningTimeEntry->user_id === auth()->id())
                    <button wire:click.stop="stopTimer({{ $task->id }})"
                            class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded hover:bg-red-200">
                        ⏹ Stop
                    </button>
                @else
                    <button wire:click.stop="startTimer({{ $task->id }})"
                            class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200">
                        ▶ Start
                    </button>
                @endif

                <button wire:click.stop="openTimeModal({{ $task->id }})"
                        class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200">
                    + Log
                </button>
            </div>
        @else
            <div></div>
        @endif

        <!-- Notes indicator -->
        @if($task->notes && $task->notes->count() > 0)
            <div class="text-xs text-gray-500 flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                </svg>
                {{ $task->notes->count() }}
            </div>
        @endif
    </div>

    @if(!auth()->user()->isCustomer())
        <!-- Total Time -->
        @if($task->timeEntries && $task->timeEntries->count() > 0)
            <div class="mt-2 text-xs text-gray-500">
                Total: {{ number_format($task->total_time / 60, 1) }}h
            </div>
        @endif
    @endif

    <!-- Drop zones for ordering -->
    <div class="drop-zone drop-zone-after h-2 -mx-4 -mb-4 mt-2 opacity-0 transition-opacity"
         data-position="after" data-task-id="{{ $task->id }}"></div>
</div>
