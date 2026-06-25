@props(['title' => ''])

<div class="mb-2 flex flex-col gap-2">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <flux:heading size="lg">{{ $title }}</flux:heading>
        @isset($actions)
            <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
        @endisset
    </div>
    @if (isset($tabs) || isset($toolbar))
        <div class="flex items-center justify-between gap-2">
            @isset($tabs)
                <div class="flex min-w-0 flex-1 gap-0.5 overflow-x-auto">{{ $tabs }}</div>
            @endisset
            @isset($toolbar)
                <div class="ms-auto flex shrink-0 items-center gap-1.5">{{ $toolbar }}</div>
            @endisset
        </div>
    @endif
</div>
