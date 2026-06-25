<?php

namespace App\Support;

/**
 * Static demo data for the ARA Inc Payment Gateway proposal build.
 *
 * Everything here is in-memory mock data (no database, no real provider APIs) so
 * the admin can be clicked through for the proposal. Provider details mirror the
 * "Payment Gateway Integration" proposal: GCash, Maya and GoTyme Bank.
 */
class AdminDemoData
{
    /** Provider catalog keys, in proposal order. */
    public const GATEWAYS = ['GCash', 'Maya', 'GoTyme'];

    /**
     * Top-line KPIs surfaced on the dashboard and the sidebar summary panel.
     * Amounts are in PHP (₱).
     *
     * @return array<string, int>
     */
    public static function summary(): array
    {
        return [
            'totalDeposits' => 4820500,
            'totalWithdrawals' => 2613000,
            'todayNet' => 2207500,
            'pendingWithdrawals' => 7,
            'members' => 65,
            'newMembers' => 12,
            'liveUsers' => 8,
            'reconVariance' => 500,
        ];
    }

    /**
     * @return array<string, int>
     */
    public static function actionBadges(): array
    {
        return ['deposit' => 3, 'withdraw' => 2, 'approval' => 7];
    }

    /**
     * @return array<int, array{title:string,time:string}>
     */
    public static function notifications(): array
    {
        return [
            ['title' => __('New GCash deposit pending'), 'time' => __(':n min ago', ['n' => 2])],
            ['title' => __('Maya withdrawal awaiting approval'), 'time' => __(':n min ago', ['n' => 15])],
            ['title' => __('GoTyme reconciliation completed'), 'time' => __(':n hour ago', ['n' => 1])],
        ];
    }

