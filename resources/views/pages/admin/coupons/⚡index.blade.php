<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Coupons & Events')] class extends Component {
    use HasListToolbar;

    public string $tab = 'coupons';
    public string $status = '';
    public string $keyword = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    // Create-coupon modal state.
    public string $form_code = '';
    public string $form_type = 'fixed';
    public int $form_value = 100;
    public int $form_minDeposit = 500;
    public int $form_rollover = 10;
    public string $form_expiry = '';
    public int $form_maxUses = 500;

    public function mount(): void
    {
        $this->items = AdminDemoData::coupons();
    }

    public function updatedStatus(): void
    {
        // no pagination on this list — filtering is computed live
    }

    public function resetFilters(): void
    {
        $this->reset(['status', 'keyword']);
    }

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function rows(): array
    {
        return collect($this->items)
            ->when($this->status !== '', fn ($c) => $c->where('status', $this->status))
            ->when($this->keyword !== '', fn ($c) => $c->filter(
                fn ($r) => str_contains(strtolower($r['code']), strtolower($this->keyword))
            ))
            ->values()
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function events(): array
    {
        return AdminDemoData::events();
    }

    public function createCoupon(): void
    {
        $this->reset(['form_code', 'form_type', 'form_value', 'form_minDeposit', 'form_rollover', 'form_expiry', 'form_maxUses']);
        $this->form_type = 'fixed';
        $this->form_value = 100;
        $this->form_minDeposit = 500;
        $this->form_rollover = 10;
        $this->form_maxUses = 500;
        Flux::modal('coupon-form')->show();
    }

    public function saveCoupon(): void
    {
        $validated = $this->validate([
            'form_code' => ['required', 'string', 'max:30'],
            'form_type' => ['required', 'in:fixed,percent'],
            'form_value' => ['required', 'integer', 'min:1'],
            'form_minDeposit' => ['required', 'integer', 'min:0'],
            'form_rollover' => ['required', 'integer', 'min:0'],
            'form_expiry' => ['required', 'date'],
            'form_maxUses' => ['required', 'integer', 'min:1'],
        ]);

        $this->items[] = [
            'id' => (collect($this->items)->max('id') ?? 0) + 1,
            'code' => strtoupper($validated['form_code']),
            'type' => $validated['form_type'],
            'value' => $validated['form_value'],
            'minDeposit' => $validated['form_minDeposit'],
            'rollover' => $validated['form_rollover'],
            'expiry' => $validated['form_expiry'],
            'maxUses' => $validated['form_maxUses'],
            'used' => 0,
            'status' => 'active',
        ];

        Flux::modal('coupon-form')->close();
        Flux::toast(text: __('Coupon created'), variant: 'success');
    }

    protected function reloadListData(): void
    {
        $this->items = AdminDemoData::coupons();
    }

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect($this->rows);
    }

    protected function toolbarExportColumns(): array
    {
        return ['id', 'code', 'type', 'value', 'minDeposit', 'rollover', 'expiry', 'maxUses', 'used', 'status'];
    }

    protected function toolbarExportName(): string
    {
        return 'coupons';
    }
}; ?>

@php
    $statusMeta = [
        'active' => ['color' => 'green', 'label' => __('Active')],
        'scheduled' => ['color' => 'blue', 'label' => __('Scheduled')],
        'expired' => ['color' => 'zinc', 'label' => __('Expired')],
        'disabled' => ['color' => 'red', 'label' => __('Disabled')],
    ];
    $tabs = [
        ['key' => 'coupons', 'label' => __('Coupons')],
        ['key' => 'events', 'label' => __('Events')],
    ];
@endphp

