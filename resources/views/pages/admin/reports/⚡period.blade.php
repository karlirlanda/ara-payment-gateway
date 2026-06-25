<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Period Sales Report')] class extends Component {
    use HasListToolbar;

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function rows(): array
    {
        return AdminDemoData::periodSales();
    }

    protected function reloadListData(): void {}

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect(AdminDemoData::periodSales());
    }

    protected function toolbarExportColumns(): array
    {
        return ['period', 'deposit', 'withdraw', 'net', 'players'];
    }

    protected function toolbarExportName(): string
    {
        return 'period-sales';
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Period Sales')" route="admin.reports.period" :breadcrumb="[__('Reports'), __('Period Sales')]" />

    <x-admin.page-header :title="__('Period Sales Report')">
        <x-slot:actions>
            <flux:button wire:click="export(false)" icon="arrow-down-tray" size="sm" variant="subtle">{{ __('CSV') }}</flux:button>
            <flux:button x-data x-on:click="window.print()" icon="printer" size="sm" variant="filled">{{ __('Print') }}</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table :selectable="false" stick-first>
        <x-slot:head><tr>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Period') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Deposits') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Withdrawals') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Net') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('New Players') }}</th>
        </tr></x-slot:head>
        @foreach ($this->rows as $r)
            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2 font-semibold text-zinc-800 dark:text-zinc-100">{{ $r['period'] }}</td>
                <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($r['deposit']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums text-amber-600 dark:text-amber-400">₱{{ number_format($r['withdraw']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">₱{{ number_format($r['net']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ number_format($r['players']) }}</td>
            </tr>
        @endforeach
    </x-admin.table>
</div>
