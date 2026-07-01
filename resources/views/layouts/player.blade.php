@php
    $player = session('player.demo', \App\Support\PlayerDemoData::profile());
    $nav = [
        ['route' => 'player.lobby', 'label' => __('Home'), 'icon' => 'home'],
        ['route' => 'player.cashier', 'label' => __('Cashier'), 'icon' => 'banknotes'],
        ['route' => 'player.history', 'label' => __('History'), 'icon' => 'clock'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <style>
            /* Player portal — neon gaming accent (distinct from the Flux admin theme). */
            :root {
                --color-accent: #f59e0b;
                --color-accent-content: #f59e0b;
                --color-accent-foreground: #1c1917;
            }
            .player-bg {
                background:
                    radial-gradient(60rem 40rem at 80% -10%, rgba(245, 158, 11, 0.12), transparent 60%),
                    radial-gradient(50rem 40rem at -10% 10%, rgba(168, 85, 247, 0.14), transparent 55%),
                    #0a0a0f;
            }
        </style>
    </head>
    <body class="player-bg min-h-screen text-zinc-100 antialiased">
        {{-- Top bar --}}
        <header class="sticky top-0 z-30 border-b border-white/10 bg-zinc-950/70 backdrop-blur">
            <div class="mx-auto flex w-full max-w-5xl items-center justify-between gap-3 px-4 py-3">
                <a href="{{ route('player.lobby') }}" wire:navigate class="flex items-center gap-2">
                    <span class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-amber-400 to-fuchsia-600 font-black text-zinc-950">A</span>
                    <span class="text-lg font-black tracking-tight">ARA<span class="text-amber-400">Play</span></span>
                </a>

                <div class="flex items-center gap-2">
                    {{-- Wallet pill --}}
                    <div class="flex items-center gap-2 rounded-full border border-amber-400/30 bg-amber-400/10 px-3 py-1.5">
                        <flux:icon icon="wallet" class="size-4 text-amber-400" />
                        <span class="text-sm font-bold tabular-nums text-amber-300">₱{{ number_format((int) $player['balance']) }}</span>
                    </div>

                    <flux:button as="a" href="{{ route('player.cashier') }}" wire:navigate variant="primary" size="sm" icon="plus">
                        {{ __('Deposit') }}
                    </flux:button>

                    <flux:dropdown position="bottom" align="end">
                        <flux:button variant="subtle" size="sm" icon="user-circle" icon:variant="solid" class="text-zinc-300" />
                        <flux:menu>
                            <flux:menu.item icon="user" disabled>{{ $player['username'] }}</flux:menu.item>
                            <flux:menu.separator />
                            <form method="POST" action="{{ route('player.logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" variant="danger" class="w-full cursor-pointer">
                                    {{ __('Log out') }}
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        </header>

        {{-- Main --}}
        <main class="mx-auto w-full max-w-5xl px-4 pt-5 pb-28 sm:pb-10">
            {{ $slot }}
        </main>

        {{-- Mobile bottom nav --}}
        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-white/10 bg-zinc-950/90 backdrop-blur sm:hidden">
            <div class="mx-auto flex max-w-5xl items-stretch justify-around">
                @foreach ($nav as $item)
                    @php($active = request()->routeIs($item['route']))
                    <a href="{{ route($item['route']) }}" wire:navigate
                        @class([
                            'flex flex-1 flex-col items-center gap-1 py-2.5 text-xs',
                            'text-amber-400' => $active,
                            'text-zinc-400' => ! $active,
                        ])>
                        <flux:icon :icon="$item['icon']" class="size-5" />
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </nav>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
