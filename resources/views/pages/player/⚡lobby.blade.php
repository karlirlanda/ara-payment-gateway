<?php

use App\Support\PlayerDemoData;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Lobby')] #[Layout('layouts.player')] class extends Component {
    /** @return array{name:string, username:string, level:int, balance:int} */
    public function player(): array
    {
        return session('player.demo', PlayerDemoData::profile());
    }

    /**
     * Game launch is disabled in this frontend demo — surface a toast instead.
     */
    public function play(string $title): void
    {
        Flux::toast(
            heading: $title,
            text: __('Game launch is disabled in this frontend demo.'),
            variant: 'warning',
        );
    }
}; ?>

@php
    $player = $this->player();
    $banners = \App\Support\PlayerDemoData::banners();
    $categories = \App\Support\PlayerDemoData::gameCategories();
    $games = \App\Support\PlayerDemoData::games();
@endphp

<div class="flex flex-col gap-6">
    {{-- Hero banner carousel --}}
    <div
        x-data="{ active: 0, count: {{ count($banners) }}, init() { setInterval(() => this.active = (this.active + 1) % this.count, 5000) } }"
        class="relative"
    >
        @foreach ($banners as $i => $banner)
            <div
                x-show="active === {{ $i }}"
                x-transition.opacity.duration.500ms
                @class(['relative overflow-hidden rounded-2xl bg-gradient-to-r p-6 sm:p-8', $banner['art'], 'hidden' => $i !== 0])
            >
                <div class="relative z-10 max-w-md">
                    <h2 class="text-2xl font-black text-white drop-shadow sm:text-3xl">{{ $banner['title'] }}</h2>
                    <p class="mt-1 text-sm font-medium text-white/90">{{ $banner['subtitle'] }}</p>
                    <flux:button as="a" href="{{ route('player.cashier') }}" wire:navigate variant="primary" size="sm" class="mt-4">
                        {{ __('Deposit now') }}
                    </flux:button>
                </div>
                <div class="absolute -right-8 -bottom-8 size-40 rounded-full bg-white/10"></div>
            </div>
        @endforeach
        <div class="mt-3 flex justify-center gap-1.5">
            @foreach ($banners as $i => $banner)
                <button type="button" @click="active = {{ $i }}"
                    class="h-1.5 rounded-full transition-all"
                    :class="active === {{ $i }} ? 'w-5 bg-amber-400' : 'w-1.5 bg-white/30'"></button>
            @endforeach
        </div>
    </div>

    {{-- Wallet summary --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-white/10 bg-zinc-900/60 p-4 sm:col-span-2">
            <p class="text-xs font-medium text-zinc-400">{{ __('Wallet balance') }}</p>
            <p class="mt-1 text-3xl font-black tabular-nums text-amber-400">₱{{ number_format((int) $player['balance']) }}</p>
            <p class="mt-1 text-xs text-zinc-500">{{ __('Welcome back, :name', ['name' => $player['name']]) }}</p>
        </div>
        <div class="flex gap-3">
            <flux:button as="a" href="{{ route('player.cashier') }}" wire:navigate variant="primary" icon="plus" class="h-full flex-1">
                {{ __('Deposit') }}
            </flux:button>
            <flux:button as="a" href="{{ route('player.cashier', ['tab' => 'withdraw']) }}" wire:navigate variant="filled" icon="arrow-up-tray" class="h-full flex-1">
                {{ __('Withdraw') }}
            </flux:button>
        </div>
    </div>

    {{-- Games --}}
    <div x-data="{ cat: 'popular' }">
        {{-- Category tabs --}}
        <div class="mb-4 flex gap-2 overflow-x-auto pb-1">
            @foreach ($categories as $category)
                <button type="button" @click="cat = '{{ $category['key'] }}'"
                    class="flex shrink-0 items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-sm font-medium transition-colors"
                    :class="cat === '{{ $category['key'] }}' ? 'border-amber-400 bg-amber-400/15 text-amber-300' : 'border-white/10 bg-zinc-900/60 text-zinc-400'">
                    <flux:icon :icon="$category['icon']" class="size-4" />
                    {{ $category['label'] }}
                </button>
            @endforeach
        </div>

        {{-- Game grid --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($games as $game)
                <div
                    x-show="cat === 'popular' ? {{ $game['hot'] ? 'true' : 'false' }} : cat === '{{ $game['category'] }}'"
                    x-transition.opacity
                    wire:click="play(@js($game['title']))"
                    class="group relative cursor-pointer overflow-hidden rounded-xl border border-white/10"
                >
                    <div class="aspect-[4/5] bg-gradient-to-br {{ $game['art'] }}"></div>
                    @if ($game['hot'])
                        <span class="absolute left-2 top-2 rounded-md bg-red-600 px-1.5 py-0.5 text-[10px] font-bold uppercase text-white">{{ __('Hot') }}</span>
                    @endif
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-2.5">
                        <p class="truncate text-sm font-bold text-white">{{ $game['title'] }}</p>
                        <p class="text-[11px] text-zinc-300">{{ $game['provider'] }}</p>
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                        <flux:icon icon="play" variant="solid" class="size-10 text-white" />
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
