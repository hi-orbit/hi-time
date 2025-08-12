<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;

class TimeEntryEditor extends Component
{
    public $timeEntry;
    public $isEditing = false;
    public $duration;
    public $description;
    public $showViewTaskLink = true;
    public $showDeleteButton = true;

    public function mount(TimeEntry $timeEntry, $showViewTaskLink = true, $showDeleteButton = true)
    {
        $this->timeEntry = $timeEntry;
        $this->showViewTaskLink = $showViewTaskLink;
        $this->showDeleteButton = $showDeleteButton;
        $this->resetEditFields();
    }

    public function startEdit()
    {
        if ($this->timeEntry->user_id !== Auth::id()) {
            $this->dispatch('error', 'You can only edit your own time entries.');
            return;
        }

        $this->isEditing = true;
        $this->duration = $this->timeEntry->duration_minutes;
        $this->description = $this->timeEntry->description ?? '';
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->resetEditFields();
    }

    public function saveEdit()
    {
        if ($this->timeEntry->user_id !== Auth::id()) {
            $this->dispatch('error', 'You can only edit your own time entries.');
            return;
        }

        $this->validate([
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string|max:1000',
        ]);

        $this->timeEntry->update([
            'duration_minutes' => (int) $this->duration,
            'description' => $this->description,
        ]);

        $this->isEditing = false;
        $this->resetEditFields();

        $this->dispatch('timeEntryUpdated', ['id' => $this->timeEntry->id]);
        $this->dispatch('success', 'Time entry updated successfully.');
    }

    public function deleteEntry()
    {
        if ($this->timeEntry->user_id !== Auth::id()) {
            $this->dispatch('error', 'You can only delete your own time entries.');
            return;
        }

        $entryId = $this->timeEntry->id;
        $this->timeEntry->delete();

        $this->dispatch('timeEntryDeleted', ['id' => $entryId]);
        $this->dispatch('success', 'Time entry deleted successfully.');
    }

    private function resetEditFields()
    {
        $this->duration = '';
        $this->description = '';
    }

    public function render()
    {
        return view('livewire.components.time-entry-editor');
    }
}
