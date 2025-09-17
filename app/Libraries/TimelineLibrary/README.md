# Timeline Library Documentation

The Timeline Library is a comprehensive Laravel/Livewire package for visualizing and editing time entries with interactive timeline charts, hover tooltips, and inline editing capabilities.

## Features

- **Interactive Timeline Visualization**: 24-hour timeline with color-coded entries
- **Layer Management**: Automatic layering of overlapping time entries
- **Hover Tooltips**: Detailed information on hover with task, project, and duration
- **Inline Editing**: Edit time entries directly in the timeline view
- **Configurable Display**: Customizable colors, tooltips, legend, and layout options
- **Manual Entry Support**: Visual indicators for manual vs timed entries
- **Running Timer Support**: Real-time visualization of currently running timers
- **Responsive Design**: Works on desktop and mobile devices

## Installation

1. The library is already installed in your Laravel application under `app/Libraries/TimelineLibrary/`
2. The service provider is registered in `bootstrap/providers.php`

## Basic Usage

### Using the Timeline Chart Service

```php
use App\Libraries\TimelineLibrary\Services\TimelineChart;
use Carbon\Carbon;

// Create timeline chart instance
$timelineChart = new TimelineChart([
    'show_tooltips' => true,
    'show_legend' => true,
    'manual_entry_pattern' => 'striped',
]);

// Generate timeline data
$timelineData = $timelineChart->generateTimelineData($timeEntries, Carbon::today());
```

### Using Timeline in Blade Views

```blade
{{-- Include the timeline chart directly --}}
@include('timeline-library::timeline-chart', [
    'timelineData' => $timelineData,
    'date' => $date
])
```

### Using Livewire Components

```blade
{{-- Timeline Viewer Component --}}
@livewire('timeline-viewer', [
    'timeEntries' => $entries,
    'date' => $date,
    'config' => ['show_date_picker' => true]
])

{{-- Time Entry Editor Component --}}
@livewire('timeline-entry-editor', [
    'timeEntry' => $entry,
    'config' => ['compact_mode' => true]
])
```

## Configuration Options

### TimelineChart Service Configuration

```php
$config = [
    // Display options
    'show_tooltips' => true,           // Enable hover tooltips
    'show_legend' => true,             // Show color legend
    'hours_in_day' => 24,              // Hours to display (24 for full day)
    'time_format' => 'H:i',            // Time format for display

    // Layout options
    'default_entry_height' => 20,      // Height of timeline entries in pixels
    'layer_offset' => 25,              // Vertical spacing between layers
    'max_layers' => 10,                // Maximum number of layers

    // Visual indicators
    'manual_entry_pattern' => 'striped', // Pattern for manual entries
    'running_entry_animation' => true,   // Animate running timers
];
```

### TimeEntryEditor Component Configuration

```php
$config = [
    'show_view_task_link' => true,     // Show "View Task" link
    'show_delete_button' => true,     // Show delete button
    'allow_editing' => true,          // Allow editing entries
    'compact_mode' => false,          // Use compact display
    'custom_actions' => [             // Add custom action buttons
        'clone' => [
            'label' => 'Clone Entry',
            'color' => 'blue'
        ]
    ]
];
```

## Color Customization

### Default Colors

The library uses the following default colors:

```php
$colors = [
    'default' => '#6366f1',    // Default entry color
    'general' => '#10b981',    // General activity color
    'running' => '#ef4444',    // Running timer color
    'manual' => '#f59e0b',     // Manual entry color
    'background' => '#f3f4f6', // Background color
    'text' => '#374151',       // Text color
    'border' => '#d1d5db',     // Border color
];
```

### Project Colors

Project-based entries automatically get assigned colors from a predefined palette:

```php
$projectColors = [
    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', 
    '#8b5cf6', '#06b6d4', '#84cc16', '#f97316', 
    '#ec4899', '#14b8a6'
];
```

### Custom Color Configuration

```php
$timelineChart = new TimelineChart();
$timelineChart->setColors([
    'running' => '#ff0000',
    'general' => '#00ff00',
]);
```

## Component Integration Examples

### In a Livewire Component

