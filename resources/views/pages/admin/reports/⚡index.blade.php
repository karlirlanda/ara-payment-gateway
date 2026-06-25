<?php

use App\Support\AdminDemoData;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Reports')] class extends Component {
    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function catalog(): array
    {
        return AdminDemoData::reportCatalog();
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('All Reports')" route="admin.reports" :breadcrumb="[__('Reports'), __('All Reports')]" />

    <x-admin.page-header :title="__('Sales & Revenue Reports')">
        <x-slot:actions>
            <flux:button :href="route('admin.reports.sales')" wire:navigate icon="document-chart-bar" size="sm" variant="primary">{{ __('Open Sales Report') }}</flux:button>
            <flux:button :href="route('admin.reports.daily')" wire:navigate icon="chart-bar-square" size="sm" variant="filled">{{ __('Daily Sales') }}</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <p class="text-sm text-zinc-500">
        {{ __('Every report supports date-range, brand, agent and game filtering, plus Excel / CSV export. All figures in Philippine Peso (₱).') }}
    </p>

    <x-admin.table :selectable="false" stick-first>
        <x-slot:head>
            <tr>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Report') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Key Metrics') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Grouping') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Export') }}</th>
                <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Open') }}</th>
            </tr>
        </x-slot:head>

        @foreach ($this->catalog as $r)
            <tr wire:key="report-{{ $loop->index }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2 font-semibold text-zinc-800 dark:text-zinc-100">{{ $r['name'] }}</td>
                <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">{{ $r['metrics'] }}</td>
                <td class="px-3 py-2 text-zinc-500">{{ $r['grouping'] }}</td>
                <td class="px-3 py-2">
                    <flux:badge color="green" size="sm" inset="top bottom">{{ $r['export'] }}</flux:badge>
                </td>
                <td class="px-3 py-2 text-end">
                    @if ($r['route'])
                        <flux:button :href="$r['param'] ? route($r['route'], $r['param']) : route($r['route'])" wire:navigate size="xs" variant="subtle" icon="arrow-up-right">{{ __('View') }}</flux:button>
                    @else
                        <flux:badge color="zinc" size="sm" inset="top bottom">{{ __('Demo stub') }}</flux:badge>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-admin.table>
</div>
