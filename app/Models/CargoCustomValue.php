<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Custom field value for a specific cargo (Spec §14.4).
 */
class CargoCustomValue extends Model
{
    protected $fillable = [
        'field_id', 'cargo_id', 'value_text', 'value_number', 'value_bool',
    ];

    protected $casts = [
        'value_number' => 'decimal:4',
        'value_bool' => 'boolean',
    ];

    public function field(): BelongsTo
    {
        return $this->belongsTo(CargoCustomField::class, 'field_id');
    }
}
