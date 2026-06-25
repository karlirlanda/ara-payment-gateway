{{-- Dashboard widget card: a titled panel with a "More" link and a compact table in the slot. --}}
@props(['title' => '', 'href' => null])

<div class="flex min-w-0 flex-col border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
    <div class="flex items-center justify-between border-b border-zinc-200 px-3 py-2 dark:border-zinc-700">
        <flux:heading size="sm">{{ $title }}</flux:heading>
        @if ($href)
            <a href="{{ $href }}" wire:navigate class="text-xs text-accent hover:underline">{{ __('More') }}</a>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap text-[13px]">
            {{ $slot }}
        </table>
    </div>
</div>
