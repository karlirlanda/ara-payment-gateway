<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Sales Report')] class extends Component {
    use HasListToolbar;

    /** Active quick-range chip (display-only in the demo). */
    public string $range = 'today';

    /** @return array<string, array{label:string, cells:array<int, array<string, string>>}> */
    #[Computed]
    public function report(): array
    {
        return AdminDemoData::salesReport();
    }

    public function setRange(string $range): void
    {
        $this->range = $range;
    }

    protected function reloadListData(): void
    {
        // Static demo figures — nothing to re-pull.
    }

    /** Flatten the grouped report into group / metric / value rows for CSV export. */
    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect(AdminDemoData::salesReport())
            ->flatMap(fn ($group) => collect($group['cells'])->map(fn ($cell) => [
                'group' => $group['label'],
                'metric' => $cell['label'],
                'value' => $cell['value'],
            ]))
            ->values();
    }

    protected function toolbarExportColumns(): array
    {
        return ['group', 'metric', 'value'];
    }

    protected function toolbarExportName(): string
    {
        return 'sales-report';
    }
}; ?>

@php
    $toneClass = [
        'pos' => 'text-emerald-600 dark:text-emerald-400',
        'warn' => 'text-amber-600 dark:text-amber-400',
        'highlight' => 'text-accent',
        'plain' => 'text-zinc-800 dark:text-zinc-100',
    ];
    $ranges = [
        'today' => __('Today'),
        'yesterday' => __('Yesterday'),
        'week' => __('This Week'),
        'month' => __('This Month'),
        'custom' => __('Custom Range'),
    ];
@endphp

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Sales Report')" route="admin.reports.sales" :breadcrumb="[__('Reports'), __('Sales Report')]" />

    <x-admin.page-header :title="__('Sales Report')">
        <x-slot:actions>
            <flux:button wire:click="export(false)" icon="arrow-down-tray" size="sm" variant="subtle">{{ __('Excel') }}</flux:button>
            <flux:button wire:click="export(false)" icon="arrow-down-tray" size="sm" variant="subtle">{{ __('CSV') }}</flux:button>
            <flux:button x-data x-on:click="window.print()" icon="printer" size="sm" variant="filled">{{ __('Print') }}</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Filter chips + cosmetic brand/agent/game selectors (multi-brand isn't modeled in the demo). --}}
    <div class="flex flex-wrap items-center gap-2 border border-zinc-200 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800">
        @foreach ($ranges as $key => $label)
            <flux:button wire:click="setRange('{{ $key }}')" size="sm" :variant="$range === $key ? 'primary' : 'subtle'">{{ $label }}</flux:button>
        @endforeach
        <flux:spacer />
        <flux:select size="sm" class="w-36"><flux:select.option>{{ __('All Brands') }}</flux:select.option></flux:select>
        <flux:select size="sm" class="w-36"><flux:select.option>{{ __('All Agents') }}</flux:select.option></flux:select>
        <flux:select size="sm" class="w-36"><flux:select.option>{{ __('All Games') }}</flux:select.option></flux:select>
    </div>

    @foreach ($this->report as $group)
        <div wire:key="grp-{{ $loop->index }}" class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            <div class="border-b border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900/50">
                {{ $group['label'] }}
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($group['cells'] as $cell)
                    <div class="border-b border-e border-zinc-100 px-3 py-2.5 dark:border-zinc-700/60">
                        <div class="text-[13px] font-bold tabular-nums {{ $toneClass[$cell['tone']] ?? $toneClass['plain'] }}">{{ $cell['value'] }}</div>
                        <div class="mt-0.5 text-[10px] font-medium uppercase leading-tight tracking-wide text-zinc-400">{{ $cell['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
