@php
    $s = \App\Support\AdminDemoData::summary();
@endphp

{{-- Hidden when the sidebar is collapsed to the icon rail on desktop (the label/value
     rows don't fit a 56px rail). Shows normally when expanded or on mobile. --}}
<div class="border-b border-zinc-200 px-4 py-3 text-xs in-[[data-flux-sidebar-collapsed-desktop]]:hidden dark:border-zinc-700">
    @php
        // Labels are translated here (English base keys → lang/ko.json), so the panel
        // follows the chosen language instead of being fixed to one locale.
        $rows = [
            [__('Total deposits'), '₱'.number_format($s['totalDeposits'])],
            [__('Total withdrawals'), '₱'.number_format($s['totalWithdrawals'])],
            [__('Net (in − out)'), '₱'.number_format($s['todayNet'])],
        ];
        $rows2 = [
            [__('Pending withdrawals'), (string) $s['pendingWithdrawals']],
            [__('Members'), (string) $s['members']],
            [__('New members'), (string) $s['newMembers']],
            [__('Live users'), (string) $s['liveUsers']],
        ];
    @endphp
    @foreach ($rows as [$label, $value])
        <div class="flex justify-between py-0.5">
            <span class="text-zinc-500">{{ $label }}</span>
            <span class="font-bold tabular-nums text-zinc-800 dark:text-zinc-100">{{ $value }}</span>
        </div>
    @endforeach
    <flux:separator class="my-1.5" />
    @foreach ($rows2 as [$label, $value])
        <div class="flex justify-between py-0.5">
            <span class="text-zinc-500">{{ $label }}</span>
            <span class="font-bold tabular-nums text-zinc-800 dark:text-zinc-100">{{ $value }}</span>
        </div>
    @endforeach
</div>
