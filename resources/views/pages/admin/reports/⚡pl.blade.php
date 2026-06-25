<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profit & Loss Report')] class extends Component {
    use HasListToolbar;

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function rows(): array
    {
        return AdminDemoData::profitLoss();
    }

    /** @return array{gross:int, bonus:int, promo:int, profit:int} */
    #[Computed]
    public function totals(): array
    {
        $r = collect(AdminDemoData::profitLoss());

        return [
            'gross' => (int) $r->sum('gross'),
            'bonus' => (int) $r->sum('bonus'),
            'promo' => (int) $r->sum('promo'),
            'profit' => (int) $r->sum('profit'),
        ];
    }

    protected function reloadListData(): void {}

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect(AdminDemoData::profitLoss());
    }

    protected function toolbarExportColumns(): array
    {
        return ['label', 'gross', 'bonus', 'promo', 'profit'];
    }

    protected function toolbarExportName(): string
    {
        return 'profit-loss';
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Profit & Loss')" route="admin.reports.pl" :breadcrumb="[__('Reports'), __('Profit & Loss')]" />

    <x-admin.page-header :title="__('Profit & Loss Report')">
        <x-slot:actions>
            <flux:button wire:click="export(false)" icon="arrow-down-tray" size="sm" variant="subtle">{{ __('CSV') }}</flux:button>
            <flux:button x-data x-on:click="window.print()" icon="printer" size="sm" variant="filled">{{ __('Print') }}</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.stat-strip>
        <x-admin.stat-cell :label="__('Gross revenue')" :value="'₱'.number_format($this->totals['gross'])" />
        <x-admin.stat-cell :label="__('Bonus cost')" :value="'₱'.number_format($this->totals['bonus'])" />
        <x-admin.stat-cell :label="__('Promo cost')" :value="'₱'.number_format($this->totals['promo'])" />
        <x-admin.stat-cell :label="__('Operating profit')" :value="'₱'.number_format($this->totals['profit'])" />
    </x-admin.stat-strip>

    <x-admin.table :selectable="false" stick-first>
        <x-slot:head><tr>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Period') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Gross Revenue') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Bonus Cost') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Promo Cost') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Operating Profit') }}</th>
        </tr></x-slot:head>
        @foreach ($this->rows as $r)
            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2 font-semibold text-zinc-800 dark:text-zinc-100">{{ $r['label'] }}</td>
                <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($r['gross']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums text-amber-600 dark:text-amber-400">₱{{ number_format($r['bonus']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums text-amber-600 dark:text-amber-400">₱{{ number_format($r['promo']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">₱{{ number_format($r['profit']) }}</td>
            </tr>
        @endforeach
    </x-admin.table>
</div>
