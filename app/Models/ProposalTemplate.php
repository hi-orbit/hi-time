<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProposalTemplate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'content',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get proposals using this template
     */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'template_id');
    }

    /**
     * Get only active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Replace template variables in content
     */
    public function processContent(array $variables = []): string
    {
        $content = $this->content;

        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }

    /**
     * Extract variables from template content
     */
    public function extractVariables(): array
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $this->content, $matches);
        return array_unique($matches[1]);
    }
}
