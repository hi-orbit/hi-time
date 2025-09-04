<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskNote extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'content',
        'description',
        'hours',
        'minutes',
        'total_minutes',
        'start_time',
        'end_time',
        'duration_minutes',
        'entry_date',
        'is_running',
        'activity_type',
        'project_id',
        'source'
    ];

    protected $casts = [
        'hours' => 'integer',
        'minutes' => 'integer',
        'total_minutes' => 'integer',
        'duration_minutes' => 'integer',
        'is_running' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'entry_date' => 'date',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getFormattedTimeAttribute(): string
    {
        if (!$this->total_minutes) {
            return '';
        }

        $hours = intval($this->total_minutes / 60);
        $minutes = $this->total_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    public function getFormattedDecimalHoursAttribute(): string
    {
        if ($this->is_running && $this->start_time) {
            // For running timers, calculate current elapsed time
            $elapsedMinutes = $this->start_time->diffInMinutes(now());
            $hours = $elapsedMinutes / 60;
            return number_format($hours, 1) . 'h';
        }

        if (!$this->total_minutes) {
            return '0.0h';
        }

        $hours = $this->total_minutes / 60;
        return number_format($hours, 1) . 'h';
    }
}
