<?php

use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Advanced settings')] class extends Component {
    public bool $transactionsEnabled = true;
    public bool $maskAccountNumbers = true;
    public bool $reconciliationEnabled = true;
    public string $welcomeMessage = 'Welcome to ARA Pay!';
    public int $signupBonusPoints = 0;
    public string $reconciliationTime = '02:00';

    public function save(): void
    {
        Flux::toast(text: __('Settings saved'), variant: 'success');
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Advanced settings')" route="admin.settings.advanced" :breadcrumb="[__('Settings'), __('Advanced settings')]" />

    <x-admin.page-header :title="__('Settings')">
        <x-slot:tabs>
            <x-admin.settings-tabs />
        </x-slot:tabs>
    </x-admin.page-header>

    <form wire:submit="save" class="flex flex-col gap-4">
        <flux:card>
            <flux:heading size="sm" class="mb-3">{{ __('Transactions') }}</flux:heading>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:switch wire:model="transactionsEnabled" :label="__('Transactions enabled')" align="right" />
                <flux:switch wire:model="maskAccountNumbers" :label="__('Mask account numbers')" align="right" />
            </div>
        </flux:card>

        <flux:card>
            <flux:heading size="sm" class="mb-3">{{ __('Member defaults') }}</flux:heading>
            <div class="flex flex-col gap-4">
                <flux:textarea wire:model="welcomeMessage" :label="__('Welcome message')" rows="3" />
                <flux:input wire:model="signupBonusPoints" type="number" :label="__('Signup bonus points')" class="md:w-1/2" />
            </div>
        </flux:card>

        <flux:card>
            <flux:heading size="sm" class="mb-3">{{ __('Reconciliation') }}</flux:heading>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:switch wire:model="reconciliationEnabled" :label="__('Daily reconciliation enabled')" align="right" />
                <flux:input wire:model="reconciliationTime" type="time" :label="__('Daily reconciliation time')" />
            </div>
            <div class="mt-4 flex justify-end">
                <flux:button wire:click="save" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </flux:card>
    </form>
</div>
