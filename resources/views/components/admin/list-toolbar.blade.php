{{-- Smart list toolbar, rendered in the page-header `toolbar` slot (right of the tabs row).
     Two modes:
       • idle (no selection)   → Refresh · Export all · Columns · Density
       • selected (count > 0)  → "N selected" · {{ $bulk }} page-specific actions · Clear
     The host page must wrap its root in `x-data="listTools('<key>')"` (drives Columns/Density),
     and use the HasListToolbar trait (or define refreshList/export/clearSelection) for the
     idle actions. The optional `bulk` slot carries the page's bulk-action buttons. --}}
@props([
    'selectedCount' => 0,
    'refreshable' => true,
    'exportable' => true,
    'columns' => true,
    'density' => true,
])

@if ($selectedCount > 0)
    <span class="text-xs font-medium text-zinc-500">{{ trans_choice(':count selected', $selectedCount, ['count' => $selectedCount]) }}</span>
    {{ $bulk ?? '' }}
    <flux:button wire:click="clearSelection" size="sm" variant="ghost">{{ __('Clear') }}</flux:button>
@else
    {{-- Desktop: utilities inline. --}}
    <div class="contents max-lg:hidden">
        @if ($refreshable)
            <flux:button wire:click="refreshList" icon="arrow-path" size="sm" variant="subtle" :tooltip="__('Refresh')" />
        @endif
        @if ($exportable)
            <flux:button wire:click="export(false)" icon="arrow-down-tray" size="sm" variant="subtle">{{ __('Export all') }}</flux:button>
        @endif
        @if ($columns)
            <flux:dropdown>
                <flux:button icon="view-columns" icon:trailing="chevron-down" size="sm" variant="subtle">{{ __('Columns') }}</flux:button>
                <flux:menu>
                    <div class="max-h-72 overflow-y-auto">
                        <template x-for="col in cols" :key="col.i">
                            <label class="flex cursor-pointer items-center gap-2 px-2 py-1 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/40">
                                <input type="checkbox" :checked="!isHidden(col.i)" x-on:change="toggle(col.i)" class="size-3.5 accent-[var(--color-accent)]" />
                                <span x-text="col.label"></span>
                            </label>
                        </template>
                    </div>
                </flux:menu>
            </flux:dropdown>
        @endif
        @if ($density)
            <flux:button x-on:click="toggleDensity()" icon="bars-3" size="sm" variant="subtle" :tooltip="__('Density')" />
        @endif
    </div>

    {{-- Mobile portrait: collapse the utilities into a single overflow menu. --}}
    <flux:dropdown class="lg:hidden" align="end">
        <flux:button icon="ellipsis-horizontal" size="sm" variant="subtle" :tooltip="__('Tools')" />
        <flux:menu>
            @if ($refreshable)
                <flux:menu.item wire:click="refreshList" icon="arrow-path">{{ __('Refresh') }}</flux:menu.item>
            @endif
            @if ($exportable)
                <flux:menu.item wire:click="export(false)" icon="arrow-down-tray">{{ __('Export all') }}</flux:menu.item>
            @endif
            @if ($density)
                <flux:menu.item x-on:click="toggleDensity()" icon="bars-3">{{ __('Density') }}</flux:menu.item>
            @endif
            @if ($columns)
                <flux:menu.separator />
                <div class="px-2 py-1 text-xs font-medium text-zinc-500">{{ __('Columns') }}</div>
                <div class="max-h-60 overflow-y-auto">
                    <template x-for="col in cols" :key="col.i">
                        <label class="flex cursor-pointer items-center gap-2 px-2 py-1 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/40">
                            <input type="checkbox" :checked="!isHidden(col.i)" x-on:change="toggle(col.i)" class="size-3.5 accent-[var(--color-accent)]" />
                            <span x-text="col.label"></span>
                        </label>
                    </template>
                </div>
            @endif
        </flux:menu>
    </flux:dropdown>
@endif
