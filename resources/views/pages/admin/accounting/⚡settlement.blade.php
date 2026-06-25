<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Daily Settlement')] class extends Component {
    use HasListToolbar;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public function mount(): void
    {
        $this->items = AdminDemoData::dailySettlement();
    }

    public function settle(string $date): void
    {
        $this->items = collect($this->items)->map(function ($r) use ($date) {
            if ($r['date'] === $date) {
                $r['status'] = 'settled';
            }

            return $r;
        })->all();

        Flux::toast(text: __('Day marked as settled'), variant: 'success');
    }

    protected function reloadListData(): void
    {
        $this->items = AdminDemoData::dailySettlement();
    }

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect($this->items);
    }

    protected function toolbarExportColumns(): array
    {
        return ['date', 'in', 'out', 'gross', 'bonus', 'net', 'status'];
    }

    protected function toolbarExportName(): string
    {
        return 'daily-settlement';
    }
}; ?>

<div class="flex flex-col" x-data="listTools('settlement')">
    <x-page-meta :title="__('Daily Settlement')" route="admin.accounting.settlement" :breadcrumb="[__('Accounting'), __('Daily Settlement')]" />

    <x-admin.page-header :title="__('Daily Settlement')">
        <x-slot:toolbar>
            <x-admin.list-toolbar :columns="false" :density="false" />
        </x-slot:toolbar>
    </x-admin.page-header>

    <x-admin.table :selectable="false" stick-first>
        <x-slot:head><tr>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Date') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Total In') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Total Out') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Gross') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Bonus Cost') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Net') }}</th>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Status') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Manage') }}</th>
        </tr></x-slot:head>
        @foreach ($this->items as $r)
            <tr wire:key="settle-{{ $r['date'] }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2 font-semibold tabular-nums text-zinc-800 dark:text-zinc-100">{{ $r['date'] }}</td>
                <td class="px-3 py-2 text-end tabular-nums text-emerald-600 dark:text-emerald-400">₱{{ number_format($r['in']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums text-amber-600 dark:text-amber-400">₱{{ number_format($r['out']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($r['gross']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums text-zinc-500">₱{{ number_format($r['bonus']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums font-semibold">₱{{ number_format($r['net']) }}</td>
                <td class="px-3 py-2"><flux:badge :color="$r['status'] === 'settled' ? 'green' : 'amber'" size="sm" inset="top bottom">{{ $r['status'] === 'settled' ? __('Settled') : __('Open') }}</flux:badge></td>
                <td class="px-3 py-2 text-end">
                    @if ($r['status'] !== 'settled')
                        <flux:button wire:click="settle('{{ $r['date'] }}')" size="xs" variant="primary">{{ __('Mark settled') }}</flux:button>
                    @else
                        <span class="text-xs text-zinc-400">—</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-admin.table>
</div>
