@props([
    'label' => '',
    'value' => '',
    'count' => null,
])

<div class="flex-1 border-e border-zinc-200 px-3 py-2 last:border-e-0 dark:border-zinc-700">
    <div class="text-xs text-zinc-500">{{ $label }}</div>
    <div class="text-[13px] font-bold tabular-nums text-zinc-900 dark:text-zinc-100">
        {{ $value }}
        @isset($count)
            <span class="text-xs font-normal text-zinc-400">({{ $count }})</span>
        @endisset
    </div>
</div>
