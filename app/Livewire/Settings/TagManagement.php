<?php

namespace App\Livewire\Settings;

use App\Models\Tag;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class TagManagement extends Component
{
    use WithPagination;

    public $showCreateForm = false;
    public $editingTag = null;
    public $name = '';
    public $color = '#3B82F6';
    public $description = '';
    public $search = '';
    public $sortBy = 'name';
    public $sortDirection = 'asc';

    protected $rules = [
        'name' => 'required|string|max:255|unique:tags,name',
        'color' => 'required|string|max:7',
        'description' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'name.required' => 'Tag name is required.',
        'name.unique' => 'A tag with this name already exists.',
        'color.required' => 'Please select a color for the tag.',
    ];

    public function mount()
    {
        $this->color = Tag::getDefaultColors()[0];
    }

    public function render()
    {
        $tags = Tag::query()
            ->with('customer')
            ->when($this->search, fn($query) => $query->search($this->search))
            ->withCount('tasks')
            ->when($this->sortBy === 'customer', function($query) {
                $query->leftJoin('customers', 'tags.customer_id', '=', 'customers.id')
                      ->orderBy('customers.name', $this->sortDirection)
                      ->select('tags.*');
            }, function($query) {
                $query->orderBy($this->sortBy, $this->sortDirection);
            })
            ->paginate(15);

        return view('livewire.settings.tag-management', [
            'tags' => $tags,
            'defaultColors' => Tag::getDefaultColors(),
        ]);
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function showCreateForm()
    {
        $this->resetForm();
        $this->showCreateForm = true;
    }

    public function hideCreateForm()
    {
        $this->showCreateForm = false;
        $this->resetForm();
    }

    public function createTag()
    {
        $this->validate();

        Tag::create([
            'name' => $this->name,
            'color' => $this->color,
            'description' => $this->description,
        ]);

        session()->flash('message', 'Tag created successfully!');
        $this->resetForm();
        $this->showCreateForm = false;
    }

    public function editTag($tagId)
    {
        $tag = Tag::findOrFail($tagId);
        $this->editingTag = $tag;
        $this->name = $tag->name;
        $this->color = $tag->color;
        $this->description = $tag->description;
    }

    public function updateTag()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $this->editingTag->id,
            'color' => 'required|string|max:7',
            'description' => 'nullable|string|max:500',
        ]);

        $this->editingTag->update([
            'name' => $this->name,
            'color' => $this->color,
            'description' => $this->description,
        ]);

        session()->flash('message', 'Tag updated successfully!');
        $this->cancelEdit();
    }

    public function cancelEdit()
    {
        $this->editingTag = null;
        $this->resetForm();
    }

    public function deleteTag($tagId)
    {
        $tag = Tag::findOrFail($tagId);

        if ($tag->tasks()->count() > 0) {
            session()->flash('error', 'Cannot delete tag that is assigned to tasks. Remove it from all tasks first.');
            return;
        }

        $tag->delete();
        session()->flash('message', 'Tag deleted successfully!');
    }

    public function resetForm()
    {
        $this->name = '';
        $this->color = Tag::getDefaultColors()[0];
        $this->description = '';
        $this->resetErrorBag();
    }
}
