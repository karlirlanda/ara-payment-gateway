<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Coupon Usage Report')] class extends Component {
    use HasListToolbar;

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function rows(): array
    {
        return AdminDemoData::couponUsage();
    }

    protected function reloadListData(): void {}

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect(AdminDemoData::couponUsage());
    }

    protected function toolbarExportColumns(): array
    {
        return ['code', 'issued', 'redeemed', 'expired', 'rate', 'cost'];
    }

    protected function toolbarExportName(): string
    {
        return 'coupon-usage';
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Coupon Usage')" route="admin.reports.coupon-usage" :breadcrumb="[__('Reports'), __('Coupon Usage')]" />

    <x-admin.page-header :title="__('Coupon Usage Report')">
        <x-slot:actions>
            <flux:button wire:click="export(false)" icon="arrow-down-tray" size="sm" variant="subtle">{{ __('CSV') }}</flux:button>
            <flux:button x-data x-on:click="window.print()" icon="printer" size="sm" variant="filled">{{ __('Print') }}</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table :selectable="false" stick-first>
        <x-slot:head><tr>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Code') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Issued') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Redeemed') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Expired') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Redemption %') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Cost') }}</th>
        </tr></x-slot:head>
        @foreach ($this->rows as $r)
            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2 font-mono text-xs font-semibold">{{ $r['code'] }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ number_format($r['issued']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ number_format($r['redeemed']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums text-zinc-500">{{ number_format($r['expired']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ $r['rate'] }}%</td>
                <td class="px-3 py-2 text-end tabular-nums text-amber-600 dark:text-amber-400">₱{{ number_format($r['cost']) }}</td>
            </tr>
        @endforeach
    </x-admin.table>
</div>
