<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Proposal extends Model
{
    protected $fillable = [
        'proposal_number',
        'title',
        'description',
        'lead_id',
        'customer_id',
        'template_id',
        'content',
        'amount',
        'terms',
        'status',
        'recipient_email',
        'sent_at',
        'viewed_at',
        'responded_at',
        'signature_token',
        'signature_data',
        'email_subject',
        'email_body',
        'pdf_path',
        'created_by',
        'valid_until',
        'client_name',
        'client_email',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'responded_at' => 'datetime',
        'valid_until' => 'date',
        'signature_data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($proposal) {
            if (empty($proposal->proposal_number)) {
                $proposal->proposal_number = 'PROP-' . date('Y') . '-' . str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT);
            }
            if (empty($proposal->signature_token)) {
                $proposal->signature_token = Str::random(64);
            }
        });
    }

    /**
     * Get the lead this proposal belongs to
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the customer this proposal belongs to
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the template used for this proposal
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ProposalTemplate::class, 'template_id');
    }

    /**
     * Get the user who created this proposal
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the recipient (lead or customer)
     */
    public function getRecipientAttribute()
    {
        return $this->lead ?: $this->customer;
    }

    /**
     * Get the recipient name
     */
    public function getRecipientNameAttribute(): string
    {
        if ($this->lead) {
            return $this->lead->display_name;
        }
        return $this->customer ? $this->customer->name : 'Unknown';
    }

    /**
     * Mark proposal as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark proposal as viewed
     */
    public function markAsViewed(): void
    {
        if ($this->status === 'sent') {
            $this->update([
                'status' => 'viewed',
                'viewed_at' => now(),
            ]);
        }
    }

    /**
     * Accept the proposal
     */
    public function accept(array $signatureData = []): void
    {
        $this->update([
            'status' => 'signed', // Change from 'accepted' to 'signed'
            'responded_at' => now(),
            'signature_data' => $signatureData,
        ]);

        // Convert lead to customer if applicable
        if ($this->lead && !$this->lead->isConverted()) {
            $customer = $this->lead->convertToCustomer();
            $this->update(['customer_id' => $customer->id]);
        }
    }

    /**
     * Reject the proposal
     */
    public function reject(): void
    {
        $this->update([
            'status' => 'rejected',
            'responded_at' => now(),
        ]);
    }

    /**
     * Get the public URL for viewing/signing
     */
    public function getPublicUrlAttribute(): string
    {
        return route('proposals.public.view', ['token' => $this->signature_token]);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'sent' => 'blue',
            'viewed' => 'yellow',
            'signed' => 'green',
            'rejected' => 'red',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Check if proposal can be edited
     */
    public function canBeEdited(): bool
    {
        return !in_array($this->status, ['accepted', 'signed']);
    }

    /**
     * Check if proposal can be sent
     */
    public function canBeSent(): bool
    {
        return in_array($this->status, ['draft']);
    }

    /**
     * Check if proposal can be deleted
     */
    public function canBeDeleted(): bool
    {
        return !in_array($this->status, ['accepted', 'signed']);
    }
}
