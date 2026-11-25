<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Logist income accrual for a trip (Spec §15.5). By period_year/period_month
 * the dashboard and rating aggregate data — this is the monthly "reset".
 */
class IncomeEntry extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'trip_id', 'income_total', 'income_logist', 'period_year', 'period_month',
    ];

    protected $casts = [
        'income_total' => 'decimal:2',
        'income_logist' => 'decimal:2',
        'period_year' => 'integer',
        'period_month' => 'integer',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
