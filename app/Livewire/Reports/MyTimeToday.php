<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\TaskNote;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Libraries\TimelineLibrary\Services\TimelineChart;
use App\Traits\GeneratesTimelineData;

class MyTimeToday extends Component
{
    use GeneratesTimelineData;
    public $totalHours = 0;
    public $totalMinutes = 0;
    public $timeEntries = [];
    public $selectedDate;
    public $chartData = [];
    public $timelineData = [];

    // Inline editing properties
    public $editingTimeEntry = null;
    public $editDuration;
    public $editStartTime;
    public $editEndTime;
    public $editDescription;

    protected $listeners = ['timeEntryUpdated' => 'loadTimeEntries', 'timeEntryDeleted' => 'loadTimeEntries'];

    public function mount()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->loadTimeEntries();
    }

    public function updatedSelectedDate()
    {
        $this->loadTimeEntries();
        $this->generateChartData(); // This now also generates timeline data
    }

    public function loadTimeEntries()
    {
        $date = Carbon::parse($this->selectedDate);

        $this->timeEntries = TaskNote::with(['task.project', 'user'])
            ->whereNotNull('total_minutes') // Only get entries with time logged
            ->where('user_id', Auth::id())
            ->where(function($query) use ($date) {
                $query->whereDate('entry_date', $date)
                      ->orWhere(function($subQuery) use ($date) {
                          $subQuery->whereNull('entry_date')
                                   ->whereDate('created_at', $date);
                      });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate total time using total_minutes (primary) or duration_minutes (fallback)
        $totalMinutes = collect($this->timeEntries)->sum(function($entry) {
            return $entry->total_minutes ?? $entry->duration_minutes ?? 0;
        });

        $this->totalHours = intval($totalMinutes / 60);
        $this->totalMinutes = $totalMinutes % 60;

        $this->generateChartData();
        $this->generateTimelineData();
    }

    public function generateTimelineData()
    {
        $timelineChart = new TimelineChart([
            'show_tooltips' => true,
            'show_legend' => true,
            'manual_entry_pattern' => 'striped',
            'running_entry_animation' => true,
        ]);

        $this->timelineData = $timelineChart->generateTimelineData(
            collect($this->timeEntries),
            Carbon::parse($this->selectedDate)
        );
    }

    public function generateChartData()
    {
        $date = Carbon::parse($this->selectedDate);

        // Use the consolidated timeline generation
        $result = $this->generateTimelineVisualization($date);
        $entries = $result['entries'];
        $this->timelineData = $result['timelineData'];

        // Generate legacy chart data for backward compatibility
        $this->chartData = $this->generateLegacyChartData($entries, $date);
    }

    public function deleteTimeEntry($entryId)
    {
        $entry = TaskNote::find($entryId);

        if ($entry && $entry->user_id === Auth::id()) {
            $entry->delete();
            $this->loadTimeEntries();
            session()->flash('message', 'Time entry deleted successfully.');
        } else {
            session()->flash('error', 'You can only delete your own time entries.');
        }
    }

    // Inline Time Entry Editing Methods
    public function editTimeEntry($entryId)
    {
        $entry = TaskNote::findOrFail($entryId);

        // Check if user can edit this entry
        if ($entry->user_id !== Auth::id()) {
            session()->flash('error', 'You can only edit your own time entries.');
            return;
        }

        $this->editingTimeEntry = $entryId;
        $this->editDuration = $entry->total_minutes ?? $entry->duration_minutes ?? 0;
        $this->editStartTime = $entry->start_time ? \Carbon\Carbon::parse($entry->start_time)->format('H:i') : '';
        $this->editEndTime = $entry->end_time ? \Carbon\Carbon::parse($entry->end_time)->format('H:i') : '';
        $this->editDescription = $entry->description ?? $entry->content ?? '';
    }

    public function saveTimeEntry($entryId)
    {
        $entry = TaskNote::findOrFail($entryId);

        // Check if user can edit this entry
        if ($entry->user_id !== Auth::id()) {
            session()->flash('error', 'You can only edit your own time entries.');
            return;
        }

        $this->validate([
            'editDuration' => 'required|numeric|min:0.01',
            'editDescription' => 'nullable|string|max:1000',
            'editStartTime' => 'nullable|date_format:H:i',
            'editEndTime' => 'nullable|date_format:H:i',
        ]);

        // Update the entry
        $updateData = [
            'total_minutes' => (float) $this->editDuration,
            'duration_minutes' => (float) $this->editDuration,
            'hours' => floor((float) $this->editDuration / 60),
            'minutes' => (float) $this->editDuration % 60,
        ];

        if ($this->editDescription) {
            $updateData['description'] = $this->editDescription;
            $updateData['content'] = $this->editDescription;
        }

        if ($this->editStartTime && $this->editEndTime) {
            $date = $entry->entry_date ?? $entry->created_at->toDateString();

            // Parse the date and time more safely
            try {
                $updateData['start_time'] = \Carbon\Carbon::parse($date . ' ' . $this->editStartTime);
                $updateData['end_time'] = \Carbon\Carbon::parse($date . ' ' . $this->editEndTime);
            } catch (\Exception $e) {
                // Fallback: use current date if parsing fails
                $updateData['start_time'] = \Carbon\Carbon::today()->setTimeFromTimeString($this->editStartTime);
                $updateData['end_time'] = \Carbon\Carbon::today()->setTimeFromTimeString($this->editEndTime);
            }
        }

        $entry->update($updateData);

        $this->cancelEdit();
        $this->loadTimeEntries();

        session()->flash('message', 'Time entry updated successfully.');
    }

    public function cancelEdit()
    {
        $this->editingTimeEntry = null;
        $this->editDuration = null;
        $this->editStartTime = null;
        $this->editEndTime = null;
        $this->editDescription = null;
    }

    public function render()
    {
        return view('livewire.reports.my-time-today');
    }
}
