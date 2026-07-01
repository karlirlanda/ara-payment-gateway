<?php

namespace App\Support;

/**
 * Static demo data for the frontend player portal.
 *
 * Everything here is in-memory mock data (no database, no real provider APIs)
 * so the player site can be clicked through for the proposal. Cash-in / cash-out
 * providers mirror the admin build: GCash, Maya and GoTyme Bank.
 *
 * @see AdminDemoData
 */
class PlayerDemoData
{
    /** Preset demo credentials surfaced on the login screen. */
    public const DEMO_USERNAME = 'player';

    public const DEMO_PASSWORD = 'demo123';

    /** Starting wallet balance (PHP ₱) for a fresh demo session. */
    public const STARTING_BALANCE = 12500;

    /** Quick-amount chips offered on the cashier (PHP ₱). */
    public const QUICK_AMOUNTS = [100, 500, 1000, 5000];

    /**
     * The demo player profile seeded into the session on login.
     *
     * @return array{name:string, username:string, level:int, balance:int}
     */
    public static function profile(): array
    {
        return [
            'name' => 'Juan dela Cruz',
            'username' => self::DEMO_USERNAME,
            'level' => 2,
            'balance' => self::STARTING_BALANCE,
        ];
    }

    /**
     * Cash-in / cash-out providers. Icons and colours match the admin gateway cards.
     *
     * @return array<int, array{key:string, name:string, tagline:string, icon:string, color:string, account:string, min:int, max:int}>
     */
    public static function providers(): array
    {
        return [
            [
                'key' => 'gcash',
                'name' => 'GCash',
                'tagline' => 'Pay with your GCash wallet',
                'icon' => 'device-phone-mobile',
                'color' => 'sky',
                'account' => '0917 ••• 1234',
                'min' => 100,
                'max' => 50000,
            ],
            [
                'key' => 'maya',
                'name' => 'Maya',
                'tagline' => 'Instant Maya transfer',
                'icon' => 'wallet',
                'color' => 'emerald',
                'account' => '0998 ••• 5521',
                'min' => 100,
                'max' => 50000,
            ],
            [
                'key' => 'gotyme',
                'name' => 'GoTyme',
                'tagline' => 'GoTyme Bank account',
                'icon' => 'building-library',
                'color' => 'amber',
                'account' => '0945 ••• 7788',
                'min' => 200,
                'max' => 100000,
            ],
        ];
    }

    /**
     * Look up a single provider by key.
     *
     * @return array{key:string, name:string, tagline:string, icon:string, color:string, account:string, min:int, max:int}|null
     */
    public static function provider(string $key): ?array
    {
        foreach (self::providers() as $provider) {
            if ($provider['key'] === $key) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * Game category tabs for the lobby.
     *
     * @return array<int, array{key:string, label:string, icon:string}>
     */
    public static function gameCategories(): array
    {
        return [
            ['key' => 'popular', 'label' => __('Popular'), 'icon' => 'fire'],
            ['key' => 'slots', 'label' => __('Slots'), 'icon' => 'sparkles'],
            ['key' => 'live', 'label' => __('Live Casino'), 'icon' => 'video-camera'],
            ['key' => 'sports', 'label' => __('Sports'), 'icon' => 'trophy'],
            ['key' => 'fishing', 'label' => __('Fishing'), 'icon' => 'bug-ant'],
        ];
    }

    /**
     * Game tiles for the lobby. `art` drives a placeholder gradient in the view.
     *
     * @return array<int, array{id:int, title:string, provider:string, category:string, art:string, hot:bool}>
     */
    public static function games(): array
    {
        return [
            ['id' => 1, 'title' => 'Golden Dragon', 'provider' => 'JILI', 'category' => 'slots', 'art' => 'from-amber-500 to-red-600', 'hot' => true],
            ['id' => 2, 'title' => 'Super Ace', 'provider' => 'JILI', 'category' => 'slots', 'art' => 'from-fuchsia-500 to-purple-700', 'hot' => true],
            ['id' => 3, 'title' => 'Live Baccarat', 'provider' => 'Evolution', 'category' => 'live', 'art' => 'from-emerald-500 to-teal-700', 'hot' => true],
            ['id' => 4, 'title' => 'Lightning Roulette', 'provider' => 'Evolution', 'category' => 'live', 'art' => 'from-sky-500 to-indigo-700', 'hot' => false],
            ['id' => 5, 'title' => 'Royal Fishing', 'provider' => 'JILI', 'category' => 'fishing', 'art' => 'from-cyan-500 to-blue-700', 'hot' => false],
            ['id' => 6, 'title' => 'Mega Ball', 'provider' => 'Evolution', 'category' => 'live', 'art' => 'from-rose-500 to-pink-700', 'hot' => false],
            ['id' => 7, 'title' => 'Fortune Gems', 'provider' => 'JILI', 'category' => 'slots', 'art' => 'from-yellow-400 to-orange-600', 'hot' => true],
            ['id' => 8, 'title' => 'Sportsbook', 'provider' => 'SABA', 'category' => 'sports', 'art' => 'from-green-500 to-emerald-700', 'hot' => false],
            ['id' => 9, 'title' => 'Money Coming', 'provider' => 'JILI', 'category' => 'slots', 'art' => 'from-violet-500 to-indigo-700', 'hot' => false],
            ['id' => 10, 'title' => 'Crazy Time', 'provider' => 'Evolution', 'category' => 'live', 'art' => 'from-pink-500 to-rose-700', 'hot' => true],
            ['id' => 11, 'title' => 'Boxing King', 'provider' => 'JILI', 'category' => 'slots', 'art' => 'from-orange-500 to-red-700', 'hot' => false],
            ['id' => 12, 'title' => 'Jackpot Fishing', 'provider' => 'JILI', 'category' => 'fishing', 'art' => 'from-teal-500 to-cyan-700', 'hot' => false],
        ];
    }

    /**
     * Hero / promo banners for the lobby carousel (text only).
     *
     * @return array<int, array{title:string, subtitle:string, art:string}>
     */
    public static function banners(): array
    {
        return [
            ['title' => __('100% Welcome Bonus'), 'subtitle' => __('Up to ₱5,000 on your first deposit'), 'art' => 'from-indigo-600 via-purple-600 to-fuchsia-600'],
            ['title' => __('Daily Cashback'), 'subtitle' => __('Earn up to 5% back every day'), 'art' => 'from-emerald-600 via-teal-600 to-cyan-600'],
            ['title' => __('Refer a Friend'), 'subtitle' => __('Get ₱200 for every friend who joins'), 'art' => 'from-amber-500 via-orange-600 to-rose-600'],
        ];
    }

    /**
     * Seed transaction history for a fresh demo session. New deposits and
     * withdrawals from the cashier are prepended to this list in the session.
     *
     * @return array<int, array{id:int, direction:string, provider:string, amount:int, status:string, reference:string, time:string}>
     */
    public static function seedTransactions(): array
    {
        return [
            ['id' => 1003, 'direction' => 'deposit', 'provider' => 'GCash', 'amount' => 5000, 'status' => 'completed', 'reference' => 'DEP-20260627-7741', 'time' => '2026-06-27 19:42'],
            ['id' => 1002, 'direction' => 'withdraw', 'provider' => 'Maya', 'amount' => 2500, 'status' => 'completed', 'reference' => 'WDL-20260626-5519', 'time' => '2026-06-26 11:08'],
            ['id' => 1001, 'direction' => 'deposit', 'provider' => 'GoTyme', 'amount' => 10000, 'status' => 'completed', 'reference' => 'DEP-20260625-3120', 'time' => '2026-06-25 21:15'],
        ];
    }
}
