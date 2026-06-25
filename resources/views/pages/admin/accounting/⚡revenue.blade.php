<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Revenue Summary')] class extends Component {
    use HasListToolbar;

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function rows(): array
    {
        return AdminDemoData::revenueSummary();
    }

    #[Computed]
    public function peak(): int
    {
        return (int) collect(AdminDemoData::revenueSummary())->max('net') ?: 1;
    }

    protected function reloadListData(): void {}

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect(AdminDemoData::revenueSummary());
    }

    protected function toolbarExportColumns(): array
    {
        return ['month', 'revenue', 'withdrawals', 'bonus', 'net'];
    }

    protected function toolbarExportName(): string
    {
        return 'revenue-summary';
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Revenue Summary')" route="admin.accounting.revenue" :breadcrumb="[__('Accounting'), __('Revenue Summary')]" />

    <x-admin.page-header :title="__('Revenue Summary')">
        <x-slot:actions>
            <flux:button wire:click="export(false)" icon="arrow-down-tray" size="sm" variant="subtle">{{ __('CSV') }}</flux:button>
            <flux:button x-data x-on:click="window.print()" icon="printer" size="sm" variant="filled">{{ __('Print') }}</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-3 text-xs font-semibold text-zinc-500">{{ __('Net platform profit by month (₱)') }}</div>
        <div class="flex h-40 items-end gap-3">
            @foreach (array_reverse($this->rows) as $r)
                <div class="flex flex-1 flex-col items-center gap-1">
                    <div class="flex h-32 w-full items-end justify-center">
                        <div class="w-2/3 rounded-t bg-emerald-500" style="height: {{ max(2, (int) round($r['net'] / $this->peak * 100)) }}%" title="₱{{ number_format($r['net']) }}"></div>
                    </div>
                    <div class="text-[10px] font-medium text-zinc-400">{{ $r['month'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <x-admin.table :selectable="false" stick-first>
        <x-slot:head><tr>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Month') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Deposit Revenue') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Withdrawal Liability') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Bonus Expense') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Net Profit') }}</th>
        </tr></x-slot:head>
        @foreach ($this->rows as $r)
            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2 font-semibold text-zinc-800 dark:text-zinc-100">{{ $r['month'] }}</td>
                <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($r['revenue']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums text-amber-600 dark:text-amber-400">₱{{ number_format($r['withdrawals']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums text-zinc-500">₱{{ number_format($r['bonus']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">₱{{ number_format($r['net']) }}</td>
            </tr>
        @endforeach
    </x-admin.table>
</div>
