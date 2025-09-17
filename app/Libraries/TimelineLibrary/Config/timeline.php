<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Timeline Chart Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the default configuration for the Timeline Library
    | components. You can override these values by passing custom config
    | arrays to the components.
    |
    */

    'defaults' => [
        'show_tooltips' => true,
        'show_legend' => true,
        'hours_in_day' => 24,
        'time_format' => 'H:i',
        'default_entry_height' => 20,
        'layer_offset' => 25,
        'max_layers' => 10,
        'manual_entry_pattern' => 'striped',
        'running_entry_animation' => true,
    ],

    'colors' => [
        'default' => '#6366f1',
        'general' => '#10b981',
        'running' => '#ef4444',
        'manual' => '#f59e0b',
        'background' => '#f3f4f6',
        'text' => '#374151',
        'border' => '#d1d5db',
        'project_colors' => [
            '#3b82f6',
            '#10b981',
            '#f59e0b',
            '#ef4444',
            '#8b5cf6',
            '#06b6d4',
            '#84cc16',
            '#f97316',
            '#ec4899',
            '#14b8a6',
        ],
    ],

    'editor' => [
        'show_view_task_link' => true,
        'show_delete_button' => true,
        'allow_editing' => true,
        'compact_mode' => false,
        'custom_actions' => [],
    ],

    'viewer' => [
        'show_date_picker' => false,
        'editable' => false,
        'auto_refresh' => true,
        'refresh_interval' => 30, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Styling Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the CSS classes and styling for timeline components
    |
    */

    'styling' => [
        'container_classes' => 'timeline-chart-container',
        'entry_classes' => 'timeline-entry',
        'running_classes' => 'timeline-entry-running',
        'manual_classes' => 'timeline-entry-manual',
        'tooltip_classes' => 'timeline-tooltip',
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for optimizing timeline performance with large datasets
    |
    */

    'performance' => [
        'max_entries_per_day' => 1000,
        'enable_caching' => false,
        'cache_duration' => 300, // seconds
        'lazy_load_tooltips' => false,
    ],
];
