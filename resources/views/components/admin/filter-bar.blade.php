@props(['statuses' => []])

{{-- Desktop: a single compact labelled row (the table sits high). Mobile portrait:
     the card collapses behind a "Filters" toggle and, when open, stacks into a clean
     full-width form with the per-field labels hidden (placeholder-driven) to save space.
     Both the toggle and the card live under the page's `listTools` Alpine scope, which
     owns `filtersOpen` + `filterCount`. Desktop layout/classes are unchanged. --}}

{{-- Mobile-only toggle (collapsed by default). --}}
<div class="mb-2 lg:hidden">
    <flux:button type="button" x-on:click="filtersOpen = ! filtersOpen" icon="funnel" size="sm" variant="subtle" class="w-full justify-start" data-test="filters-toggle">
        {{ __('Filters') }}
        <span x-show="filterCount > 0" x-cloak class="ms-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-accent px-1 text-[10px] font-semibold text-[color:var(--color-accent-foreground)]" x-text="filterCount"></span>
    </flux:button>
</div>

<div data-filter-bar
    class="mb-2 flex-wrap items-center gap-x-2 gap-y-2 border border-zinc-200 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 max-lg:flex-col max-lg:items-stretch max-lg:hidden lg:flex"
    :class="{ 'max-lg:!flex': filtersOpen }">
    <span class="text-xs font-medium text-zinc-500 max-lg:hidden">{{ __('Date') }}</span>
    <div class="flex items-center gap-2 max-lg:w-full">
        <div class="w-36 max-lg:w-auto max-lg:flex-1"><flux:input type="date" wire:model.live="dateFrom" :aria-label="__('From')" size="sm" class="w-full" /></div>
        <span class="text-zinc-400">~</span>
        <div class="w-36 max-lg:w-auto max-lg:flex-1"><flux:input type="date" wire:model.live="dateTo" :aria-label="__('To')" size="sm" class="w-full" /></div>
    </div>
    <div class="flex items-center gap-2 max-lg:w-full">
        <flux:button wire:click="$set('dateFrom', '{{ now()->toDateString() }}')" size="sm" variant="subtle" class="max-lg:flex-1">{{ __('Today') }}</flux:button>
        <flux:button wire:click="$set('dateFrom', '{{ now()->subDay()->toDateString() }}')" size="sm" variant="subtle" class="max-lg:flex-1">{{ __('Yesterday') }}</flux:button>
    </div>

    @if (! empty($statuses))
        <span class="ms-1 text-xs font-medium text-zinc-500 max-lg:hidden">{{ __('Status') }}</span>
        <div class="w-32 max-lg:w-full">
            <flux:select wire:model.live="status" :aria-label="__('Status')" size="sm" class="w-full">
                <flux:select.option value="">{{ __('All') }}</flux:select.option>
                @foreach ($statuses as $value => $label)
                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    @endif

    <span class="ms-1 text-xs font-medium text-zinc-500 max-lg:hidden">{{ __('Search') }}</span>
    <div class="w-52 max-lg:w-full"><flux:input wire:model.live.debounce.300ms="keyword" :aria-label="__('Search')" :placeholder="__('Keyword')" icon="magnifying-glass" size="sm" class="w-full" /></div>

    <flux:button wire:click="resetFilters" variant="danger" size="sm" class="max-lg:w-full">{{ __('Reset') }}</flux:button>

    @isset($actions)
        <div class="ms-auto flex items-center gap-2 max-lg:ms-0 max-lg:w-full">{{ $actions }}</div>
    @endisset
</div>
