<?php

namespace App\Libraries\TimelineLibrary\Components;

use Livewire\Component;
use App\Models\TaskNote;
use Illuminate\Support\Facades\Auth;

class TimeEntryEditor extends Component
{
    public $timeEntry;
    public $isEditing = false;
    public $duration;
    public $description;
    public $entryDate;

    // Configuration options
    public $showViewTaskLink = true;
    public $showDeleteButton = true;
    public $allowEditing = true;
    public $compactMode = false;
    public $customActions = [];

    public function mount(TaskNote $timeEntry, array $config = [])
    {
        $this->timeEntry = $timeEntry;

        // Apply configuration
        $this->showViewTaskLink = $config['show_view_task_link'] ?? true;
        $this->showDeleteButton = $config['show_delete_button'] ?? true;
        $this->allowEditing = $config['allow_editing'] ?? true;
        $this->compactMode = $config['compact_mode'] ?? false;
        $this->customActions = $config['custom_actions'] ?? [];

        $this->resetEditFields();
    }

    public function startEdit()
    {
        if (!$this->allowEditing) {
            $this->dispatch('error', 'Editing is not allowed for this entry.');
            return;
        }

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
            // Use total_minutes (primary) or duration_minutes (fallback)
            $minutes = $this->timeEntry->total_minutes ?? $this->timeEntry->duration_minutes ?? 0;
            $this->duration = round($minutes, 2);
        }

        $this->description = $this->timeEntry->description ?? $this->timeEntry->content ?? '';
        $this->entryDate = $this->timeEntry->entry_date ? $this->timeEntry->entry_date->format('Y-m-d') : $this->timeEntry->created_at->format('Y-m-d');
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->resetEditFields();
    }

    public function saveEdit()
    {
        if (!$this->allowEditing) {
            $this->dispatch('error', 'Editing is not allowed for this entry.');
            return;
        }

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
            'entry_date' => \Carbon\Carbon::parse($this->entryDate),
            'total_minutes' => (float) $this->duration,
            'hours' => floor((float) $this->duration / 60),
            'minutes' => (float) $this->duration % 60,
        ];

        // Update content if description is provided
        if ($this->description) {
            $updateData['content'] = $this->description;
        }

        // Handle running vs stopped timers differently
        if ($this->timeEntry->is_running) {
            // For running timers, adjust the start_time to reflect the new duration
            $newStartTime = now()->subMinutes((float) $this->duration);
            $updateData['start_time'] = $newStartTime;
        } else {
            // For stopped timers, update duration fields
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
        if (!$this->showDeleteButton) {
            $this->dispatch('error', 'Delete is not allowed for this entry.');
            return;
        }

        if ($this->timeEntry->user_id !== Auth::id()) {
            $this->dispatch('error', 'You can only delete your own time entries.');
            return;
        }

        $entryId = $this->timeEntry->id;
        $this->timeEntry->delete();

        $this->dispatch('timeEntryDeleted', ['id' => $entryId]);
        $this->dispatch('success', 'Time entry deleted successfully.');
    }

    public function executeCustomAction($actionKey)
    {
        if (!isset($this->customActions[$actionKey])) {
            $this->dispatch('error', 'Invalid custom action.');
            return;
        }

        $action = $this->customActions[$actionKey];

        // Dispatch event with entry data for parent component to handle
        $this->dispatch('customActionExecuted', [
            'action' => $actionKey,
            'entry_id' => $this->timeEntry->id,
            'entry' => $this->timeEntry->toArray(),
        ]);
    }

    private function resetEditFields()
    {
        $this->duration = '';
        $this->description = '';
        $this->entryDate = '';
    }

    public function getFormattedDurationProperty()
    {
        $minutes = $this->timeEntry->total_minutes ?? $this->timeEntry->duration_minutes ?? 0;
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return "{$hours}h {$mins}m";
    }

    public function getDisplayTitleProperty()
    {
        if ($this->timeEntry->task) {
            return $this->timeEntry->task->title;
        } elseif ($this->timeEntry->activity_type) {
            return $this->timeEntry->activity_type;
        }
        return 'General Time Entry';
    }

    public function getDisplaySubtitleProperty()
    {
        if ($this->timeEntry->task && $this->timeEntry->task->project) {
            return $this->timeEntry->task->project->name;
        } elseif ($this->timeEntry->project) {
            return $this->timeEntry->project->name;
        }
        return null;
    }

    public function render()
    {
        return view('timeline-library::time-entry-editor', [
            'formattedDuration' => $this->getFormattedDurationProperty(),
            'displayTitle' => $this->getDisplayTitleProperty(),
            'displaySubtitle' => $this->getDisplaySubtitleProperty(),
        ]);
    }
}
