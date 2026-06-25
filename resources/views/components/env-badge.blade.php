{{-- Environment badge — a loud reminder of which environment you're looking at, so a
     non-production app is never mistaken for prod. Renders ONLY when the app is not in
     production (local / staging / testing / …); in production nothing is output. Staging
     (the dangerous prod look-alike) is rose; everything else is amber. Pass classes for
     spacing at the call site, e.g. <x-env-badge class="ms-2" />. --}}
@unless (app()->isProduction())
    @php($staging = app()->environment('staging'))
    <span
        data-test="env-badge"
        {{ $attributes->class([
            'inline-flex shrink-0 items-center rounded px-1.5 py-0.5 text-[10px] font-bold uppercase leading-none tracking-wide',
            'bg-rose-500 text-white' => $staging,
            'bg-amber-400 text-amber-950' => ! $staging,
        ]) }}
        title="{{ __('Environment') }}: {{ app()->environment() }}"
    >{{ app()->environment() }}</span>
@endunless
