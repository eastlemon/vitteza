<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        <div class="rounded-xl border p-6 fi-card" style="border-color:var(--color-brand-border-light,#E5E7EB)">
            <h2 class="text-lg font-semibold mb-1" style="font-family:var(--font-heading)">Финансовая формула (§15.4)</h2>
            <p class="text-sm mb-4" style="color:#64748B">Доход компании = стоимость рейса × доля дохода. Доход логиста = доход компании × доля логиста.</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <label class="block">
                    <span class="text-sm font-medium">Доля дохода (от стоимости)</span>
                    <input type="number" step="0.01" min="0" max="1" wire:model="income_share" class="mt-1 block w-full rounded-lg border px-3 py-2" style="border-color:#E5E7EB">
                    <span class="text-xs" style="color:#64748B">income_share (по умолч. 0,20)</span>
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Доля логиста (от дохода)</span>
                    <input type="number" step="0.01" min="0" max="1" wire:model="logist_share" class="mt-1 block w-full rounded-lg border px-3 py-2" style="border-color:#E5E7EB">
                    <span class="text-xs" style="color:#64748B">logist_share (по умолч. 0,50)</span>
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Статус-триггер дохода</span>
                    <select wire:model="income_trigger_status" class="mt-1 block w-full rounded-lg border px-3 py-2" style="border-color:#E5E7EB">
                        @foreach (\App\Enums\TripStatus::options() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="text-xs" style="color:#64748B">income_trigger_status (по умолч. paid)</span>
                </label>
            </div>
        </div>

        <div class="rounded-xl border p-6 fi-card" style="border-color:var(--color-brand-border-light,#E5E7EB)">
            <h2 class="text-lg font-semibold mb-1" style="font-family:var(--font-heading)">Веса рейтинга логистов (§16.2)</h2>
            <p class="text-sm mb-4" style="color:#64748B">Балл = W_рейсов × (рейсы/max) + W_дохода × (доход/max). Сумма весов обычно = 1.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-sm font-medium">Вес числа рейсов</span>
                    <input type="number" step="0.01" min="0" max="1" wire:model="rating_w_trips" class="mt-1 block w-full rounded-lg border px-3 py-2" style="border-color:#E5E7EB">
                    <span class="text-xs" style="color:#64748B">rating_w_trips (по умолч. 0,40)</span>
                </label>
                <label class="block">
                    <span class="text-sm font-medium">Вес дохода</span>
                    <input type="number" step="0.01" min="0" max="1" wire:model="rating_w_income" class="mt-1 block w-full rounded-lg border px-3 py-2" style="border-color:#E5E7EB">
                    <span class="text-xs" style="color:#64748B">rating_w_income (по умолч. 0,60)</span>
                </label>
            </div>
        </div>

        <button type="submit" class="rounded-lg px-5 py-2.5 font-semibold" style="background-color:var(--color-brand-accent,#38BDF8);color:#0B1220">
            Сохранить
        </button>
    </form>
</x-filament-panels::page>
