{{-- Timeline Viewer Component --}}
<div class="timeline-viewer-component">
    @if($config['show_date_picker'] ?? false)
        <div class="mb-4 flex items-center justify-between">
            <div>
                <label for="timeline-date" class="block text-sm font-medium text-gray-700 mb-1">Select Date</label>
                <input type="date"
                       id="timeline-date"
                       wire:model.live="date"
                       wire:change="updateDate($event.target.value)"
                       class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
        </div>
    @endif

    @include('timeline-library::timeline-chart', [
        'timelineData' => $timelineData,
        'date' => $date
    ])
</div>
