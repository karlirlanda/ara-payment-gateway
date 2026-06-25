<x-layouts::app :title="__('Dashboard')">
    <x-page-meta :title="__('Dashboard')" route="dashboard" :breadcrumb="[]" />

    @php
        $summary = \App\Support\AdminDemoData::summary();
        $gateways = \App\Support\AdminDemoData::gateways();

        $statusColor = ['pending' => 'amber', 'completed' => 'green', 'cancelled' => 'red'];
        $statusLabel = ['pending' => __('Pending'), 'completed' => __('Completed'), 'cancelled' => __('Cancelled')];

        $txnWidgets = [
            ['title' => __('Deposits'), 'href' => route('admin.transactions', 'deposit'), 'rows' => array_slice(\App\Support\AdminDemoData::transactions('deposit'), 0, 5)],
            ['title' => __('Withdrawals'), 'href' => route('admin.transactions', 'withdraw'), 'rows' => array_slice(\App\Support\AdminDemoData::transactions('withdraw'), 0, 5)],
        ];
        $signups = array_slice(\App\Support\AdminDemoData::members(), 0, 5);
    @endphp

    {{-- KPI strip --}}
    <div class="mb-4 grid auto-rows-min grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat-card :label="__('Total deposits')" :value="'₱'.number_format($summary['totalDeposits'])" :sublabel="__('Today, all gateways')" icon="arrow-down-circle" color="emerald" />
        <x-stat-card :label="__('Total withdrawals')" :value="'₱'.number_format($summary['totalWithdrawals'])" :sublabel="__('Today, all gateways')" icon="arrow-up-circle" color="indigo" />
        <x-stat-card :label="__('Pending withdrawals')" :value="number_format($summary['pendingWithdrawals'])" :sublabel="__('Awaiting approval')" icon="check-badge" color="violet" />
        <x-stat-card :label="__('Reconciliation variance')" :value="'₱'.number_format($summary['reconVariance'])" :sublabel="__('Last daily run')" icon="scale" color="indigo" />
    </div>

    {{-- Per-gateway volume --}}
    <div class="mb-4 grid auto-rows-min grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach ($gateways as $g)
            <a href="{{ route('admin.gateways') }}" wire:navigate
                class="flex items-center justify-between rounded-lg border border-zinc-200 bg-white px-4 py-3 hover:border-accent dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center gap-2.5">
                    <flux:icon :icon="$g['icon']" class="size-5 text-zinc-400" />
                    <div>
                        <div class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $g['name'] }}</div>
                        <div class="text-xs text-zinc-500">{{ $g['todayCount'] }} {{ __('transactions') }}</div>
                    </div>
                </div>
                <div class="text-end tabular-nums text-sm font-bold text-zinc-800 dark:text-zinc-100">₱{{ number_format($g['todayVolume']) }}</div>
            </a>
        @endforeach
    </div>

    {{-- Recent-activity widgets --}}
    <div class="grid gap-4 xl:grid-cols-2">
        @foreach ($txnWidgets as $w)
            <x-admin.widget :title="$w['title']" :href="$w['href']">
                <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-900/50">
                    <tr>
                        @foreach (['Status', 'Level / ID', 'Gateway', 'Amount', 'Applied'] as $h)
                            <th class="px-3 py-1.5 text-start font-semibold">{{ __($h) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                    @forelse ($w['rows'] as $r)
                        <tr>
                            <td class="px-3 py-1.5"><flux:badge :color="$statusColor[$r['status']]" size="sm" inset="top bottom">{{ $statusLabel[$r['status']] }}</flux:badge></td>
                            <td class="px-3 py-1.5"><x-admin.level-badge :level="$r['level']" :id="$r['username']" /></td>
                            <td class="px-3 py-1.5">{{ $r['gateway'] }}</td>
                            <td class="px-3 py-1.5 text-end tabular-nums">₱{{ number_format($r['amount']) }}</td>
                            <td class="px-3 py-1.5 tabular-nums text-zinc-500">{{ $r['appliedAt'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-center text-zinc-400">{{ __('No data found') }}</td></tr>
                    @endforelse
                </tbody>
            </x-admin.widget>
        @endforeach

        {{-- Member signups --}}
        <x-admin.widget :title="__('Member signups')" :href="route('admin.members')">
            <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-900/50">
                <tr>
                    @foreach (['Level / ID', 'Joined', 'Gateway', 'Domain'] as $h)
                        <th class="px-3 py-1.5 text-start font-semibold">{{ __($h) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                @foreach ($signups as $m)
                    <tr>
                        <td class="px-3 py-1.5"><x-admin.level-badge :level="$m['level']" :id="$m['username']" /></td>
                        <td class="px-3 py-1.5 tabular-nums">{{ $m['joinedAt'] }}</td>
                        <td class="px-3 py-1.5">{{ $m['gateway'] }}</td>
                        <td class="px-3 py-1.5 text-zinc-500">{{ $m['domain'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.widget>
    </div>
</x-layouts::app>
