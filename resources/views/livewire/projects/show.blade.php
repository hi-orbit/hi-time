<div class="py-12">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $project->name }}</h2>
                        @if($project->customer)
                            <div class="mt-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $project->customer->name }}
                                </span>
                                <div class="mt-1">
                                    <a href="{{ route('projects.index') }}"
                                       class="text-sm text-gray-500 hover:text-gray-700">
                                        ← Back to Projects
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="mt-1">
                                <a href="{{ route('projects.index') }}"
                                   class="text-sm text-gray-500 hover:text-gray-700">
                                    ← Back to Projects
                                </a>
                            </div>
                        @endif
                        @if($project->archived)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mt-2">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                Archived Project
                            </span>
                        @endif
                        @if($project->description)
                            <p class="text-gray-600 mt-1">{{ $project->description }}</p>
                        @endif
                    </div>
                    <div class="flex items-center space-x-3">
                        <!-- Search Section -->
                        <div class="relative">
                            <div class="relative">
                                <input type="text"
                                       id="search"
                                       wire:model.live="searchQuery"
                                       placeholder="Search tasks..."
                                       class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                @if($searchQuery)
                                    <button wire:click="clearSearch"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>

                            <!-- Search Results Dropdown -->
                            @if($showSearchResults && count($searchResults) > 0)
                                <div class="absolute z-50 mt-1 w-80 bg-white shadow-lg border border-gray-300 rounded-md max-h-60 overflow-auto">
                                    @foreach($searchResults as $task)
                                        <div wire:click="selectSearchResult({{ $task->id }})"
                                             class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                            <div class="font-medium text-sm text-gray-900">{{ $task->title }}</div>
                                            @if($task->description)
                                                <div class="text-xs text-gray-500 mt-1 line-clamp-2">{{ Str::limit($task->description, 100) }}</div>
                                            @endif
                                            <div class="flex items-center mt-2 space-x-2">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    {{ $task->status === 'todo' ? 'bg-gray-100 text-gray-800' : '' }}
                                                    {{ $task->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $task->status === 'done' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $task->status === 'backlog' ? 'bg-blue-100 text-blue-800' : '' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                </span>
                                                @if($task->assignedUser)
                                                    <span class="text-xs text-gray-500">{{ $task->assignedUser->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($showSearchResults && $searchQuery && count($searchResults) === 0)
                                <div class="absolute z-50 mt-1 w-80 bg-white shadow-lg border border-gray-300 rounded-md p-3">
                                    <div class="text-sm text-gray-500">No tasks found matching "{{ $searchQuery }}"</div>
                                </div>
                            @endif
                        </div>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('projects.edit', $project) }}"
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md font-medium">
                                Edit Project
                            </a>
                        @endif
                        @if(auth()->user()->isAdmin() || auth()->user()->isUser())
                            <button wire:click="openTimeModal()"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium">
                                + Log General Time
                            </button>
                        @endif
                        @if(auth()->user()->isAdmin() || auth()->user()->isUser() || auth()->user()->isCustomer())
                            <button wire:click="openTaskModal"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-medium">
                                + New Task
                            </button>
                        @endif
                    </div>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('message') }}
                    </div>
                @endif

                <!-- Kanban Board -->
                <div id="kanban-board" class="grid gap-4 xl:gap-6 transition-all duration-300" style="grid-template-columns: repeat(5, 1fr) 80px;" data-collapsed="true">
                    <style>
                        @media (min-width: 1536px) {
                            #kanban-board {
                                gap: 1.5rem;
                            }
                        }
                        @media (max-width: 1280px) {
                            #kanban-board {
                                gap: 1rem;
                            }
                        }
                        @media (max-width: 768px) {
                            #kanban-board {
                                grid-template-columns: 1fr !important;
                                gap: 1rem;
                            }
                        }
                    </style>
                    <!-- Backlog Column -->
                    <div class="bg-gray-50 rounded-lg p-3 lg:p-4 xl:p-5 min-h-96"
                         ondrop="handleDrop(event, 'backlog')"
                         ondragover="event.preventDefault()"
                         ondragenter="event.preventDefault()">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <span class="w-3 h-3 bg-gray-400 rounded-full mr-2"></span>
                            Backlog ({{ $columns['backlog']->count() }})
                        </h3>
                        <div class="space-y-3">
                            @foreach($columns['backlog'] as $task)
                                @include('livewire.projects.partials.task-card', ['task' => $task])
                            @endforeach
                        </div>
                    </div>

                    <!-- In Progress Column -->
                    <div class="bg-blue-50 rounded-lg p-3 lg:p-4 xl:p-5 min-h-96"
                         ondrop="handleDrop(event, 'in_progress')"
                         ondragover="event.preventDefault()"
                         ondragenter="event.preventDefault()">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <span class="w-3 h-3 bg-blue-400 rounded-full mr-2"></span>
                            In Progress ({{ $columns['in_progress']->count() }})
                        </h3>
                        <div class="space-y-3">
                            @foreach($columns['in_progress'] as $task)
                                @include('livewire.projects.partials.task-card', ['task' => $task])
                            @endforeach
                        </div>
                    </div>

                    <!-- In Test Column -->
                    <div class="bg-yellow-50 rounded-lg p-3 lg:p-4 xl:p-5 min-h-96"
                         ondrop="handleDrop(event, 'in_test')"
                         ondragover="event.preventDefault()"
                         ondragenter="event.preventDefault()">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <span class="w-3 h-3 bg-yellow-400 rounded-full mr-2"></span>
                            In Test ({{ $columns['in_test']->count() }})
                        </h3>
                        <div class="space-y-3">
                            @foreach($columns['in_test'] as $task)
                                @include('livewire.projects.partials.task-card', ['task' => $task])
                            @endforeach
                        </div>
                    </div>

                    <!-- Failed Testing Column -->
                    <div class="bg-red-50 rounded-lg p-3 lg:p-4 xl:p-5 min-h-96"
                         ondrop="handleDrop(event, 'failed_testing')"
                         ondragover="event.preventDefault()"
                         ondragenter="event.preventDefault()">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <span class="w-3 h-3 bg-red-400 rounded-full mr-2"></span>
                            Failed Testing ({{ $columns['failed_testing']->count() }})
                        </h3>
                        <div class="space-y-3">
                            @foreach($columns['failed_testing'] as $task)
                                @include('livewire.projects.partials.task-card', ['task' => $task])
                            @endforeach
                        </div>
                    </div>

                    <!-- Ready to Release Column -->
                    <div class="bg-purple-50 rounded-lg p-3 lg:p-4 xl:p-5 min-h-96"
                         ondrop="handleDrop(event, 'ready_to_release')"
                         ondragover="event.preventDefault()"
                         ondragenter="event.preventDefault()">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <span class="w-3 h-3 bg-purple-400 rounded-full mr-2"></span>
                            Ready to Release ({{ $columns['ready_to_release']->count() }})
                        </h3>
                        <div class="space-y-3">
                            @foreach($columns['ready_to_release'] as $task)
                                @include('livewire.projects.partials.task-card', ['task' => $task])
                            @endforeach
                        </div>
                    </div>

                    <!-- Done Column -->
                    <div id="done-column" class="bg-green-50 rounded-lg p-3 lg:p-4 xl:p-5 min-h-96 transition-all duration-300"
                         ondrop="handleDrop(event, 'done')"
                         ondragover="event.preventDefault()"
                         ondragenter="event.preventDefault()">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center justify-between cursor-pointer"
                            onclick="toggleDoneColumn()">
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-green-400 rounded-full mr-2"></span>
                                <span id="done-title">Done ({{ $columns['done']->count() }})</span>
                            </div>
                            <svg id="done-chevron" class="w-5 h-5 transform transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </h3>
                        <div id="done-tasks" class="space-y-3" style="display: none;">
                            @foreach($columns['done'] as $task)
                                @include('livewire.projects.partials.task-card', ['task' => $task])
                            @endforeach
                        </div>
                        <!-- Collapsed state indicator -->
                        <div id="done-collapsed-indicator" class="text-center text-gray-500 text-xs">
                            <div class="transform -rotate-90 origin-center whitespace-nowrap">
                                {{ $columns['done']->count() }}
                            </div>
                            @if($columns['done']->count() > 0)
                                <div class="mt-2 text-xs">↑</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Task Modal -->
    @if($showTaskModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ $editingTask ? 'Edit Task' : 'Create New Task' }}
                    </h3>
                    <form wire:submit.prevent="{{ $editingTask ? 'updateTask' : 'createTask' }}">
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Task Title</label>
                            <input wire:model="title" type="text" id="title"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea wire:model="description" id="description" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">Assign To</label>
                            <select wire:model="assigned_to" id="assigned_to"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Unassigned</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_to') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select wire:model="status" id="status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="backlog">Backlog</option>
                                <option value="in_progress">In Progress</option>
                                <option value="in_test">In Test</option>
                                <option value="failed_testing">Failed Testing</option>
                                <option value="ready_to_release">Ready to Release</option>
                                <option value="done">Done</option>
                            </select>
                            @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        @if($editingTask)
                        <div class="mb-4">
                            <label for="moveToProjectId" class="block text-sm font-medium text-gray-700 mb-2">Move to Project</label>
                            <select wire:model="moveToProjectId" id="moveToProjectId"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Keep in current project</option>
                                @foreach(App\Models\Project::where('id', '!=', $project->id)->where('status', 'active')->orderBy('name')->get() as $proj)
                                    <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                                @endforeach
                            </select>
                            @error('moveToProjectId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        @endif
                        <div class="flex justify-end space-x-3">
                            <button type="button" wire:click="closeTaskModal"
                                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                {{ $editingTask ? 'Update Task' : 'Create Task' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Log Time Modal -->
    @if($showTimeModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        @if($isGeneralActivity)
                            Log General Activity Time
                        @else
                            Log Time - {{ $selectedTask->title }}
                        @endif
                    </h3>
                    <form wire:submit.prevent="logTime">
                        @if($isGeneralActivity)
                            <div class="mb-4">
                                <label for="activityType" class="block text-sm font-medium text-gray-700 mb-2">Activity Type</label>
                                <select wire:model="activityType" id="activityType"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select Activity Type</option>
                                    @foreach($this->getStandardActivityTypes() as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('activityType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        <div class="mb-4">
                            <label for="timeDescription" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea wire:model="timeDescription" id="timeDescription" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                      placeholder="{{ $isGeneralActivity ? 'Describe the activity...' : 'What did you work on?' }}"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="hours" class="block text-sm font-medium text-gray-700 mb-2">Hours <span class="text-gray-500 text-xs">(optional)</span></label>
                                <input wire:model="hours" type="number" id="hours" min="0" max="23" placeholder="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                @error('hours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="minutes" class="block text-sm font-medium text-gray-700 mb-2">Minutes <span class="text-red-500">*</span></label>
                                <input wire:model="minutes" type="number" id="minutes" min="0" max="59" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                @error('minutes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" wire:click="closeTimeModal"
                                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Log Time
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Task Details Modal -->
    @if($showTaskDetailsModal && $selectedTask)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
             wire:key="task-details-modal-{{ $selectedTask->id }}"
             x-data="{
                 showSuccessMessage: false,
                 showErrorMessage: false,
                 successMessage: '',
                 errorMessage: ''
             }"
             x-on:note-added.window="showSuccessMessage = true; successMessage = $event.detail.message; setTimeout(() => showSuccessMessage = false, 3000)"
             x-on:assignment-updated.window="showSuccessMessage = true; successMessage = $event.detail.message; setTimeout(() => showSuccessMessage = false, 3000)"
             x-on:status-updated.window="showSuccessMessage = true; successMessage = $event.detail.message; setTimeout(() => showSuccessMessage = false, 3000)"
             x-on:time-logged.window="showSuccessMessage = true; successMessage = $event.detail.message; setTimeout(() => showSuccessMessage = false, 3000)"
             x-on:time-log-error.window="showErrorMessage = true; errorMessage = $event.detail.message; setTimeout(() => showErrorMessage = false, 3000)"
             x-on:files-uploaded.window="showSuccessMessage = true; successMessage = $event.detail.message; setTimeout(() => showSuccessMessage = false, 3000)"
             x-on:attachment-deleted.window="showSuccessMessage = true; successMessage = $event.detail.message; setTimeout(() => showSuccessMessage = false, 3000)"
             x-on:attachment-error.window="showErrorMessage = true; errorMessage = $event.detail.message; setTimeout(() => showErrorMessage = false, 3000)"
             x-on:upload-error.window="showErrorMessage = true; errorMessage = $event.detail.message; setTimeout(() => showErrorMessage = false, 3000)">
            <div class="relative top-10 mx-auto p-5 border max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">


                    <!-- Buttons Row -->
                    <div class="flex justify-end items-center space-x-2 mb-6">
                        @if(!auth()->user()->isCustomer())
                            <!-- Time Tracking Buttons -->
                            @php
                                $hasRunningTimer = auth()->user()->timeEntries()
                                    ->where('task_id', $selectedTask->id)
                                    ->where('is_running', true)
                                    ->exists();
                            @endphp

                            @if($hasRunningTimer)
                                <button wire:click="stopTimer({{ $selectedTask->id }})"
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-medium">
                                    ⏹ Stop Timer
                                </button>
                            @else
                                <button wire:click="startTimer({{ $selectedTask->id }})"
                                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm font-medium">
                                    ▶ Start Timer
                                </button>
                            @endif
                        @endif

                        <!-- Shareable Link Button -->
                        <button onclick="copyShareableLink({{ $selectedTask->id }})"
                                class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm font-medium"
                                title="Copy shareable link">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                        </button>

                        @if(auth()->user()->isAdmin() || auth()->user()->id == $selectedTask->assigned_to || auth()->user()->id == $selectedTask->created_by)
                            <button wire:click="editTask({{ $selectedTask->id }})"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-sm font-medium">
                                Edit Task
                            </button>
                            <button wire:click="deleteTask({{ $selectedTask->id }})"
                                    onclick="return confirm('Are you sure you want to delete this task? This will permanently delete all time entries and notes associated with this task.')"
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm font-medium">
                                Delete Task
                            </button>
                        @endif
                        <button wire:click="closeTaskDetailsModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Title Row -->
                    <div class="mb-6">
                        <h3 class="text-xl font-medium text-gray-900">{{ $selectedTask->title }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $selectedTask->project->name }}</p>
                    </div>

                    <!-- Success message using Alpine.js instead of session flash -->
                    <div x-show="showSuccessMessage"
                         x-transition
                         class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        <span x-text="successMessage"></span>
                    </div>

                    <!-- Error message using Alpine.js -->
                    <div x-show="showErrorMessage"
                         x-transition
                         class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <span x-text="errorMessage"></span>
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

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column: Task Details and Time Tracking -->
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 mb-3">Task Details</h4>

                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <div class="mb-3">
                                    <label class="text-sm font-medium text-gray-700">Description:</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ $selectedTask->description ?: 'No description provided.' }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4 mb-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Status:</label>
                                        @if(auth()->user()->isAdmin() || auth()->user()->id == $selectedTask->assigned_to || auth()->user()->id == $selectedTask->created_by)
                                            <select wire:model="taskStatus" wire:change="updateTaskStatusFromModal"
                                                    wire:key="task-status-{{ $selectedTask->id }}"
                                                    class="block w-full mt-1 px-3 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="backlog">Backlog</option>
                                                <option value="in_progress">In Progress</option>
                                                <option value="in_test">In Test</option>
                                                <option value="failed_testing">Failed Testing</option>
                                                <option value="ready_to_release">Ready to Release</option>
                                                <option value="done">Done</option>
                                            </select>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1
                                                @switch($selectedTask->status)
                                                    @case('backlog') bg-gray-100 text-gray-800 @break
                                                    @case('in_progress') bg-blue-100 text-blue-800 @break
                                                    @case('in_test') bg-yellow-100 text-yellow-800 @break
                                                    @case('ready_to_release') bg-purple-100 text-purple-800 @break
                                                    @case('done') bg-green-100 text-green-800 @break
                                                @endswitch">
                                                {{ ucfirst(str_replace('_', ' ', $selectedTask->status)) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Assigned to:</label>
                                        @if(auth()->user()->isAdmin() || auth()->user()->id == $selectedTask->assigned_to || auth()->user()->id == $selectedTask->created_by)
                                            <select wire:model="taskAssignment" wire:change="updateTaskAssignment"
                                                    wire:key="task-assignment-{{ $selectedTask->id }}"
                                                    class="block w-full mt-1 px-3 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="">Unassigned</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <p class="text-sm text-gray-900 mt-1">{{ $selectedTask->assignedUser->name ?? 'Unassigned' }}</p>
                                        @endif
                                    </div>
                                </div>

                                @if(!auth()->user()->isCustomer())
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Total time tracked:</label>
                                        <p class="text-sm text-gray-900 mt-1">{{ number_format(($selectedTask->getTotalTimeFromNotesAttribute() + $selectedTask->total_time) / 60, 1) }}h</p>
                                    </div>
                                @endif
                            </div>
                        </div>


                        <!-- Right Column: Notes and Attachments -->
                        <div>
                            <!-- Notes Section -->
                            <div wire:key="notes-section-{{ $selectedTask->id }}">
                                <h4 class="text-lg font-medium text-gray-900 mb-3">Notes & Time</h4>

                                <!-- Add Note Form -->
                                <form wire:submit.prevent="addNote" class="mb-4" wire:key="add-note-form-{{ $selectedTask->id }}">
                                    <div class="mb-3">
                                        <label for="newNote" class="block text-sm font-medium text-gray-700 mb-2">Add a note</label>
                                        <textarea wire:model="newNote" id="newNote" rows="3"
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                                  placeholder="Add a note..."></textarea>
                                        @error('newNote') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    @if(!auth()->user()->isCustomer())
                                        <div class="mb-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Time spent (optional)</label>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <input wire:model="newNoteHours" type="number" min="0" max="23" placeholder="Hours"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                                    @error('newNoteHours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <input wire:model="newNoteMinutes" type="number" min="0" max="59" placeholder="Minutes"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                                    @error('newNoteMinutes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <button type="submit"
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                        Add Note
                                    </button>
                                </form>

                            </div>
                        </div>
                        <div class="lg:col-span-2">
                                <!-- Notes List -->
                                <div wire:key="notes-list-{{ $selectedTask->id }}">
                                    @if($selectedTask->notes && $selectedTask->notes->count() > 0)
                                        @foreach($selectedTask->notes->sortByDesc('created_at') as $note)
                                            <div class="bg-white border rounded-lg p-4 mb-3 w-full" wire:key="note-{{ $note->id }}">
                                                <div class="flex items-start justify-between mb-3">
                                                    <div class="flex items-center space-x-3">
                                                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-300 text-sm font-medium text-gray-700">
                                                            {{ substr($note->user->name, 0, 1) }}
                                                        </span>
                                                        <div>
                                                            <div class="flex items-center space-x-2">
                                                                <p class="text-sm font-medium text-gray-900">{{ $note->user->name }}</p>
                                                                <p class="text-xs text-gray-500">{{ $note->created_at->format('M j, Y H:i') }}</p>
                                                                @if(!auth()->user()->isCustomer() && $note->total_minutes)
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                        {{ $note->formatted_time }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if($note->user_id === auth()->id())
                                                        <button wire:click="deleteNote({{ $note->id }})"
                                                                onclick="return confirm('Are you sure you want to delete this note?')"
                                                                class="text-red-600 hover:text-red-800 p-1 rounded"
                                                                title="Delete note">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-gray-700 w-full">{{ $note->content }}</p>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-gray-500 text-center py-8">No notes yet. Add the first note above.</p>
                                    @endif
                                </div>
                            </div>

                        </div>


                    <!-- Attachments Section (Full Width) -->
                    <div class="mt-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-3">Attachments</h4>

                        <!-- Upload Form -->
                        <div class="mb-4">
                            <div class="space-y-3">
                                <!-- Dropzone Upload -->
                                <div class="border border-gray-300 rounded-lg p-4 bg-blue-50"
                                     x-data="{
                                         refreshTimer: null,
                                         isModalOpen: false,
                                         startAutoRefresh() {
                                             this.refreshTimer = setInterval(() => {
                                                 // Only refresh if no modal is open
                                                 const modal = document.getElementById('imagePreviewModal');
                                                 if (!modal || modal.classList.contains('hidden')) {
                                                     $wire.refreshDropzoneState();
                                                 }
                                             }, 2000); // Check every 2 seconds
                                         },
                                         stopAutoRefresh() {
                                             if (this.refreshTimer) {
                                                 clearInterval(this.refreshTimer);
                                                 this.refreshTimer = null;
                                             }
                                         }
                                     }"
                                     x-init="startAutoRefresh()"
                                     x-on:beforeunload.window="stopAutoRefresh()">
                                    <h5 class="text-sm font-medium text-gray-900 mb-2">Upload Files (Auto-upload)</h5>
                                    <livewire:dropzone
                                        wire:model="dropzoneFiles"
                                        :rules="['file', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt,zip,csv,xlsx,xls,mp4,avi,mov,wmv,flv,webm,mkv,m4v,3gp']"
                                        :multiple="true"
                                        :key="'task-dropzone-' . ($selectedTask->id ?? 'new')" />

                                    <!-- Auto-upload info -->
                                    <div class="mt-3 space-y-2">
                                        <!-- Info message -->
                                        <div class="text-sm text-gray-600 bg-blue-50 border border-blue-200 rounded-md p-2">
                                            <svg class="inline w-4 h-4 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                            Files will be uploaded automatically when added to the dropzone
                                            <br><strong>Size limits:</strong> Images/Documents up to 10MB, Videos up to 20MB
                                        </div>

                                        <!-- Debug info -->
                                        <div class="text-xs text-gray-500">
                                            Debug: {{ count($dropzoneFiles ?? []) }} files in dropzoneFiles array
                                            <span class="ml-2 text-green-600">• Auto-refresh every 2s</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attachments List -->
                        <div class="space-y-2">
                        @if($selectedTask->attachments && $selectedTask->attachments->count() > 0)
                            @foreach($selectedTask->attachments as $attachment)
                                <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                    <div class="flex items-center space-x-3">
                                        <!-- File Icon -->
                                        <div class="flex-shrink-0">
                                            @if($attachment->is_image)
                                                <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                                </svg>
                                            @elseif($attachment->is_video)
                                                <svg class="w-8 h-8 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path>
                                                </svg>
                                            @else
                                                <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        </div>

                                        <!-- File Info -->
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $attachment->original_name }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $attachment->file_size_human }} •
                                                Uploaded by {{ $attachment->uploader->name }} •
                                                {{ $attachment->created_at->format('M j, Y H:i') }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center space-x-2">
                                        <!-- Preview for images -->
                                        @if($attachment->is_image)
                                            <button type="button" onclick="showImagePreview('{{ Storage::disk('public')->url($attachment->file_path) }}', '{{ $attachment->original_name }}')"
                                                    class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                                                Preview
                                            </button>
                                        @elseif($attachment->is_video)
                                            <button type="button" onclick="showVideoPreview('{{ Storage::disk('public')->url($attachment->file_path) }}', '{{ $attachment->original_name }}')"
                                                    class="text-purple-600 hover:text-purple-500 text-sm font-medium">
                                                Play
                                            </button>
                                        @endif

                                        <!-- Download -->
                                        <a href="{{ Storage::disk('public')->url($attachment->file_path) }}"
                                           download="{{ $attachment->original_name }}"
                                           class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                                            Download
                                        </a>

                                        <!-- Delete (only for uploader or admin) -->
                                        @if($attachment->uploaded_by === auth()->id() || auth()->user()->isAdmin())
                                            <button type="button" wire:click="deleteAttachment({{ $attachment->id }})"
                                                    onclick="return confirm('Are you sure you want to delete this attachment?')"
                                                    class="text-red-600 hover:text-red-500 text-sm font-medium">
                                                Delete
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-gray-500 text-center py-8">No attachments yet. Drag and drop files above to upload them automatically.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Image Preview Modal -->
    <div id="imagePreviewModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 flex items-center justify-center" onclick="closeImagePreview()">
        <div class="max-w-4xl max-h-full p-4">
            <div class="bg-white rounded-lg overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 id="imagePreviewTitle" class="text-lg font-medium text-gray-900"></h3>
                    <button onclick="closeImagePreview()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    <img id="imagePreviewImage" src="" alt="" class="max-w-full max-h-96 mx-auto">
                </div>
            </div>
        </div>
    </div>

    <!-- Video Preview Modal -->
    <div id="videoPreviewModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 flex items-center justify-center" onclick="closeVideoPreview()">
        <div class="max-w-4xl max-h-full p-4">
            <div class="bg-white rounded-lg overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 id="videoPreviewTitle" class="text-lg font-medium text-gray-900"></h3>
                    <button onclick="closeVideoPreview()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    <video id="videoPreviewPlayer" controls class="max-w-full max-h-96 mx-auto" controlslist="nodownload">
                        <source id="videoPreviewSource" src="" type="">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showImagePreview(imageUrl, title) {
    document.getElementById('imagePreviewImage').src = imageUrl;
    document.getElementById('imagePreviewTitle').textContent = title;
    document.getElementById('imagePreviewModal').classList.remove('hidden');
}

function closeImagePreview() {
    document.getElementById('imagePreviewModal').classList.add('hidden');
    document.getElementById('imagePreviewImage').src = '';
}

function showVideoPreview(videoUrl, title) {
    const videoPlayer = document.getElementById('videoPreviewPlayer');
    const videoSource = document.getElementById('videoPreviewSource');
    const videoTitle = document.getElementById('videoPreviewTitle');

    // Set the video source and type
    videoSource.src = videoUrl;
    // Try to determine the video type from the URL
    const extension = videoUrl.split('.').pop().toLowerCase();
    const mimeTypes = {
        'mp4': 'video/mp4',
        'webm': 'video/webm',
        'ogg': 'video/ogg',
        'avi': 'video/avi',
        'mov': 'video/quicktime',
        'wmv': 'video/x-ms-wmv',
        'flv': 'video/x-flv',
        'mkv': 'video/x-matroska',
        'm4v': 'video/x-m4v',
        '3gp': 'video/3gpp'
    };
    videoSource.type = mimeTypes[extension] || 'video/mp4';

    videoTitle.textContent = title;
    videoPlayer.load(); // Reload the video element
    document.getElementById('videoPreviewModal').classList.remove('hidden');
}

function closeVideoPreview() {
    const videoPlayer = document.getElementById('videoPreviewPlayer');
    const videoSource = document.getElementById('videoPreviewSource');

    videoPlayer.pause();
    videoPlayer.currentTime = 0;
    videoSource.src = '';
    videoPlayer.load();
    document.getElementById('videoPreviewModal').classList.add('hidden');
}
</script>

<script>
let draggedTaskId = null;
let draggedElement = null;

function handleDrop(event, newStatus) {
    event.preventDefault();
    event.stopPropagation();

    const taskId = event.dataTransfer.getData('text/plain');
    const dropTarget = event.target.closest('.drop-zone');

    if (taskId) {
        let targetTaskId = null;
        let position = 'after';

        // Check if dropped on a specific drop zone
        if (dropTarget) {
            targetTaskId = dropTarget.dataset.taskId;
            position = dropTarget.dataset.position;
        }

        @this.call('updateTaskOrder', taskId, newStatus, targetTaskId, position);
    }

    // Clean up visual indicators
    hideAllDropZones();
    cleanupColumnStyles();
}

function showDropZones(except = null) {
    document.querySelectorAll('.drop-zone').forEach(zone => {
        const card = zone.closest('.task-card');
        if (!except || card?.dataset.taskId !== except) {
            zone.classList.remove('opacity-0');
            zone.classList.add('opacity-100', 'bg-indigo-100', 'border-2', 'border-dashed', 'border-indigo-400');
        }
    });
}

function hideAllDropZones() {
    document.querySelectorAll('.drop-zone').forEach(zone => {
        zone.classList.add('opacity-0');
        zone.classList.remove('opacity-100', 'bg-indigo-100', 'border-2', 'border-dashed', 'border-indigo-400');
    });
}

function cleanupColumnStyles() {
    document.querySelectorAll('[ondrop]').forEach(column => {
        column.classList.remove('bg-opacity-75', 'border-2', 'border-dashed', 'border-indigo-400');
    });
}

// Add visual feedback during drag
document.addEventListener('DOMContentLoaded', function() {
    const columns = document.querySelectorAll('[ondrop]');

    // Column drag events
    columns.forEach(column => {
        column.addEventListener('dragenter', function(e) {
            e.preventDefault();
            this.classList.add('bg-opacity-75', 'border-2', 'border-dashed', 'border-indigo-400');
        });

        column.addEventListener('dragleave', function(e) {
            e.preventDefault();
            // Only remove styles if we're actually leaving the column
            if (!this.contains(e.relatedTarget)) {
                this.classList.remove('bg-opacity-75', 'border-2', 'border-dashed', 'border-indigo-400');
            }
        });

        column.addEventListener('drop', function(e) {
            this.classList.remove('bg-opacity-75', 'border-2', 'border-dashed', 'border-indigo-400');
        });
    });

    // Task drag events
    document.addEventListener('dragstart', function(e) {
        if (e.target.classList.contains('task-card')) {
            draggedTaskId = e.target.dataset.taskId;
            draggedElement = e.target;
            e.target.style.opacity = '0.5';

            // Show drop zones for other tasks
            setTimeout(() => showDropZones(draggedTaskId), 0);
        }
    });

    document.addEventListener('dragend', function(e) {
        if (e.target.classList.contains('task-card')) {
            e.target.style.opacity = '1';
            hideAllDropZones();
            cleanupColumnStyles();
            draggedTaskId = null;
            draggedElement = null;
        }
    });

    // Drop zone events
    document.addEventListener('dragover', function(e) {
        if (e.target.classList.contains('drop-zone')) {
            e.preventDefault();
            e.stopPropagation();

            // Highlight the specific drop zone
            e.target.classList.add('bg-indigo-200', 'border-indigo-600');
        }
    });

    document.addEventListener('dragleave', function(e) {
        if (e.target.classList.contains('drop-zone')) {
            e.target.classList.remove('bg-indigo-200', 'border-indigo-600');
        }
    });

    document.addEventListener('drop', function(e) {
        if (e.target.classList.contains('drop-zone')) {
            e.preventDefault();
            e.stopPropagation();

            const taskId = e.dataTransfer.getData('text/plain');
            const targetTaskId = e.target.dataset.taskId;
            const position = e.target.dataset.position;

            // Get the column status from the closest column
            const column = e.target.closest('[ondrop]');
            const newStatus = column?.getAttribute('ondrop')?.match(/'([^']+)'/)?.[1];

            if (taskId && newStatus) {
                @this.call('updateTaskOrder', taskId, newStatus, targetTaskId, position);
            }

            // Clean up
            e.target.classList.remove('bg-indigo-200', 'border-indigo-600');
            hideAllDropZones();
            cleanupColumnStyles();
        }
    });
});

// Toggle Done Column
function toggleDoneColumn() {
    const doneTasks = document.getElementById('done-tasks');
    const doneChevron = document.getElementById('done-chevron');
    const doneIndicator = document.getElementById('done-collapsed-indicator');
    const doneColumn = document.getElementById('done-column');
    const kanbanBoard = document.getElementById('kanban-board');
    const doneTitle = document.getElementById('done-title');

    const isCollapsed = kanbanBoard.getAttribute('data-collapsed') === 'true';

    if (isCollapsed) {
        // Expand
        doneTasks.style.display = 'block';
        doneIndicator.style.display = 'none';
        doneChevron.style.transform = 'rotate(180deg)';
        doneTitle.style.display = 'block';

        // Restore full width grid
        kanbanBoard.style.gridTemplateColumns = 'repeat(6, 1fr)';
        kanbanBoard.setAttribute('data-collapsed', 'false');

        // Add responsive classes for mobile
        kanbanBoard.classList.add('md:grid-cols-6');
    } else {
        // Collapse
        doneTasks.style.display = 'none';
        doneIndicator.style.display = 'block';
        doneChevron.style.transform = 'rotate(0deg)';
        doneTitle.style.display = 'none';

        // Collapse to narrow width
        kanbanBoard.style.gridTemplateColumns = 'repeat(5, 1fr) 80px';
        kanbanBoard.setAttribute('data-collapsed', 'true');

        // Remove responsive classes for mobile
        kanbanBoard.classList.remove('md:grid-cols-6');
    }
}

// Initialize collapsed state on page load
document.addEventListener('DOMContentLoaded', function() {
    // Already starts collapsed due to initial inline styles
    const doneTasks = document.getElementById('done-tasks');
    const doneIndicator = document.getElementById('done-collapsed-indicator');
    const doneTitle = document.getElementById('done-title');

    doneTasks.style.display = 'none';
    doneIndicator.style.display = 'block';
    doneTitle.style.display = 'none';
});

// Copy shareable link function - called directly from button click
function copyShareableLink(taskId) {
    // Get the current project ID from the URL or construct the URL
    const currentUrl = window.location.href;
    const projectId = currentUrl.split('/projects/')[1]?.split('/')[0] || currentUrl.split('/projects/')[1]?.split('?')[0];
    const shareableUrl = `${window.location.origin}/projects/${projectId}?task=${taskId}`;

    console.log('Copying link:', shareableUrl); // Debug log

    // Use modern Clipboard API if available
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(shareableUrl).then(function() {
            console.log('Successfully copied to clipboard'); // Debug log
            showSuccessMessage('Shareable link copied to clipboard!');
        }).catch(function(err) {
            console.error('Failed to copy text: ', err);
            // Fallback to older method
            fallbackCopyTextToClipboard(shareableUrl, 'Shareable link copied to clipboard!');
        });
    } else {
        // Fallback for older browsers or non-secure contexts
        fallbackCopyTextToClipboard(shareableUrl, 'Shareable link copied to clipboard!');
    }
}

function showSuccessMessage(message) {
    // Find the Alpine.js modal and show success message
    const modal = document.querySelector('[x-data*="showSuccessMessage"]');
    if (modal && modal.__x) {
        modal.__x.$data.showSuccessMessage = true;
        modal.__x.$data.successMessage = message;
        setTimeout(() => {
            if (modal.__x) {
                modal.__x.$data.showSuccessMessage = false;
            }
        }, 3000);
    }
}

// Copy to clipboard functionality - Listen for Livewire events on window (keeping for other uses)
window.addEventListener('copy-to-clipboard', function(event) {
    const url = event.detail.url;
    const message = event.detail.message;

    console.log('Copy to clipboard event received:', { url, message }); // Debug log

    // Use modern Clipboard API if available, fallback to execCommand
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(function() {
            console.log('Successfully copied to clipboard'); // Debug log
            // Show success message using Alpine.js dispatch
            window.dispatchEvent(new CustomEvent('show-success', {
                detail: { message: message }
            }));
        }).catch(function(err) {
            console.error('Failed to copy text: ', err);
            // Fallback to execCommand
            fallbackCopyTextToClipboard(url, message);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyTextToClipboard(url, message);
    }
});

function fallbackCopyTextToClipboard(text, message) {
    console.log('Using fallback copy method'); // Debug log
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.left = '-999999px';
    textarea.style.top = '-999999px';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();

    try {
        const successful = document.execCommand('copy');
        console.log('Fallback copy result:', successful); // Debug log
        if (successful) {
            // Show success message
            window.dispatchEvent(new CustomEvent('show-success', {
                detail: { message: message }
            }));
        }
    } catch (err) {
        console.error('Fallback: Failed to copy text: ', err);
    }

    document.body.removeChild(textarea);
}

// Simple function to copy shareable link
function copyShareableLink(taskId) {
    const currentUrl = window.location.href.split('?')[0]; // Remove existing query params
    const shareableUrl = `${currentUrl}?task=${taskId}`;

    navigator.clipboard.writeText(shareableUrl).then(function() {
        // Show success message
        const modal = document.querySelector('[x-data*="showSuccessMessage"]');
        if (modal && modal.__x) {
            modal.__x.$data.showSuccessMessage = true;
            modal.__x.$data.successMessage = 'Shareable link copied to clipboard!';
            setTimeout(() => {
                if (modal.__x) {
                    modal.__x.$data.showSuccessMessage = false;
                }
            }, 3000);
        }
    }).catch(function(err) {
        console.error('Failed to copy link: ', err);
    });
}

// Listen for Alpine.js show-success event
document.addEventListener('show-success', function(event) {
    // This will be handled by Alpine.js in the modal
    const modal = document.querySelector('[x-data*="showSuccessMessage"]');
    if (modal) {
        // Trigger the Alpine.js success message display
        modal.__x.$data.showSuccessMessage = true;
        modal.__x.$data.successMessage = event.detail.message;
        setTimeout(() => {
            if (modal.__x) {
                modal.__x.$data.showSuccessMessage = false;
            }
        }, 3000);
    }
});
</script>

```
