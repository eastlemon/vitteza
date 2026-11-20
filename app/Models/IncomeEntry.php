<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Начисление дохода логисту за рейс (ТЗ §15.5). По period_year/period_month
 * дашборд и рейтинг агрегируют данные — это и есть «обнуление» по месяцам.
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
