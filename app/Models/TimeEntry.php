<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TimeEntry extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'entry_date',
        'description',
        'start_time',
        'end_time',
        'duration_minutes',
        'is_running',
        'activity_type',
        'project_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'entry_date' => 'date',
        'is_running' => 'boolean',
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

    public function getDurationAttribute()
    {
        // First, try to calculate from start and end times if both exist
        if ($this->start_time && $this->end_time) {
            $diffInMinutes = $this->start_time->diffInMinutes($this->end_time);
            return max(0, $diffInMinutes);
        }

        // If running, calculate current duration
        if ($this->start_time && $this->is_running) {
            $diffInMinutes = $this->start_time->diffInMinutes(Carbon::now());
            return max(0, $diffInMinutes);
        }

        // Finally, fall back to stored duration_minutes
        if ($this->duration_minutes && $this->duration_minutes > 0) {
            return $this->duration_minutes;
        }

        return 0;
    }

    public function getFormattedDurationAttribute()
    {
        $minutes = $this->duration;
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return sprintf('%dh %dm', $hours, $mins);
    }

    public function getDecimalHoursAttribute()
    {
        $minutes = $this->duration;
        return round($minutes / 60, 2);
    }

    public function getFormattedDecimalHoursAttribute()
    {
        return number_format($this->decimal_hours, 2) . 'h';
    }
}
