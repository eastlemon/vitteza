@php($d = $this->compute())

<div class="space-y-6">
    {{-- Period switcher --}}
    <div class="flex items-center justify-between flex-wrap gap-2">
        <h2 class="text-xl font-semibold" style="font-family:var(--font-heading)">{{ __('dashboard.title') }} — {{ $d['periodLabel'] }}</h2>
        <div class="flex items-center gap-2">
            <button wire:click="prevPeriod" class="rounded-lg border px-3 py-1.5 text-sm" style="border-color:#E5E7EB">{{ __('common.prev_period') }}</button>
            <span class="text-sm" style="color:#64748B">{{ __('dashboard.monthly_reset') }}</span>
            <button wire:click="nextPeriod" class="rounded-lg border px-3 py-1.5 text-sm" style="border-color:#E5E7EB">{{ __('common.next_period') }}</button>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        <x-vitteza-stat label="{{ __('dashboard.stats.trips_count') }}" value="{{ number_format($d['tripsCount'], 0, ',', ' ') }}" />
        <x-vitteza-stat label="{{ __('dashboard.stats.total_cost') }}" value="{{ number_format($d['totalCost'], 0, ',', ' ') }}" />
        <x-vitteza-stat label="{{ __('dashboard.stats.income_total') }}" value="{{ number_format($d['incomeTotal'], 0, ',', ' ') }}" hint="{{ __('dashboard.stats.income_total_hint') }}" />
        <x-vitteza-stat label="{{ __('dashboard.stats.income_logist') }}" value="{{ number_format($d['incomeLogist'], 0, ',', ' ') }}" accent />
        @if ($d['isAdmin'] && $d['adminExtras'])
            <x-vitteza-stat label="{{ __('dashboard.stats.avg_check') }}" value="{{ number_format($d['adminExtras']['avg_check'], 0, ',', ' ') }}" />
            <x-vitteza-stat label="{{ __('dashboard.stats.avg_income') }}" value="{{ number_format($d['adminExtras']['avg_income'], 0, ',', ' ') }}" />
            <x-vitteza-stat label="{{ __('dashboard.stats.unfinished') }}" value="{{ number_format($d['adminExtras']['unfinished'], 0, ',', ' ') }}" />
            <x-vitteza-stat label="{{ __('dashboard.stats.overdue') }}" value="{{ number_format($d['adminExtras']['overdue'], 0, ',', ' ') }}" hint="{{ __('dashboard.stats.overdue_hint') }}" warn />
        @endif
    </div>

    {{-- Per-day charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="rounded-xl border p-4" style="border-color:#E5E7EB">
            <div class="text-sm font-medium mb-2" style="font-family:var(--font-heading)">{{ __('dashboard.charts.trips_by_day') }}</div>
            <div class="flex items-end gap-px h-28">
                @foreach ($d['days'] as $day)
                    <div wire:click="selectDay({{ $day['day'] }})" class="flex-1 flex flex-col justify-end h-full cursor-pointer" title="{{ __('dashboard.day_trips', ['day' => $day['day'], 'count' => $day['trips']]) }}">
                        @if ($day['trips'] > 0)
                            <div style="height:{{ $d['maxTrips'] > 0 ? max(6, round($day['trips'] / $d['maxTrips'] * 100)) : 0 }}%;background:#38BDF8" class="w-full rounded-t-sm"></div>
                        @else
                            <div style="height:2px;background:#E5E7EB" class="w-full"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="rounded-xl border p-4" style="border-color:#E5E7EB">
            <div class="text-sm font-medium mb-2" style="font-family:var(--font-heading)">{{ __('dashboard.charts.income_by_day') }}</div>
            <div class="flex items-end gap-px h-28">
                @foreach ($d['days'] as $day)
                    <div wire:click="selectDay({{ $day['day'] }})" class="flex-1 flex flex-col justify-end h-full cursor-pointer" title="{{ $day['day'] }}: {{ number_format($day['income'], 0, ',', ' ') }} ₽">
                        @if ($day['income'] > 0)
                            <div style="height:{{ $d['maxIncome'] > 0 ? max(6, round($day['income'] / $d['maxIncome'] * 100)) : 0 }}%;background:#0B1220" class="w-full rounded-t-sm"></div>
                        @else
                            <div style="height:2px;background:#E5E7EB" class="w-full"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Calendar --}}
    <div class="rounded-xl border p-4" style="border-color:#E5E7EB">
        <div class="text-sm font-medium mb-3" style="font-family:var(--font-heading)">{{ __('dashboard.calendar.title') }}</div>
        <div class="grid grid-cols-7 gap-1 text-center text-xs" style="color:#64748B">
            @foreach (__('dashboard.weekdays') as $wd) <div class="py-1">{{ $wd }}</div> @endforeach
        </div>
        <div class="grid grid-cols-7 gap-1 text-center mt-1">
            @for ($i = 0; $i < $d['leadingBlanks']; $i++) <div></div> @endfor
            @for ($day = 1; $day <= $d['daysInMonth']; $day++)
                @php($cnt = $d['calendarCounts'][$day] ?? 0)
                <button wire:click="selectDay({{ $day }})"
                    @if ($d['selectedDay'] === $day) style="background:#0B1220;color:#38BDF8" @elseif ($cnt > 0) style="background:rgba(56,189,248,0.18);color:#0B1220" @else style="color:#64748B" @endif
                    class="rounded-md py-2 text-sm relative">
                    {{ $day }}
                    @if ($cnt > 0) <span class="absolute -top-1 -right-1 text-[10px] rounded-full px-1" style="background:#38BDF8;color:#0B1220">{{ $cnt }}</span> @endif
                </button>
            @endfor
        </div>
    </div>

    {{-- Trips of the selected day --}}
    @if ($d['selectedDay'])
        <div class="rounded-xl border p-4" style="border-color:#E5E7EB">
            <div class="flex items-center justify-between mb-2">
                <div class="text-sm font-medium" style="font-family:var(--font-heading)">{{ __('dashboard.selected_day') }} {{ sprintf('%02d', $d['selectedDay']) }}.{{ sprintf('%02d', $this->month) }}.{{ $this->year }}</div>
                <button wire:click="selectDay({{ $d['selectedDay'] }})" class="text-xs" style="color:#64748B">{{ __('common.close') }}</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead style="color:#64748B"><tr><th class="text-left py-1">{{ __('dashboard.table.route') }}</th><th class="text-left">{{ __('dashboard.table.customer') }}</th><th class="text-left">{{ __('dashboard.table.driver') }}</th><th class="text-right">{{ __('dashboard.table.cost') }}</th><th class="text-left">{{ __('dashboard.table.status') }}</th></tr></thead>
                    <tbody>
                        @forelse ($d['selectedDayTrips'] as $t)
                            <tr class="border-t" style="border-color:#E5E7EB"><td class="py-1">{{ $t->route }}</td><td>{{ $t->customer?->name }}</td><td>{{ $t->driver?->full_name }}</td><td class="text-right">{{ number_format((float) $t->cost, 0, ',', ' ') }}</td><td>{{ $d['statusLabels'][$t->status] ?? $t->status }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="py-3 text-center" style="color:#64748B">{{ __('common.no_trips') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Top customers + latest trips --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="rounded-xl border p-4" style="border-color:#E5E7EB">
            <div class="text-sm font-medium mb-2" style="font-family:var(--font-heading)">{{ __('dashboard.top_customers') }}</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead style="color:#64748B"><tr><th class="text-left py-1">{{ __('dashboard.table.customer') }}</th><th class="text-right">{{ __('dashboard.table.trips') }}</th><th class="text-right">{{ __('common.cost') }}</th><th class="text-right">{{ __('dashboard.table.income') }}</th></tr></thead>
                    <tbody>
                        @forelse ($d['topCustomers'] as $c)
                            <tr class="border-t" style="border-color:#E5E7EB"><td class="py-1">{{ $c->name }}</td><td class="text-right">{{ $c->cnt }}</td><td class="text-right">{{ number_format((float) $c->sum_cost, 0, ',', ' ') }}</td><td class="text-right">{{ number_format((float) $c->sum_inc, 0, ',', ' ') }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="py-3 text-center" style="color:#64748B">{{ __('common.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="rounded-xl border p-4" style="border-color:#E5E7EB">
            <div class="text-sm font-medium mb-2" style="font-family:var(--font-heading)">{{ __('dashboard.latest_trips') }}</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead style="color:#64748B"><tr><th class="text-left py-1">{{ __('dashboard.table.date') }}</th><th class="text-left">{{ __('dashboard.table.route') }}</th><th class="text-left">{{ __('dashboard.table.logistic') }}</th><th class="text-left">{{ __('dashboard.table.status') }}</th></tr></thead>
                    <tbody>
                        @forelse ($d['recentTrips'] as $t)
                            <tr class="border-t" style="border-color:#E5E7EB"><td class="py-1">{{ $t->load_date?->format('d.m.Y') }}</td><td>{{ \Illuminate\Support\Str::limit($t->route, 22) }}</td><td>{{ $t->logistic?->name }}</td><td>{{ $d['statusLabels'][$t->status] ?? $t->status }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="py-3 text-center" style="color:#64748B">{{ __('common.no_trips') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
