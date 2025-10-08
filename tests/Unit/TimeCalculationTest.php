<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Livewire\TimeTracking\Index;
use App\Livewire\Reports\MyTimeToday;
use App\Livewire\Reports\TimeByUserLivewire;

class TimeCalculationTest extends TestCase
{
    /** @test */
    public function it_calculates_duration_from_start_and_end_times_in_time_tracking_index()
    {
        $component = new Index();

        // Set up the component with start and end times
        $component->editStartTime = '09:00';
        $component->editEndTime = '17:30';

        // Call the private method using reflection
        $reflection = new \ReflectionClass($component);
        $method = $reflection->getMethod('calculateDurationFromTimes');
        $method->setAccessible(true);
        $method->invoke($component);

        // Assert duration is calculated correctly (8.5 hours = 510 minutes)
        $this->assertEquals(510, $component->editDuration);
    }

    /** @test */
    public function it_handles_overnight_shifts_in_time_tracking_index()
    {
        $component = new Index();

        $component->editStartTime = '23:00';
        $component->editEndTime = '07:00';

        $reflection = new \ReflectionClass($component);
        $method = $reflection->getMethod('calculateDurationFromTimes');
        $method->setAccessible(true);
        $method->invoke($component);

        // Assert duration is calculated correctly (8 hours = 480 minutes)
        $this->assertEquals(480, $component->editDuration);
    }

    /** @test */
    public function it_calculates_duration_in_my_time_today_component()
    {
        $component = new MyTimeToday();

        $component->editStartTime = '10:30';
        $component->editEndTime = '18:00';

        $reflection = new \ReflectionClass($component);
        $method = $reflection->getMethod('calculateDurationFromTimes');
        $method->setAccessible(true);
        $method->invoke($component);

        // Assert duration is calculated correctly (7.5 hours = 450 minutes)
        $this->assertEquals(450, $component->editDuration);
    }

    /** @test */
    public function it_calculates_duration_in_time_by_user_component()
    {
        $component = new TimeByUserLivewire();

        $component->editStartTime = '08:15';
        $component->editEndTime = '16:45';

        $reflection = new \ReflectionClass($component);
        $method = $reflection->getMethod('calculateDurationFromTimes');
        $method->setAccessible(true);
        $method->invoke($component);

        // Assert duration is calculated correctly (8.5 hours = 510 minutes)
        $this->assertEquals(510, $component->editDuration);
    }

    /** @test */
    public function it_handles_invalid_time_format_gracefully()
    {
        $component = new Index();
        $component->editDuration = 100; // Set initial value

        $component->editStartTime = 'invalid';
        $component->editEndTime = '17:00';

        $reflection = new \ReflectionClass($component);
        $method = $reflection->getMethod('calculateDurationFromTimes');
        $method->setAccessible(true);
        $method->invoke($component);

        // Duration should remain unchanged when time format is invalid
        $this->assertEquals(100, $component->editDuration);
    }

    /** @test */
    public function it_handles_partial_time_inputs()
    {
        $component = new Index();
        $component->editDuration = 50; // Set initial value

        // Only start time provided
        $component->editStartTime = '09:00';
        $component->editEndTime = '';

        $reflection = new \ReflectionClass($component);
        $method = $reflection->getMethod('calculateDurationFromTimes');
        $method->setAccessible(true);
        $method->invoke($component);

        // Duration should remain unchanged when only one time is provided
        $this->assertEquals(50, $component->editDuration);
    }

    /** @test */
    public function it_calculates_short_durations_correctly()
    {
        $component = new Index();

        $component->editStartTime = '14:00';
        $component->editEndTime = '14:30';

        $reflection = new \ReflectionClass($component);
        $method = $reflection->getMethod('calculateDurationFromTimes');
        $method->setAccessible(true);
        $method->invoke($component);

        // Assert duration is calculated correctly (30 minutes)
        $this->assertEquals(30, $component->editDuration);
    }
}
