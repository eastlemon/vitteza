<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        <div class="rounded-xl border p-6 fi-card" style="border-color:var(--color-brand-border-light,#E5E7EB)">
            <h2 class="text-lg font-semibold mb-1" style="font-family:var(--font-heading)">{{ __('settings.finance_title') }}</h2>
            <p class="text-sm mb-4" style="color:#64748B">{{ __('settings.finance_formula') }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <label class="block">
                    <span class="text-sm font-medium">{{ __('settings.income_share') }}</span>
                    <input type="number" step="0.01" min="0" max="1" wire:model="income_share" class="mt-1 block w-full rounded-lg border px-3 py-2" style="border-color:#E5E7EB">
                    <span class="text-xs" style="color:#64748B">{{ __('settings.hint_income_share') }}</span>
                </label>
                <label class="block">
                    <span class="text-sm font-medium">{{ __('settings.logist_share') }}</span>
                    <input type="number" step="0.01" min="0" max="1" wire:model="logist_share" class="mt-1 block w-full rounded-lg border px-3 py-2" style="border-color:#E5E7EB">
                    <span class="text-xs" style="color:#64748B">{{ __('settings.hint_logist_share') }}</span>
                </label>
                <label class="block">
                    <span class="text-sm font-medium">{{ __('settings.trigger') }}</span>
                    <select wire:model="income_trigger_status" class="mt-1 block w-full rounded-lg border px-3 py-2" style="border-color:#E5E7EB">
                        @foreach (\App\Enums\TripStatus::options() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="text-xs" style="color:#64748B">{{ __('settings.hint_trigger') }}</span>
                </label>
            </div>
        </div>

        <div class="rounded-xl border p-6 fi-card" style="border-color:var(--color-brand-border-light,#E5E7EB)">
            <h2 class="text-lg font-semibold mb-1" style="font-family:var(--font-heading)">{{ __('settings.weights_title') }}</h2>
            <p class="text-sm mb-4" style="color:#64748B">{{ __('settings.weights_formula') }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-sm font-medium">{{ __('settings.w_trips') }}</span>
                    <input type="number" step="0.01" min="0" max="1" wire:model="rating_w_trips" class="mt-1 block w-full rounded-lg border px-3 py-2" style="border-color:#E5E7EB">
                    <span class="text-xs" style="color:#64748B">{{ __('settings.hint_w_trips') }}</span>
                </label>
                <label class="block">
                    <span class="text-sm font-medium">{{ __('settings.w_income') }}</span>
                    <input type="number" step="0.01" min="0" max="1" wire:model="rating_w_income" class="mt-1 block w-full rounded-lg border px-3 py-2" style="border-color:#E5E7EB">
                    <span class="text-xs" style="color:#64748B">{{ __('settings.hint_w_income') }}</span>
                </label>
            </div>
        </div>

        <button type="submit" class="rounded-lg px-5 py-2.5 font-semibold" style="background-color:var(--color-brand-accent,#38BDF8);color:#0B1220">
            {{ __('common.save') }}
        </button>
    </form>
</x-filament-panels::page>
