<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cargo instance in a trip — overridable template values (Spec §14.2/§14.6).
 */
class Cargo extends Model
{
    protected $fillable = [
        'template_id', 'trip_id', 'name', 'cargo_class', 'weight_t', 'volume_m3',
        'packaging', 'pieces', 'dimensions', 'temperature_regime',
        'special_conditions', 'declared_value',
    ];

    protected $casts = [
        'weight_t' => 'decimal:2',
        'volume_m3' => 'decimal:2',
        'pieces' => 'integer',
        'declared_value' => 'decimal:2',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(CargoTemplate::class, 'template_id');
    }

    /**
     * Creates an instance from a template, copying all parameters (Spec §14.5).
     */
    public static function fromTemplate(CargoTemplate $tpl, ?int $tripId = null): self
    {
        return self::create([
            'template_id' => $tpl->id,
            'trip_id' => $tripId,
            'name' => $tpl->name,
            'cargo_class' => $tpl->cargo_class,
            'weight_t' => $tpl->default_weight_t,
            'volume_m3' => $tpl->default_volume_m3,
            'packaging' => $tpl->default_packaging,
            'pieces' => $tpl->default_pieces,
            'dimensions' => $tpl->default_dimensions,
            'temperature_regime' => $tpl->temperature_regime,
            'special_conditions' => $tpl->special_conditions,
            'declared_value' => $tpl->declared_value,
        ]);
    }
}
