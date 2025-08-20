<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center space-x-4">
                        <h2 class="text-2xl font-bold text-gray-900">
                            {{ $showArchived ? 'Archived Projects' : 'Projects' }}
                        </h2>
                        <!-- Archive Toggle -->
                        <button wire:click="toggleArchivedView"
                                class="flex items-center px-3 py-1 text-sm font-medium rounded-md {{ $showArchived ? 'bg-gray-200 text-gray-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"></path>
                                <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $showArchived ? 'Show Active' : 'Show Archived' }}
                        </button>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- View Toggle -->
                        <div class="flex bg-gray-100 rounded-lg p-1">
                            <button wire:click="setViewMode('cards')"
                                    class="px-3 py-1 text-sm font-medium rounded-md transition-colors {{ $viewMode === 'cards' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                </svg>
                                Cards
                            </button>
                            <button wire:click="setViewMode('list')"
                                    class="px-3 py-1 text-sm font-medium rounded-md transition-colors {{ $viewMode === 'list' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                List
                            </button>
                        </div>
                        @if(auth()->user()->isAdmin() && !$showArchived && !auth()->user()->isCustomer())
                            <a href="{{ route('projects.create') }}"
                               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-medium">
                                + New Project
                            </a>
                        @endif
                    </div>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('message') }}
                    </div>
                @endif

                @if($projects->count() > 0)
                    @if($viewMode === 'cards')
                        <!-- Card View -->
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($projects as $project)
                                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                                <a href="{{ route('projects.show', $project) }}"
                                                   class="hover:text-indigo-600">
                                                    {{ $project->name }}
                                                </a>
                                            </h3>
                                            @if($project->description)
                                                <p class="text-gray-600 text-sm mb-3">{{ $project->description }}</p>
                                            @endif
                                            @if($project->customer)
                                                <div class="mb-3">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        {{ $project->customer->name }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="flex items-center justify-between text-sm text-gray-500">
                                                <span>{{ $project->tasks_count }} tasks</span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @switch($project->status)
                                                        @case('active') bg-green-100 text-green-800 @break
                                                        @case('completed') bg-blue-100 text-blue-800 @break
                                                        @case('on_hold') bg-yellow-100 text-yellow-800 @break
                                                    @endswitch">
                                                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                                </span>
                                            </div>
                                            <div class="mt-2 text-xs text-gray-400">
                                                Created by {{ $project->creator->name }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex items-center justify-between">
                                        <a href="{{ route('projects.show', $project) }}"
                                           class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            View Kanban Board →
                                        </a>
                                        @if(auth()->user()->isAdmin())
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('projects.edit', $project) }}"
                                                   class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                                                    Edit
                                                </a>
                                                @if($showArchived)
                                                    <button wire:click="unarchiveProject({{ $project->id }})"
                                                            onclick="return confirm('Are you sure you want to unarchive this project?')"
                                                            class="text-green-600 hover:text-green-900 text-sm font-medium">
                                                        Unarchive
                                                    </button>
                                                @else
                                                    <div class="flex space-x-2">
                                                        <button wire:click="archiveProject({{ $project->id }})"
                                                                onclick="return confirm('Are you sure you want to archive this project? It will be hidden from the main view.')"
                                                                class="text-orange-600 hover:text-orange-900 text-sm font-medium">
                                                            Archive
                                                        </button>
                                                        <button wire:click="deleteProject({{ $project->id }})"
                                                                onclick="return confirm('Are you sure you want to delete this project? This will permanently delete all tasks, time entries, and notes associated with this project.')"
                                                                class="text-red-600 hover:text-red-900 text-sm font-medium">
                                                            Delete
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- List View -->
                        <div class="bg-white shadow overflow-hidden sm:rounded-md">
                            <ul class="divide-y divide-gray-200">
                                @foreach($projects as $project)
                                    <li>
                                        <div class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center justify-between">
                                                        <h3 class="text-lg font-medium text-indigo-600 truncate">
                                                            <a href="{{ route('projects.show', $project) }}" class="hover:text-indigo-900">
                                                                {{ $project->name }}
                                                            </a>
                                                        </h3>
                                                        <div class="ml-2 flex-shrink-0 flex">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                                @switch($project->status)
                                                                    @case('active') bg-green-100 text-green-800 @break
                                                                    @case('completed') bg-blue-100 text-blue-800 @break
                                                                    @case('on_hold') bg-yellow-100 text-yellow-800 @break
                                                                @endswitch">
                                                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    @if($project->description)
                                                        <p class="mt-1 text-sm text-gray-600">
                                                            {{ Str::limit($project->description, 120) }}
                                                        </p>
                                                    @endif
                                                    @if($project->customer)
                                                        <div class="mt-1">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                                </svg>
                                                                {{ $project->customer->name }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    <div class="mt-2 sm:flex sm:justify-between">
                                                        <div class="sm:flex">
                                                            <p class="flex items-center text-sm text-gray-500">
                                                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                                                </svg>
                                                                {{ $project->tasks_count }} tasks
                                                            </p>
                                                            <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                                </svg>
                                                                Created by {{ $project->creator->name }}
                                                            </p>
                                                        </div>
                                                        <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                                            <p class="text-sm text-gray-500">
                                                                {{ $project->created_at->format('M j, Y') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="ml-4 flex items-center space-x-3">
                                                    <a href="{{ route('projects.show', $project) }}"
                                                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                                                        View Board
                                                    </a>
                                                    @if(auth()->user()->isAdmin())
                                                        <a href="{{ route('projects.edit', $project) }}"
                                                           class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200">
                                                            Edit
                                                        </a>
                                                        @if($showArchived)
                                                            <button wire:click="unarchiveProject({{ $project->id }})"
                                                                    onclick="return confirm('Are you sure you want to unarchive this project?')"
                                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200">
                                                                Unarchive
                                                            </button>
                                                        @else
                                                            <button wire:click="archiveProject({{ $project->id }})"
                                                                    onclick="return confirm('Are you sure you want to archive this project? It will be hidden from the main view.')"
                                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-orange-700 bg-orange-100 hover:bg-orange-200">
                                                                Archive
                                                            </button>
                                                            <button wire:click="deleteProject({{ $project->id }})"
                                                                    onclick="return confirm('Are you sure you want to delete this project? This will permanently delete all tasks, time entries, and notes associated with this project.')"
                                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200">
                                                                Delete
                                                            </button>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <div class="text-gray-400 text-6xl mb-4">
                            {{ $showArchived ? '�' : '�📋' }}
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                            {{ $showArchived ? 'No archived projects' : 'No projects yet' }}
                        </h3>
                        <p class="text-gray-500 mb-4">
                            {{ $showArchived ? 'No projects have been archived yet.' : 'Get started by creating your first project.' }}
                        </p>
                        @if(auth()->user()->isAdmin() && !$showArchived)
                            <button wire:click="openCreateModal"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-medium">
                                Create Project
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Create Project Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Project</h3>
                    <form wire:submit.prevent="createProject">
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Project Name</label>
                            <input wire:model="name" type="text" id="name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea wire:model="description" id="description" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" wire:click="closeCreateModal"
                                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Create Project
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
