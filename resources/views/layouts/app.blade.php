<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        @include('partials.tags-view')

        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
