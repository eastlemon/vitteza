<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Driver and the assigned vehicle (Spec §11). The fields passport_data,
 * license_series_no, ip_requisites are sensitive (§11.4).
 */
class Driver extends Model
{
    protected $fillable = [
        'full_name', 'plate_number', 'passport_data', 'vehicle_cert_no', 'vehicle_owner',
        'license_series_no', 'license_expiry', 'capacity_t', 'volume_m3', 'body_params',
        'ip_requisites', 'phone', 'is_active', 'comment',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'capacity_t' => 'decimal:2',
        'volume_m3' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
}
