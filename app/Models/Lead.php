<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'company_number',
        'address',
        'source',
        'status',
        'notes',
        'contacted_at',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
    ];

    /**
     * Get the customer this lead was converted to
     */
    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_customer_id');
    }

    /**
     * Get proposals for this lead
     */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    /**
     * Convert this lead to a customer
     */
    public function convertToCustomer(): Customer
    {
        $customer = Customer::create([
            'name' => $this->company ?: $this->name,
            'company_number' => $this->company_number,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'notes' => $this->notes,
        ]);

        $this->update([
            'status' => 'converted',
            'converted_customer_id' => $customer->id,
            'converted_at' => now(),
        ]);

        return $customer;
    }

    /**
     * Check if lead is converted
     */
    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }

    /**
     * Get the display name for this lead
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->company ?: $this->name;
    }
}
