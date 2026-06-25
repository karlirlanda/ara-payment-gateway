<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Activity Logs')] class extends Component {
    use HasListToolbar;

    public string $status = ''; // reused by filter-bar as the "type" filter
    public string $keyword = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public function mount(): void
    {
        $this->items = AdminDemoData::activityLogs();
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
            ->when($this->status !== '', fn ($c) => $c->where('type', $this->status))
            ->when($this->keyword !== '', fn ($c) => $c->filter(
                fn ($r) => str_contains(strtolower($r['actor'].$r['action'].$r['target']), strtolower($this->keyword))
            ))
            ->values();
    }

    protected function reloadListData(): void
    {
        $this->items = AdminDemoData::activityLogs();
    }

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return $this->filtered();
    }

    protected function toolbarExportColumns(): array
    {
        return ['id', 'type', 'actor', 'action', 'target', 'before', 'after', 'ip', 'at'];
    }

    protected function toolbarExportName(): string
    {
        return 'activity-logs';
    }
}; ?>

@php
    $typeMeta = [
        'admin_action' => ['color' => 'indigo', 'label' => __('Admin Action')],
        'login' => ['color' => 'blue', 'label' => __('Login')],
        'transaction' => ['color' => 'emerald', 'label' => __('Transaction')],
        'setting' => ['color' => 'amber', 'label' => __('Setting Change')],
    ];
@endphp

<div class="flex flex-col" x-data="listTools('activity-logs')">
    <x-page-meta :title="__('Activity Logs')" route="admin.activity-logs" :breadcrumb="[__('System'), __('Activity Logs')]" />

    <x-admin.page-header :title="__('Activity Logs')">
        <x-slot:toolbar>
            {{-- Read-only: no row selection, so suppress columns/density and keep refresh + export. --}}
            <x-admin.list-toolbar :columns="false" :density="false" />
        </x-slot:toolbar>
    </x-admin.page-header>

    <p class="mb-2 text-sm text-zinc-500">{{ __('Every admin action, login, transaction and setting change is recorded. Logs are read-only and cannot be modified or deleted.') }}</p>

    <x-admin.filter-bar :statuses="['admin_action' => __('Admin Action'), 'login' => __('Login'), 'transaction' => __('Transaction'), 'setting' => __('Setting Change')]" />

    <x-admin.table :selectable="false" stick-first>
        <x-slot:head>
            <tr>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Type') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Actor') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Action') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Target') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Before → After') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('IP') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Timestamp') }}</th>
            </tr>
        </x-slot:head>

        @forelse ($this->rows as $log)
            <tr wire:key="activity-{{ $log['id'] }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2"><flux:badge :color="$typeMeta[$log['type']]['color']" size="sm" inset="top bottom">{{ $typeMeta[$log['type']]['label'] }}</flux:badge></td>
                <td class="px-3 py-2 font-medium text-zinc-800 dark:text-zinc-100">{{ $log['actor'] }}</td>
                <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">{{ $log['action'] }}</td>
                <td class="px-3 py-2 font-mono text-xs text-zinc-500">{{ $log['target'] }}</td>
                <td class="px-3 py-2 text-xs">
                    <span class="text-zinc-400">{{ $log['before'] }}</span>
                    <span class="mx-1 text-zinc-300">→</span>
                    <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $log['after'] }}</span>
                </td>
                <td class="px-3 py-2 font-mono text-xs text-zinc-500">{{ $log['ip'] }}</td>
                <td class="px-3 py-2 tabular-nums text-zinc-500">{{ $log['at'] }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-3 py-8 text-center text-zinc-400">{{ __('No data found') }}</td></tr>
        @endforelse
    </x-admin.table>
</div>
