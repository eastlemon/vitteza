<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Договор заказчика (ТЗ §13.4). В рейсе «Номер договора» выбирается из активных договоров.
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
