<?php

namespace App\Filament\Pages;

use App\Models\User;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

/**
 * The "Logists" tab (Spec §12) is virtual: an aggregation over users + trips
 * for the selected calendar month (current by default, §12.3).
 */
class LogistsPage extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationLabel(): string
    {
        return __('logists.nav');
    }

    protected static ?string $slug = 'logists';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.logists';

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
        $d = Carbon::createFromDate($this->year, $this->month, 1)->addMonths($delta);
        $this->year = (int) $d->year;
        $this->month = (int) $d->month;
    }

    /** @return array{rows: mixed, periodLabel: string} */
    public function compute(): array
    {
        $start = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $rows = User::where('role', 'user')
            ->leftJoin('trips', function ($j) use ($start, $end) {
                $j->on('trips.logistic_id', '=', 'users.id')
                    ->whereBetween('trips.load_date', [$start->toDateString(), $end->toDateString()]);
            })
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.is_active',
                DB::raw('COUNT(trips.id) AS trips_count'),
                DB::raw('MIN(trips.load_date) AS first_load'),
                DB::raw('MAX(trips.load_date) AS last_load'),
                DB::raw('COALESCE(SUM(trips.cost),0) AS total_cost'),
                DB::raw('COALESCE(SUM(trips.income_total),0) AS total_income'),
                DB::raw('COALESCE(SUM(trips.income_logist),0) AS income_logist'),
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.is_active')
            ->orderByDesc('income_logist')
            ->get();

        return [
            'rows' => $rows,
            'periodLabel' => $start->translatedFormat('F Y'),
        ];
    }

    /**
     * The "Logists" section — admin only (see EmployeeResource:
     * administrative employee data is not available to regular roles).
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() === true;
    }
}
