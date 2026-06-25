@php
    $items = \App\Support\AdminDemoData::notifications();
    $count = count($items);
@endphp

<flux:dropdown position="bottom" align="end">
    <flux:button icon="bell" variant="subtle" size="sm" class="relative" :tooltip="__('Notifications')">
        @if ($count > 0)
            <span class="absolute -end-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white ring-2 ring-white dark:ring-zinc-900">{{ $count > 99 ? '99+' : $count }}</span>
        @endif
    </flux:button>
    <flux:menu class="min-w-80">
        <div class="flex items-center justify-between px-2 py-1.5">
            <span class="text-sm font-semibold">{{ __('Notifications') }}</span>
            @if ($count > 0)
                <flux:badge size="sm" color="rose" inset="top bottom">{{ $count }}</flux:badge>
            @endif
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
