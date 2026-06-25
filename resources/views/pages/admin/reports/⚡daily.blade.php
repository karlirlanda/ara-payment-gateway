<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Daily Sales')] class extends Component {
    use HasListToolbar;

    /** @return array<int, array{day:string, deposit:int, withdraw:int, profit:int}> */
    #[Computed]
    public function series(): array
    {
        return AdminDemoData::dailySeries();
    }

    /** Largest single value across the series — drives bar heights. */
    #[Computed]
    public function peak(): int
    {
        return collect($this->series)
            ->flatMap(fn ($d) => [$d['deposit'], $d['withdraw'], $d['profit']])
            ->max() ?: 1;
    }

    /** @return array{deposit:int, withdraw:int, profit:int} */
    #[Computed]
    public function totals(): array
    {
        $s = collect($this->series);

        return [
            'deposit' => (int) $s->sum('deposit'),
            'withdraw' => (int) $s->sum('withdraw'),
            'profit' => (int) $s->sum('profit'),
        ];
    }

    protected function reloadListData(): void
    {
        // Static demo series.
    }

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect(AdminDemoData::dailySeries())->values();
    }

    protected function toolbarExportColumns(): array
    {
        return ['day', 'deposit', 'withdraw', 'profit'];
    }

    protected function toolbarExportName(): string
    {
        return 'daily-sales';
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Daily Sales')" route="admin.reports.daily" :breadcrumb="[__('Reports'), __('Daily Sales')]" />

    <x-admin.page-header :title="__('Daily Sales Report')">
        <x-slot:actions>
            <flux:button wire:click="export(false)" icon="arrow-down-tray" size="sm" variant="subtle">{{ __('CSV') }}</flux:button>
            <flux:button x-data x-on:click="window.print()" icon="printer" size="sm" variant="filled">{{ __('Print') }}</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.stat-strip>
        <x-admin.stat-cell :label="__('Total deposits')" :value="'₱'.number_format($this->totals['deposit'])" />
        <x-admin.stat-cell :label="__('Total withdrawals')" :value="'₱'.number_format($this->totals['withdraw'])" />
        <x-admin.stat-cell :label="__('Net profit')" :value="'₱'.number_format($this->totals['profit'])" />
    </x-admin.stat-strip>

    {{-- CSS bar chart: deposit / withdrawal / profit per day (last 7 days). --}}
    <div class="border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="mb-3 text-xs font-semibold text-zinc-500">{{ __('Deposit vs Withdrawal vs Profit in ₱ (last 7 days)') }}</div>
        <div class="flex h-48 items-end gap-3">
            @foreach ($this->series as $d)
                <div class="flex flex-1 flex-col items-center gap-1">
                    <div class="flex h-40 w-full items-end justify-center gap-1">
                        <div class="w-1/3 rounded-t bg-blue-500" style="height: {{ max(2, (int) round($d['deposit'] / $this->peak * 100)) }}%" title="₱{{ number_format($d['deposit']) }}"></div>
                        <div class="w-1/3 rounded-t bg-red-500" style="height: {{ max(2, (int) round($d['withdraw'] / $this->peak * 100)) }}%" title="₱{{ number_format($d['withdraw']) }}"></div>
                        <div class="w-1/3 rounded-t bg-emerald-500" style="height: {{ max(2, (int) round($d['profit'] / $this->peak * 100)) }}%" title="₱{{ number_format($d['profit']) }}"></div>
                    </div>
                    <div class="text-[10px] font-medium text-zinc-400">{{ $d['day'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="mt-3 flex gap-4 text-[11px] text-zinc-500">
            <span class="flex items-center gap-1.5"><span class="size-2 rounded-sm bg-blue-500"></span>{{ __('Deposit (₱)') }}</span>
            <span class="flex items-center gap-1.5"><span class="size-2 rounded-sm bg-red-500"></span>{{ __('Withdrawal (₱)') }}</span>
            <span class="flex items-center gap-1.5"><span class="size-2 rounded-sm bg-emerald-500"></span>{{ __('Net Profit (₱)') }}</span>
        </div>
    </div>

    <x-admin.table :selectable="false" stick-first>
        <x-slot:head>
            <tr>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Day') }}</th>
                <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Deposit') }}</th>
                <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Withdrawal') }}</th>
                <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Net Profit') }}</th>
            </tr>
        </x-slot:head>

        @foreach ($this->series as $d)
            <tr wire:key="day-{{ $loop->index }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2 font-semibold text-zinc-800 dark:text-zinc-100">{{ $d['day'] }}</td>
                <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($d['deposit']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums text-amber-600 dark:text-amber-400">₱{{ number_format($d['withdraw']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums text-emerald-600 dark:text-emerald-400">₱{{ number_format($d['profit']) }}</td>
            </tr>
        @endforeach
    </x-admin.table>
</div>
