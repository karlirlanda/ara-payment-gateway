<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Withdrawal Approvals')] class extends Component {
    use HasListToolbar;

    /** @var array<int, array<string, mixed>> */
    public array $requests = [];

    public function mount(): void
    {
        $this->requests = $this->pendingRequests();
    }

    public function approve(int $id): void
    {
        $this->requests = collect($this->requests)->reject(fn ($r) => $r['id'] === $id)->values()->all();
        Flux::toast(text: __('Withdrawal approved'), variant: 'success');
    }

    public function reject(int $id): void
    {
        $this->requests = collect($this->requests)->reject(fn ($r) => $r['id'] === $id)->values()->all();
        Flux::toast(text: __('Withdrawal rejected'), variant: 'warning');
    }

    /** @return array<int, array<string, mixed>> */
    protected function pendingRequests(): array
    {
        return collect(AdminDemoData::transactions('withdraw'))
            ->take(5)
            ->map(function ($r) {
                $r['status'] = 'pending';

                return $r;
            })
            ->values()
            ->all();
    }

    protected function reloadListData(): void
    {
        $this->requests = $this->pendingRequests();
    }

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect($this->requests)->values();
    }

    protected function toolbarExportColumns(): array
    {
        return ['id', 'level', 'username', 'gateway', 'amount', 'account', 'reference', 'appliedAt'];
    }

    protected function toolbarExportName(): string
    {
        return 'withdrawal-approvals';
    }
}; ?>

<div class="flex flex-col" x-data="listTools('withdrawal-approvals')">
    <x-page-meta :title="__('Withdrawal Approvals')" route="admin.withdrawals.approvals" :breadcrumb="[__('Approvals'), __('Withdrawal Approvals')]" />

    <x-admin.page-header :title="__('Withdrawal Approvals')">
        <x-slot:toolbar>
            <x-admin.list-toolbar />
        </x-slot:toolbar>
    </x-admin.page-header>

    <x-admin.table>
        <x-slot:head>
            <tr>
                <x-admin.th-select />
                @foreach (['Level / ID', 'Gateway', 'Requested amount', 'Account details', 'Reference', 'Requested at', 'Status', 'Manage'] as $h)
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __($h) }}</th>
                @endforeach
            </tr>
        </x-slot:head>

        @forelse ($requests as $r)
            <tr wire:key="wd-{{ $r['id'] }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <x-admin.td-select />
                <td class="px-3 py-2"><x-admin.level-badge :level="$r['level']" :id="$r['username']" /></td>
                <td class="px-3 py-2"><flux:badge color="zinc" size="sm" inset="top bottom">{{ $r['gateway'] }}</flux:badge></td>
                <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($r['amount']) }}</td>
                <td class="px-3 py-2 tabular-nums">{{ $r['account'] }}</td>
                <td class="px-3 py-2 font-mono text-xs text-zinc-500">{{ $r['reference'] }}</td>
                <td class="px-3 py-2 tabular-nums">{{ $r['appliedAt'] }}</td>
                <td class="px-3 py-2">
                    <flux:badge color="amber" size="sm" inset="top bottom">{{ __('Pending') }}</flux:badge>
                </td>
                <td class="px-3 py-2">
                    <x-admin.row-actions>
                        <flux:button wire:click="approve({{ $r['id'] }})" size="xs" variant="primary">{{ __('Approve') }}</flux:button>
                        <flux:button wire:click="reject({{ $r['id'] }})" size="xs" variant="danger">{{ __('Reject') }}</flux:button>
                    </x-admin.row-actions>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="px-3 py-8 text-center text-zinc-400">{{ __('No pending withdrawals') }}</td>
            </tr>
        @endforelse
    </x-admin.table>
</div>
