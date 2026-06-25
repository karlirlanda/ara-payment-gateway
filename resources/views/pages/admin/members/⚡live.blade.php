<?php

use App\Livewire\Concerns\HasListToolbar;
use App\Support\AdminDemoData;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Live Users')] class extends Component {
    use HasListToolbar;

    /** @var array<int, array<string, mixed>> */
    public array $users = [];

    public bool $autoRefresh = true;

    public ?string $forceLogoutUser = null;

    public function mount(): void
    {
        $this->users = AdminDemoData::liveUsers();
    }

    /** Silent refresh driven by wire:poll (no toast); pings the live indicator to reset. */
    public function tick(): void
    {
        $this->users = AdminDemoData::liveUsers();
        $this->dispatch('live-refreshed');
    }

    public function confirmForceLogout(string $username): void
    {
        $this->forceLogoutUser = $username;
        Flux::modal('force-logout-live')->show();
    }

    public function forceLogout(): void
    {
        $user = $this->forceLogoutUser;
        $this->forceLogoutUser = null;
        Flux::modal('force-logout-live')->close();
        Flux::toast(text: __('Forced :user to log out', ['user' => $user]), variant: 'success');
    }

    /** @return array<string, mixed>|null */
    public function forceLogoutTarget(): ?array
    {
        return collect($this->users)->firstWhere('username', $this->forceLogoutUser);
    }

    /** @return array<string, int> */
    #[Computed]
    public function stats(): array
    {
        $c = collect($this->users);

        return [
            'online' => $c->count(),
            'active' => $c->where('state', 'active')->count(),
            'idle' => $c->where('state', 'idle')->count(),
            'newHour' => $c->where('newLogin', true)->count(),
        ];
    }

    /** How many online users are on each IP — drives the shared-IP (collusion) flag. @return array<string, int> */
    #[Computed]
    public function ipCounts(): array
    {
        return collect($this->users)->countBy('ip')->all();
    }

    protected function reloadListData(): void
    {
        $this->users = AdminDemoData::liveUsers();
    }

    protected function toolbarExportRows(bool $selectedOnly): Collection
    {
        return collect($this->users)->values();
    }

    protected function toolbarExportColumns(): array
    {
        return ['level', 'username', 'store', 'storeLevel', 'activity', 'stake', 'balance', 'device', 'ip', 'loginAt', 'sessionSeconds', 'lastSeenSeconds', 'state'];
    }

    protected function toolbarExportName(): string
    {
        return 'live-users';
    }
}; ?>

