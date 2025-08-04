<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TimeEntry extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'description',
        'start_time',
        'end_time',
        'duration_minutes',
        'is_running',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
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

    public function getDurationAttribute()
    {
        if ($this->duration_minutes) {
            return $this->duration_minutes;
        }

        if ($this->start_time && $this->end_time) {
            return $this->start_time->diffInMinutes($this->end_time);
        }

        if ($this->start_time && $this->is_running) {
            return $this->start_time->diffInMinutes(Carbon::now());
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
}
