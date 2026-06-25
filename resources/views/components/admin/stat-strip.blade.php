{{-- Desktop: single flex row (unchanged). Mobile portrait: 2-up grid so stat cells don't squish. --}}
<div {{ $attributes->merge(['class' => 'flex max-lg:grid max-lg:grid-cols-2 border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800']) }}>
    {{ $slot }}
</div>
