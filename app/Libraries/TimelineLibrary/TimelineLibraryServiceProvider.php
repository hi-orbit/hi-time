<?php

namespace App\Libraries\TimelineLibrary;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Illuminate\Support\Facades\Blade;
use App\Libraries\TimelineLibrary\Components\TimelineViewer;
use App\Libraries\TimelineLibrary\Components\TimeEntryEditor;
use App\Libraries\TimelineLibrary\Services\TimelineChart;

class TimelineLibraryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the TimelineChart service
        $this->app->singleton(TimelineChart::class, function ($app) {
            return new TimelineChart();
        });

        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/Config/timeline.php', 'timeline');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Livewire components with timeline-library namespace
        Livewire::component('timeline-library.timeline-viewer', TimelineViewer::class);
        Livewire::component('timeline-library.time-entry-editor', TimeEntryEditor::class);

        // Register view namespace
        $this->loadViewsFrom(__DIR__ . '/Views', 'timeline-library');

        // Register Blade directives
        $this->registerBladeDirectives();

        // Publish configuration and views if needed
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Config/timeline.php' => config_path('timeline.php'),
            ], 'timeline-config');

            $this->publishes([
                __DIR__ . '/Views' => resource_path('views/vendor/timeline-library'),
            ], 'timeline-views');
        }
    }

    /**
     * Register custom Blade directives
     */
    protected function registerBladeDirectives(): void
    {
        // @timeline directive for easy timeline inclusion
        Blade::directive('timeline', function ($expression) {
            return "<?php echo app('App\Libraries\TimelineLibrary\Services\TimelineChart')->generateTimelineHtml({$expression}); ?>";
        });

        // @timelineViewer directive for Livewire component
        Blade::directive('timelineViewer', function ($expression) {
            $params = $expression ?: '[]';
            return "<?php echo \Livewire\Livewire::mount('timeline-library.timeline-viewer', {$params})->html(); ?>";
        });

        // @timeEntryEditor directive for entry editing
        Blade::directive('timeEntryEditor', function ($expression) {
            return "<?php echo \Livewire\Livewire::mount('timeline-library.time-entry-editor', {$expression})->html(); ?>";
        });
    }
}
