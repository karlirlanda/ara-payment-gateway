<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Agent Commission Report')] class extends Component {
    use HasListToolbar;

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function rows(): array
    {
        return AdminDemoData::agentCommission();
    }

    protected function reloadListData(): void {}

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect(AdminDemoData::agentCommission());
    }

    protected function toolbarExportColumns(): array
    {
        return ['agent', 'players', 'volume', 'rate', 'commission'];
    }

    protected function toolbarExportName(): string
    {
        return 'agent-commission';
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Agent Commission')" route="admin.reports.agent-commission" :breadcrumb="[__('Reports'), __('Agent Commission')]" />

    <x-admin.page-header :title="__('Agent Commission Report')">
        <x-slot:actions>
            <flux:button wire:click="export(false)" icon="arrow-down-tray" size="sm" variant="subtle">{{ __('CSV') }}</flux:button>
            <flux:button x-data x-on:click="window.print()" icon="printer" size="sm" variant="filled">{{ __('Print') }}</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table :selectable="false" stick-first>
        <x-slot:head><tr>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Agent') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Players') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Volume') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Rolling Rate') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Commission') }}</th>
        </tr></x-slot:head>
        @foreach ($this->rows as $r)
            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2 font-mono text-xs font-semibold">{{ $r['agent'] }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ number_format($r['players']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($r['volume']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ $r['rate'] }}%</td>
                <td class="px-3 py-2 text-end tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">₱{{ number_format($r['commission']) }}</td>
            </tr>
        @endforeach
    </x-admin.table>
</div>
