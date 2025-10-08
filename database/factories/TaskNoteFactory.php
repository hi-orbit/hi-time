<?php

namespace Database\Factories;

use App\Models\TaskNote;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class TaskNoteFactory extends Factory
{
    protected $model = TaskNote::class;

    public function definition()
    {
        $startTime = Carbon::instance($this->faker->dateTimeBetween('-1 week', 'now'));
        $durationMinutes = $this->faker->numberBetween(30, 480);
        $endTime = $startTime->copy()->addMinutes($durationMinutes);

        return [
            'user_id' => User::factory(),
            'task_id' => null,
            'project_id' => null,
            'content' => $this->faker->sentence(),
            'description' => $this->faker->sentence(),
            'hours' => floor($durationMinutes / 60),
            'minutes' => $durationMinutes % 60,
            'total_minutes' => $durationMinutes,
            'duration_minutes' => $durationMinutes,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'entry_date' => $startTime->format('Y-m-d'),
            'is_running' => false,
            'activity_type' => $this->faker->randomElement(['Development', 'Meeting', 'Research', 'Testing']),
            'source' => 'manual',
        ];
    }

    public function running()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_running' => true,
                'end_time' => null,
                'start_time' => now()->subMinutes($this->faker->numberBetween(5, 120)),
            ];
        });
    }

    public function withTask()
    {
        return $this->state(function (array $attributes) {
            return [
                'task_id' => Task::factory(),
            ];
        });
    }

    public function withProject()
    {
        return $this->state(function (array $attributes) {
            return [
                'project_id' => Project::factory(),
            ];
        });
    }
}