```php
<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Libraries\TimelineLibrary\Services\TimelineChart;
use Carbon\Carbon;

class MyReport extends Component
{
    public $timelineData = [];
    public $selectedDate;

    public function mount()
    {
        $this->selectedDate = Carbon::today();
        $this->generateTimeline();
    }

    public function generateTimeline()
    {
        $timeEntries = $this->getUserTimeEntries();
        
        $timelineChart = new TimelineChart([
            'show_tooltips' => true,
            'show_legend' => true,
        ]);
        
        $this->timelineData = $timelineChart->generateTimelineData(
            $timeEntries, 
            $this->selectedDate
        );
    }

    public function render()
    {
        return view('livewire.reports.my-report');
    }
}
```

### In the Blade View

```blade
<div>
    <!-- Date Picker -->
    <input type="date" wire:model.live="selectedDate">

    <!-- Timeline Display -->
    @if(count($timelineData['entries'] ?? []) > 0)
        @include('timeline-library::timeline-chart', [
            'timelineData' => $timelineData,
            'date' => $selectedDate
        ])
    @else
        <p>No entries for this date.</p>
    @endif
</div>
```

## Advanced Features

### Custom Action Handlers

For the TimeEntryEditor component, you can handle custom actions:

```php
// In your Livewire component
protected $listeners = ['customActionExecuted' => 'handleCustomAction'];

public function handleCustomAction($data)
{
    $action = $data['action'];
    $entryId = $data['entry_id'];
    
    switch ($action) {
        case 'clone':
            $this->cloneEntry($entryId);
            break;
        // Handle other custom actions
    }
}
```

### Timeline Data Structure

The `generateTimelineData()` method returns an array with the following structure:

```php
[
    'entries' => [
        [
            'id' => 123,
            'title' => 'Task Title',
            'description' => 'Task description',
            'duration' => 120,                    // minutes
            'duration_formatted' => '2h 0m',
            'color' => '#3b82f6',
            'is_running' => false,
            'is_manual' => true,
            'start_position' => 37.5,            // percentage
            'width' => 8.33,                     // percentage
            'start_time' => '09:00',
            'end_time' => '11:00',
            'layer' => 0,
            'entry' => $originalEntryObject
        ]
    ],
    'layers' => [
        'layers' => [...],                       // entries grouped by layer
        'max_layer' => 2,
        'total_height' => 75                     // pixels
    ],
    'hour_markers' => [
        [
            'hour' => 9,
            'position' => 37.5,                 // percentage
            'label' => '09:00',
            'is_major' => false
        ]
    ],
    'legend' => [
        [
            'type' => 'task',
            'title' => 'Task Name',
            'subtitle' => 'Project Name',
            'color' => '#3b82f6',
            'count' => 3,
            'total_duration' => 180              // minutes
        ]
    ],
    'total_hours' => '3h 0m',
    'config' => [...],                           // applied configuration
]
```

## Performance Considerations

- **Large Datasets**: The library handles up to 1000 entries per day efficiently
- **Caching**: Enable caching for large datasets in the configuration
- **Lazy Loading**: Tooltips can be lazy-loaded for better performance

```php
$config = [
    'performance' => [
        'max_entries_per_day' => 1000,
        'enable_caching' => true,
        'cache_duration' => 300,        // seconds
        'lazy_load_tooltips' => true,
    ]
];
```

## Troubleshooting

### Common Issues

1. **Tooltips not showing**: Ensure Alpine.js is loaded on your page
2. **Colors not applying**: Check that the entry has proper task/project relationships
3. **Overlapping entries**: The layer system automatically handles overlaps
4. **Missing timeline data**: Verify that timeEntries have `total_minutes` or `duration_minutes`

### Debug Information

Enable debug mode to see timeline calculation details:

```php
$timelineChart = new TimelineChart(['debug' => true]);
```

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Dependencies

- Laravel 11+
- Livewire 3+
- Alpine.js (for tooltips and interactions)
- Tailwind CSS (for styling)

## Future Enhancements

- Drag-and-drop entry editing
- Zoom functionality for detailed time views
- Export timeline as image/PDF
- Real-time collaborative editing
- Mobile touch gestures
