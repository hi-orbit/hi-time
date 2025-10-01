<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'company_number',
        'email',
        'phone',
        'address',
        'contact_person',
        'notes',
        'status'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationship to Projects
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    // Relationship to Tags
    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    // Scope for active customers
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for inactive customers
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    // Get total project count
    public function getTotalProjectsAttribute()
    {
        return $this->projects()->count();
    }

    // Get active project count
    public function getActiveProjectsAttribute()
    {
        return $this->projects()->active()->count();
    }
}
