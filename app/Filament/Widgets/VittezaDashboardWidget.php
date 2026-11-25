<?php

namespace App\Filament\Widgets;

use App\Enums\TripStatus;
use App\Models\IncomeEntry;
use App\Models\Trip;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

/**
 * VITTEZA main dashboard (Spec §15): period = calendar month with a switcher
 * (§15.3 "reset" = changing the calculation window). Widgets: trip count,
 * logist income, company income, per-day chart, calendar with highlighting, top customers,
 * latest trips. Extra metrics for the administrator (§15.6).
 */
class VittezaDashboardWidget extends Widget
{
    protected static bool $isDiscovered = false;

    /** The main dashboard renders immediately, without a lazy placeholder. */
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

        // Base trip query for the period (for a user — own trips only)
        $tripQuery = Trip::query()
            ->whereBetween('load_date', [$start->toDateString(), $end->toDateString()]);
        if (! $isAdmin && $user) {
            $tripQuery->where('logistic_id', $user->id);
        }

        $tripsCount = (clone $tripQuery)->count();
        $totalCost = (float) (clone $tripQuery)->sum('cost');

        // Income — from income_entries for the period (paid trips only, §15.5)
        $incomeQuery = IncomeEntry::query()
            ->where('period_year', $this->year)
            ->where('period_month', $this->month);
        if (! $isAdmin && $user) {
            $incomeQuery->where('user_id', $user->id);
        }
        $incomeLogist = (float) (clone $incomeQuery)->sum('income_logist');
        $incomeTotal = (float) (clone $incomeQuery)->sum('income_total');

        // Per day of month: trip count + logist income (by load_date).
        // DAYOFMONTH(): MySQL dialect — SQLite is removed from the project, prod and tests are on MySQL.
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

        // Calendar: highlight days with completed/paid trips (§15.3)
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

        // Calendar grid (Monday is the first day)
        $firstWeekday = (int) $start->dayOfWeekIso; // 1=Mon..7=Sun
        $leadingBlanks = $firstWeekday - 1;

        // Top customers by cost for the period
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

        // Latest 10 trips
        $recentQ = Trip::query()->with(['logistic', 'customer', 'driver'])->latest('created_at');
        if (! $isAdmin && $user) {
            $recentQ->where('logistic_id', $user->id);
        }
        $recentTrips = $recentQ->limit(10)->get();

        // Trips of the selected day (calendar click)
        $selectedDayTrips = collect();
        if ($this->selectedDay) {
            $date = Carbon::createFromDate($this->year, $this->month, $this->selectedDay)->toDateString();
            $sq = Trip::query()->with(['customer', 'driver'])->whereDate('load_date', $date);
            if (! $isAdmin && $user) {
                $sq->where('logistic_id', $user->id);
            }
            $selectedDayTrips = $sq->orderBy('route')->get();
        }

        // Extra administrator metrics (§15.6)
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