<div class="flex flex-col" x-data="listTools('coupons')">
    <x-page-meta :title="__('Coupons & Events')" route="admin.coupons" :breadcrumb="[__('Engagement'), __('Coupons & Events')]" />

    <x-admin.page-header :title="__('Coupons & Events')">
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
        <x-slot:actions>
            @if ($tab === 'coupons')
                <flux:button wire:click="createCoupon" icon="plus" variant="primary" size="sm">{{ __('Create coupon') }}</flux:button>
            @endif
        </x-slot:actions>
        <x-slot:toolbar>
            @if ($tab === 'coupons')
                <x-admin.list-toolbar :columns="false" />
            @endif
        </x-slot:toolbar>
    </x-admin.page-header>

    @if ($tab === 'coupons')
        <x-admin.filter-bar :statuses="['active' => __('Active'), 'scheduled' => __('Scheduled'), 'expired' => __('Expired'), 'disabled' => __('Disabled')]" />

        <x-admin.table :selectable="false" stick-first>
            <x-slot:head>
                <tr>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Code') }}</th>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Value') }}</th>
                    <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Min deposit') }}</th>
                    <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Rollover') }}</th>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Expiry') }}</th>
                    <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Used / Max') }}</th>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Status') }}</th>
                </tr>
            </x-slot:head>

            @forelse ($this->rows as $c)
                <tr wire:key="coupon-{{ $c['id'] }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                    <td class="px-3 py-2 font-mono text-xs font-semibold text-zinc-800 dark:text-zinc-100">{{ $c['code'] }}</td>
                    <td class="px-3 py-2 tabular-nums">{{ $c['type'] === 'percent' ? $c['value'].'%' : '₱'.number_format($c['value']) }}</td>
                    <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($c['minDeposit']) }}</td>
                    <td class="px-3 py-2 text-end tabular-nums">×{{ $c['rollover'] }}</td>
                    <td class="px-3 py-2 tabular-nums">{{ $c['expiry'] }}</td>
                    <td class="px-3 py-2 text-end tabular-nums">{{ number_format($c['used']) }} / {{ number_format($c['maxUses']) }}</td>
                    <td class="px-3 py-2">
                        <flux:badge :color="$statusMeta[$c['status']]['color']" size="sm" inset="top bottom">{{ $statusMeta[$c['status']]['label'] }}</flux:badge>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-3 py-8 text-center text-zinc-400">{{ __('No data found') }}</td></tr>
            @endforelse
        </x-admin.table>
    @else
        <x-admin.table :selectable="false" stick-first>
            <x-slot:head>
                <tr>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Event') }}</th>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Type') }}</th>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Reward') }}</th>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Period') }}</th>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Status') }}</th>
                </tr>
            </x-slot:head>

            @foreach ($this->events as $e)
                <tr wire:key="event-{{ $e['id'] }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                    <td class="px-3 py-2 font-semibold text-zinc-800 dark:text-zinc-100">{{ $e['name'] }}</td>
                    <td class="px-3 py-2">{{ $e['type'] }}</td>
                    <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">{{ $e['reward'] }}</td>
                    <td class="px-3 py-2 tabular-nums text-zinc-500">{{ $e['period'] }}</td>
                    <td class="px-3 py-2">
                        <flux:badge :color="$e['status'] === 'active' ? 'green' : 'blue'" size="sm" inset="top bottom">{{ $e['status'] === 'active' ? __('Active') : __('Scheduled') }}</flux:badge>
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif

    {{-- Create coupon modal --}}
    <flux:modal name="coupon-form" class="w-full max-w-md">
        <form wire:submit="saveCoupon" class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Create coupon') }}</flux:heading>
            <flux:input wire:model="form_code" :label="__('Code')" placeholder="WELCOME100" />
            <div class="grid grid-cols-2 gap-3">
                <flux:select wire:model="form_type" :label="__('Type')">
                    <flux:select.option value="fixed">{{ __('Fixed ₱') }}</flux:select.option>
                    <flux:select.option value="percent">{{ __('Percent %') }}</flux:select.option>
                </flux:select>
                <flux:input type="number" wire:model="form_value" :label="__('Value')" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <flux:input type="number" wire:model="form_minDeposit" :label="__('Min deposit (₱)')" />
                <flux:input type="number" wire:model="form_rollover" :label="__('Rollover ×')" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <flux:input type="date" wire:model="form_expiry" :label="__('Expiry')" />
                <flux:input type="number" wire:model="form_maxUses" :label="__('Max uses')" />
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
