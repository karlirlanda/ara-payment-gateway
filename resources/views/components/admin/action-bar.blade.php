@php
    $b = \App\Support\AdminDemoData::actionBadges();
@endphp

<div {{ $attributes->merge(['class' => 'flex items-stretch gap-1.5']) }}>
    @php
        $actions = [
            [__('Deposits'), 'arrow-down-circle', $b['deposit'], route('admin.transactions', 'deposit')],
            [__('Withdrawals'), 'arrow-up-circle', $b['withdraw'], route('admin.transactions', 'withdraw')],
            [__('Approvals'), 'check-badge', $b['approval'], route('admin.withdrawals.approvals')],
        ];
    @endphp
    @foreach ($actions as [$label, $icon, $count, $href])
        <a href="{{ $href }}" wire:navigate class="relative flex flex-col items-center gap-1 px-3 py-1.5 text-[11px] text-zinc-600 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-700/40">
            <flux:icon :icon="$icon" class="size-4 text-zinc-400" aria-hidden="true" />
            <span>{{ $label }}</span>
            @if ($count > 0)
                <span class="absolute -end-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white ring-2 ring-white dark:ring-zinc-900">{{ $count > 99 ? '99+' : $count }}</span>
            @endif
        </a>
    @endforeach
</div>
