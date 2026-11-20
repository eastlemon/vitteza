<x-filament-panels::page>
    @php($data = $this->compute())

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold" style="font-family:var(--font-heading)">Логисты — {{ $data['periodLabel'] }}</h2>
        <div class="flex gap-2">
            <button wire:click="prevPeriod" class="rounded-lg border px-3 py-1.5 text-sm" style="border-color:#E5E7EB">← Пред.</button>
            <button wire:click="nextPeriod" class="rounded-lg border px-3 py-1.5 text-sm" style="border-color:#E5E7EB">След. →</button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border" style="border-color:#E5E7EB">
        <table class="w-full text-sm">
            <thead style="background:#0B1220;color:#E6EDF5;font-family:var(--font-heading)">
                <tr>
                    <th class="text-left px-3 py-2">ФИО логиста</th>
                    <th class="text-left px-3 py-2">E-mail</th>
                    <th class="text-right px-3 py-2">Рейсов</th>
                    <th class="text-left px-3 py-2">Погрузка (первая/последняя)</th>
                    <th class="text-right px-3 py-2">Стоимость, ₽</th>
                    <th class="text-right px-3 py-2">Доход компании, ₽</th>
                    <th class="text-right px-3 py-2">Доход логиста, ₽</th>
                    <th class="text-center px-3 py-2">Статус</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['rows'] as $r)
                    <tr class="border-t" style="border-color:#E5E7EB">
                        <td class="px-3 py-2 font-medium">{{ $r->name }}</td>
                        <td class="px-3 py-2" style="color:#64748B">{{ $r->email }}</td>
                        <td class="px-3 py-2 text-right">{{ $r->trips_count }}</td>
                        <td class="px-3 py-2" style="color:#64748B">
                            @if ($r->first_load) {{ \Carbon\Carbon::parse($r->first_load)->format('d.m.Y') }} / {{ \Carbon\Carbon::parse($r->last_load)->format('d.m.Y') }} @else — @endif
                        </td>
                        <td class="px-3 py-2 text-right">{{ number_format((float) $r->total_cost, 0, ',', ' ') }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format((float) $r->total_income, 0, ',', ' ') }}</td>
                        <td class="px-3 py-2 text-right font-semibold" style="color:#38BDF8">{{ number_format((float) $r->income_logist, 0, ',', ' ') }}</td>
                        <td class="px-3 py-2 text-center">
                            @if ($r->is_active) <span style="color:#22C55E">активен</span> @else <span style="color:#EF4444">блок</span> @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-3 py-6 text-center" style="color:#64748B">Нет данных за период</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
