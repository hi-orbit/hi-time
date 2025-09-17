<?php

namespace App\Libraries\TimelineLibrary\Components;

use Livewire\Component;
use App\Libraries\TimelineLibrary\Services\TimelineChart;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TimelineViewer extends Component
{
    public $timeEntries;
    public $date;
    public $config;
    public $timelineData;

    protected $listeners = [
        'timeEntryUpdated' => 'refreshTimeline',
        'timeEntryDeleted' => 'refreshTimeline',
        'timeEntryAdded' => 'refreshTimeline',
    ];

    public function mount(Collection $timeEntries, ?Carbon $date = null, array $config = [])
    {
        $this->timeEntries = $timeEntries;
        $this->date = $date ?? now();
        $this->config = array_merge([
            'show_tooltips' => true,
            'show_legend' => true,
            'editable' => false,
            'show_date_picker' => false,
        ], $config);

        $this->generateTimelineData();
    }

    public function refreshTimeline()
    {
        $this->generateTimelineData();
    }

    public function updateDate($newDate)
    {
        $this->date = Carbon::parse($newDate);
        $this->generateTimelineData();
    }

    protected function generateTimelineData()
    {
        $timelineChart = new TimelineChart($this->config);
        $this->timelineData = $timelineChart->generateTimelineData($this->timeEntries, $this->date);
    }

    public function render()
    {
        return view('timeline-library::timeline-viewer', [
            'timelineData' => $this->timelineData,
            'date' => $this->date,
        ]);
    }
}
