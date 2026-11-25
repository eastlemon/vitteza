<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Customer contract (Spec §13.4). In a trip the "Contract number" is chosen from active contracts.
 */
class CustomerContract extends Model
{
    protected $fillable = [
        'customer_id', 'contract_no', 'signed_at', 'expires_at', 'subject', 'is_active',
    ];

    protected $casts = [
        'signed_at' => 'date',
        'expires_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