    /**
     * Provider overview cards — sourced directly from the proposal.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function gateways(): array
    {
        return [
            [
                'name' => 'GCash',
                'operator' => 'G-Xchange Inc. (Globe Telecom)',
                'license' => 'E-Money Issuer',
                'users' => '94M+',
                'deposit' => 'GCash QR, GCash Express, Direct API',
                'withdraw' => 'Instant transfer to GCash wallet',
                'fee' => 'Standard',
                'feeColor' => 'zinc',
                'segment' => 'Mass market — primary channel',
                'color' => 'sky',
                'icon' => 'device-phone-mobile',
                'status' => 'active',
                'todayVolume' => 2140000,
                'todayCount' => 318,
            ],
            [
                'name' => 'Maya',
                'operator' => 'Maya Philippines Inc. (PLDT / Voyager)',
                'license' => 'BSP-licensed Digital Bank',
                'users' => '50M+',
                'deposit' => 'Maya QR, Maya Checkout, Direct API',
                'withdraw' => 'Instant transfer to Maya wallet / bank',
                'fee' => 'Competitive',
                'feeColor' => 'emerald',
                'segment' => 'Mid–high value, banked users',
                'color' => 'emerald',
                'icon' => 'building-library',
                'status' => 'active',
                'todayVolume' => 1680500,
                'todayCount' => 142,
            ],
            [
                'name' => 'GoTyme',
                'operator' => 'GoTyme Bank Corp. (Gokongwei + Tyme)',
                'license' => 'BSP-licensed Digital Bank',
                'users' => '5M+ (growing)',
                'deposit' => 'GoTyme QR, InstaPay, PESONet',
                'withdraw' => 'Instant transfer to GoTyme account',
                'fee' => 'Zero-fee',
                'feeColor' => 'amber',
                'segment' => 'Youth / rewards-driven',
                'color' => 'amber',
                'icon' => 'gift',
                'status' => 'active',
                'todayVolume' => 1000000,
                'todayCount' => 96,
            ],
        ];
    }

    /**
     * Daily reconciliation against provider transaction logs — per gateway.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function reconciliation(): array
    {
        return [
            ['gateway' => 'GCash', 'date' => '2026-06-24', 'platformCount' => 318, 'platformAmount' => 2140000, 'providerCount' => 318, 'providerAmount' => 2140000, 'variance' => 0, 'status' => 'matched'],
            ['gateway' => 'Maya', 'date' => '2026-06-24', 'platformCount' => 142, 'platformAmount' => 1680500, 'providerCount' => 142, 'providerAmount' => 1680000, 'variance' => 500, 'status' => 'variance'],
            ['gateway' => 'GoTyme', 'date' => '2026-06-24', 'platformCount' => 96, 'platformAmount' => 1000000, 'providerCount' => 95, 'providerAmount' => 1000000, 'variance' => 0, 'status' => 'pending'],
        ];
    }

    /**
     * Member roster (online players) — PH names, PHP balances and a preferred
     * gateway folded into the masked "account" field used by the member list.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function members(): array
    {
        $first = ['Juan', 'Maria', 'Jose', 'Ana', 'Pedro', 'Liza', 'Mark', 'Grace', 'Paolo', 'Trisha'];
        $last = ['Dela Cruz', 'Santos', 'Reyes', 'Bautista', 'Aquino', 'Ramos', 'Garcia', 'Mendoza', 'Cruz', 'Torres'];
        $gateways = self::GATEWAYS;
        $statuses = ['normal', 'normal', 'normal', 'suspended', 'withdrawn'];
        $members = [];

        for ($i = 1; $i <= 65; $i++) {
            $gateway = $gateways[$i % count($gateways)];
            $holder = $first[$i % count($first)].' '.$last[$i % count($last)];
            $mobile = '0917'.str_pad((string) (1000000 + $i * 1234 % 9000000), 7, '0', STR_PAD_LEFT);

            // Every 4th member is a net-negative (withdraw > deposit) so the red
            // "difference" styling is visible in the list; the rest stay positive.
            $deposit = 1000 * $i;
            $withdraw = $i % 4 === 0 ? 1100 * $i + 2500 : 800 * ($i - 1);

            $members[] = [
                'id' => $i,
                'level' => ($i % 5) + 1,
                'username' => 'player'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'nickname' => $first[$i % count($first)].$i,
                'store' => $gateway,
                'storeLevel' => 0,
                'gateway' => $gateway,
                // "name / account / holder" — the member list masks the middle segment.
                'bank' => $gateway.' / '.$mobile.' / '.$holder,
                'phone' => '+63 '.substr($mobile, 1, 3).' '.substr($mobile, 4, 3).' '.substr($mobile, 7),
                'balance' => 1200 * $i,
                'commissionType' => $i % 2 === 0 ? 'turnover' : 'loss',
                'points' => 50 * $i,
                'deposit' => $deposit,
                'withdraw' => $withdraw,
                'status' => $statuses[$i % count($statuses)],
                'lastLogin' => now()->subMinutes((int) ($i * $i * 33 + $i * 17))->format('Y-m-d H:i'),
                'ip' => '180.191.81.'.(200 + $i),
                'joinedAt' => '2026-05-'.str_pad((string) (($i % 28) + 1), 2, '0', STR_PAD_LEFT),
                'domain' => 'ara-pay.ph',
            ];
        }

        return $members;
    }

    /**
     * Currently-online members for the live-monitoring page. Activity reflects the
     * payment context (lobby / deposit / withdraw). Values are deterministic so the
     * demo is stable across reloads.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function liveUsers(): array
    {
        $activities = ['Dashboard', 'Deposit · GCash', 'Withdraw · Maya', 'Wallet', 'Deposit · GoTyme'];
        $devices = ['Chrome · Windows', 'Safari · iOS', 'Chrome · Android', 'Edge · Windows', 'Samsung · Android'];
        $lastSeen = [4, 2, 360, 0, 9, 120, 30, 600];      // seconds since last activity
        $session = [1391, 482, 8400, 72, 933, 5200, 1500, 88]; // session length in seconds
        // Some users deliberately share an IP so the "shared IP" collusion flag is visible.
        $ips = ['175.223.10.21', '175.223.10.21', '211.36.5.7', '121.165.9.55', '175.223.10.21', '211.36.5.7', '39.7.22.88', '218.50.3.90'];
        $loginAt = ['14:02:09', '15:31:44', '11:50:02', '15:38:18', '15:24:51', '15:35:33', '15:14:20', '15:39:55'];
        $stake = [0, 5000, 1000, 0, 0, 0, 2000, 2500]; // current transaction amount (₱)

        return collect(self::members())->take(8)->values()->map(function ($m, $i) use ($activities, $devices, $lastSeen, $session, $ips, $loginAt, $stake) {
            $seen = $lastSeen[$i] ?? $i * 11;
            $sess = $session[$i] ?? $i * 60 + 45;

            return [
                'level' => $m['level'],
                'username' => $m['username'],
                'store' => $m['store'],
                'storeLevel' => $m['storeLevel'],
                'balance' => $m['balance'],
                'activity' => $activities[$i % count($activities)],
                'stake' => $stake[$i] ?? 0,
                'device' => $devices[$i % count($devices)],
                'ip' => $ips[$i] ?? $m['ip'],
                'loginAt' => $loginAt[$i] ?? '—',
                'domain' => $m['domain'],
                'sessionSeconds' => $sess,
                'lastSeenSeconds' => $seen,
                'state' => $seen > 120 ? 'idle' : 'active',
                'newLogin' => $sess < 900,
                'joinedAt' => $m['joinedAt'],
                'status' => 'online',
            ];
        })->all();
    }

    /**
     * Gateway deposit / withdrawal rows. Carries the provider, masked PH account,
     * a reference number and the pending → completed / cancelled workflow status.
     *
     * @param  'deposit'|'withdraw'  $direction
     * @return array<int, array<string, mixed>>
     */
    public static function transactions(string $direction): array
    {
        $usernames = ['player001', 'player002', 'player003', 'player004', 'player005', 'player006', 'player007', 'player008'];
        $gateways = ['GCash', 'Maya', 'GoTyme', 'GCash', 'Maya', 'GCash', 'GoTyme', 'Maya'];
        $accounts = ['0917 ••• 1234', '0998 ••• 5521', '0945 ••• 7788', '0917 ••• 0090', '0966 ••• 2231', '0917 ••• 5567', '0905 ••• 8842', '0998 ••• 3390'];
        $amounts = [1000, 5000, 2500, 3000, 6000, 10000, 500, 2000];
        // First two pending, then a realistic completed/cancelled mix.
        $statuses = ['pending', 'pending', 'completed', 'completed', 'cancelled', 'completed', 'completed', 'cancelled'];
        $levelMap = [1, 1, 2, 1, 2, 3, 2, 4];

        $prefix = $direction === 'deposit' ? 'DEP' : 'WDL';
        $rows = [];

        for ($i = 1; $i <= 8; $i++) {
            $idx = $i - 1;
            $day = str_pad((string) ((($i * 3) % 27) + 1), 2, '0', STR_PAD_LEFT);
            $applied = '2026-06-'.$day.' '.str_pad((string) (10 + $i), 2, '0', STR_PAD_LEFT).':'.str_pad((string) ($i * 7 % 60), 2, '0', STR_PAD_LEFT).':00';
            $processed = $statuses[$idx] === 'pending'
                ? $applied
                : '2026-06-'.$day.' '.str_pad((string) (11 + $i), 2, '0', STR_PAD_LEFT).':'.str_pad((string) ($i * 11 % 60), 2, '0', STR_PAD_LEFT).':00';

            $rows[] = [
                'id' => $i,
                'level' => $levelMap[$idx],
                'username' => $usernames[$idx],
                'gateway' => $gateways[$idx],
                'amount' => $amounts[$idx],
                'account' => $accounts[$idx],
                'reference' => $prefix.'-2026'.str_pad((string) ($i * 137), 6, '0', STR_PAD_LEFT),
                'appliedAt' => $applied,
                'processedAt' => $processed,
                'status' => $statuses[$idx],
            ];
        }

        return $rows;
    }

