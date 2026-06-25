@props([
    'level' => 1,
    'id' => '',
])

@php
    $palette = [
        1 => 'bg-indigo-600', 2 => 'bg-sky-600', 3 => 'bg-emerald-600',
        4 => 'bg-amber-600', 5 => 'bg-rose-600',
    ];
    $bg = $palette[$level] ?? 'bg-zinc-500';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5']) }}>
    <span class="inline-flex size-4 items-center justify-center text-[10px] font-bold text-white {{ $bg }}">{{ $level }}</span>
    <span class="font-medium">{{ $id }}</span>
</span>
