{{-- Per-row action button group. Just a flex wrapper; host supplies the buttons. --}}
<div {{ $attributes->merge(['class' => 'flex items-center gap-1']) }}>
    {{ $slot }}
</div>