    /**
     * Grouped Sales Report figures (proposal §6). Each group is a labelled block of
     * metric cells; `tone` drives the colour in the view (pos = green, warn = amber,
     * highlight = blue, plain = default). Values are pre-formatted PHP (₱) strings so
     * the demo reads exactly like the documentation.
     *
     * @return array<string, array{label:string, cells:array<int, array{label:string, value:string, tone:string}>}>
     */
    public static function salesReport(): array
    {
        return [
            'depositWithdrawal' => [
                'label' => __('Deposit & Withdrawal Summary'),
                'cells' => [
                    ['label' => __('Total Deposit Amount'), 'value' => '₱4,820,500.00', 'tone' => 'pos'],
                    ['label' => __('Total Deposit Count'), 'value' => '248', 'tone' => 'plain'],
                    ['label' => __('Total Withdrawal Amount'), 'value' => '₱1,230,000.00', 'tone' => 'warn'],
                    ['label' => __('Total Withdrawal Count'), 'value' => '134', 'tone' => 'warn'],
                    ['label' => __('Net Difference (Deposit − Withdrawal)'), 'value' => '₱3,590,500.00', 'tone' => 'highlight'],
                    ['label' => __('Total Agent Balance'), 'value' => '₱96,250,000.00', 'tone' => 'plain'],
                ],
            ],
            'playerTransactions' => [
                'label' => __('Player Transaction Summary'),
                'cells' => [
                    ['label' => __('Total Player Deposit Amount'), 'value' => '₱4,820,500.00', 'tone' => 'plain'],
                    ['label' => __('Total Player Withdrawal Amount'), 'value' => '₱1,230,000.00', 'tone' => 'warn'],
                    ['label' => __('Net Player Movement'), 'value' => '₱3,590,500.00', 'tone' => 'highlight'],
                ],
            ],
            'agentWorksheet' => [
                'label' => __('Agent Payment Worksheet'),
                'cells' => [
                    ['label' => __('Advance Deposit (from Agent)'), 'value' => '₱5,000,000.00', 'tone' => 'plain'],
                    ['label' => __('Total Store Deposit'), 'value' => '₱4,820,500.00', 'tone' => 'plain'],
                    ['label' => __('Total Store Withdrawal'), 'value' => '₱1,230,000.00', 'tone' => 'warn'],
                    ['label' => __('Agent Convert Commission'), 'value' => '₱375,600.00', 'tone' => 'warn'],
                    ['label' => __('Website Commission (1%)'), 'value' => '₱48,205.00', 'tone' => 'warn'],
                    ['label' => __('Bank Commission (0.5%)'), 'value' => '₱24,102.50', 'tone' => 'warn'],
                    ['label' => __('Provider Commission (0.75%)'), 'value' => '₱36,153.75', 'tone' => 'warn'],
                    ['label' => __('Remaining Deposit (Final Balance)'), 'value' => '₱485,538.75', 'tone' => 'highlight'],
                ],
            ],
            'commission' => [
                'label' => __('Agent Commission Summary'),
                'cells' => [
                    ['label' => __('Total Agent Commission Amount'), 'value' => '₱375,600.00', 'tone' => 'plain'],
                    ['label' => __('Total Commission Count'), 'value' => '1,482', 'tone' => 'plain'],
                ],
            ],
        ];
    }

    /**
     * Seven-day deposit / withdrawal / profit series for the daily sales bar chart
     * (proposal §6). Amounts are in PHP (₱).
     *
     * @return array<int, array{day:string, deposit:int, withdraw:int, profit:int}>
     */
    public static function dailySeries(): array
    {
        return [
            ['day' => __('Mon'), 'deposit' => 3_180_000, 'withdraw' => 1_520_000, 'profit' => 740_000],
            ['day' => __('Tue'), 'deposit' => 3_980_000, 'withdraw' => 2_310_000, 'profit' => 880_000],
            ['day' => __('Wed'), 'deposit' => 2_640_000, 'withdraw' => 1_280_000, 'profit' => 610_000],
            ['day' => __('Thu'), 'deposit' => 4_720_000, 'withdraw' => 2_580_000, 'profit' => 1_120_000],
            ['day' => __('Fri'), 'deposit' => 3_420_000, 'withdraw' => 1_810_000, 'profit' => 790_000],
            ['day' => __('Sat'), 'deposit' => 4_210_000, 'withdraw' => 2_870_000, 'profit' => 690_000],
            ['day' => __('Sun'), 'deposit' => 4_820_500, 'withdraw' => 2_280_000, 'profit' => 1_310_000],
        ];
    }

