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
                'commissionType' => $i % 2 === 0 ? 'betting' : 'losing',
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
        $activities = ['Lobby', 'Deposit · GCash', 'Withdraw · Maya', 'Wallet', 'Deposit · GoTyme'];
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
}
