{{-- Wide, horizontally-scrollable admin table. Pass <x-slot:head> (tr of <th>) and the rows as the default slot (<tr>…).
     When :selectable (default), pages prepend <x-admin.th-select> / <x-admin.td-select> as the first cell; the
     checkbox column + the row-identifier column (2 cols) then freeze on horizontal scroll, and the header
     checkbox toggles every row checkbox. Pass :selectable="false" for read-only/detail tables (e.g. modals). --}}
{{-- $stick = number of leading columns to freeze on horizontal scroll, including the
     checkbox column (default 2: checkbox + the row identifier). Raise to 3 for tables
     whose identifier is split across separate ID + username columns. --}}
{{-- $stickFirst: freeze just the first column on horizontal scroll on mobile (max-lg).
     For non-selectable wide tables (no checkbox/adminTable) whose row identifier should
     stay visible while scrolling sideways on a phone. --}}
@props(['selectable' => true, 'stick' => 2, 'stickFirst' => false])

<div {{ $attributes->merge(['class' => 'border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800']) }}
    @if ($selectable) x-data="adminTable({ stick: {{ (int) $stick }} })" @endif>
    <div class="overflow-x-auto">
        <table @class(['admin-table w-full whitespace-nowrap text-[13px]', 'admin-table--select' => $selectable, 'admin-table--stick-first' => $stickFirst])>
            <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                {{ $head }}
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @isset($footer)
        <div class="border-t border-zinc-200 p-2 dark:border-zinc-700">{{ $footer }}</div>
    @endisset
</div>