    /**
     * Catalog of every available report (proposal §6 "All Available Reports"). `route`
     * links the row to a live demo page where one exists; the rest are demo stubs.
     *
     * @return array<int, array{name:string, metrics:string, grouping:string, export:string, route:?string, param:?string}>
     */
    public static function reportCatalog(): array
    {
        return [
            ['name' => __('Daily Sales Report'), 'metrics' => __('Deposits, withdrawals, net revenue, tx count, new players'), 'grouping' => __('By hour, brand, agent'), 'export' => 'Excel · CSV', 'route' => 'admin.reports.daily', 'param' => null],
            ['name' => __('Sales Report (grouped)'), 'metrics' => __('Deposit/withdrawal summary, player transactions, agent worksheet, commission'), 'grouping' => __('By brand, agent'), 'export' => 'Excel · CSV', 'route' => 'admin.reports.sales', 'param' => null],
            ['name' => __('Period Sales Report'), 'metrics' => __('Weekly / monthly / yearly with trend comparison'), 'grouping' => __('By week, month, quarter, year'), 'export' => 'Excel · CSV', 'route' => 'admin.reports.period', 'param' => null],
            ['name' => __('Profit & Loss Report'), 'metrics' => __('Gross revenue, bonus costs, promo costs, operating profit'), 'grouping' => __('By brand, game type'), 'export' => 'Excel · PDF', 'route' => 'admin.reports.pl', 'param' => null],
            ['name' => __('Deposit Summary'), 'metrics' => __('Count, total ₱, avg., method breakdown, approval rate'), 'grouping' => __('By payment method, brand'), 'export' => 'Excel · CSV', 'route' => 'admin.transactions', 'param' => 'deposit'],
            ['name' => __('Withdrawal Summary'), 'metrics' => __('Count, total ₱, avg. processing time, rejection rate'), 'grouping' => __('By status, brand, agent'), 'export' => 'Excel · CSV', 'route' => 'admin.transactions', 'param' => 'withdraw'],
            ['name' => __('Player Activity Report'), 'metrics' => __('New signups, active, churned, retention, avg. session'), 'grouping' => __('By brand, agent, VIP tier'), 'export' => 'Excel · CSV', 'route' => 'admin.reports.player-activity', 'param' => null],
            ['name' => __('Agent Commission Report'), 'metrics' => __('Commission earned, player count, volume, rolling rate'), 'grouping' => __('By agent, period'), 'export' => 'Excel · CSV', 'route' => 'admin.reports.agent-commission', 'param' => null],
            ['name' => __('Brand Comparison Report'), 'metrics' => __('Revenue, player count, retention side-by-side'), 'grouping' => __('All / selected brands'), 'export' => 'Excel · PDF', 'route' => 'admin.reports.brand-comparison', 'param' => null],
            ['name' => __('Coupon Usage Report'), 'metrics' => __('Issued, redeemed, expired, redemption rate, cost'), 'grouping' => __('By coupon type, event'), 'export' => 'Excel · CSV', 'route' => 'admin.reports.coupon-usage', 'param' => null],
        ];
    }

    /**
     * Top players today leaderboard for the dashboard (proposal §3).
     *
     * @return array<int, array{username:string, level:int, metric:string, amount:int}>
     */
    public static function topPlayers(): array
    {
        return [
            ['username' => 'player012', 'level' => 5, 'metric' => __('Highest deposit'), 'amount' => 480_000],
            ['username' => 'player004', 'level' => 4, 'metric' => __('Highest withdrawal'), 'amount' => 415_000],
            ['username' => 'player027', 'level' => 3, 'metric' => __('Highest deposit'), 'amount' => 362_000],
            ['username' => 'player008', 'level' => 4, 'metric' => __('Highest deposit'), 'amount' => 305_000],
            ['username' => 'player015', 'level' => 2, 'metric' => __('Highest withdrawal'), 'amount' => 268_000],
        ];
    }

    /**
     * Live transaction ticker for the dashboard (proposal §3). The view rotates the
     * window on each poll so it feels real-time without a broadcast backend.
     *
     * @return array<int, array{username:string, gateway:string, direction:string, amount:int, time:string}>
     */
    public static function liveFeed(): array
    {
        $rows = [
            ['username' => 'player021', 'gateway' => 'GCash', 'direction' => 'deposit', 'amount' => 5_000],
            ['username' => 'player008', 'gateway' => 'Maya', 'direction' => 'withdraw', 'amount' => 12_000],
            ['username' => 'player044', 'gateway' => 'GoTyme', 'direction' => 'deposit', 'amount' => 2_500],
            ['username' => 'player017', 'gateway' => 'GCash', 'direction' => 'deposit', 'amount' => 8_000],
            ['username' => 'player033', 'gateway' => 'Maya', 'direction' => 'deposit', 'amount' => 1_500],
            ['username' => 'player002', 'gateway' => 'GoTyme', 'direction' => 'withdraw', 'amount' => 6_500],
            ['username' => 'player056', 'gateway' => 'GCash', 'direction' => 'deposit', 'amount' => 10_000],
            ['username' => 'player011', 'gateway' => 'Maya', 'direction' => 'deposit', 'amount' => 3_000],
        ];

        // Rotate by the current 10-second window so each poll surfaces a fresh order.
        $offset = (int) floor(now()->timestamp / 10) % count($rows);

        $mapped = collect($rows)
            ->map(fn ($r, $i) => $r + ['time' => now()->subSeconds(($i + 1) * 7)->format('H:i:s')])
            ->values();

        return $mapped->slice($offset)->concat($mapped->slice(0, $offset))->values()->all();
    }

