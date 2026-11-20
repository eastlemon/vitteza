<?php

namespace App\Models;

use App\Enums\TripStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * Рейс — центральная сущность учёта перевозок (ТЗ §10).
 * Хранит статус жизненного цикла; при переходе в статус-триггер (по умолчанию `paid`)
 * начисляется доход логисту и создаётся запись в income_entries (§10.5, §15.5).
 */
class Trip extends Model
{
    protected $fillable = [
        'load_date', 'unload_date', 'logistic_id', 'driver_id', 'customer_id', 'cargo_id',
        'route', 'weight_t', 'volume_m3', 'contract_no', 'cost', 'act_no',
        'sent_electronic_at', 'sent_post_at', 'delivered', 'payment_due_date',
        'payment_date', 'payment_fact', 'status', 'income_total', 'income_logist',
        'paid_to_logist', 'comment', 'created_by',
    ];

    protected $casts = [
        'load_date' => 'date',
        'unload_date' => 'date',
        'sent_electronic_at' => 'date',
        'sent_post_at' => 'date',
        'payment_due_date' => 'date',
        'payment_date' => 'date',
        'weight_t' => 'decimal:2',
        'volume_m3' => 'decimal:2',
        'cost' => 'decimal:2',
        'payment_fact' => 'decimal:2',
        'income_total' => 'decimal:2',
        'income_logist' => 'decimal:2',
        'delivered' => 'boolean',
        'paid_to_logist' => 'boolean',
    ];

    public function logistic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logistic_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    public function incomeEntry(): HasOne
    {
        return $this->hasOne(IncomeEntry::class, 'trip_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Триггер начисления дохода при сохранении (§10.5, §15.5).
     * Идемпотентен: срабатывает один раз при первом переходе в статус-триггер.
     */
    protected static function booted(): void
    {
        static::saved(function (Trip $trip) {
            $trip->applyIncomeTrigger();
        });
    }

    public function applyIncomeTrigger(): void
    {
        $trigger = Setting::getString('income_trigger_status', 'paid');
        if ((string) $this->status !== $trigger) {
            return;
        }
        if ($this->paid_to_logist) {
            return;
        }

        $incomeShare = Setting::getFloat('income_share', 0.20);
        $logistShare = Setting::getFloat('logist_share', 0.50);
        $total = round((float) $this->cost * $incomeShare, 2);
        $logist = round($total * $logistShare, 2);

        DB::transaction(function () use ($total, $logist) {
            // Отмечаем без повторного вызова saved-триггера начисления.
            $this->paid_to_logist = true;
            $this->income_total = $total;
            $this->income_logist = $logist;
            $this->saveQuietly();

            IncomeEntry::create([
                'user_id' => $this->logistic_id,
                'trip_id' => $this->id,
                'income_total' => $total,
                'income_logist' => $logist,
                'period_year' => (int) now()->year,
                'period_month' => (int) now()->month,
            ]);
        });
    }

    /**
     * Видимость рейсов (§8.3): админ — все; user с trips.view_all — все; иначе — только свои.
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isAdmin() || $user->hasCompetency('trips.view_all')) {
            return $query;
        }

        return $query->where('logistic_id', $user->id);
    }
}
