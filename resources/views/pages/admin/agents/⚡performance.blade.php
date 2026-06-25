<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Agent Performance')] class extends Component {
    use HasListToolbar;

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function rows(): array
    {
        return AdminDemoData::agentPerformance();
    }

    protected function reloadListData(): void {}

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect(AdminDemoData::agentPerformance());
    }

    protected function toolbarExportColumns(): array
    {
        return ['rank', 'username', 'players', 'volume', 'commission', 'newSignups'];
    }

    protected function toolbarExportName(): string
    {
        return 'agent-performance';
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Agent Performance')" route="admin.agents.performance" :breadcrumb="[__('Agents'), __('Performance')]" />

    <x-admin.page-header :title="__('Agent Performance')">
        <x-slot:actions>
            <flux:button wire:click="export(false)" icon="arrow-down-tray" size="sm" variant="subtle">{{ __('CSV') }}</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table :selectable="false" stick-first>
        <x-slot:head><tr>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Rank') }}</th>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Agent') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Players') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Volume') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Commission') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('New Signups') }}</th>
        </tr></x-slot:head>
        @foreach ($this->rows as $a)
            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2 font-bold text-zinc-400">#{{ $a['rank'] }}</td>
                <td class="px-3 py-2 font-mono text-xs font-semibold">{{ $a['username'] }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ number_format($a['players']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($a['volume']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums text-emerald-600 dark:text-emerald-400">₱{{ number_format($a['commission']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ number_format($a['newSignups']) }}</td>
            </tr>
        @endforeach
    </x-admin.table>
</div>