    /**
     * Coupon catalog (proposal §9). Mix of fixed-₱ and percentage coupons across the
     * active / scheduled / expired / disabled lifecycle.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function coupons(): array
    {
        return [
            ['id' => 1, 'code' => 'WELCOME100', 'type' => 'fixed', 'value' => 100, 'minDeposit' => 500, 'rollover' => 10, 'expiry' => '2026-07-31', 'maxUses' => 1000, 'used' => 642, 'status' => 'active'],
            ['id' => 2, 'code' => 'RELOAD20', 'type' => 'percent', 'value' => 20, 'minDeposit' => 1000, 'rollover' => 12, 'expiry' => '2026-07-15', 'maxUses' => 500, 'used' => 318, 'status' => 'active'],
            ['id' => 3, 'code' => 'WEEKEND15', 'type' => 'percent', 'value' => 15, 'minDeposit' => 800, 'rollover' => 8, 'expiry' => '2026-06-30', 'maxUses' => 800, 'used' => 455, 'status' => 'active'],
            ['id' => 4, 'code' => 'VIPGOLD500', 'type' => 'fixed', 'value' => 500, 'minDeposit' => 5000, 'rollover' => 15, 'expiry' => '2026-08-31', 'maxUses' => 100, 'used' => 12, 'status' => 'scheduled'],
            ['id' => 5, 'code' => 'CASHBACK10', 'type' => 'percent', 'value' => 10, 'minDeposit' => 0, 'rollover' => 5, 'expiry' => '2026-06-20', 'maxUses' => 2000, 'used' => 2000, 'status' => 'expired'],
            ['id' => 6, 'code' => 'REFER250', 'type' => 'fixed', 'value' => 250, 'minDeposit' => 1000, 'rollover' => 10, 'expiry' => '2026-07-31', 'maxUses' => 600, 'used' => 187, 'status' => 'active'],
            ['id' => 7, 'code' => 'BIRTHDAY1K', 'type' => 'fixed', 'value' => 1000, 'minDeposit' => 2000, 'rollover' => 20, 'expiry' => '2026-12-31', 'maxUses' => 300, 'used' => 41, 'status' => 'active'],
            ['id' => 8, 'code' => 'FLASH30', 'type' => 'percent', 'value' => 30, 'minDeposit' => 1500, 'rollover' => 18, 'expiry' => '2026-06-10', 'maxUses' => 400, 'used' => 400, 'status' => 'disabled'],
        ];
    }

    /**
     * Time-limited promotional events (proposal §9).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function events(): array
    {
        return [
            ['id' => 1, 'name' => __('First Deposit Bonus'), 'type' => __('Deposit match'), 'reward' => __('100% up to ₱2,000'), 'period' => '2026-06-01 → 2026-07-31', 'status' => 'active'],
            ['id' => 2, 'name' => __('Weekend Cashback'), 'type' => __('Cashback'), 'reward' => __('10% of net loss'), 'period' => __('Every Sat–Sun'), 'status' => 'active'],
            ['id' => 3, 'name' => __('Referral Bonus'), 'type' => __('Referral'), 'reward' => __('₱250 per referral'), 'period' => __('Ongoing'), 'status' => 'active'],
            ['id' => 4, 'name' => __('Lunar Festival Raffle'), 'type' => __('Raffle'), 'reward' => __('₱500,000 prize pool'), 'period' => '2026-08-01 → 2026-08-15', 'status' => 'scheduled'],
        ];
    }

    /**
     * Support ticket queue with an inline conversation thread and account snapshot
     * (proposal §10).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function tickets(): array
    {
        return [
            [
                'id' => 4821, 'player' => 'player012', 'subject' => __('Deposit not credited'), 'brand' => 'Dolphin',
                'priority' => 'urgent', 'status' => 'open', 'updated' => '2026-06-25 14:32', 'balance' => 250_000, 'lastTx' => __('₱5,000 GCash deposit · pending'),
                'thread' => [
                    ['from' => 'player', 'body' => __('I deposited ₱5,000 via GCash 20 minutes ago but my balance has not updated.'), 'time' => '14:10'],
                    ['from' => 'agent', 'body' => __('Hi, thank you for reaching out. Let me check the transaction reference for you now.'), 'time' => '14:18'],
                ],
            ],
            [
                'id' => 4820, 'player' => 'player004', 'subject' => __('Withdrawal taking too long'), 'brand' => 'Champion',
                'priority' => 'high', 'status' => 'in_progress', 'updated' => '2026-06-25 13:05', 'balance' => 88_500, 'lastTx' => __('₱12,000 Maya withdrawal · processing'),
                'thread' => [
                    ['from' => 'player', 'body' => __('My withdrawal has been processing for 2 hours. Any update?'), 'time' => '11:50'],
                    ['from' => 'agent', 'body' => __('It is in the finance approval queue and will be released shortly.'), 'time' => '12:40'],
                ],
            ],
            [
                'id' => 4819, 'player' => 'player027', 'subject' => __('How do I claim the weekend cashback?'), 'brand' => 'Hera',
                'priority' => 'normal', 'status' => 'open', 'updated' => '2026-06-25 10:22', 'balance' => 41_200, 'lastTx' => __('₱2,500 GoTyme deposit · completed'),
                'thread' => [
                    ['from' => 'player', 'body' => __('Where can I find the weekend cashback coupon?'), 'time' => '10:22'],
                ],
            ],
            [
                'id' => 4818, 'player' => 'player008', 'subject' => __('Password reset request'), 'brand' => 'Dolphin',
                'priority' => 'low', 'status' => 'closed', 'updated' => '2026-06-24 19:48', 'balance' => 305_000, 'lastTx' => __('₱8,000 GCash deposit · completed'),
                'thread' => [
                    ['from' => 'player', 'body' => __('Please reset my password, I am locked out.'), 'time' => '19:10'],
                    ['from' => 'agent', 'body' => __('Done — a reset link has been sent to your email. Closing this ticket.'), 'time' => '19:48'],
                ],
            ],
        ];
    }

    /** Canned support replies (proposal §10). @return array<int, string> */
    public static function cannedResponses(): array
    {
        return [
            __('Thanks for your patience — your deposit is being verified and will reflect shortly.'),
            __('Your withdrawal is in the finance approval queue and will be released within the hour.'),
            __('A password reset link has been sent to your registered email address.'),
            __('The bonus has been credited to your account. Please refresh to see the updated balance.'),
        ];
    }

