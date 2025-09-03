<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskNote extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'content',
        'hours',
        'minutes',
        'total_minutes'
    ];

    protected $casts = [
        'hours' => 'integer',
        'minutes' => 'integer',
        'total_minutes' => 'integer',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
}
