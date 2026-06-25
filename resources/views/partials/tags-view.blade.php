{{--
    History-tabs ("tags-view") strip. Rendered at the top of <flux:main> (grid-area: main)
    so it never lands in Flux's reserved `aside` grid column. Negative margins cancel
    flux:main's padding so the bar is full-bleed under the header. State lives in the
    global Alpine `tabs` store (see resources/js/app.js); this markup is a pure view.
--}}
<div
    data-tags-view
    x-data="{ menu: { open: false, x: 0, y: 0, tab: null } }"
    class="max-lg:hidden -mx-6 -mt-6 mb-4 flex items-stretch gap-1 overflow-x-auto border-b border-zinc-200 bg-zinc-50 px-3 py-1.5 lg:-mx-8 lg:-mt-8 lg:mb-4 dark:border-zinc-700 dark:bg-zinc-900"
>
    <template x-for="tab in $store.tabs.items" :key="tab.path">
        {{--
            The close button is a SIBLING of the navigating anchor, never nested inside it.
            A <button> inside an <a wire:navigate> still triggers Livewire navigation on
            click (the document-level navigate listener ignores Alpine's .stop on a child),
            which made "close" navigate to the tab instead of removing it.
        --}}
        <div
            class="group flex shrink-0 items-stretch border text-xs transition-colors"
            :class="$store.tabs.isActive(tab)
                ? 'border-accent bg-accent text-[color:var(--color-accent-foreground)]'
                : 'border-zinc-200 bg-white text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300'"
        >
            <a
                :href="tab.path.startsWith('/') ? tab.path : '/' + tab.path"
                wire:navigate
                @contextmenu.prevent="menu = { open: true, x: $event.clientX, y: $event.clientY, tab }"
                class="flex items-center gap-1.5 py-1 ps-2.5 pe-1.5 transition-colors"
                :class="$store.tabs.isActive(tab) ? '' : 'hover:text-accent'"
            >
                <span class="size-1.5 rounded-full" :class="$store.tabs.isActive(tab) ? 'bg-[color:var(--color-accent-foreground)]' : 'bg-zinc-400'"></span>
                <span x-text="tab.title"></span>
            </a>
            <button
                x-show="!tab.affix"
                @click.prevent.stop="$store.tabs.close(tab)"
                type="button"
                class="flex items-center px-1.5 leading-none opacity-60 hover:bg-black/10 hover:opacity-100 dark:hover:bg-white/10"
                :aria-label="'Close ' + tab.title"
            >&times;</button>
        </div>
    </template>

    {{-- Right-click context menu --}}
    <div
        x-show="menu.open"
        x-cloak
        :style="`position:fixed; left:${menu.x}px; top:${menu.y}px; z-index:50`"
        class="min-w-36 border border-zinc-200 bg-white py-1 text-xs shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
        @click.outside="menu.open = false"
    >
        <button class="block w-full px-3 py-1.5 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700" @click="window.Livewire.navigate(window.location.pathname); menu.open = false">{{ __('Refresh') }}</button>
        <button class="block w-full px-3 py-1.5 text-left hover:bg-zinc-100 disabled:opacity-40 dark:hover:bg-zinc-700" :disabled="menu.tab?.affix" @click="$store.tabs.close(menu.tab); menu.open = false">{{ __('Close') }}</button>
        <button class="block w-full px-3 py-1.5 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700" @click="$store.tabs.closeOthers(menu.tab); menu.open = false">{{ __('Close Others') }}</button>
        <button class="block w-full px-3 py-1.5 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700" @click="$store.tabs.closeAll(); menu.open = false">{{ __('Close All') }}</button>
    </div>
</div>
