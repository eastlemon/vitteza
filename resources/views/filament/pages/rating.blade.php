<x-filament-panels::page>
    @php($data = $this->compute())

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-xl font-semibold" style="font-family:var(--font-heading)">{{ __('rating.title') }} — {{ $data['periodLabel'] }}</h2>
            <p class="text-sm" style="color:#64748B">{{ __('rating.weights_line', ['trips' => $data['wTrips'], 'income' => $data['wIncome']]) }} {{ __('rating.participants', ['count' => $data['totalParticipants']]) }}</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="prevPeriod" class="rounded-lg border px-3 py-1.5 text-sm" style="border-color:#E5E7EB">{{ __('common.prev_period') }}</button>
            <button wire:click="nextPeriod" class="rounded-lg border px-3 py-1.5 text-sm" style="border-color:#E5E7EB">{{ __('common.next_period') }}</button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border" style="border-color:#E5E7EB">
        <table class="w-full text-sm">
            <thead style="background:#0B1220;color:#E6EDF5;font-family:var(--font-heading)">
                <tr>
                    <th class="text-center px-3 py-2">{{ __('rating.cols.place') }}</th>
                    <th class="text-left px-3 py-2">{{ __('common.logistic') }}</th>
                    <th class="text-right px-3 py-2">{{ __('rating.cols.trips_paid') }}</th>
                    <th class="text-right px-3 py-2">{{ __('common.income_logist') }}</th>
                    <th class="text-right px-3 py-2">{{ __('common.income_total') }}</th>
                    <th class="text-right px-3 py-2">{{ __('rating.cols.score') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['rows'] as $r)
                    <tr class="border-t" style="border-color:#E5E7EB">
                        <td class="px-3 py-2 text-center font-semibold" style="color:#38BDF8">{{ $r->place }}</td>
                        <td class="px-3 py-2 font-medium">{{ $r->name }}</td>
                        <td class="px-3 py-2 text-right">{{ $r->trips_count }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format((float) $r->income_logist, 0, ',', ' ') }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format((float) $r->income_total, 0, ',', ' ') }}</td>
                        <td class="px-3 py-2 text-right font-semibold">{{ number_format((float) $r->score, 2, ',', ' ') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-6 text-center" style="color:#64748B">{{ __('common.no_period_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
