<?php

use App\Support\AdminDemoData;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Balance Sheet')] class extends Component {
    /** @return array{liabilities:array<int, array<string, mixed>>, assets:array<int, array<string, mixed>>} */
    #[Computed]
    public function sheet(): array
    {
        return AdminDemoData::balanceSheet();
    }
}; ?>

@php
    $sheet = $this->sheet;
    $totalLiabilities = collect($sheet['liabilities'])->sum('amount');
    $totalAssets = collect($sheet['assets'])->sum('amount');
    $netPosition = $totalAssets - $totalLiabilities;
@endphp

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Balance Sheet')" route="admin.accounting.balance-sheet" :breadcrumb="[__('Accounting'), __('Balance Sheet')]" />

    <x-admin.page-header :title="__('Balance Sheet')">
        <x-slot:actions>
            <flux:button x-data x-on:click="window.print()" icon="printer" size="sm" variant="filled">{{ __('Print') }}</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.stat-strip>
        <x-admin.stat-cell :label="__('Total assets')" :value="'₱'.number_format($totalAssets)" />
        <x-admin.stat-cell :label="__('Total liabilities')" :value="'₱'.number_format($totalLiabilities)" />
        <x-admin.stat-cell :label="__('Net position')" :value="'₱'.number_format($netPosition)" />
    </x-admin.stat-strip>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            <div class="border-b border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-bold uppercase tracking-wide text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900/50">{{ __('Assets (cash on hand)') }}</div>
            @foreach ($sheet['assets'] as $a)
                <div class="flex items-center justify-between border-b border-zinc-100 px-3 py-2.5 last:border-b-0 dark:border-zinc-700/50">
                    <span class="text-sm text-zinc-700 dark:text-zinc-200">{{ $a['label'] }}</span>
                    <span class="tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">₱{{ number_format($a['amount']) }}</span>
                </div>
            @endforeach
            <div class="flex items-center justify-between border-t-2 border-zinc-200 px-3 py-2.5 dark:border-zinc-600">
                <span class="text-sm font-bold">{{ __('Total assets') }}</span>
                <span class="tabular-nums font-bold">₱{{ number_format($totalAssets) }}</span>
            </div>
        </div>

        <div class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            <div class="border-b border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-bold uppercase tracking-wide text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900/50">{{ __('Liabilities (player & agent balances)') }}</div>
            @foreach ($sheet['liabilities'] as $l)
                <div class="flex items-center justify-between border-b border-zinc-100 px-3 py-2.5 last:border-b-0 dark:border-zinc-700/50">
                    <span class="text-sm text-zinc-700 dark:text-zinc-200">{{ $l['label'] }}</span>
                    <span class="tabular-nums font-semibold text-amber-600 dark:text-amber-400">₱{{ number_format($l['amount']) }}</span>
                </div>
            @endforeach
            <div class="flex items-center justify-between border-t-2 border-zinc-200 px-3 py-2.5 dark:border-zinc-600">
                <span class="text-sm font-bold">{{ __('Total liabilities') }}</span>
                <span class="tabular-nums font-bold">₱{{ number_format($totalLiabilities) }}</span>
            </div>
        </div>
    </div>
</div>
