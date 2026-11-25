<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Custom cargo field definition (Spec §14.4).
 */
class CargoCustomField extends Model
{
    protected $fillable = [
        'code', 'label', 'field_type', 'options', 'is_required', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(CargoCustomValue::class, 'field_id');
    }
}
