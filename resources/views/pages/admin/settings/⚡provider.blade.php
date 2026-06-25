<?php

use App\Support\AdminDemoData;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Gateway settings')] class extends Component {
    public bool $gcashEnabled = true;
    public bool $mayaEnabled = true;
    public bool $gotymeEnabled = true;
    public string $defaultGateway = 'GCash';
    public int $minDeposit = 100;
    public int $maxWithdrawal = 50000;
    public bool $autoCreditDeposits = true;
    public bool $requireWithdrawalApproval = true;

    public function save(): void
    {
        Flux::toast(text: __('Settings saved'), variant: 'success');
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Gateway settings')" route="admin.settings.provider" :breadcrumb="[__('Settings'), __('Gateway settings')]" />

    <x-admin.page-header :title="__('Settings')">
        <x-slot:tabs>
            <x-admin.settings-tabs />
        </x-slot:tabs>
    </x-admin.page-header>

    <form wire:submit="save" class="flex flex-col gap-4">
        <flux:card>
            <flux:heading size="sm" class="mb-3">{{ __('Enabled gateways') }}</flux:heading>
            <div class="grid gap-4 md:grid-cols-3">
                <flux:switch wire:model="gcashEnabled" label="GCash" align="right" />
                <flux:switch wire:model="mayaEnabled" label="Maya" align="right" />
                <flux:switch wire:model="gotymeEnabled" label="GoTyme" align="right" />
            </div>
        </flux:card>

        <flux:card>
            <flux:heading size="sm" class="mb-3">{{ __('Deposit & withdrawal limits') }}</flux:heading>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:select wire:model="defaultGateway" :label="__('Default gateway')">
                    @foreach (AdminDemoData::GATEWAYS as $gw)
                        <flux:select.option :value="$gw">{{ $gw }}</flux:select.option>
                    @endforeach
                </flux:select>
                <div></div>
                <flux:input wire:model="minDeposit" type="number" :label="__('Minimum deposit (₱)')" />
                <flux:input wire:model="maxWithdrawal" type="number" :label="__('Maximum withdrawal (₱)')" />
            </div>
        </flux:card>

        <flux:card>
            <flux:heading size="sm" class="mb-3">{{ __('Processing') }}</flux:heading>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:switch wire:model="autoCreditDeposits" :label="__('Auto-credit wallet on deposit confirmation')" align="right" />
                <flux:switch wire:model="requireWithdrawalApproval" :label="__('Require admin approval for withdrawals')" align="right" />
            </div>
            <div class="mt-4 flex justify-end">
                <flux:button wire:click="save" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </flux:card>
    </form>
</div>
