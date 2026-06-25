@props([
    'label' => '',
    'value' => '0',
    'sublabel' => '',
    'icon' => 'chart-bar',
    'color' => 'indigo',   // indigo | emerald | violet
])

@php
    $palette = [
        'emerald' => 'bg-emerald-500',
        'indigo'  => 'bg-indigo-500',
        'violet'  => 'bg-violet-500',
    ];
    $bg = $palette[$color] ?? $palette['indigo'];
@endphp

<div {{ $attributes->merge(['class' => "relative overflow-hidden p-4 text-white $bg"]) }}>
    <flux:icon :icon="$icon" aria-hidden="true" class="absolute -right-2 -bottom-2 size-20 opacity-15" />
    <div class="text-xs font-medium opacity-90">{{ $label }}</div>
    <div class="mt-1 text-3xl font-bold tabular-nums">{{ $value }}</div>
    <div class="mt-1 text-[11px] opacity-80">{{ $sublabel }}</div>
</div>
