<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Transactions')] class extends Component {
    use HasListToolbar, WithPagination;

    public string $direction = 'deposit';

    public string $dateFrom = '';
    public string $dateTo = '';
    public string $keyword = '';
    public string $status = '';
    public int $perPage = 50;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public function mount(string $direction): void
    {
        $this->direction = $direction;
        $this->items = AdminDemoData::transactions($this->direction);
    }

    public function updatedKeyword(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['dateFrom', 'dateTo', 'keyword', 'status']);
        $this->resetPage();
    }

    /**
     * Advance a row through the pending / completed / cancelled workflow. Pending rows can be
     * completed or cancelled; processed rows can be reverted to pending.
     */
    public function setStatus(int $id, string $status): void
    {
        $this->items = collect($this->items)->map(function ($r) use ($id, $status) {
            if ($r['id'] === $id) {
                $r['status'] = $status;
                $r['processedAt'] = $status === 'pending' ? $r['appliedAt'] : now()->format('Y-m-d H:i:s');
            }

            return $r;
        })->all();

        Flux::toast(text: __('Transaction updated'), variant: 'success');
    }

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        $filtered = collect($this->items)
            ->when($this->status !== '', fn ($c) => $c->where('status', $this->status))
            ->when($this->keyword !== '', fn ($c) => $c->filter(
                fn ($r) => str_contains(strtolower($r['username'].$r['gateway']), strtolower($this->keyword))
            ))
            ->values();

        $page = $this->getPage();

        return new LengthAwarePaginator(
            $filtered->forPage($page, $this->perPage)->values(),
            $filtered->count(),
            $this->perPage,
            $page,
            ['path' => request()->url()],
        );
    }

    protected function reloadListData(): void
    {
        $this->items = AdminDemoData::transactions($this->direction);
    }

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect($this->items)
            ->when($this->status !== '', fn ($c) => $c->where('status', $this->status))
            ->when($this->keyword !== '', fn ($c) => $c->filter(
                fn ($r) => str_contains(strtolower($r['username'].$r['gateway']), strtolower($this->keyword))
            ))
            ->values();
    }

    protected function toolbarExportColumns(): array
    {
        return ['id', 'level', 'username', 'gateway', 'amount', 'account', 'reference', 'appliedAt', 'processedAt', 'status'];
    }

    protected function toolbarExportName(): string
    {
        return 'transactions-'.$this->direction;
    }

    /**
     * @return array{in:int, inCount:int, out:int, outCount:int, net:int}
     */
    #[Computed]
    public function totals(): array
    {
        $completed = collect($this->items)->where('status', 'completed');
        $sum = (int) $completed->sum('amount');
        $count = $completed->count();
        $isDeposit = $this->direction === 'deposit';

        return [
            'in' => $isDeposit ? $sum : 0,
            'inCount' => $isDeposit ? $count : 0,
            'out' => $isDeposit ? 0 : $sum,
            'outCount' => $isDeposit ? 0 : $count,
            'net' => $isDeposit ? $sum : -$sum,
        ];
    }
}; ?>

@php
    $isDeposit = $direction === 'deposit';
    $amountLabel = $isDeposit ? __('Deposit amount') : __('Requested amount');
    $statusMeta = [
        'pending' => ['color' => 'amber', 'label' => __('Pending')],
        'completed' => ['color' => 'green', 'label' => __('Completed')],
        'cancelled' => ['color' => 'red', 'label' => __('Cancelled')],
    ];
    $tabs = [
        ['label' => __('Deposits'), 'href' => route('admin.transactions', 'deposit'), 'active' => $isDeposit],
        ['label' => __('Withdrawals'), 'href' => route('admin.transactions', 'withdraw'), 'active' => ! $isDeposit],
    ];
    $pageTitle = $isDeposit ? __('Deposits') : __('Withdrawals');
@endphp