    /**
     * Platform / brand announcements (proposal §11).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function announcements(): array
    {
        return [
            ['id' => 1, 'title' => __('Scheduled maintenance — GCash gateway'), 'audience' => __('All brands'), 'scheduledAt' => '2026-06-26 02:00', 'pinned' => true, 'status' => 'scheduled', 'body' => __('GCash deposits and withdrawals will be unavailable for 30 minutes during provider maintenance.')],
            ['id' => 2, 'title' => __('GoTyme zero-fee deposits now live'), 'audience' => 'Dolphin, Champion', 'scheduledAt' => '2026-06-25 09:00', 'pinned' => true, 'status' => 'published', 'body' => __('Deposit via GoTyme with zero transaction fees, available now.')],
            ['id' => 3, 'title' => __('Weekend cashback returns'), 'audience' => __('All brands'), 'scheduledAt' => '2026-06-27 00:00', 'pinned' => false, 'status' => 'scheduled', 'body' => __('Earn 10% cashback on net losses every weekend.')],
            ['id' => 4, 'title' => __('Updated KYC verification policy'), 'audience' => __('All brands'), 'scheduledAt' => '2026-06-20 12:00', 'pinned' => false, 'status' => 'published', 'body' => __('New players must complete KYC before their first withdrawal.')],
        ];
    }

    /**
     * Login pop-ups shown to players (proposal §11).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function popups(): array
    {
        return [
            ['id' => 1, 'title' => __('Welcome Bonus'), 'audience' => __('New players'), 'frequency' => __('Once per player'), 'start' => '2026-06-01', 'end' => '2026-07-31', 'status' => 'active'],
            ['id' => 2, 'title' => __('Weekend Cashback'), 'audience' => __('All players'), 'frequency' => __('Once per day'), 'start' => '2026-06-01', 'end' => '2026-12-31', 'status' => 'active'],
            ['id' => 3, 'title' => __('VIP Upgrade'), 'audience' => __('Gold tier'), 'frequency' => __('Once per week'), 'start' => '2026-06-15', 'end' => '2026-09-15', 'status' => 'scheduled'],
        ];
    }

    /**
     * Immutable audit trail (proposal §12). Read-only — every admin action, login,
     * transaction and setting change is recorded with before → after context.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function activityLogs(): array
    {
        return [
            ['id' => 1, 'type' => 'admin_action', 'actor' => 'admin', 'action' => __('Approved withdrawal'), 'target' => 'WDL-2026000274 · player004', 'before' => 'pending', 'after' => 'completed', 'ip' => '180.191.81.10', 'at' => '2026-06-25 14:40:11'],
            ['id' => 2, 'type' => 'transaction', 'actor' => 'system', 'action' => __('Deposit credited'), 'target' => 'DEP-2026000137 · player001', 'before' => '₱245,000', 'after' => '₱250,000', 'ip' => '—', 'at' => '2026-06-25 14:32:50'],
            ['id' => 3, 'type' => 'login', 'actor' => 'test', 'action' => __('Admin login'), 'target' => 'test@example.com', 'before' => '—', 'after' => __('success'), 'ip' => '49.150.22.4', 'at' => '2026-06-25 14:20:03'],
            ['id' => 4, 'type' => 'setting', 'actor' => 'admin', 'action' => __('Changed auto-credit threshold'), 'target' => 'settings.provider', 'before' => '₱3,000', 'after' => '₱5,000', 'ip' => '180.191.81.10', 'at' => '2026-06-25 11:08:44'],
            ['id' => 5, 'type' => 'admin_action', 'actor' => 'admin', 'action' => __('Suspended member'), 'target' => 'player052', 'before' => 'normal', 'after' => 'suspended', 'ip' => '180.191.81.10', 'at' => '2026-06-25 10:55:19'],
            ['id' => 6, 'type' => 'login', 'actor' => 'unknown', 'action' => __('Failed login'), 'target' => 'admin', 'before' => '—', 'after' => __('failure'), 'ip' => '203.177.42.91', 'at' => '2026-06-25 09:31:02'],
            ['id' => 7, 'type' => 'transaction', 'actor' => 'admin', 'action' => __('Cancelled deposit'), 'target' => 'DEP-2026000548 · player005', 'before' => 'pending', 'after' => 'cancelled', 'ip' => '180.191.81.10', 'at' => '2026-06-24 22:14:37'],
            ['id' => 8, 'type' => 'setting', 'actor' => 'admin', 'action' => __('Enabled GoTyme gateway'), 'target' => 'settings.provider', 'before' => 'disabled', 'after' => 'enabled', 'ip' => '180.191.81.10', 'at' => '2026-06-24 18:02:55'],
        ];
    }

    /**
     * Full player profile (proposal §4) — header + per-tab data, derived deterministically
     * from the member roster row so the demo stays internally consistent.
     *
     * @return array<string, mixed>
     */
    public static function playerProfile(int $id): array
    {
        $member = collect(self::members())->firstWhere('id', $id) ?? self::members()[0];
        $tiers = ['Bronze', 'Silver', 'Gold', 'Platinum'];
        $tier = $tiers[$id % count($tiers)];

        $transactions = [];
        for ($i = 1; $i <= 6; $i++) {
            $dir = $i % 3 === 0 ? 'withdraw' : 'deposit';
            $transactions[] = [
                'reference' => ($dir === 'deposit' ? 'DEP' : 'WDL').'-2026'.str_pad((string) ($id * 100 + $i), 6, '0', STR_PAD_LEFT),
                'type' => $dir,
                'gateway' => $member['gateway'],
                'amount' => 1000 * $i + $id * 50,
                'status' => ['completed', 'completed', 'pending', 'completed', 'cancelled', 'completed'][$i - 1],
                'at' => '2026-06-'.str_pad((string) (($i * 4 % 27) + 1), 2, '0', STR_PAD_LEFT).' '.str_pad((string) (9 + $i), 2, '0', STR_PAD_LEFT).':12',
            ];
        }

        $logins = [];
        $devices = ['Chrome · Windows', 'Safari · iOS', 'Chrome · Android', 'Edge · Windows'];
        for ($i = 1; $i <= 5; $i++) {
            $logins[] = [
                'at' => '2026-06-'.str_pad((string) (25 - $i), 2, '0', STR_PAD_LEFT).' '.str_pad((string) (8 + $i), 2, '0', STR_PAD_LEFT).':05',
                'device' => $devices[$i % count($devices)],
                'ip' => $member['ip'],
                'duration' => ($i * 17 + 12).'m',
            ];
        }

        $coupons = [
            ['code' => 'WELCOME100', 'value' => '₱100', 'status' => 'redeemed', 'at' => '2026-05-12'],
            ['code' => 'RELOAD20', 'value' => '20%', 'status' => 'redeemed', 'at' => '2026-06-01'],
            ['code' => 'WEEKEND15', 'value' => '15%', 'status' => 'expired', 'at' => '2026-06-20'],
        ];

        $notes = [
            ['author' => 'admin', 'body' => __('KYC verified, documents on file.'), 'at' => '2026-05-13'],
            ['author' => 'cs_manager', 'body' => __('Requested faster withdrawals — explained approval flow.'), 'at' => '2026-06-10'],
        ];

        $activity = [
            ['action' => __('Logged in'), 'at' => $member['lastLogin']],
            ['action' => __('Deposit completed'), 'at' => '2026-06-24 11:20'],
            ['action' => __('Coupon redeemed: RELOAD20'), 'at' => '2026-06-01 09:14'],
            ['action' => __('VIP tier upgraded to '.$tier), 'at' => '2026-05-30 16:00'],
        ];

        return [
            'id' => $member['id'],
            'username' => $member['username'],
            'brand' => $member['gateway'].' Brand',
            'agent' => 'ag_'.strtolower(explode(' ', $member['bank'])[2] ?? 'pedro'),
            'registered' => $member['joinedAt'],
            'lastIp' => $member['ip'],
            'status' => $member['status'],
            'tier' => $tier,
            'balance' => $member['balance'],
            'bonusBalance' => (int) ($member['balance'] * 0.06),
            'totalDeposit' => $member['deposit'],
            'totalWithdraw' => $member['withdraw'],
            'net' => $member['deposit'] - $member['withdraw'],
            'totalTransactions' => $id * 3 + 18,
            'totalLogins' => $id * 4 + 40,
            'transactions' => $transactions,
            'logins' => $logins,
            'coupons' => $coupons,
            'notes' => $notes,
            'activity' => $activity,
        ];
    }

