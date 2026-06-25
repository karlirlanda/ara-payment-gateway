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

        $series = \App\Support\AdminDemoData::dailySeries();
        $peak = collect($series)->flatMap(fn ($d) => [$d['deposit'], $d['withdraw'], $d['profit']])->max() ?: 1;
        $liveFeed = \App\Support\AdminDemoData::liveFeed();
        $topPlayers = \App\Support\AdminDemoData::topPlayers();
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

    {{-- Revenue chart + live transaction feed --}}
    <div class="mb-4 grid gap-4 xl:grid-cols-3">
        {{-- Revenue bar chart (CSS bars) --}}
        <div class="border border-zinc-200 bg-white p-4 xl:col-span-2 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-3 flex items-center justify-between">
                <flux:heading size="sm">{{ __('Revenue — last 7 days (₱)') }}</flux:heading>
                <a href="{{ route('admin.reports.daily') }}" wire:navigate class="text-xs text-accent hover:underline">{{ __('More') }}</a>
            </div>
            <div class="flex h-40 items-end gap-3">
                @foreach ($series as $d)
                    <div class="flex flex-1 flex-col items-center gap-1">
                        <div class="flex h-32 w-full items-end justify-center gap-1">
                            <div class="w-1/3 rounded-t bg-blue-500" style="height: {{ max(2, (int) round($d['deposit'] / $peak * 100)) }}%" title="₱{{ number_format($d['deposit']) }}"></div>
                            <div class="w-1/3 rounded-t bg-red-500" style="height: {{ max(2, (int) round($d['withdraw'] / $peak * 100)) }}%" title="₱{{ number_format($d['withdraw']) }}"></div>
                            <div class="w-1/3 rounded-t bg-emerald-500" style="height: {{ max(2, (int) round($d['profit'] / $peak * 100)) }}%" title="₱{{ number_format($d['profit']) }}"></div>
                        </div>
                        <div class="text-[10px] font-medium text-zinc-400">{{ $d['day'] }}</div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 flex gap-4 text-[11px] text-zinc-500">
                <span class="flex items-center gap-1.5"><span class="size-2 rounded-sm bg-blue-500"></span>{{ __('Deposit') }}</span>
                <span class="flex items-center gap-1.5"><span class="size-2 rounded-sm bg-red-500"></span>{{ __('Withdrawal') }}</span>
                <span class="flex items-center gap-1.5"><span class="size-2 rounded-sm bg-emerald-500"></span>{{ __('Net Profit') }}</span>
            </div>
        </div>

        {{-- Live transaction feed (Alpine rotates the window so it feels real-time without a broadcast backend) --}}
        <div class="flex flex-col border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800"
            x-data="{
                feed: @js($liveFeed),
                tick() { this.feed.push(this.feed.shift()); },
                init() { this.timer = setInterval(() => this.tick(), 3500); },
                destroy() { clearInterval(this.timer); },
            }">
            <div class="flex items-center justify-between border-b border-zinc-200 px-3 py-2 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Live transactions') }}</flux:heading>
                <span class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-400">
                    <span class="size-1.5 animate-pulse rounded-full bg-emerald-500"></span>{{ __('Live') }}
                </span>
            </div>
            <div class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                <template x-for="(row, i) in feed.slice(0, 6)" :key="row.username + i">
                    <div class="flex items-center justify-between px-3 py-2 text-[13px]">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex size-5 items-center justify-center rounded-full text-[10px] font-bold"
                                :class="row.direction === 'deposit' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'"
                                x-text="row.direction === 'deposit' ? '↓' : '↑'"></span>
                            <div>
                                <div class="font-medium text-zinc-800 dark:text-zinc-100" x-text="row.username"></div>
                                <div class="text-[10px] text-zinc-400" x-text="row.gateway + ' · ' + row.time"></div>
                            </div>
                        </div>
                        <div class="tabular-nums font-semibold"
                            :class="row.direction === 'deposit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'"
                            x-text="'₱' + row.amount.toLocaleString()"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Top players today leaderboard --}}
    <div class="mb-4">
        <x-admin.widget :title="__('Top players today')" :href="route('admin.members')">
            <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-900/50">
                <tr>
                    @foreach (['Rank', 'Level / ID', 'Highlight', 'Amount'] as $h)
                        <th class="px-3 py-1.5 text-start font-semibold">{{ __($h) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                @foreach ($topPlayers as $i => $p)
                    <tr>
                        <td class="px-3 py-1.5 font-bold text-zinc-400">#{{ $i + 1 }}</td>
                        <td class="px-3 py-1.5"><x-admin.level-badge :level="$p['level']" :id="$p['username']" /></td>
                        <td class="px-3 py-1.5 text-zinc-500">{{ $p['metric'] }}</td>
                        <td class="px-3 py-1.5 text-end tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">₱{{ number_format($p['amount']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.widget>
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
