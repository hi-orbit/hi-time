<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The tasks that belong to the tag.
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class)->withTimestamps();
    }

    /**
     * Get the count of tasks using this tag.
     */
    public function getTaskCountAttribute(): int
    {
        return $this->tasks()->count();
    }

    /**
     * Scope a query to order tags by most used.
     */
    public function scopeOrderByUsage($query)
    {
        return $query->withCount('tasks')->orderBy('tasks_count', 'desc');
    }

    /**
     * Scope a query to search for tags by name.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%');
    }

    /**
     * Get the tag's display name with usage count.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' (' . $this->task_count . ')';
    }

    /**
     * Get a default set of colors for tags.
     */
    public static function getDefaultColors(): array
    {
        return [
            '#3B82F6', // Blue
            '#EF4444', // Red
            '#10B981', // Green
            '#F59E0B', // Yellow
            '#8B5CF6', // Purple
            '#EC4899', // Pink
            '#14B8A6', // Teal
            '#F97316', // Orange
            '#6B7280', // Gray
            '#84CC16', // Lime
        ];
    }
}
