@props([
    'label' => '',
    'value' => '',
    'hint' => null,
    'accent' => false,
    'warn' => false,
])

@php
    $valueColor = $accent ? '#38BDF8' : ($warn ? '#F59E0B' : '#0B1220');
@endphp

<div class="rounded-xl border p-4" style="border-color:#E5E7EB">
    <div class="text-xs" style="color:#64748B">{{ $label }}</div>
    <div class="text-2xl font-semibold mt-1" style="font-family:var(--font-heading);color:{{ $valueColor }}">{{ $value }}</div>
    @if ($hint) <div class="text-[11px] mt-1" style="color:#64748B">{{ $hint }}</div> @endif
</div>
