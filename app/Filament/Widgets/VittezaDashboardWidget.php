<?php

namespace App\Filament\Widgets;

use App\Enums\TripStatus;
use App\Models\IncomeEntry;
use App\Models\Trip;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

/**
 * Главный дашборд VITTEZA (ТЗ §15): период = календарный месяц с переключателем
 * (§15.3 «обнуление» = смена расчётного окна). Виджеты: число рейсов, доход
 * логиста, доход компании, график по дням, календарь с подсветкой, топ заказчиков,
 * последние рейсы. Администратору — доп. показатели (§15.6).
 */
class VittezaDashboardWidget extends Widget
{
    protected static bool $isDiscovered = false;

    /** Главный дашборд рендерится сразу, без lazy-плейсхолдера. */
    protected static ?bool $isLazyLoaded = false;

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -3;

    protected string $view = 'filament.widgets.vitteza-dashboard';

    public int $year;
    public int $month;
    public ?int $selectedDay = null;

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

    public function selectDay(int $day): void
    {
        $this->selectedDay = $this->selectedDay === $day ? null : $day;
    }

    protected function shift(int $delta): void
    {
        $c = Carbon::createFromDate($this->year, $this->month, 1)->addMonths($delta);
        $this->year = (int) $c->year;
        $this->month = (int) $c->month;
        $this->selectedDay = null;
    }

    public function compute(): array
    {
        $user = auth()->user();
        $isAdmin = $user?->isAdmin() ?? false;

        $start = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $daysInMonth = (int) $start->daysInMonth;

        // Базовый запрос рейсов за период (для пользователя — только свои)
        $tripQuery = Trip::query()
            ->whereBetween('load_date', [$start->toDateString(), $end->toDateString()]);
        if (! $isAdmin && $user) {
            $tripQuery->where('logistic_id', $user->id);
        }

        $tripsCount = (clone $tripQuery)->count();
        $totalCost = (float) (clone $tripQuery)->sum('cost');

        // Доход — из income_entries за период (только оплаченные рейсы, §15.5)
        $incomeQuery = IncomeEntry::query()
            ->where('period_year', $this->year)
            ->where('period_month', $this->month);
        if (! $isAdmin && $user) {
            $incomeQuery->where('user_id', $user->id);
        }
        $incomeLogist = (float) (clone $incomeQuery)->sum('income_logist');
        $incomeTotal = (float) (clone $incomeQuery)->sum('income_total');

        // По дням месяца: число рейсов + доход логиста (по load_date).
        // DAYOFMONTH(): MySQL-диалект — SQLite из проекта удалён, прод и тесты на MySQL.
        $byDayRows = (clone $tripQuery)
            ->selectRaw("DAYOFMONTH(load_date) as d, COUNT(*) as cnt, COALESCE(SUM(income_logist),0) as inc")
            ->groupBy(DB::raw('DAYOFMONTH(load_date)'))
            ->get()
            ->keyBy('d');
        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $row = $byDayRows->get($d);
            $days[] = [
                'day' => $d,
                'trips' => (int) ($row?->cnt ?? 0),
                'income' => (float) ($row?->inc ?? 0),
            ];
        }
        $maxTrips = max(array_map(fn ($x) => $x['trips'], $days)) ?: 1;
        $maxIncome = max(array_map(fn ($x) => $x['income'], $days)) ?: 1;

        // Календарь: подсветка дней с завершёнными/оплаченными рейсами (§15.3)
        $completedStatuses = ['completed', 'paid'];
        $calRows = (clone $tripQuery)
            ->whereIn('status', $completedStatuses)
            ->selectRaw("DAYOFMONTH(load_date) as d, COUNT(*) as cnt")
            ->groupBy(DB::raw('DAYOFMONTH(load_date)'))
            ->pluck('cnt', 'd');
        $calendarCounts = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $calendarCounts[$d] = (int) ($calRows[$d] ?? 0);
        }

        // Сетка календаря (понедельник — первый день)
        $firstWeekday = (int) $start->dayOfWeekIso; // 1=Mon..7=Sun
        $leadingBlanks = $firstWeekday - 1;

        // Топ заказчиков по стоимости за период
        $topCustomers = (clone $tripQuery)
            ->join('customers', 'customers.id', '=', 'trips.customer_id')
            ->select(
                'customers.name',
                DB::raw('COUNT(trips.id) AS cnt'),
                DB::raw('COALESCE(SUM(trips.cost),0) AS sum_cost'),
                DB::raw('COALESCE(SUM(trips.income_logist),0) AS sum_inc'),
            )
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('sum_cost')
            ->limit(5)
            ->get();

        // Последние 10 рейсов
        $recentQ = Trip::query()->with(['logistic', 'customer', 'driver'])->latest('created_at');
        if (! $isAdmin && $user) {
            $recentQ->where('logistic_id', $user->id);
        }
        $recentTrips = $recentQ->limit(10)->get();

        // Рейсы выбранного дня (клик по календарю)
        $selectedDayTrips = collect();
        if ($this->selectedDay) {
            $date = Carbon::createFromDate($this->year, $this->month, $this->selectedDay)->toDateString();
            $sq = Trip::query()->with(['customer', 'driver'])->whereDate('load_date', $date);
            if (! $isAdmin && $user) {
                $sq->where('logistic_id', $user->id);
            }
            $selectedDayTrips = $sq->orderBy('route')->get();
        }

        // Доп. показатели администратора (§15.6)
        $adminExtras = null;
        if ($isAdmin) {
            $unfinished = (clone $tripQuery)->whereNotIn('status', ['paid', 'cancelled'])->count();
            $overdue = (clone $tripQuery)
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->whereNotNull('payment_due_date')
                ->whereDate('payment_due_date', '<', Carbon::today())
                ->whereNull('payment_date')
                ->count();
            $avgCheck = $tripsCount > 0 ? round($totalCost / $tripsCount, 2) : 0;
            $avgIncome = $tripsCount > 0 ? round($incomeTotal / $tripsCount, 2) : 0;
            $adminExtras = [
                'unfinished' => $unfinished,
                'overdue' => $overdue,
                'avg_check' => $avgCheck,
                'avg_income' => $avgIncome,
            ];
        }

        return [
            'isAdmin' => $isAdmin,
            'periodLabel' => $start->translatedFormat('F Y'),
            'tripsCount' => $tripsCount,
            'totalCost' => $totalCost,
            'incomeLogist' => $incomeLogist,
            'incomeTotal' => $incomeTotal,
            'days' => $days,
            'maxTrips' => $maxTrips,
            'maxIncome' => $maxIncome,
            'calendarCounts' => $calendarCounts,
            'leadingBlanks' => $leadingBlanks,
            'daysInMonth' => $daysInMonth,
            'topCustomers' => $topCustomers,
            'recentTrips' => $recentTrips,
            'selectedDay' => $this->selectedDay,
            'selectedDayTrips' => $selectedDayTrips,
            'adminExtras' => $adminExtras,
            'statusLabels' => TripStatus::labels(),
        ];
    }
}
