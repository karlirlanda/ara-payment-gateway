@props([
    'title' => '',
    'route' => '',
    'breadcrumb' => [],   // array of strings, e.g. ['System', 'Users']
])

{{-- Hidden carrier read by the tags-view + breadcrumb Alpine store on each navigation. --}}
<div
    id="page-meta"
    data-title="{{ $title }}"
    data-route="{{ $route }}"
    data-path="{{ request()->path() }}"
    data-breadcrumb='@json($breadcrumb)'
    hidden
></div>
