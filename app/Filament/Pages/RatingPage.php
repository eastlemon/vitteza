<?php

namespace App\Filament\Pages;

use App\Models\IncomeEntry;
use App\Models\Setting;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

/**
 * Рейтинг логистов (ТЗ §16). Балл = W_рейсов × (рейсы/max) + W_дохода × (доход/max),
 * нормировка по максимуму за период. Период — календарный месяц (по умолч. текущий).
 * Источник — income_entries (оплаченные рейсы, синхронно с дашбордом §15).
 * Администратор видит весь рейтинг; пользователь — только свою строку и общее число участников.
 */
class RatingPage extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationLabel = 'Рейтинг логистов';

    protected static ?string $slug = 'rating';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.rating';

    public int $year;
    public int $month;

    public function mount(): void
    {
        $now = Carbon::now();
        $this->year = (int) $now->year;
        $this->month = (int) $now->month;
    }

    public function prevPeriod(): void
    {
        $this->shift(-1);
    }

    public function nextPeriod(): void
    {
        $this->shift(1);
    }

    protected function shift(int $delta): void
    {
        $c = Carbon::createFromDate($this->year, $this->month, 1)->addMonths($delta);
        $this->year = (int) $c->year;
        $this->month = (int) $c->month;
    }

    public function compute(): array
    {
        $wTrips = Setting::getFloat('rating_w_trips', 0.40);
        $wIncome = Setting::getFloat('rating_w_income', 0.60);

        $rows = IncomeEntry::where('period_year', $this->year)
            ->where('period_month', $this->month)
            ->join('users', 'users.id', '=', 'income_entries.user_id')
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(income_entries.trip_id) AS trips_count'),
                DB::raw('COALESCE(SUM(income_entries.income_total),0) AS income_total'),
                DB::raw('COALESCE(SUM(income_entries.income_logist),0) AS income_logist'),
            )
            ->groupBy('users.id', 'users.name')
            ->get();

        $maxTrips = (float) ($rows->max('trips_count') ?: 1);
        $maxIncome = (float) ($rows->max('income_logist') ?: 1);

        $rows = $rows->map(function ($r) use ($wTrips, $wIncome, $maxTrips, $maxIncome) {
            $score = $wTrips * ((float) $r->trips_count / $maxTrips)
                + $wIncome * ((float) $r->income_logist / $maxIncome);
            $r->score = round($score, 2);

            return $r;
        })->sortByDesc('score')->values();

        // Места
        $rows = $rows->map(function ($r, $i) {
            $r->place = $i + 1;

            return $r;
        });

        $user = auth()->user();
        $totalParticipants = $rows->count();
        if ($user && ! $user->isAdmin()) {
            $rows = $rows->filter(fn ($r) => (int) $r->id === (int) $user->id)->values();
        }

        return [
            'rows' => $rows,
            'totalParticipants' => $totalParticipants,
            'periodLabel' => Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth()->translatedFormat('F Y'),
            'wTrips' => $wTrips,
            'wIncome' => $wIncome,
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
