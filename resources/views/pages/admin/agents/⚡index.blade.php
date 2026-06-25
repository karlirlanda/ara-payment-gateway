<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Agents')] class extends Component {
    use HasListToolbar;

    public string $tab = 'list';

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function rows(): array
    {
        return AdminDemoData::agents();
    }

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function tree(): array
    {
        return AdminDemoData::agentTree();
    }

    protected function reloadListData(): void {}

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect(AdminDemoData::agents());
    }

    protected function toolbarExportColumns(): array
    {
        return ['id', 'username', 'level', 'parent', 'players', 'volume', 'commission', 'rate', 'balance', 'status'];
    }

    protected function toolbarExportName(): string
    {
        return 'agents';
    }
}; ?>

@php
    $statusMeta = [
        'active' => ['color' => 'green', 'label' => __('Active')],
        'suspended' => ['color' => 'amber', 'label' => __('Suspended')],
    ];
    $tabs = [
        ['key' => 'list', 'label' => __('Agent List')],
        ['key' => 'tree', 'label' => __('Hierarchy')],
    ];
@endphp

<div class="flex flex-col" x-data="listTools('agents')">
    <x-page-meta :title="__('Agents')" route="admin.agents" :breadcrumb="[__('Agents'), __('List & Hierarchy')]" />

    <x-admin.page-header :title="__('Agent Management')">
        <x-slot:tabs>
            @foreach ($tabs as $t)
                <button type="button" wire:click="$set('tab', '{{ $t['key'] }}')"
                    @class([
                        'border border-b-0 px-4 py-1.5 text-xs',
                        'border-accent bg-accent text-[color:var(--color-accent-foreground)]' => $tab === $t['key'],
                        'border-zinc-200 bg-white text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' => $tab !== $t['key'],
                    ])>{{ $t['label'] }}</button>
            @endforeach
        </x-slot:tabs>
        <x-slot:toolbar>
            @if ($tab === 'list')
                <x-admin.list-toolbar :columns="false" />
            @endif
        </x-slot:toolbar>
    </x-admin.page-header>

    @if ($tab === 'list')
        <x-admin.table :selectable="false" stick-first>
            <x-slot:head><tr>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Agent') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Level') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Upline') }}</th>
                <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Players') }}</th>
                <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Volume') }}</th>
                <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Commission') }}</th>
                <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Balance') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Status') }}</th>
            </tr></x-slot:head>
            @foreach ($this->rows as $a)
                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                    <td class="px-3 py-2 font-mono text-xs font-semibold">{{ $a['username'] }}</td>
                    <td class="px-3 py-2"><flux:badge :color="$a['level'] === 'Head' ? 'indigo' : 'zinc'" size="sm" inset="top bottom">{{ $a['level'] }}</flux:badge></td>
                    <td class="px-3 py-2 font-mono text-xs text-zinc-500">{{ $a['parent'] ?? '—' }}</td>
                    <td class="px-3 py-2 text-end tabular-nums">{{ number_format($a['players']) }}</td>
                    <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($a['volume']) }}</td>
                    <td class="px-3 py-2 text-end tabular-nums text-emerald-600 dark:text-emerald-400">₱{{ number_format($a['commission']) }}</td>
                    <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($a['balance']) }}</td>
                    <td class="px-3 py-2"><flux:badge :color="$statusMeta[$a['status']]['color']" size="sm" inset="top bottom">{{ $statusMeta[$a['status']]['label'] }}</flux:badge></td>
                </tr>
            @endforeach
        </x-admin.table>
    @else
        <div class="flex flex-col gap-3">
            <div class="flex items-center gap-2 border border-zinc-200 bg-zinc-900 px-4 py-2 text-sm font-bold text-white dark:border-zinc-700">
                <flux:icon icon="building-office-2" class="size-4" /> {{ __('Super Admin · ARA Inc') }}
            </div>
            @foreach ($this->tree as $head)
                <div class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex items-center justify-between border-b border-zinc-200 bg-indigo-50 px-4 py-2 dark:border-zinc-700 dark:bg-indigo-950/30">
                        <span class="flex items-center gap-2 text-sm font-bold text-indigo-800 dark:text-indigo-300"><flux:badge color="indigo" size="sm">{{ __('Head') }}</flux:badge> {{ $head['username'] }}</span>
                        <span class="text-xs text-zinc-500">{{ $head['players'] }} {{ __('players') }}</span>
                    </div>
                    @forelse ($head['subs'] as $sub)
                        <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-2 ps-10 last:border-b-0 dark:border-zinc-700/50">
                            <span class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200"><flux:badge color="zinc" size="sm">{{ __('Sub') }}</flux:badge> {{ $sub['username'] }}</span>
                            <span class="text-xs text-zinc-500">{{ $sub['players'] }} {{ __('players') }}</span>
                        </div>
                    @empty
                        <div class="px-4 py-2 ps-10 text-xs text-zinc-400">{{ __('No sub-agents') }}</div>
                    @endforelse
                </div>
            @endforeach
        </div>
    @endif
</div>
