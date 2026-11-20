<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Шаблон груза — типовой груз с параметрами (ТЗ §14.3).
 */
class CargoTemplate extends Model
{
    protected $fillable = [
        'name', 'cargo_class', 'description', 'default_weight_t', 'default_volume_m3',
        'default_packaging', 'default_pieces', 'default_dimensions',
        'temperature_regime', 'special_conditions', 'declared_value', 'is_active',
    ];

    protected $casts = [
        'default_weight_t' => 'decimal:2',
        'default_volume_m3' => 'decimal:2',
        'default_pieces' => 'integer',
        'declared_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class, 'template_id');
    }
}
