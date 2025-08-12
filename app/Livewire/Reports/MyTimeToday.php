<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MyTimeToday extends Component
{
    public $totalHours = 0;
    public $totalMinutes = 0;
    public $timeEntries = [];
    public $selectedDate;

    protected $listeners = ['timeEntryUpdated' => 'loadTimeEntries', 'timeEntryDeleted' => 'loadTimeEntries'];

    public function mount()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->loadTimeEntries();
    }

    public function updatedSelectedDate()
    {
        $this->loadTimeEntries();
    }

    public function loadTimeEntries()
    {
        $date = Carbon::parse($this->selectedDate);

        $this->timeEntries = TimeEntry::with(['task.project', 'user'])
            ->where('user_id', Auth::id())
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate total time
        $totalMinutes = collect($this->timeEntries)->sum('duration_minutes');
        $this->totalHours = intval($totalMinutes / 60);
        $this->totalMinutes = $totalMinutes % 60;
    }

    public function deleteTimeEntry($entryId)
    {
        $entry = TimeEntry::find($entryId);

        if ($entry && $entry->user_id === Auth::id()) {
            $entry->delete();
            $this->loadTimeEntries();
            session()->flash('message', 'Time entry deleted successfully.');
        } else {
            session()->flash('error', 'You can only delete your own time entries.');
        }
    }

    public function render()
    {
        return view('livewire.reports.my-time-today');
    }
}
