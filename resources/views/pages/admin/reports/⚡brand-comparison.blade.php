<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Brand Comparison Report')] class extends Component {
    use HasListToolbar;

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function rows(): array
    {
        return AdminDemoData::brandComparison();
    }

    #[Computed]
    public function peak(): int
    {
        return (int) collect(AdminDemoData::brandComparison())->max('revenue') ?: 1;
    }

    protected function reloadListData(): void {}

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect(AdminDemoData::brandComparison());
    }

    protected function toolbarExportColumns(): array
    {
        return ['brand', 'revenue', 'players', 'retention'];
    }

    protected function toolbarExportName(): string
    {
        return 'brand-comparison';
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Brand Comparison')" route="admin.reports.brand-comparison" :breadcrumb="[__('Reports'), __('Brand Comparison')]" />

    <x-admin.page-header :title="__('Brand Comparison Report')">
        <x-slot:actions>
            <flux:button wire:click="export(false)" icon="arrow-down-tray" size="sm" variant="subtle">{{ __('CSV') }}</flux:button>
            <flux:button x-data x-on:click="window.print()" icon="printer" size="sm" variant="filled">{{ __('Print') }}</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Revenue share bars --}}
    <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-3 text-xs font-semibold text-zinc-500">{{ __('Revenue by brand (₱)') }}</div>
        <div class="flex flex-col gap-2">
            @foreach ($this->rows as $r)
                <div class="flex items-center gap-3">
                    <div class="w-32 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $r['brand'] }}</div>
                    <div class="h-5 flex-1 overflow-hidden rounded bg-zinc-100 dark:bg-zinc-700">
                        <div class="h-full rounded bg-indigo-500" style="width: {{ max(2, (int) round($r['revenue'] / $this->peak * 100)) }}%"></div>
                    </div>
                    <div class="w-28 shrink-0 text-end tabular-nums text-sm font-semibold">₱{{ number_format($r['revenue']) }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <x-admin.table :selectable="false" stick-first>
        <x-slot:head><tr>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Brand') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Revenue') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Players') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Retention %') }}</th>
        </tr></x-slot:head>
        @foreach ($this->rows as $r)
            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2 font-semibold text-zinc-800 dark:text-zinc-100">{{ $r['brand'] }}</td>
                <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($r['revenue']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ number_format($r['players']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ $r['retention'] }}%</td>
            </tr>
        @endforeach
    </x-admin.table>
</div>
