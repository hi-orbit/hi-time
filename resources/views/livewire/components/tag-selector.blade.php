<div class="space-y-3" x-data="{ open: false }">
    <div class="flex items-center justify-between">
        <label class="block text-sm font-medium text-gray-700">Tags</label>
        <button type="button"
                @click="open = !open"
                class="text-sm text-indigo-600 hover:text-indigo-800">
            <span x-show="!open">+ Add Tags</span>
            <span x-show="open">- Hide Tags</span>
        </button>
    </div>

    <!-- Selected Tags Display -->
    @if(count($selectedTags) > 0)
        <div class="flex flex-wrap gap-2">
            @foreach($selectedTagsData as $tag)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white"
                      style="background-color: {{ $tag['color'] }}">
                    {{ $tag['name'] }}
                    <button type="button"
                            wire:click="toggleTag({{ $tag['id'] }})"
                            class="ml-1.5 text-white hover:text-gray-200">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </span>
            @endforeach
        </div>
    @endif

    <!-- Tag Selection Interface -->
    <div x-show="open" x-transition class="border border-gray-200 rounded-lg p-4 bg-gray-50">
        <!-- Search -->
        <div class="mb-3">
            <input wire:model.live="searchQuery"
                   type="text"
                   placeholder="Search or create tags..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <!-- Available Tags -->
        <div class="space-y-2">
            @if($filteredTags->count() > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($filteredTags as $tag)
                        <button type="button"
                                wire:click="toggleTag({{ $tag['id'] }})"
                                class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium border transition-colors
                                       {{ in_array($tag['id'], $selectedTags)
                                          ? 'text-white border-transparent'
                                          : 'text-gray-700 border-gray-300 bg-white hover:bg-gray-50' }}"
                                style="{{ in_array($tag['id'], $selectedTags) ? 'background-color: ' . $tag['color'] : '' }}">
                            {{ $tag['name'] }}
                            @if(in_array($tag['id'], $selectedTags))
                                <svg class="ml-1 w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif

            <!-- Create New Tag -->
            @if($searchQuery && $filteredTags->where('name', $searchQuery)->count() === 0)
                <div class="border-t border-gray-200 pt-3">
                    @if(!$showCreateForm)
                        <button type="button"
                                wire:click="showCreateTagForm"
                                class="flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create "{{ $searchQuery }}"
                        </button>
                    @else
                        <div class="bg-white border border-gray-200 rounded-lg p-3">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Create New Tag</h4>

                            <div class="space-y-3">
                                <div>
                                    <input wire:model="newTagName"
                                           type="text"
                                           placeholder="Tag name"
                                           class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    @error('newTagName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-gray-500">Color:</span>
                                    @foreach($defaultColors as $color)
                                        <button type="button"
                                                wire:click="$set('newTagColor', '{{ $color }}')"
                                                class="w-6 h-6 rounded-full border-2 {{ $newTagColor === $color ? 'border-gray-800' : 'border-gray-300' }}"
                                                style="background-color: {{ $color }}"></button>
                                    @endforeach
                                </div>

                                <div class="flex space-x-2">
                                    <button type="button"
                                            wire:click="createTag"
                                            class="px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">
                                        Create
                                    </button>
                                    <button type="button"
                                            wire:click="hideCreateTagForm"
                                            class="px-3 py-1 border border-gray-300 text-gray-700 text-xs rounded hover:bg-gray-50">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
