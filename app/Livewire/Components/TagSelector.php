<?php

namespace App\Livewire\Components;

use App\Models\Tag;
use Livewire\Component;

class TagSelector extends Component
{
    public $selectedTags = [];
    public $availableTags = [];
    public $showCreateForm = false;
    public $newTagName = '';
    public $newTagColor = '#3B82F6';
    public $searchQuery = '';

    protected $listeners = ['tagsUpdated' => 'loadTags'];

    public function mount($selectedTags = [])
    {
        $this->selectedTags = is_array($selectedTags) ? $selectedTags : $selectedTags->pluck('id')->toArray();
        $this->loadTags();
    }

    public function render()
    {
        $filteredTags = collect($this->availableTags)->filter(function ($tag) {
            return empty($this->searchQuery) ||
                   stripos($tag['name'], $this->searchQuery) !== false;
        });

        $selectedTagsData = collect($this->availableTags)->whereIn('id', $this->selectedTags)->values();

        return view('livewire.components.tag-selector', [
            'filteredTags' => $filteredTags,
            'defaultColors' => Tag::getDefaultColors(),
            'selectedTagsData' => $selectedTagsData,
        ]);
    }

    public function loadTags()
    {
        $this->availableTags = Tag::orderBy('name')->get()->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'color' => $tag->color,
            ];
        })->toArray();
    }

    public function toggleTag($tagId)
    {
        if (in_array($tagId, $this->selectedTags)) {
            $this->selectedTags = array_values(array_diff($this->selectedTags, [$tagId]));
        } else {
            $this->selectedTags[] = $tagId;
        }

        $this->dispatch('tagsSelected', $this->selectedTags);
        $this->dispatch('updateTaskTags', $this->selectedTags); // Dispatch to parent component
    }

    public function showCreateTagForm()
    {
        $this->showCreateForm = true;
        $this->newTagName = $this->searchQuery;
    }

    public function hideCreateTagForm()
    {
        $this->showCreateForm = false;
        $this->resetCreateForm();
    }

    public function createTag()
    {
        $this->validate([
            'newTagName' => 'required|string|max:255|unique:tags,name',
            'newTagColor' => 'required|string|max:7',
        ], [
            'newTagName.required' => 'Tag name is required.',
            'newTagName.unique' => 'A tag with this name already exists.',
        ]);

        $tag = Tag::create([
            'name' => $this->newTagName,
            'color' => $this->newTagColor,
        ]);

        $this->selectedTags[] = $tag->id;
        $this->loadTags();
        $this->hideCreateTagForm();
        $this->searchQuery = '';

        $this->dispatch('tagsSelected', $this->selectedTags);
        // Temporarily disable these dispatches to isolate the issue
        // $this->dispatch('updateTaskTags', $this->selectedTags);
        // $this->dispatch('tagsUpdated');
    }

    public function resetCreateForm()
    {
        $this->newTagName = '';
        $this->newTagColor = Tag::getDefaultColors()[0];
        $this->resetErrorBag();
    }
}
