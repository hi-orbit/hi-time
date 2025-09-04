<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'project_id',
        'assigned_to',
        'created_by',
        'order',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function timeEntries()
    {
        return $this->hasMany(TaskNote::class);
    }

    public function notes()
    {
        return $this->hasMany(TaskNote::class);
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function getTotalTimeAttribute()
    {
        return $this->timeEntries()
            ->whereNotNull('end_time')
            ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, start_time, end_time)'));
    }

    public function getTotalTimeFromNotesAttribute(): int
    {
        return $this->notes()->sum('total_minutes') ?? 0;
    }

    public function getRunningTimeEntryAttribute()
    {
        return $this->timeEntries()->where('is_running', true)->first();
    }

    public function isRunning()
    {
        return $this->timeEntries()->where('is_running', true)->exists();
    }
}
