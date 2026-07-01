<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <style>
            :root {
                --color-accent: #f59e0b;
                --color-accent-content: #f59e0b;
                --color-accent-foreground: #1c1917;
            }
            .player-bg {
                background:
                    radial-gradient(60rem 40rem at 80% -10%, rgba(245, 158, 11, 0.14), transparent 60%),
                    radial-gradient(50rem 40rem at -10% 10%, rgba(168, 85, 247, 0.16), transparent 55%),
                    #0a0a0f;
            }
        </style>
    </head>
    <body class="player-bg flex min-h-screen flex-col items-center justify-center px-4 text-zinc-100 antialiased">
        <div class="w-full max-w-sm">
            {{ $slot }}
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
