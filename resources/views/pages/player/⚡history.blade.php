<?php

use App\Support\PlayerDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('History')] #[Layout('layouts.player')] class extends Component {
    /** Filter: "all" | "deposit" | "withdraw". */
    public string $filter = 'all';

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, ['all', 'deposit', 'withdraw'], true) ? $filter : 'all';
    }

    /** @return array<int, array{id:int, direction:string, provider:string, amount:int, status:string, reference:string, time:string}> */
    public function transactions(): array
    {
        $transactions = session('player.transactions', PlayerDemoData::seedTransactions());

        if ($this->filter !== 'all') {
            $transactions = array_values(array_filter(
                $transactions,
                fn (array $t): bool => $t['direction'] === $this->filter,
            ));
        }

        return $transactions;
    }
}; ?>

@php
    $transactions = $this->transactions();
    $filters = [
        'all' => __('All'),
        'deposit' => __('Deposits'),
        'withdraw' => __('Withdrawals'),
    ];
    $statusMeta = [
        'completed' => ['color' => 'green', 'label' => __('Completed')],
        'pending' => ['color' => 'amber', 'label' => __('Pending')],
        'cancelled' => ['color' => 'red', 'label' => __('Cancelled')],
    ];
@endphp

<div class="flex flex-col gap-5">
    <h1 class="text-xl font-black text-white">{{ __('Transaction History') }}</h1>

    {{-- Filter pills --}}
    <div class="flex gap-2">
        @foreach ($filters as $key => $label)
            <button type="button" wire:click="setFilter('{{ $key }}')"
                @class([
                    'rounded-full border px-4 py-1.5 text-sm font-medium transition-colors',
                    'border-amber-400 bg-amber-400/15 text-amber-300' => $filter === $key,
                    'border-white/10 bg-zinc-900/60 text-zinc-400' => $filter !== $key,
                ])>{{ $label }}</button>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-white/10 bg-zinc-900/60">
        @forelse ($transactions as $t)
            @php($isDeposit = $t['direction'] === 'deposit')
            <div wire:key="ptxn-{{ $t['id'] }}" class="flex items-center gap-3 border-b border-white/5 p-4 last:border-b-0">
                <span @class([
                    'flex size-9 shrink-0 items-center justify-center rounded-full',
                    'bg-emerald-500/15 text-emerald-400' => $isDeposit,
                    'bg-indigo-500/15 text-indigo-400' => ! $isDeposit,
                ])>
                    <flux:icon :icon="$isDeposit ? 'arrow-down-left' : 'arrow-up-right'" class="size-4" />
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-white">{{ $isDeposit ? __('Deposit') : __('Withdrawal') }}</span>
                        <flux:badge color="zinc" size="sm" inset="top bottom">{{ $t['provider'] }}</flux:badge>
                    </div>
                    <p class="truncate font-mono text-xs text-zinc-500">{{ $t['reference'] }} · {{ $t['time'] }}</p>
                </div>

                <div class="text-end">
                    <p @class([
                        'text-sm font-bold tabular-nums',
                        'text-emerald-400' => $isDeposit,
                        'text-zinc-200' => ! $isDeposit,
                    ])>{{ $isDeposit ? '+' : '−' }}₱{{ number_format($t['amount']) }}</p>
                    <flux:badge :color="$statusMeta[$t['status']]['color']" size="sm" inset="top bottom">{{ $statusMeta[$t['status']]['label'] }}</flux:badge>
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-sm text-zinc-500">{{ __('No transactions yet.') }}</div>
        @endforelse
    </div>
</div>
