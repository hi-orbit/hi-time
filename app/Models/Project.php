<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
        'customer_id',
        'archived',
        'archived_at',
    ];

    protected $casts = [
        'archived' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'project_users');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function timeEntries()
    {
        return $this->hasManyThrough(TaskNote::class, Task::class);
    }

    public function getTotalTimeAttribute()
    {
        return $this->timeEntries()
            ->whereNotNull('end_time')
            ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, start_time, end_time)'));
    }

    public function archive()
    {
        $this->update([
            'archived' => true,
            'archived_at' => now(),
        ]);
    }

    public function unarchive()
    {
        $this->update([
            'archived' => false,
            'archived_at' => null,
        ]);
    }

    public function isArchived()
    {
        return $this->archived;
    }

    // Scope for active (non-archived) projects
    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }

    // Scope for archived projects
    public function scopeArchived($query)
    {
        return $query->where('archived', true);
    }
}
