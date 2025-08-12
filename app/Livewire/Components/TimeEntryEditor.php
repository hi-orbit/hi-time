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
    public $entryDate;
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

        // For running timers, calculate current elapsed time and round to 2 decimal places
        if ($this->timeEntry->is_running) {
            $elapsedMinutes = $this->timeEntry->start_time->diffInMinutes(now());
            $this->duration = round($elapsedMinutes, 2);
        } else {
            $this->duration = round($this->timeEntry->duration_minutes, 2);
        }

        $this->description = $this->timeEntry->description ?? '';
        $this->entryDate = $this->timeEntry->entry_date ? $this->timeEntry->entry_date->format('Y-m-d') : $this->timeEntry->created_at->format('Y-m-d');
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
            'duration' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:1000',
            'entryDate' => 'required|date',
        ]);

        $updateData = [
            'description' => $this->description,
            'entry_date' => $this->entryDate,
        ];

        // Handle running vs stopped timers differently
        if ($this->timeEntry->is_running) {
            // For running timers, adjust the start_time to reflect the new duration
            $newStartTime = now()->subMinutes((float) $this->duration);
            $updateData['start_time'] = $newStartTime;
        } else {
            // For stopped timers, just update the duration_minutes
            $updateData['duration_minutes'] = (float) $this->duration;
        }

        $this->timeEntry->update($updateData);

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
        $this->entryDate = '';
    }

    public function render()
    {
        return view('livewire.components.time-entry-editor');
    }
}
