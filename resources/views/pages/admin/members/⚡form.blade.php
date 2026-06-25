<?php

use App\Support\AdminDemoData;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Member Form')] class extends Component {
    public ?int $id = null;

    public string $type = 'offline';
    public string $upline = '';
    public int $level = 1;
    public string $username = '';
    public string $nickname = '';
    public string $password = '';
    public string $passwordConfirm = '';
    public string $withdrawPin = '';
    public string $email = '';
    public string $messenger = '';
    public string $phone = '';
    public string $memberStatus = 'normal';
    public string $memberType = 'normal';
    public string $memberClass = 'normal';
    public string $accountGrade = 'normal';
    public int $balance = 0;
    public int $points = 0;
    public string $memo = '';

    public bool $memberAccountEnabled = true;
    public string $memberBankName = '';
    public string $memberHolder = '';
    public string $memberAccountNo = '';

    public bool $depositAccountEnabled = false;
    public string $depositBankName = '';
    public string $depositHolder = '';
    public string $depositAccountNo = '';

    public bool $commissionEnabled = true;
    public string $commissionType = 'turnover';
    public int $depositBonusPct = 0;
    public int $referralPct = 0;
    public int $cashbackPct = 0;

    public function mount(?int $id = null): void
    {
        $this->id = $id;

        if ($id !== null) {
            $member = collect(AdminDemoData::members())->firstWhere('id', $id);
            if ($member) {
                $this->username = $member['username'];
                $this->nickname = $member['nickname'];
                $this->level = $member['level'];
                $this->phone = $member['phone'];
                $this->balance = $member['balance'];
                $this->points = $member['points'];
                $this->memberStatus = $member['status'];
                $this->commissionType = $member['commissionType'];
            }
        }
    }

    public function save(): void
    {
        $this->validate([
            'username' => ['required', 'string', 'max:50'],
            'nickname' => ['required', 'string', 'max:50'],
            'level' => ['required', 'integer', 'between:1,5'],
            'type' => ['required', 'in:offline,online'],
            'memberStatus' => ['required', 'in:normal,suspended,withdrawn'],
            'commissionType' => ['required', 'in:turnover,loss'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            'passwordConfirm' => ['nullable', 'same:password'],
        ]);

        Flux::toast(text: $this->id ? __('Member updated') : __('Member created'), variant: 'success');
        $this->redirectRoute('admin.members', navigate: true);
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="$id ? __('Edit member') : __('Create member')" route="admin.members.form" :breadcrumb="[__('Members'), $id ? __('Edit member') : __('Create member')]" />

    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ $id ? __('Edit member') : __('Create member') }}</flux:heading>
        <div class="flex gap-2">
            <flux:button :href="route('admin.members')" wire:navigate variant="ghost">{{ __('Cancel') }}</flux:button>
            <flux:button wire:click="save" variant="primary">{{ __('Save') }}</flux:button>
        </div>
    </div>

    <form wire:submit="save" class="flex flex-col gap-4">
        <flux:card>
            <flux:heading size="sm" class="mb-3">{{ __('Account') }}</flux:heading>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:radio.group wire:model="type" :label="__('Type')" variant="segmented">
                    <flux:radio value="offline" :label="__('Offline')" />
                    <flux:radio value="online" :label="__('Online')" />
                </flux:radio.group>
                <flux:select wire:model="memberStatus" :label="__('Member status')">
                    <flux:select.option value="normal">{{ __('Normal') }}</flux:select.option>
                    <flux:select.option value="suspended">{{ __('Suspended') }}</flux:select.option>
                    <flux:select.option value="withdrawn">{{ __('Withdrawn') }}</flux:select.option>
                </flux:select>
                <flux:input wire:model="upline" :label="__('Store (upline)')" :placeholder="__('Recommend code or username')" />
                <flux:select wire:model="level" :label="__('Level')">
                    @foreach (range(1, 5) as $l)
                        <flux:select.option :value="$l">{{ __('Level') }} {{ $l }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="username" :label="__('Username')" />
                <flux:input wire:model="nickname" :label="__('Nickname')" />
                <flux:input wire:model="password" type="password" :label="__('Password')" />
                <flux:input wire:model="passwordConfirm" type="password" :label="__('Confirm password')" />
                <flux:input wire:model="withdrawPin" type="password" :label="__('Withdraw password / PIN')" />
                <flux:input wire:model="email" type="email" :label="__('Email')" />
                <flux:input wire:model="phone" :label="__('Phone')" />
                <flux:input wire:model="messenger" :label="__('Messenger')" />
                <flux:select wire:model="memberType" :label="__('Member type')">
                    <flux:select.option value="normal">{{ __('Normal') }}</flux:select.option>
                    <flux:select.option value="watch">{{ __('Watch') }}</flux:select.option>
                    <flux:select.option value="caution">{{ __('Caution') }}</flux:select.option>
                </flux:select>
                <flux:select wire:model="memberClass" :label="__('Member class')">
                    <flux:select.option value="normal">{{ __('Normal') }}</flux:select.option>
                    <flux:select.option value="watch">{{ __('Watch') }}</flux:select.option>
                    <flux:select.option value="caution">{{ __('Caution') }}</flux:select.option>
                </flux:select>
                <flux:select wire:model="accountGrade" :label="__('Account grade')">
                    <flux:select.option value="normal">{{ __('Normal') }}</flux:select.option>
                    <flux:select.option value="silver">Silver</flux:select.option>
                    <flux:select.option value="gold">Gold</flux:select.option>
                    <flux:select.option value="vip">VIP</flux:select.option>
                </flux:select>
                <flux:input wire:model="balance" type="number" :label="__('Balance')" />
                <flux:input wire:model="points" type="number" :label="__('Points')" />
            </div>
            <flux:textarea wire:model="memo" :label="__('Memo')" rows="3" class="mt-4" />
        </flux:card>

        <flux:card>
            <flux:heading size="sm" class="mb-3">{{ __('Bank account') }}</flux:heading>

            <div class="mb-2 flex items-center justify-between">
                <flux:text class="font-medium">{{ __('Member account (default)') }}</flux:text>
                <flux:switch wire:model="memberAccountEnabled" :label="__('Enabled')" align="right" />
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <flux:input wire:model="memberBankName" :label="__('Bank name')" />
                <flux:input wire:model="memberHolder" :label="__('Account holder')" />
                <flux:input wire:model="memberAccountNo" :label="__('Account number')" />
            </div>

            <flux:separator class="my-4" />

            <div class="mb-2 flex items-center justify-between">
                <flux:text class="font-medium">{{ __('Deposit account') }}</flux:text>
                <flux:switch wire:model="depositAccountEnabled" :label="__('Enabled')" align="right" />
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <flux:input wire:model="depositBankName" :label="__('Bank name')" />
                <flux:input wire:model="depositHolder" :label="__('Account holder')" />
                <flux:input wire:model="depositAccountNo" :label="__('Account number')" />
            </div>
        </flux:card>

        <flux:card>
            <div class="mb-3 flex items-center justify-between">
                <flux:heading size="sm">{{ __('Rewards & commission') }}</flux:heading>
                <flux:switch wire:model="commissionEnabled" :label="__('Enabled')" align="right" />
            </div>
            <flux:radio.group wire:model="commissionType" :label="__('Commission type')" variant="segmented" class="mb-4">
                <flux:radio value="turnover" :label="__('Turnover')" />
                <flux:radio value="loss" :label="__('Loss rebate')" />
            </flux:radio.group>
            <div class="grid gap-4 md:grid-cols-3">
                <flux:input wire:model="depositBonusPct" type="number" :label="__('Deposit bonus %')" />
                <flux:input wire:model="referralPct" type="number" :label="__('Referral %')" />
                <flux:input wire:model="cashbackPct" type="number" :label="__('Cashback %')" />
            </div>
        </flux:card>
    </form>
</div>
