<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Agent Transactions')] class extends Component {
    use HasListToolbar;

    public string $status = '';
    public string $keyword = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public function mount(): void
    {
        $this->items = AdminDemoData::agentTransactions();
    }

    public function resetFilters(): void
    {
        $this->reset(['status', 'keyword', 'dateFrom', 'dateTo']);
    }

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function rows(): array
    {
        return $this->filtered()->all();
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function filtered(): Collection
    {
        return collect($this->items)
            ->when($this->status !== '', fn ($c) => $c->where('status', $this->status))
            ->when($this->keyword !== '', fn ($c) => $c->filter(
                fn ($r) => str_contains(strtolower($r['agent'].$r['player']), strtolower($this->keyword))
            ))
            ->values();
    }

    protected function reloadListData(): void
    {
        $this->items = AdminDemoData::agentTransactions();
    }

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return $this->filtered();
    }

    protected function toolbarExportColumns(): array
    {
        return ['id', 'agent', 'player', 'type', 'amount', 'status', 'at'];
    }

    protected function toolbarExportName(): string
    {
        return 'agent-transactions';
    }
}; ?>

@php
    $statusMeta = [
        'completed' => ['color' => 'green', 'label' => __('Completed')],
        'pending' => ['color' => 'amber', 'label' => __('Pending')],
    ];
@endphp

<div class="flex flex-col" x-data="listTools('agent-transactions')">
    <x-page-meta :title="__('Agent Transactions')" route="admin.agents.transactions" :breadcrumb="[__('Agents'), __('Agent Transactions')]" />

    <x-admin.page-header :title="__('Agent Transactions')">
        <x-slot:toolbar>
            <x-admin.list-toolbar :columns="false" :density="false" />
        </x-slot:toolbar>
    </x-admin.page-header>

    <x-admin.filter-bar :statuses="['completed' => __('Completed'), 'pending' => __('Pending')]" />

    <x-admin.table :selectable="false" stick-first>
        <x-slot:head><tr>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Agent') }}</th>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Player') }}</th>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Type') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Amount') }}</th>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Status') }}</th>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Date') }}</th>
        </tr></x-slot:head>
        @forelse ($this->rows as $t)
            <tr wire:key="agtx-{{ $t['id'] }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2 font-mono text-xs font-semibold">{{ $t['agent'] }}</td>
                <td class="px-3 py-2">{{ $t['player'] }}</td>
                <td class="px-3 py-2">{{ $t['type'] === 'deposit' ? __('Deposit') : __('Withdraw') }}</td>
                <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($t['amount']) }}</td>
                <td class="px-3 py-2"><flux:badge :color="$statusMeta[$t['status']]['color']" size="sm" inset="top bottom">{{ $statusMeta[$t['status']]['label'] }}</flux:badge></td>
                <td class="px-3 py-2 tabular-nums text-zinc-500">{{ $t['at'] }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-3 py-8 text-center text-zinc-400">{{ __('No data found') }}</td></tr>
        @endforelse
    </x-admin.table>
</div>