    /**
     * Profit & Loss report rows (proposal §6).
     *
     * @return array<int, array{label:string, gross:int, bonus:int, promo:int, profit:int}>
     */
    public static function profitLoss(): array
    {
        return [
            ['label' => __('This week'), 'gross' => 3_590_500, 'bonus' => 320_000, 'promo' => 145_000, 'profit' => 3_125_500],
            ['label' => __('Last week'), 'gross' => 3_180_000, 'bonus' => 298_000, 'promo' => 132_000, 'profit' => 2_750_000],
            ['label' => __('Month to date'), 'gross' => 12_540_000, 'bonus' => 1_180_000, 'promo' => 540_000, 'profit' => 10_820_000],
            ['label' => __('Last month'), 'gross' => 14_120_000, 'bonus' => 1_310_000, 'promo' => 610_000, 'profit' => 12_200_000],
        ];
    }

    /**
     * Coupon usage report (proposal §6/§9).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function couponUsage(): array
    {
        return collect(self::coupons())->map(fn ($c) => [
            'code' => $c['code'],
            'issued' => $c['maxUses'],
            'redeemed' => $c['used'],
            'expired' => max(0, $c['maxUses'] - $c['used']),
            'rate' => round($c['used'] / max(1, $c['maxUses']) * 100, 1),
            'cost' => $c['type'] === 'fixed' ? $c['value'] * $c['used'] : (int) ($c['used'] * 250),
        ])->all();
    }

    /**
     * Player activity report (proposal §6).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function playerActivity(): array
    {
        return [
            ['brand' => 'GCash Brand', 'signups' => 84, 'active' => 312, 'churned' => 18, 'retention' => 88.4, 'avgSession' => '42m'],
            ['brand' => 'Maya Brand', 'signups' => 51, 'active' => 198, 'churned' => 12, 'retention' => 85.1, 'avgSession' => '37m'],
            ['brand' => 'GoTyme Brand', 'signups' => 33, 'active' => 121, 'churned' => 9, 'retention' => 81.7, 'avgSession' => '29m'],
        ];
    }

    /**
     * Agent commission report (proposal §6).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function agentCommission(): array
    {
        return collect(self::agents())->map(fn ($a) => [
            'agent' => $a['username'],
            'players' => $a['players'],
            'volume' => $a['volume'],
            'rate' => $a['rate'],
            'commission' => (int) ($a['volume'] * $a['rate'] / 100),
        ])->all();
    }

    /**
     * Brand comparison report (proposal §6).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function brandComparison(): array
    {
        return [
            ['brand' => 'GCash Brand', 'revenue' => 1_540_000, 'players' => 312, 'retention' => 88.4],
            ['brand' => 'Maya Brand', 'revenue' => 1_120_000, 'players' => 198, 'retention' => 85.1],
            ['brand' => 'GoTyme Brand', 'revenue' => 690_000, 'players' => 121, 'retention' => 81.7],
        ];
    }

    /**
     * Period sales report (proposal §6) — weekly/monthly/yearly trend of the headline figures.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function periodSales(): array
    {
        return [
            ['period' => __('This week'), 'deposit' => 4_820_500, 'withdraw' => 1_230_000, 'net' => 3_590_500, 'players' => 84],
            ['period' => __('Last week'), 'deposit' => 4_180_000, 'withdraw' => 1_410_000, 'net' => 2_770_000, 'players' => 71],
            ['period' => __('This month'), 'deposit' => 19_240_000, 'withdraw' => 6_120_000, 'net' => 13_120_000, 'players' => 168],
            ['period' => __('Last month'), 'deposit' => 21_080_000, 'withdraw' => 7_240_000, 'net' => 13_840_000, 'players' => 192],
            ['period' => __('Year to date'), 'deposit' => 142_500_000, 'withdraw' => 48_200_000, 'net' => 94_300_000, 'players' => 1_482],
        ];
    }

    /**
     * Agent roster (proposal §7) — head/sub agents with player counts, volume and rolling rate.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function agents(): array
    {
        $names = ['ag_pedro', 'ag_maria', 'ag_jose', 'ag_liza', 'ag_mark', 'ag_grace', 'ag_paolo', 'ag_trisha'];
        $levels = ['Head', 'Head', 'Sub', 'Sub', 'Sub', 'Head', 'Sub', 'Sub'];
        $agents = [];

        foreach ($names as $i => $name) {
            $agents[] = [
                'id' => $i + 1,
                'username' => $name,
                'level' => $levels[$i],
                'parent' => $levels[$i] === 'Sub' ? $names[$i % 2 === 0 ? 0 : 1] : null,
                'players' => 12 + $i * 7,
                'volume' => 800_000 + $i * 320_000,
                'commission' => 24_000 + $i * 9_600,
                'rate' => [1.0, 1.2, 0.8, 0.9, 1.1, 1.5, 0.7, 1.0][$i],
                'balance' => 1_200_000 + $i * 540_000,
                'status' => $i % 5 === 3 ? 'suspended' : 'active',
            ];
        }

        return $agents;
    }

    /**
     * Agent hierarchy tree (proposal §7) — Super → Head → Sub structure for the tree view.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function agentTree(): array
    {
        $agents = collect(self::agents());

        return $agents->where('level', 'Head')->map(fn ($head) => [
            'username' => $head['username'],
            'players' => $head['players'],
            'subs' => $agents->where('parent', $head['username'])->map(fn ($s) => [
                'username' => $s['username'],
                'players' => $s['players'],
            ])->values()->all(),
        ])->values()->all();
    }

    /**
     * Commission settings per agent (proposal §7).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function agentCommissions(): array
    {
        return collect(self::agents())->map(fn ($a) => [
            'id' => $a['id'],
            'username' => $a['username'],
            'level' => $a['level'],
            'type' => $a['id'] % 2 === 0 ? 'turnover' : 'loss',
            'rate' => $a['rate'],
            'players' => $a['players'],
        ])->all();
    }

    /**
     * Agent transactions (proposal §7) — deposits/withdrawals processed on behalf of players.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function agentTransactions(): array
    {
        $agents = self::agents();
        $rows = [];

        for ($i = 1; $i <= 8; $i++) {
            $agent = $agents[($i - 1) % count($agents)];
            $dir = $i % 3 === 0 ? 'withdraw' : 'deposit';
            $rows[] = [
                'id' => $i,
                'agent' => $agent['username'],
                'player' => 'player'.str_pad((string) ($i * 7), 3, '0', STR_PAD_LEFT),
                'type' => $dir,
                'amount' => 2000 * $i + 500,
                'status' => ['completed', 'completed', 'pending', 'completed'][$i % 4],
                'at' => '2026-06-'.str_pad((string) (($i * 3 % 27) + 1), 2, '0', STR_PAD_LEFT).' '.str_pad((string) (10 + $i), 2, '0', STR_PAD_LEFT).':22',
            ];
        }

        return $rows;
    }

    /**
     * Agent performance leaderboard (proposal §7).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function agentPerformance(): array
    {
        return collect(self::agents())
            ->sortByDesc('volume')
            ->values()
            ->map(fn ($a, $i) => [
                'rank' => $i + 1,
                'username' => $a['username'],
                'players' => $a['players'],
                'volume' => $a['volume'],
                'commission' => $a['commission'],
                'newSignups' => 3 + ($a['id'] * 2 % 11),
            ])
            ->all();
    }

    /**
     * Daily settlement rows (proposal §13).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function dailySettlement(): array
    {
        $rows = [];
        for ($i = 0; $i < 7; $i++) {
            $in = 4_200_000 + $i * 180_000;
            $out = 1_900_000 + $i * 90_000;
            $bonus = 120_000 + $i * 8_000;
            $rows[] = [
                'date' => now()->subDays($i)->format('Y-m-d'),
                'in' => $in,
                'out' => $out,
                'gross' => $in - $out,
                'bonus' => $bonus,
                'net' => $in - $out - $bonus,
                'status' => $i === 0 ? 'open' : 'settled',
            ];
        }

        return $rows;
    }

    /**
     * Monthly revenue summary (proposal §13).
     *
     * @return array<int, array{month:string, revenue:int, withdrawals:int, bonus:int, net:int}>
     */
    public static function revenueSummary(): array
    {
        return [
            ['month' => __('Jun 2026'), 'revenue' => 19_240_000, 'withdrawals' => 6_120_000, 'bonus' => 1_180_000, 'net' => 11_940_000],
            ['month' => __('May 2026'), 'revenue' => 21_080_000, 'withdrawals' => 7_240_000, 'bonus' => 1_310_000, 'net' => 12_530_000],
            ['month' => __('Apr 2026'), 'revenue' => 18_460_000, 'withdrawals' => 6_010_000, 'bonus' => 1_090_000, 'net' => 11_360_000],
            ['month' => __('Mar 2026'), 'revenue' => 17_220_000, 'withdrawals' => 5_640_000, 'bonus' => 980_000, 'net' => 10_600_000],
        ];
    }

