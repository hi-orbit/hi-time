<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TaskNote;
use App\Livewire\TimeTracking\Index;
use App\Livewire\Reports\MyTimeToday;
use App\Livewire\Reports\TimeByUserLivewire;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TimeEntryEditTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $timeEntry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->timeEntry = TaskNote::factory()->create([
            'user_id' => $this->user->id,
            'start_time' => now()->setTime(9, 0, 0),
            'end_time' => now()->setTime(17, 30, 0),
            'total_minutes' => 510, // 8.5 hours
            'duration_minutes' => 510,
        ]);
    }

    /** @test */
    public function it_calculates_duration_when_start_time_changes_in_time_tracking_index()
    {
        $component = Livewire::actingAs($this->user)
            ->test(Index::class)
            ->set('editingTimeEntry', $this->timeEntry->id)
            ->set('editStartTime', '10:00')
            ->set('editEndTime', '18:00');

        // Duration should be calculated as 8 hours = 480 minutes
        $component->assertSet('editDuration', 480);
    }

    /** @test */
    public function it_calculates_duration_when_end_time_changes_in_time_tracking_index()
    {
        $component = Livewire::actingAs($this->user)
            ->test(Index::class)
            ->set('editingTimeEntry', $this->timeEntry->id)
            ->set('editStartTime', '09:00')
            ->set('editEndTime', '17:30');

        // Duration should be calculated as 8.5 hours = 510 minutes
        $component->assertSet('editDuration', 510);
    }

    /** @test */
    public function it_handles_overnight_time_entries()
    {
        $component = Livewire::actingAs($this->user)
            ->test(Index::class)
            ->set('editingTimeEntry', $this->timeEntry->id)
            ->set('editStartTime', '23:00')
            ->set('editEndTime', '07:00');

        // Duration should be calculated as 8 hours = 480 minutes (overnight)
        $component->assertSet('editDuration', 480);
    }

    /** @test */
    public function it_calculates_duration_in_my_time_today_component()
    {
        $component = Livewire::actingAs($this->user)
            ->test(MyTimeToday::class)
            ->set('editingTimeEntry', $this->timeEntry->id)
            ->set('editStartTime', '09:30')
            ->set('editEndTime', '17:00');

        // Duration should be calculated as 7.5 hours = 450 minutes
        $component->assertSet('editDuration', 450);
    }

    /** @test */
    public function it_calculates_duration_in_time_by_user_component()
    {
        $component = Livewire::actingAs($this->user)
            ->test(TimeByUserLivewire::class)
            ->set('editingTimeEntry', $this->timeEntry->id)
            ->set('editStartTime', '08:00')
            ->set('editEndTime', '16:30');

        // Duration should be calculated as 8.5 hours = 510 minutes
        $component->assertSet('editDuration', 510);
    }

    /** @test */
    public function it_saves_recalculated_duration_when_editing_time_entry()
    {
        Livewire::actingAs($this->user)
            ->test(Index::class)
            ->set('editingTimeEntry', $this->timeEntry->id)
            ->set('editStartTime', '10:00')
            ->set('editEndTime', '18:30')
            ->set('editDescription', 'Updated task')
            ->call('saveTimeEntry', $this->timeEntry->id);

        $this->timeEntry->refresh();

        // Verify duration was recalculated and saved (8.5 hours = 510 minutes)
        $this->assertEquals(510, $this->timeEntry->total_minutes);
        $this->assertEquals(510, $this->timeEntry->duration_minutes);
        $this->assertEquals(8, $this->timeEntry->hours);
        $this->assertEquals(30, $this->timeEntry->minutes);
    }

    /** @test */
    public function it_handles_invalid_time_formats_gracefully()
    {
        $component = Livewire::actingAs($this->user)
            ->test(Index::class)
            ->set('editingTimeEntry', $this->timeEntry->id)
            ->set('editStartTime', 'invalid')
            ->set('editEndTime', '18:00');

        // Duration should not be changed when time format is invalid
        $component->assertSet('editDuration', 0);
    }
}
