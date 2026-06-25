<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Player Activity Report')] class extends Component {
    use HasListToolbar;

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function rows(): array
    {
        return AdminDemoData::playerActivity();
    }

    protected function reloadListData(): void {}

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect(AdminDemoData::playerActivity());
    }

    protected function toolbarExportColumns(): array
    {
        return ['brand', 'signups', 'active', 'churned', 'retention', 'avgSession'];
    }

    protected function toolbarExportName(): string
    {
        return 'player-activity';
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Player Activity')" route="admin.reports.player-activity" :breadcrumb="[__('Reports'), __('Player Activity')]" />

    <x-admin.page-header :title="__('Player Activity Report')">
        <x-slot:actions>
            <flux:button wire:click="export(false)" icon="arrow-down-tray" size="sm" variant="subtle">{{ __('CSV') }}</flux:button>
            <flux:button x-data x-on:click="window.print()" icon="printer" size="sm" variant="filled">{{ __('Print') }}</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table :selectable="false" stick-first>
        <x-slot:head><tr>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Brand') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('New Signups') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Active') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Churned') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Retention %') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Avg. Session') }}</th>
        </tr></x-slot:head>
        @foreach ($this->rows as $r)
            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2 font-semibold text-zinc-800 dark:text-zinc-100">{{ $r['brand'] }}</td>
                <td class="px-3 py-2 text-end tabular-nums text-emerald-600 dark:text-emerald-400">{{ number_format($r['signups']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ number_format($r['active']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums text-red-600 dark:text-red-400">{{ number_format($r['churned']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ $r['retention'] }}%</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ $r['avgSession'] }}</td>
            </tr>
        @endforeach
    </x-admin.table>
</div>