    /**
     * Balance sheet snapshot (proposal §13) — platform liability vs assets.
     *
     * @return array{liabilities:array<int, array{label:string, amount:int}>, assets:array<int, array{label:string, amount:int}>}
     */
    public static function balanceSheet(): array
    {
        return [
            'liabilities' => [
                ['label' => __('Player wallet balances'), 'amount' => 18_420_000],
                ['label' => __('Pending withdrawals'), 'amount' => 1_230_000],
                ['label' => __('Outstanding bonuses'), 'amount' => 640_000],
                ['label' => __('Agent balances'), 'amount' => 96_250_000],
            ],
            'assets' => [
                ['label' => __('Cash on hand (gateways)'), 'amount' => 112_300_000],
                ['label' => __('Reserve account'), 'amount' => 8_500_000],
                ['label' => __('Receivables'), 'amount' => 1_840_000],
            ],
        ];
    }

    /**
     * Commission ledger (proposal §13) — earned / pending / paid per agent.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function commissionLedger(): array
    {
        return collect(self::agents())->map(fn ($a) => [
            'agent' => $a['username'],
            'earned' => $a['commission'],
            'paid' => (int) ($a['commission'] * 0.7),
            'pending' => (int) ($a['commission'] * 0.3),
            'period' => __('Jun 2026'),
        ])->all();
    }
}