<div class="flex flex-col" x-data="listTools('live-users')">
    <x-page-meta :title="__('Live Users')" route="admin.members.live" :breadcrumb="[__('Members'), __('Live Users')]" />

    <x-admin.page-header :title="__('Live Users (Members)')">
        <x-slot:actions>
            <div class="flex items-center gap-2 border border-zinc-200 bg-white px-2.5 py-1.5 text-xs text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800"
                x-data="{ ago: 0 }" x-init="setInterval(() => ago++, 1000)" @live-refreshed.window="ago = 0">
                <span class="live-dot"></span>
                <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('LIVE') }}</span>
                <span class="text-zinc-300 dark:text-zinc-600">·</span>
                <span class="tabular-nums" x-text="ago < 2 ? @js(__('Just now')) : (ago + 's ' + @js(__('ago')))"></span>
            </div>
            <flux:button wire:click="$toggle('autoRefresh')" size="sm" variant="subtle" :icon="$autoRefresh ? 'pause' : 'play'">
                {{ $autoRefresh ? __('Pause') : __('Resume') }}
            </flux:button>
        </x-slot:actions>
        <x-slot:toolbar>
            <x-admin.list-toolbar />
        </x-slot:toolbar>
    </x-admin.page-header>

    <x-admin.stat-strip class="mb-2">
        <x-admin.stat-cell :label="__('Online now')" :value="$this->stats['online']" />
        <x-admin.stat-cell :label="__('Active')" :value="$this->stats['active']" />
        <x-admin.stat-cell :label="__('Idle')" :value="$this->stats['idle']" />
        <x-admin.stat-cell :label="__('New logins · 1h')" :value="$this->stats['newHour']" />
    </x-admin.stat-strip>

    @php
        $fmtDur = fn (int $s) => $s >= 3600
            ? floor($s / 3600).'h '.floor(($s % 3600) / 60).'m'
            : floor($s / 60).'m '.($s % 60).'s';
        $fmtAgo = fn (int $s) => match (true) {
            $s === 0 => __('Just now'),
            $s < 60 => $s.'s '.__('ago'),
            $s < 3600 => floor($s / 60).'m '.__('ago'),
            default => floor($s / 3600).'h '.__('ago'),
        };
        $activityColor = ['Lobby' => 'zinc', 'Wallet' => 'indigo', 'Deposit · GCash' => 'emerald', 'Deposit · GoTyme' => 'emerald', 'Withdraw · Maya' => 'amber'];
        $deviceIcon = fn (string $d) => str_contains($d, 'iOS') || str_contains($d, 'Android') ? 'device-phone-mobile' : 'computer-desktop';
    @endphp

    <div @if ($autoRefresh) wire:poll.10s="tick" @endif>
        <x-admin.table :selectable="false" :stick-first="true">
            <x-slot:head>
                <tr>
                    @foreach (['User', 'Gateway', 'Activity', 'Amount', 'Balance', 'Device', 'IP', 'Login time', 'Session', 'Last seen', ''] as $h)
                        <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ $h ? __($h) : '' }}</th>
                    @endforeach
                </tr>
            </x-slot:head>

            @foreach ($users as $u)
                <tr wire:key="live-{{ $u['username'] }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                    <td class="px-3 py-2">
                        <span class="flex items-center gap-2">
                            <span class="live-dot {{ $u['state'] === 'idle' ? 'live-dot--idle' : '' }}" title="{{ $u['state'] === 'idle' ? __('Idle') : __('Active') }}"></span>
                            <x-admin.level-badge :level="$u['level']" :id="$u['username']" />
                        </span>
                    </td>
                    <td class="px-3 py-2">
                        @if (! empty($u['store']))
                            <flux:badge color="zinc" size="sm" inset="top bottom">{{ $u['store'] }}</flux:badge>
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-2"><flux:badge size="sm" :color="$activityColor[$u['activity']] ?? 'zinc'" inset="top bottom">{{ __($u['activity']) }}</flux:badge></td>
                    <td class="px-3 py-2 text-end tabular-nums">{{ $u['stake'] > 0 ? number_format($u['stake']) : '—' }}</td>
                    <td class="px-3 py-2 text-end tabular-nums">{{ number_format($u['balance']) }}</td>
                    <td class="px-3 py-2 text-zinc-500">
                        <span class="inline-flex items-center gap-1.5">
                            <flux:icon :icon="$deviceIcon($u['device'])" class="size-4 text-zinc-400" />
                            {{ $u['device'] }}
                        </span>
                    </td>
                    <td class="px-3 py-2 tabular-nums">
                        <span class="inline-flex items-center gap-1.5">
                            {{ $u['ip'] }}
                            @if (($this->ipCounts[$u['ip']] ?? 1) > 1)
                                <flux:badge size="sm" color="rose" inset="top bottom" :title="__('Shared IP')">{{ __('Shared') }} ×{{ $this->ipCounts[$u['ip']] }}</flux:badge>
                            @endif
                        </span>
                    </td>
                    <td class="px-3 py-2 tabular-nums text-zinc-500">{{ $u['loginAt'] }}</td>
                    <td class="px-3 py-2 text-end tabular-nums">{{ $fmtDur($u['sessionSeconds']) }}</td>
                    <td @class([
                        'px-3 py-2 tabular-nums',
                        'text-amber-600 dark:text-amber-500' => $u['state'] === 'idle',
                        'text-emerald-600 dark:text-emerald-500' => $u['state'] !== 'idle',
                    ])>{{ $fmtAgo($u['lastSeenSeconds']) }}</td>
                    <td class="px-3 py-2 text-end">
                        <flux:button wire:click="confirmForceLogout('{{ $u['username'] }}')" size="xs" variant="subtle">{{ __('Force logout') }}</flux:button>
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    </div>

    <flux:modal name="force-logout-live" class="w-full max-w-sm">
        @php $target = $this->forceLogoutTarget(); @endphp
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Force logout') }}</flux:heading>
            @if ($target)
                <div class="flex items-center border border-zinc-200 bg-zinc-50 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900/50">
                    <x-admin.level-badge :level="$target['level']" :id="$target['username']" />
                </div>
            @endif
            <flux:text>{{ __('End this member\'s active session and force them to log out?') }}</flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close>
                <flux:button wire:click="forceLogout" variant="danger" data-test="confirm-force-logout">{{ __('Force logout') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
