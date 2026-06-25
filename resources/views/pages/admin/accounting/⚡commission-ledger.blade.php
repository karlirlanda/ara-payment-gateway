<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Commission Ledger')] class extends Component {
    use HasListToolbar;

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function rows(): array
    {
        return AdminDemoData::commissionLedger();
    }

    /** @return array{earned:int, paid:int, pending:int} */
    #[Computed]
    public function totals(): array
    {
        $r = collect(AdminDemoData::commissionLedger());

        return [
            'earned' => (int) $r->sum('earned'),
            'paid' => (int) $r->sum('paid'),
            'pending' => (int) $r->sum('pending'),
        ];
    }

    protected function reloadListData(): void {}

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect(AdminDemoData::commissionLedger());
    }

    protected function toolbarExportColumns(): array
    {
        return ['agent', 'earned', 'paid', 'pending', 'period'];
    }

    protected function toolbarExportName(): string
    {
        return 'commission-ledger';
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Commission Ledger')" route="admin.accounting.commission-ledger" :breadcrumb="[__('Accounting'), __('Commission Ledger')]" />

    <x-admin.page-header :title="__('Commission Ledger')">
        <x-slot:actions>
            <flux:button wire:click="export(false)" icon="arrow-down-tray" size="sm" variant="subtle">{{ __('CSV') }}</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.stat-strip>
        <x-admin.stat-cell :label="__('Total earned')" :value="'₱'.number_format($this->totals['earned'])" />
        <x-admin.stat-cell :label="__('Total paid')" :value="'₱'.number_format($this->totals['paid'])" />
        <x-admin.stat-cell :label="__('Pending payout')" :value="'₱'.number_format($this->totals['pending'])" />
    </x-admin.stat-strip>

    <x-admin.table :selectable="false" stick-first>
        <x-slot:head><tr>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Agent') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Earned') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Paid') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Pending') }}</th>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Period') }}</th>
        </tr></x-slot:head>
        @foreach ($this->rows as $r)
            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2 font-mono text-xs font-semibold">{{ $r['agent'] }}</td>
                <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($r['earned']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums text-emerald-600 dark:text-emerald-400">₱{{ number_format($r['paid']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums text-amber-600 dark:text-amber-400">₱{{ number_format($r['pending']) }}</td>
                <td class="px-3 py-2 text-zinc-500">{{ $r['period'] }}</td>
            </tr>
        @endforeach
    </x-admin.table>
</div>
