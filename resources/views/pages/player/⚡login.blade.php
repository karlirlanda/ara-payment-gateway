<?php

use App\Support\PlayerDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Log in')] #[Layout('layouts.player-guest')] class extends Component {
    public string $username = '';

    public string $password = '';

    public function mount(): void
    {
        // Already signed in to the demo session — go straight to the lobby.
        if (session()->has('player.demo')) {
            $this->redirectRoute('player.lobby', navigate: true);
        }
    }

    /**
     * Validate the preset demo credentials and seed the mock player session.
     */
    public function login(): void
    {
        $this->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $matches = $this->username === PlayerDemoData::DEMO_USERNAME
            && $this->password === PlayerDemoData::DEMO_PASSWORD;

        if (! $matches) {
            $this->addError('username', __('Invalid demo credentials. Use the demo login below.'));

            return;
        }

        session([
            'player.demo' => PlayerDemoData::profile(),
            'player.transactions' => PlayerDemoData::seedTransactions(),
        ]);

        $this->redirectRoute('player.lobby', navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    {{-- Brand --}}
    <div class="flex flex-col items-center gap-2 text-center">
        <span class="flex size-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-fuchsia-600 text-2xl font-black text-zinc-950">A</span>
        <h1 class="text-2xl font-black tracking-tight text-white">ARA<span class="text-amber-400">Play</span></h1>
        <p class="text-sm text-zinc-400">{{ __('Sign in to play and manage your wallet') }}</p>
    </div>

    <div class="rounded-2xl border border-white/10 bg-zinc-900/60 p-6 shadow-xl">
        <form wire:submit="login" class="flex flex-col gap-5">
            <flux:input
                wire:model="username"
                :label="__('Username')"
                autofocus
                autocomplete="username"
                :placeholder="__('Enter your username')"
            />

            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                viewable
                autocomplete="current-password"
                :placeholder="__('Enter your password')"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="player-login-button">
                {{ __('Log in') }}
            </flux:button>
        </form>

        {{-- Demo credentials hint --}}
        <div class="mt-5 rounded-xl border border-amber-400/20 bg-amber-400/5 p-3 text-center text-xs text-amber-200/90">
            <p class="font-semibold">{{ __('Demo login') }}</p>
            <p class="mt-1 tabular-nums">
                {{ __('Username') }}: <span class="font-mono text-amber-300">{{ \App\Support\PlayerDemoData::DEMO_USERNAME }}</span>
                &nbsp;·&nbsp;
                {{ __('Password') }}: <span class="font-mono text-amber-300">{{ \App\Support\PlayerDemoData::DEMO_PASSWORD }}</span>
            </p>
        </div>
    </div>

    <p class="text-center text-xs text-zinc-500">{{ __('Frontend demo only — no real money or accounts.') }}</p>
</div>
