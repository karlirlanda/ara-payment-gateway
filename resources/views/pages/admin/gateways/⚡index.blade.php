<?php

use App\Support\AdminDemoData;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Gateways')] class extends Component {
    /** @var array<int, array<string, mixed>> */
    public array $gateways = [];

    public function mount(): void
    {
        $this->gateways = AdminDemoData::gateways();
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Providers')" route="admin.gateways" :breadcrumb="[__('Gateways'), __('Providers')]" />

    <x-admin.page-header :title="__('Payment Gateways')" />

    <p class="text-sm text-zinc-500">
        {{ __('Three major Philippine digital payment providers integrated as deposit and withdrawal gateways for online members.') }}
    </p>

    {{-- Provider cards --}}
    <div class="grid gap-4 lg:grid-cols-3">
        @foreach ($gateways as $g)
            <flux:card class="flex flex-col gap-3">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2.5">
                        <span @class([
                            'flex size-9 items-center justify-center rounded-lg',
                            'bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400' => $g['color'] === 'sky',
                            'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400' => $g['color'] === 'emerald',
                            'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400' => $g['color'] === 'amber',
                        ])>
                            <flux:icon :icon="$g['icon']" class="size-5" />
                        </span>
                        <div>
                            <flux:heading size="sm">{{ $g['name'] }}</flux:heading>
                            <flux:text class="text-xs">{{ $g['license'] }}</flux:text>
                        </div>
                    </div>
                    <flux:badge color="green" size="sm" inset="top bottom">{{ __('Active') }}</flux:badge>
                </div>

                <flux:separator />

                <dl class="grid grid-cols-1 gap-2 text-xs">
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">{{ __('Operator') }}</dt>
                        <dd class="text-end text-zinc-800 dark:text-zinc-200">{{ $g['operator'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">{{ __('User base') }}</dt>
                        <dd class="text-end font-semibold text-zinc-800 dark:text-zinc-200">{{ $g['users'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">{{ __('Deposit') }}</dt>
                        <dd class="text-end text-zinc-800 dark:text-zinc-200">{{ $g['deposit'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">{{ __('Withdraw') }}</dt>
                        <dd class="text-end text-zinc-800 dark:text-zinc-200">{{ $g['withdraw'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">{{ __('Transaction fee') }}</dt>
                        <dd class="text-end"><flux:badge :color="$g['feeColor']" size="sm" inset="top bottom">{{ $g['fee'] }}</flux:badge></dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-zinc-500">{{ __('Target segment') }}</dt>
                        <dd class="text-end text-zinc-800 dark:text-zinc-200">{{ $g['segment'] }}</dd>
                    </div>
                </dl>

                <flux:separator />

                <div class="flex items-end justify-between">
                    <div>
                        <div class="text-xs text-zinc-500">{{ __('Today volume') }}</div>
                        <div class="text-lg font-bold tabular-nums text-zinc-800 dark:text-zinc-100">₱{{ number_format($g['todayVolume']) }}</div>
                    </div>
                    <div class="text-end">
                        <div class="text-xs text-zinc-500">{{ __('Transactions') }}</div>
                        <div class="text-lg font-bold tabular-nums text-zinc-800 dark:text-zinc-100">{{ number_format($g['todayCount']) }}</div>
                    </div>
                </div>
            </flux:card>
        @endforeach
    </div>

    {{-- Integration flow --}}
    <flux:card>
        <flux:heading size="sm" class="mb-3">{{ __('Integration Flow') }}</flux:heading>
        <div class="grid gap-4 md:grid-cols-3">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <flux:icon icon="arrow-down-circle" class="size-4 text-emerald-500" />
                    <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ __('Deposit') }}</span>
                </div>
                <p class="text-xs text-zinc-500">{{ __('Player selects gateway → QR / redirect → provider confirms → platform credits wallet instantly.') }}</p>
            </div>
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <flux:icon icon="arrow-up-circle" class="size-4 text-indigo-500" />
                    <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ __('Withdrawal') }}</span>
                </div>
                <p class="text-xs text-zinc-500">{{ __('Player requests → admin approves → platform initiates API transfer → provider delivers.') }}</p>
            </div>
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <flux:icon icon="scale" class="size-4 text-amber-500" />
                    <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ __('Reconciliation') }}</span>
                </div>
                <p class="text-xs text-zinc-500">{{ __('Daily automated reconciliation against provider transaction logs.') }}</p>
            </div>
        </div>
    </flux:card>
</div>
