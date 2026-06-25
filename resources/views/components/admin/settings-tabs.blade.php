{{-- The Settings sub-tabs, shared by every Settings page. --}}
@php
    $settingsTabs = [
        ['label' => __('Gateway settings'), 'href' => route('admin.settings.provider'), 'active' => request()->routeIs('admin.settings.provider')],
        ['label' => __('Advanced settings'), 'href' => route('admin.settings.advanced'), 'active' => request()->routeIs('admin.settings.advanced')],
    ];
@endphp

@foreach ($settingsTabs as $tab)
    <a href="{{ $tab['href'] }}" wire:navigate
        @class([
            'border border-b-0 px-4 py-1.5 text-xs whitespace-nowrap',
            'border-accent bg-accent text-[color:var(--color-accent-foreground)]' => $tab['active'],
            'border-zinc-200 bg-white text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' => ! $tab['active'],
        ])>{{ $tab['label'] }}</a>
@endforeach
