<?php

use App\Support\AdminDemoData;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Player Profile')] class extends Component {
    public int $id = 0;
    public string $tab = 'overview';

    public function mount(int $id): void
    {
        $this->id = $id;
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function player(): array
    {
        return AdminDemoData::playerProfile($this->id);
    }
}; ?>

@php
    $p = $this->player;
    $statusMeta = [
        'normal' => ['color' => 'green', 'label' => __('Active')],
        'suspended' => ['color' => 'amber', 'label' => __('Suspended')],
        'withdrawn' => ['color' => 'red', 'label' => __('Withdrawn')],
    ];
    $txnStatus = [
        'completed' => ['color' => 'green', 'label' => __('Completed')],
        'pending' => ['color' => 'amber', 'label' => __('Pending')],
        'cancelled' => ['color' => 'red', 'label' => __('Cancelled')],
    ];
    $tabs = [
        'overview' => __('Overview'),
        'transactions' => __('Transactions'),
        'logins' => __('Login History'),
        'coupons' => __('Coupons'),
        'notes' => __('Notes'),
        'activity' => __('Activity Log'),
    ];
@endphp

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Player Profile')" route="admin.members.profile" :breadcrumb="[__('Members'), $p['username']]" />

    <div class="flex items-center gap-2">
        <flux:button :href="route('admin.members')" wire:navigate icon="arrow-left" size="sm" variant="subtle">{{ __('Members') }}</flux:button>
    </div>

    {{-- Profile header --}}
    <div class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex items-center gap-4 bg-gradient-to-r from-indigo-700 to-violet-700 px-5 py-4">
            <div class="flex size-12 items-center justify-center rounded-full bg-white/20 text-2xl ring-2 ring-white/40">
                <flux:icon icon="user" class="size-6 text-white" />
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 text-white">
                    <span class="text-base font-bold">{{ $p['username'] }}</span>
                    <span class="text-xs text-white/70">{{ __('ID') }}: {{ str_pad((string) $p['id'], 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="truncate text-xs text-indigo-100">{{ $p['brand'] }} · {{ __('Agent') }}: {{ $p['agent'] }} · {{ __('Registered') }} {{ $p['registered'] }} · {{ __('Last IP') }}: {{ $p['lastIp'] }}</div>
            </div>
            <div class="flex shrink-0 gap-1.5">
                <flux:badge :color="$statusMeta[$p['status']]['color']" size="sm">{{ $statusMeta[$p['status']]['label'] }}</flux:badge>
                <flux:badge color="blue" size="sm">{{ strtoupper($p['tier']) }} {{ __('VIP') }}</flux:badge>
            </div>
        </div>

        <x-admin.stat-strip>
            <x-admin.stat-cell :label="__('Total Deposit')" :value="'₱'.number_format($p['totalDeposit'])" />
            <x-admin.stat-cell :label="__('Total Withdraw')" :value="'₱'.number_format($p['totalWithdraw'])" />
            <x-admin.stat-cell :label="__('Net')" :value="'₱'.number_format($p['net'])" />
            <x-admin.stat-cell :label="__('Total Transactions')" :value="number_format($p['totalTransactions'])" />
            <x-admin.stat-cell :label="__('Total Logins')" :value="number_format($p['totalLogins'])" />
        </x-admin.stat-strip>
    </div>

    {{-- Tabs --}}
    <div class="flex flex-wrap gap-0.5 overflow-x-auto">
        @foreach ($tabs as $key => $label)
            <button type="button" wire:click="$set('tab', '{{ $key }}')"
                @class([
                    'border border-b-0 px-4 py-1.5 text-xs whitespace-nowrap',
                    'border-accent bg-accent text-[color:var(--color-accent-foreground)]' => $tab === $key,
                    'border-zinc-200 bg-white text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' => $tab !== $key,
                ])>{{ $label }}</button>
        @endforeach
    </div>

    @if ($tab === 'overview')
        <div class="grid grid-cols-2 gap-px border border-zinc-200 bg-zinc-200 sm:grid-cols-3 lg:grid-cols-5 dark:border-zinc-700 dark:bg-zinc-700">
            @php
                $overview = [
                    [__('Current Balance'), '₱'.number_format($p['balance']), ''],
                    [__('Bonus Balance'), '₱'.number_format($p['bonusBalance']), ''],
                    [__('Total Deposit'), '₱'.number_format($p['totalDeposit']), 'text-emerald-600 dark:text-emerald-400'],
                    [__('Total Withdraw'), '₱'.number_format($p['totalWithdraw']), 'text-amber-600 dark:text-amber-400'],
                    [__('Net Movement'), '₱'.number_format($p['net']), ''],
                    [__('Referral Source'), $p['agent'], ''],
                    [__('VIP Tier'), $p['tier'], ''],
                    [__('Status'), $statusMeta[$p['status']]['label'], ''],
                ];
            @endphp
            @foreach ($overview as [$label, $value, $cls])
                <div class="bg-white px-3 py-2.5 dark:bg-zinc-800">
                    <div class="text-[10px] font-bold uppercase tracking-wide text-zinc-400">{{ $label }}</div>
                    <div class="mt-0.5 text-sm font-semibold tabular-nums {{ $cls ?: 'text-zinc-800 dark:text-zinc-100' }}">{{ $value }}</div>
                </div>
            @endforeach
        </div>
    @elseif ($tab === 'transactions')
        <x-admin.table :selectable="false" stick-first>
            <x-slot:head><tr>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Reference') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Type') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Gateway') }}</th>
                <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Amount') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Status') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Date') }}</th>
            </tr></x-slot:head>
            @foreach ($p['transactions'] as $t)
                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                    <td class="px-3 py-2 font-mono text-xs text-zinc-500">{{ $t['reference'] }}</td>
                    <td class="px-3 py-2">{{ $t['type'] === 'deposit' ? __('Deposit') : __('Withdraw') }}</td>
                    <td class="px-3 py-2"><flux:badge color="zinc" size="sm" inset="top bottom">{{ $t['gateway'] }}</flux:badge></td>
                    <td class="px-3 py-2 text-end tabular-nums">₱{{ number_format($t['amount']) }}</td>
                    <td class="px-3 py-2"><flux:badge :color="$txnStatus[$t['status']]['color']" size="sm" inset="top bottom">{{ $txnStatus[$t['status']]['label'] }}</flux:badge></td>
                    <td class="px-3 py-2 tabular-nums text-zinc-500">{{ $t['at'] }}</td>
                </tr>
            @endforeach
        </x-admin.table>
    @elseif ($tab === 'logins')
        <x-admin.table :selectable="false" stick-first>
            <x-slot:head><tr>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Date') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Device') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('IP') }}</th>
                <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Session') }}</th>
            </tr></x-slot:head>
            @foreach ($p['logins'] as $l)
                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                    <td class="px-3 py-2 tabular-nums">{{ $l['at'] }}</td>
                    <td class="px-3 py-2">{{ $l['device'] }}</td>
                    <td class="px-3 py-2 font-mono text-xs text-zinc-500">{{ $l['ip'] }}</td>
                    <td class="px-3 py-2 text-end tabular-nums">{{ $l['duration'] }}</td>
                </tr>
            @endforeach
        </x-admin.table>
    @elseif ($tab === 'coupons')
        <x-admin.table :selectable="false" stick-first>
            <x-slot:head><tr>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Code') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Value') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Status') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Date') }}</th>
            </tr></x-slot:head>
            @foreach ($p['coupons'] as $c)
                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                    <td class="px-3 py-2 font-mono text-xs font-semibold">{{ $c['code'] }}</td>
                    <td class="px-3 py-2 tabular-nums">{{ $c['value'] }}</td>
                    <td class="px-3 py-2"><flux:badge :color="$c['status'] === 'redeemed' ? 'green' : 'zinc'" size="sm" inset="top bottom">{{ ucfirst($c['status']) }}</flux:badge></td>
                    <td class="px-3 py-2 tabular-nums text-zinc-500">{{ $c['at'] }}</td>
                </tr>
            @endforeach
        </x-admin.table>
    @elseif ($tab === 'notes')
        <div class="flex flex-col gap-2">
            @foreach ($p['notes'] as $n)
                <div class="border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $n['author'] }}</span>
                        <span class="text-zinc-400">{{ $n['at'] }}</span>
                    </div>
                    <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $n['body'] }}</div>
                </div>
            @endforeach
        </div>
    @else
        <x-admin.table :selectable="false" stick-first>
            <x-slot:head><tr>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Action') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Date') }}</th>
            </tr></x-slot:head>
            @foreach ($p['activity'] as $a)
                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                    <td class="px-3 py-2 text-zinc-700 dark:text-zinc-200">{{ $a['action'] }}</td>
                    <td class="px-3 py-2 tabular-nums text-zinc-500">{{ $a['at'] }}</td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif
</div>
