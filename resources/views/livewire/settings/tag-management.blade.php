<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Tag Management</h1>
        <button wire:click="showCreateForm"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            + Create Tag
        </button>
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

    <!-- Search -->
    <div class="mb-6">
        <div class="relative">
            <input wire:model.live="search"
                   type="text"
                   placeholder="Search tags or companies..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Create Form -->
    @if($showCreateForm)
        <div class="mb-6 bg-gray-50 border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Tag</h3>

            <form wire:submit.prevent="createTag">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Tag Name</label>
                        <input wire:model="name"
                               type="text"
                               id="name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Enter tag name">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="color" class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                        <div class="flex space-x-2">
                            @foreach($defaultColors as $defaultColor)
                                <button type="button"
                                        wire:click="$set('color', '{{ $defaultColor }}')"
                                        class="w-8 h-8 rounded-full border-2 {{ $color === $defaultColor ? 'border-gray-800' : 'border-gray-300' }}"
                                        style="background-color: {{ $defaultColor }}"></button>
                            @endforeach
                        </div>
                        <input wire:model="color"
                               type="color"
                               id="color"
                               class="mt-2 w-16 h-8 border border-gray-300 rounded">
                        @error('color') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                    <textarea wire:model="description"
                              id="description"
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="Enter description..."></textarea>
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button"
                            wire:click="hideCreateForm"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Create Tag
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Tags List -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button wire:click="sortBy('name')" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Tag</span>
                            @if($sortBy === 'name')
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    @if($sortDirection === 'asc')
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                    @else
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    @endif
                                </svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button wire:click="sortBy('customer')" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Company</span>
                            @if($sortBy === 'customer')
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    @if($sortDirection === 'asc')
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                    @else
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    @endif
                                </svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button wire:click="sortBy('tasks_count')" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Usage Count</span>
                            @if($sortBy === 'tasks_count')
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    @if($sortDirection === 'asc')
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                    @else
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    @endif
                                </svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button wire:click="sortBy('created_at')" class="flex items-center space-x-1 hover:text-gray-700">
                            <span>Created</span>
                            @if($sortBy === 'created_at')
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    @if($sortDirection === 'asc')
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                    @else
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    @endif
                                </svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($tags as $tag)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            @if($editingTag && $editingTag->id === $tag->id)
                                <input wire:model="name"
                                       type="text"
                                       class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                            @else
                                <div class="flex items-center space-x-3">
                                    <div class="w-4 h-4 rounded-full" style="background-color: {{ $tag->color }}"></div>
                                    <span class="text-sm font-medium text-gray-900">{{ $tag->name }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($tag->customer)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $tag->customer->name }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    Global
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($editingTag && $editingTag->id === $tag->id)
                                <textarea wire:model="description"
                                          rows="2"
                                          class="w-full px-2 py-1 border border-gray-300 rounded text-sm"></textarea>
                            @else
                                <span class="text-sm text-gray-600">{{ $tag->description ?: '-' }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $tag->tasks_count }} {{ Str::plural('task', $tag->tasks_count) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $tag->created_at->format('M j, Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">
                            @if($editingTag && $editingTag->id === $tag->id)
                                <!-- Color picker for editing -->
                                <div class="flex items-center space-x-2 mb-2">
                                    @foreach($defaultColors as $defaultColor)
                                        <button type="button"
                                                wire:click="$set('color', '{{ $defaultColor }}')"
                                                class="w-6 h-6 rounded-full border {{ $color === $defaultColor ? 'border-gray-800' : 'border-gray-300' }}"
                                                style="background-color: {{ $defaultColor }}"></button>
                                    @endforeach
                                </div>
                                <div class="flex space-x-2">
                                    <button wire:click="updateTag"
                                            class="text-green-600 hover:text-green-800">Save</button>
                                    <button wire:click="cancelEdit"
                                            class="text-gray-600 hover:text-gray-800">Cancel</button>
                                </div>
                            @else
                                <div class="flex space-x-2">
                                    <button wire:click="editTag({{ $tag->id }})"
                                            class="text-indigo-600 hover:text-indigo-800">Edit</button>
                                    @if($tag->tasks_count === 0)
                                        <button wire:click="deleteTag({{ $tag->id }})"
                                                onclick="return confirm('Are you sure you want to delete this tag?')"
                                                class="text-red-600 hover:text-red-800">Delete</button>
                                    @else
                                        <span class="text-gray-400 cursor-not-allowed">Cannot delete</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            @if($search)
                                No tags found matching "{{ $search }}".
                            @else
                                No tags created yet. Create your first tag!
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(method_exists($tags, 'hasPages') && $tags->hasPages())
        <div class="mt-6">
            {{ $tags->links() }}
        </div>
    @endif
</div>
