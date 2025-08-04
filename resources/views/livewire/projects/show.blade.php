<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                @if($project->customer->contact_person)
                                    <span class="ml-2 text-sm text-gray-500">{{ $project->customer->contact_person }}</span>
                                @endif
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
                    <div class="flex space-x-3">
                        <a href="{{ route('projects.index') }}"
                           class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md font-medium">
                            ← Back to Projects
                        </a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('projects.edit', $project) }}"
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md font-medium">
                                Edit Project
                            </a>
                        @endif
                        @if(auth()->user()->isAdmin() || auth()->user()->isUser())
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
                <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                    <!-- Backlog Column -->
                    <div class="bg-gray-50 rounded-lg p-4 min-h-96"
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
                    <div class="bg-blue-50 rounded-lg p-4 min-h-96"
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
                    <div class="bg-yellow-50 rounded-lg p-4 min-h-96"
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

                    <!-- Ready to Release Column -->
                    <div class="bg-purple-50 rounded-lg p-4 min-h-96"
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
                    <div class="bg-green-50 rounded-lg p-4 min-h-96"
                         ondrop="handleDrop(event, 'done')"
                         ondragover="event.preventDefault()"
                         ondragenter="event.preventDefault()">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <span class="w-3 h-3 bg-green-400 rounded-full mr-2"></span>
                            Done ({{ $columns['done']->count() }})
                        </h3>
                        <div class="space-y-3">
                            @foreach($columns['done'] as $task)
                                @include('livewire.projects.partials.task-card', ['task' => $task])
                            @endforeach
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
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ ucfirst($user->role) }})</option>
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
                                <option value="ready_to_release">Ready to Release</option>
                                <option value="done">Done</option>
                            </select>
                            @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
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
    @if($showTimeModal && $selectedTask)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Log Time - {{ $selectedTask->title }}</h3>
                    <form wire:submit.prevent="logTime">
                        <div class="mb-4">
                            <label for="timeDescription" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea wire:model="timeDescription" id="timeDescription" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                      placeholder="What did you work on?"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="hours" class="block text-sm font-medium text-gray-700 mb-2">Hours</label>
                                <input wire:model="hours" type="number" id="hours" min="0" max="23"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                @error('hours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="minutes" class="block text-sm font-medium text-gray-700 mb-2">Minutes</label>
                                <input wire:model="minutes" type="number" id="minutes" min="0" max="59"
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
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-xl font-medium text-gray-900">{{ $selectedTask->title }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ $selectedTask->project->name }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
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

                    @if (session()->has('note_added'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            {{ session('note_added') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Task Details -->
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
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Assigned to:</label>
                                        @if(auth()->user()->isAdmin() || auth()->user()->id == $selectedTask->assigned_to || auth()->user()->id == $selectedTask->created_by)
                                            <select wire:model="taskAssignment" wire:change="updateTaskAssignment"
                                                    class="block w-full mt-1 px-3 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="">Unassigned</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ ucfirst($user->role) }})</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <p class="text-sm text-gray-900 mt-1">{{ $selectedTask->assignedUser->name ?? 'Unassigned' }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Total time tracked:</label>
                                    <p class="text-sm text-gray-900 mt-1">{{ number_format($selectedTask->total_time / 60, 1) }}h</p>
                                </div>
                            </div>

                            <!-- Time Entries -->
                            @if($selectedTask->timeEntries && $selectedTask->timeEntries->count() > 0)
                                <h5 class="text-md font-medium text-gray-900 mb-2">Recent Time Entries</h5>
                                <div class="max-h-48 overflow-y-auto">
                                    @foreach($selectedTask->timeEntries->take(5) as $entry)
                                        <div class="text-xs bg-white border rounded p-2 mb-2">
                                            <div class="flex justify-between">
                                                <span class="font-medium">{{ $entry->user->name }}</span>
                                                <span>{{ $entry->formatted_duration }}</span>
                                            </div>
                                            @if($entry->description)
                                                <p class="text-gray-600 mt-1">{{ $entry->description }}</p>
                                            @endif
                                            <p class="text-gray-500 mt-1">{{ $entry->created_at->format('M j, Y H:i') }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Notes Section -->
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 mb-3">Notes</h4>

                            <!-- Add Note Form -->
                            <form wire:submit.prevent="addNote" class="mb-4">
                                <div class="mb-3">
                                    <textarea wire:model="newNote" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                              placeholder="Add a note..."></textarea>
                                    @error('newNote') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <button type="submit"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    Add Note
                                </button>
                            </form>

                            <!-- Notes List -->
                            <div class="max-h-96 overflow-y-auto">
                                @if($selectedTask->notes && $selectedTask->notes->count() > 0)
                                    @foreach($selectedTask->notes->sortByDesc('created_at') as $note)
                                        <div class="bg-white border rounded-lg p-3 mb-3">
                                            <div class="flex items-start justify-between mb-2">
                                                <div class="flex items-center">
                                                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-300 text-sm font-medium text-gray-700">
                                                        {{ substr($note->user->name, 0, 1) }}
                                                    </span>
                                                    <div class="ml-3">
                                                        <p class="text-sm font-medium text-gray-900">{{ $note->user->name }}</p>
                                                        <p class="text-xs text-gray-500">{{ $note->created_at->format('M j, Y H:i') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-700">{{ $note->content }}</p>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-gray-500 text-center py-8">No notes yet. Add the first note above.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

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
</script>
