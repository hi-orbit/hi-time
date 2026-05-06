<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
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

    /**
     * Get or create a "General Activities" task for a project.
     * This ensures general time entries maintain the relationship chain to projects and customers.
     *
     * @param int $projectId
     * @return Task
     */
    public static function getOrCreateGeneralActivitiesTask(int $projectId): Task
    {
        return self::firstOrCreate(
            [
                'project_id' => $projectId,
                'title' => 'General Activities',
            ],
            [
                'description' => 'System-generated task for general time entries (meetings, calls, etc.)',
                'status' => 'general',
                'created_by' => Auth::id(),
                'order' => 999999, // Put at the end of the task list
            ]
        );
    }
}