<div class="flex flex-col" x-data="listTools('transactions')">
    <x-page-meta :title="$pageTitle" route="admin.transactions" :breadcrumb="[__('Transactions'), $pageTitle]" />

    <x-admin.page-header :title="__('Transactions')">
        <x-slot:tabs>
            @foreach ($tabs as $tab)
                <a href="{{ $tab['href'] }}" wire:navigate
                    @class([
                        'border border-b-0 px-4 py-1.5 text-xs',
                        'border-accent bg-accent text-[color:var(--color-accent-foreground)]' => $tab['active'],
                        'border-zinc-200 bg-white text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' => ! $tab['active'],
                    ])>{{ $tab['label'] }}</a>
            @endforeach
        </x-slot:tabs>
        <x-slot:actions>
            <flux:select wire:model.live="perPage" size="sm" class="w-28">
                <flux:select.option value="25">25</flux:select.option>
                <flux:select.option value="50">50</flux:select.option>
                <flux:select.option value="100">100</flux:select.option>
            </flux:select>
        </x-slot:actions>
        <x-slot:toolbar>
            <x-admin.list-toolbar />
        </x-slot:toolbar>
    </x-admin.page-header>

    <x-admin.filter-bar :statuses="['pending' => __('Pending'), 'completed' => __('Completed'), 'cancelled' => __('Cancelled')]" />

    <x-admin.stat-strip class="mb-2">
        <x-admin.stat-cell :label="__('Total in')" :value="'₱'.number_format($this->totals['in'])" :count="$this->totals['inCount']" />
        <x-admin.stat-cell :label="__('Total out')" :value="'₱'.number_format($this->totals['out'])" :count="$this->totals['outCount']" />
        <x-admin.stat-cell :label="__('Deposit − Withdraw')" :value="'₱'.number_format($this->totals['net'])" />
    </x-admin.stat-strip>

    <x-admin.table :stick="3">
        <x-slot:head>
            <tr>
                <x-admin.th-select />
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Manage') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Level / ID') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Gateway') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ $amountLabel }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Account details') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Reference') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Applied') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Processed') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Status') }}</th>
            </tr>
        </x-slot:head>

        @forelse ($this->rows as $t)
            <tr wire:key="txn-{{ $t['id'] }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <x-admin.td-select />
                <td class="px-3 py-2">
                    <x-admin.row-actions>
                        @if ($t['status'] === 'pending')
                            <flux:button wire:click="setStatus({{ $t['id'] }}, 'completed')" size="xs" variant="primary">{{ __('Complete') }}</flux:button>
                            <flux:button wire:click="setStatus({{ $t['id'] }}, 'cancelled')" size="xs" variant="danger">{{ __('Cancel') }}</flux:button>
                        @else
                            <flux:button wire:click="setStatus({{ $t['id'] }}, 'pending')" size="xs" variant="subtle">{{ __('Set pending') }}</flux:button>
                        @endif
                    </x-admin.row-actions>
                </td>
                <td class="px-3 py-2"><x-admin.level-badge :level="$t['level']" :id="$t['username']" /></td>
                <td class="px-3 py-2">
                    <flux:badge color="zinc" size="sm" inset="top bottom">{{ $t['gateway'] }}</flux:badge>
                </td>
                <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($t['amount']) }}</td>
                <td class="px-3 py-2 tabular-nums">{{ $t['account'] }}</td>
                <td class="px-3 py-2 font-mono text-xs text-zinc-500">{{ $t['reference'] }}</td>
                <td class="px-3 py-2 tabular-nums">{{ $t['appliedAt'] }}</td>
                <td class="px-3 py-2 tabular-nums">{{ $t['processedAt'] }}</td>
                <td class="px-3 py-2">
                    <flux:badge :color="$statusMeta[$t['status']]['color']" size="sm" inset="top bottom">{{ $statusMeta[$t['status']]['label'] }}</flux:badge>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="px-3 py-8 text-center text-zinc-400">{{ __('No data found') }}</td>
            </tr>
        @endforelse

        <x-slot:footer>
            <flux:pagination :paginator="$this->rows" />
        </x-slot:footer>
    </x-admin.table>
</div>
