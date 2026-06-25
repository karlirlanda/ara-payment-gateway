<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Reconciliation')] class extends Component {
    use HasListToolbar;

    public string $date = '2026-06-24';

    /** @var array<int, array<string, mixed>> */
    public array $rows = [];

    public function mount(): void
    {
        $this->rows = AdminDemoData::reconciliation();
    }

    public function rerun(): void
    {
        $this->rows = AdminDemoData::reconciliation();
        Flux::toast(text: __('Reconciliation re-run complete'), variant: 'success');
    }

    /**
     * @return array{platform:int, provider:int, variance:int}
     */
    #[Computed]
    public function totals(): array
    {
        $rows = collect($this->rows);

        return [
            'platform' => (int) $rows->sum('platformAmount'),
            'provider' => (int) $rows->sum('providerAmount'),
            'variance' => (int) $rows->sum('variance'),
        ];
    }

    protected function reloadListData(): void
    {
        $this->rows = AdminDemoData::reconciliation();
    }

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect($this->rows)->values();
    }

    protected function toolbarExportColumns(): array
    {
        return ['gateway', 'date', 'platformCount', 'platformAmount', 'providerCount', 'providerAmount', 'variance', 'status'];
    }

    protected function toolbarExportName(): string
    {
        return 'reconciliation';
    }
}; ?>

@php
    $statusMeta = [
        'matched' => ['color' => 'green', 'label' => __('Matched')],
        'variance' => ['color' => 'red', 'label' => __('Variance')],
        'pending' => ['color' => 'amber', 'label' => __('Pending')],
    ];
@endphp

<div class="flex flex-col" x-data="listTools('reconciliation')">
    <x-page-meta :title="__('Daily Reconciliation')" route="admin.reconciliation" :breadcrumb="[__('Reconciliation'), __('Daily Reconciliation')]" />

    <x-admin.page-header :title="__('Daily Reconciliation')">
        <x-slot:actions>
            <flux:input type="date" wire:model="date" size="sm" class="w-40" />
            <flux:button wire:click="rerun" size="sm" variant="primary" icon="arrow-path">{{ __('Re-run') }}</flux:button>
        </x-slot:actions>
        <x-slot:toolbar>
            <x-admin.list-toolbar />
        </x-slot:toolbar>
    </x-admin.page-header>

    <p class="mb-2 text-sm text-zinc-500">
        {{ __('Platform transaction totals compared against provider settlement logs for the selected day.') }}
    </p>

    <x-admin.stat-strip class="mb-2">
        <x-admin.stat-cell :label="__('Platform total')" :value="'₱'.number_format($this->totals['platform'])" />
        <x-admin.stat-cell :label="__('Provider total')" :value="'₱'.number_format($this->totals['provider'])" />
        <x-admin.stat-cell :label="__('Variance')" :value="'₱'.number_format($this->totals['variance'])" />
    </x-admin.stat-strip>

    <x-admin.table>
        <x-slot:head>
            <tr>
                @foreach (['Gateway', 'Date', 'Platform count', 'Platform amount', 'Provider count', 'Provider amount', 'Variance', 'Status'] as $h)
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __($h) }}</th>
                @endforeach
            </tr>
        </x-slot:head>

        @foreach ($rows as $r)
            <tr wire:key="recon-{{ $r['gateway'] }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2"><flux:badge color="zinc" size="sm" inset="top bottom">{{ $r['gateway'] }}</flux:badge></td>
                <td class="px-3 py-2 tabular-nums text-zinc-500">{{ $r['date'] }}</td>
                <td class="px-3 py-2 tabular-nums">{{ number_format($r['platformCount']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($r['platformAmount']) }}</td>
                <td class="px-3 py-2 tabular-nums">{{ number_format($r['providerCount']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($r['providerAmount']) }}</td>
                <td @class([
                    'px-3 py-2 text-end tabular-nums',
                    'text-rose-500' => $r['variance'] !== 0,
                ])>₱{{ number_format($r['variance']) }}</td>
                <td class="px-3 py-2">
                    <flux:badge :color="$statusMeta[$r['status']]['color']" size="sm" inset="top bottom">{{ $statusMeta[$r['status']]['label'] }}</flux:badge>
                </td>
            </tr>
        @endforeach
    </x-admin.table>
</div>
