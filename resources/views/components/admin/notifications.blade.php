@php
    $items = \App\Support\AdminDemoData::notifications();
    $count = count($items);
@endphp

{{-- Notification bell with demo sound. Browsers block autoplay until the first user
     gesture, so the first bell click "unlocks" audio; thereafter a visible-only timer
     simulates live events (new badge + chime), standing in for a Pusher broadcast. --}}
<div x-data="{
        count: {{ $count }},
        muted: false,
        play(ref) {
            if (this.muted) return;
            const el = this.$refs[ref];
            if (el) { el.currentTime = 0; el.play().catch(() => {}); }
        },
        tick() {
            // Only the on-screen bell (mobile vs desktop header) should fire.
            if (this.$el.offsetParent === null) return;
            this.count++;
            this.play('deposit');
        },
        init() { this.timer = setInterval(() => this.tick(), 12000); },
        destroy() { clearInterval(this.timer); },
    }">
    <audio x-ref="deposit" src="{{ asset('audio/deposit.wav') }}" preload="auto"></audio>
    <audio x-ref="notification" src="{{ asset('audio/notification.wav') }}" preload="auto"></audio>

    <flux:dropdown position="bottom" align="end">
        <flux:button icon="bell" variant="subtle" size="sm" class="relative" :tooltip="__('Notifications')" x-on:click="play('notification')">
            <span x-show="count > 0" x-cloak class="absolute -end-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white ring-2 ring-white dark:ring-zinc-900" x-text="count > 99 ? '99+' : count"></span>
        </flux:button>
        <flux:menu class="min-w-80">
            <div class="flex items-center justify-between px-2 py-1.5">
                <span class="text-sm font-semibold">{{ __('Notifications') }}</span>
                <div class="flex items-center gap-1.5">
                    <flux:badge x-show="count > 0" x-cloak size="sm" color="rose" inset="top bottom"><span x-text="count"></span></flux:badge>
                    <button type="button" x-on:click="muted = !muted" class="text-zinc-400 hover:text-accent" :title="muted ? '{{ __('Unmute') }}' : '{{ __('Mute') }}'">
                        <flux:icon x-show="!muted" icon="speaker-wave" class="size-4" />
                        <flux:icon x-show="muted" x-cloak icon="speaker-x-mark" class="size-4" />
                    </button>
                </div>
            </div>
            <flux:menu.separator />
            @forelse ($items as $item)
                <flux:menu.item>
                    <div class="flex w-full items-start gap-2">
                        <span class="mt-1 size-1.5 shrink-0 rounded-full bg-accent"></span>
                        <span class="flex-1 truncate">{{ $item['title'] }}</span>
                        <span class="shrink-0 text-xs text-zinc-400">{{ $item['time'] }}</span>
                    </div>
                </flux:menu.item>
            @empty
                <flux:menu.item disabled>{{ __('No notifications') }}</flux:menu.item>
            @endforelse
        </flux:menu>
    </flux:dropdown>
</div>
