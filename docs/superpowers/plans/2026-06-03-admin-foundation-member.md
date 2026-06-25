# Admin Foundation + Member Management Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the reusable admin layout foundation (theme-following sidebar with summary block, header action badges + notifications, and reusable filter-bar / stat-strip / table / row-actions components) and the member-management vertical (list, create/edit as full pages, live users) — adapting the DREAMS betting-admin onto our Flux template, frontend-first on demo data.

**Architecture:** A dedicated admin layout (`layouts.admin`) the same shape as the existing app layout but composed from new `x-admin.*` Blade components. Pages are Chisel single-file Livewire components (`new #[Title][Layout] class extends Component`) opting into the admin layout, holding in-memory demo data from a single `App\Support\AdminDemoData` source. The sidebar follows the Flux light/dark theme.

**Tech Stack:** Laravel 13, Livewire 4 (Chisel SFC pages), Flux Free 2, Tailwind v4, Pretendard, Pest 4 (browser + feature). No new dependencies. No persistence.

**Spec:** `docs/superpowers/specs/2026-06-03-admin-foundation-member-design.md`

**Conventions (follow exactly):**
- Branch is already `feature/admin-dreams-foundation`. Do NOT create/switch branches or push.
- Chisel page = `resources/views/pages/<dot.path>/⚡name.blade.php`, registered `Route::livewire('url', 'pages::dot.path.name')`. The ⚡ prefix is dropped from the locator (e.g. `⚡index.blade.php` → `...index`).
- Anonymous Blade components live in `resources/views/components/admin/*.blade.php` → `<x-admin.*>`.
- Flux Free components available: `flux:badge`, `flux:switch`, `flux:button`, `flux:input`, `flux:select`, `flux:textarea`, `flux:checkbox`, `flux:modal(.close)`, `flux:heading`, `flux:icon`, `flux:dropdown`, `flux:menu`, `flux:radio`/`flux:radio.group`, `flux:field`, `flux:card`, `flux:tooltip`, `flux:avatar`, `flux:separator`.
- Run `vendor/bin/pint --dirty --format agent` after PHP edits.
- Pest browser plugin is installed; browser tests use `visit()`, `assertNoJavascriptErrors()`, `assertSee()`, `assertScript()`, `click()`, `type()`.

**File structure created by this plan:**
- `app/Support/AdminDemoData.php` — single demo-data source
- `resources/views/layouts/admin.blade.php` — admin shell (full HTML doc + `{{ $slot }}`)
- `resources/views/components/admin/{level-badge,stat-strip,stat-cell,summary-totals,action-bar,notifications,page-header,filter-bar,table,row-actions}.blade.php`
- `resources/views/pages/admin/members/{⚡index,⚡form,⚡live}.blade.php` (form shared by create + edit)
- `routes/admin.php` — member routes (append)
- `lang/ko.json` — additions
- `tests/Feature/Admin/{AdminDemoDataTest,MemberPagesTest}.php`, `tests/Browser/Admin/MemberTest.php`

---

### Task 1: Demo-data source

**Files:**
- Create: `app/Support/AdminDemoData.php`
- Test: `tests/Feature/Admin/AdminDemoDataTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/AdminDemoDataTest.php`:
```php
<?php

use App\Support\AdminDemoData;

it('exposes summary totals', function () {
    expect(AdminDemoData::summary())
        ->toHaveKeys(['storeMoney', 'memberMoney', 'points', 'honorlink', 'todayNet', 'newMembers', 'newStores', 'liveUsers']);
});

it('exposes header action badge counts', function () {
    $badges = AdminDemoData::actionBadges();
    expect($badges)->toHaveKeys(['deposit', 'withdraw', 'inquiry', 'approval', 'unregistered']);
    expect($badges['deposit'])->toBeInt();
});

it('provides at least 20 demo members with the required fields', function () {
    $members = AdminDemoData::members();
    expect($members)->toHaveCount(30);
    expect($members[0])->toHaveKeys([
        'id', 'level', 'username', 'nickname', 'store', 'bank', 'phone', 'balance',
        'commissionType', 'points', 'deposit', 'withdraw', 'status', 'lastLogin',
        'ip', 'joinedAt', 'domain',
    ]);
});

it('provides notifications and live users', function () {
    expect(AdminDemoData::notifications())->not->toBeEmpty();
    expect(AdminDemoData::liveUsers())->not->toBeEmpty();
});
```

- [ ] **Step 2: Run it (fails — class missing)**

Run: `php artisan test --compact --filter=AdminDemoDataTest`
Expected: FAIL (class `App\Support\AdminDemoData` not found).

- [ ] **Step 3: Implement the class**

Create `app/Support/AdminDemoData.php`:
```php
<?php

namespace App\Support;

class AdminDemoData
{
    /**
     * @return array<string, int>
     */
    public static function summary(): array
    {
        return [
            'storeMoney' => 3913730,
            'memberMoney' => 2878403,
            'points' => 158153,
            'honorlink' => 86380,
            'todayNet' => 0,
            'newMembers' => 0,
            'newStores' => 0,
            'liveUsers' => 1,
        ];
    }

    /**
     * @return array<string, int>
     */
    public static function actionBadges(): array
    {
        return ['deposit' => 3, 'withdraw' => 2, 'inquiry' => 7, 'approval' => 5, 'unregistered' => 0];
    }

    /**
     * @return array<int, array{title:string,time:string}>
     */
    public static function notifications(): array
    {
        return [
            ['title' => '새 매장 충전 요청', 'time' => '2분 전'],
            ['title' => '총판 문의 도착', 'time' => '15분 전'],
            ['title' => '매장 가입 승인 대기', 'time' => '1시간 전'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function members(): array
    {
        $stores = ['agent1', 'agent2', 'agent5', 'store1', 'store2', '-'];
        $statuses = ['normal', 'normal', 'normal', 'suspended', 'withdrawn'];
        $members = [];

        for ($i = 1; $i <= 30; $i++) {
            $members[] = [
                'id' => $i,
                'level' => ($i % 5) + 1,
                'username' => 'player'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'nickname' => '닉네임'.$i,
                'store' => $stores[$i % count($stores)],
                'bank' => 'TestBank / 7869'.(6000 + $i).' / Holder'.$i,
                'phone' => '010-1234-'.str_pad((string) (1000 + $i), 4, '0', STR_PAD_LEFT),
                'balance' => 12000 * $i,
                'commissionType' => $i % 2 === 0 ? 'betting' : 'losing',
                'points' => 500 * $i,
                'deposit' => 10000 * $i,
                'withdraw' => 8000 * ($i - 1),
                'status' => $statuses[$i % count($statuses)],
                'lastLogin' => '2026-06-0'.(($i % 3) + 1).' 1'.($i % 10).':30',
                'ip' => '180.191.81.'.(200 + $i),
                'joinedAt' => '2026-05-'.str_pad((string) (($i % 28) + 1), 2, '0', STR_PAD_LEFT),
                'domain' => 'ara-admin.cloud',
            ];
        }

        return $members;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function liveUsers(): array
    {
        return collect(self::members())->take(6)->map(fn ($m) => [
            'level' => $m['level'],
            'username' => $m['username'],
            'joinedAt' => $m['joinedAt'],
            'ip' => $m['ip'],
            'domain' => $m['domain'],
            'status' => 'online',
        ])->all();
    }
}
```

- [ ] **Step 4: Run it (passes)**

Run: `php artisan test --compact --filter=AdminDemoDataTest`
Expected: PASS (4 tests).

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Support/AdminDemoData.php tests/Feature/Admin/AdminDemoDataTest.php
git commit -m "feat: admin demo-data source

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 2: Presentational primitives — level-badge, stat-cell, stat-strip

**Files:**
- Create: `resources/views/components/admin/level-badge.blade.php`
- Create: `resources/views/components/admin/stat-cell.blade.php`
- Create: `resources/views/components/admin/stat-strip.blade.php`

- [ ] **Step 1: level-badge**

Create `resources/views/components/admin/level-badge.blade.php`:
```blade
@props([
    'level' => 1,
    'id' => '',
])

@php
    $palette = [
        1 => 'bg-indigo-600', 2 => 'bg-sky-600', 3 => 'bg-emerald-600',
        4 => 'bg-amber-600', 5 => 'bg-rose-600',
    ];
    $bg = $palette[$level] ?? 'bg-zinc-500';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5']) }}>
    <span class="inline-flex size-4 items-center justify-center text-[10px] font-bold text-white {{ $bg }}">{{ $level }}</span>
    <span class="font-medium">{{ $id }}</span>
</span>
```

- [ ] **Step 2: stat-cell + stat-strip**

Create `resources/views/components/admin/stat-cell.blade.php`:
```blade
@props([
    'label' => '',
    'value' => '',
    'count' => null,
])

<div class="flex-1 border-e border-zinc-200 px-3 py-2 last:border-e-0 dark:border-zinc-700">
    <div class="text-xs text-zinc-500">{{ $label }}</div>
    <div class="text-sm font-bold tabular-nums text-zinc-900 dark:text-zinc-100">
        {{ $value }}
        @isset($count)
            <span class="text-xs font-normal text-zinc-400">({{ $count }})</span>
        @endisset
    </div>
</div>
```

Create `resources/views/components/admin/stat-strip.blade.php`:
```blade
<div {{ $attributes->merge(['class' => 'flex border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800']) }}>
    {{ $slot }}
</div>
```

- [ ] **Step 3: Build (Blade only — verify compiles via a throwaway render in a later page; for now just build assets)**

Run: `npm run build`
Expected: success.

- [ ] **Step 4: Commit**

```bash
git add resources/views/components/admin/level-badge.blade.php resources/views/components/admin/stat-cell.blade.php resources/views/components/admin/stat-strip.blade.php
git commit -m "feat: admin level-badge and stat-strip components

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 3: Sidebar widgets — summary-totals, action-bar, notifications

**Files:**
- Create: `resources/views/components/admin/summary-totals.blade.php`
- Create: `resources/views/components/admin/action-bar.blade.php`
- Create: `resources/views/components/admin/notifications.blade.php`

- [ ] **Step 1: summary-totals**

Create `resources/views/components/admin/summary-totals.blade.php`:
```blade
@php($s = \App\Support\AdminDemoData::summary())

<div class="border-b border-zinc-200 px-4 py-3 text-xs dark:border-zinc-700">
    @php
        $rows = [
            ['매장보유머니', number_format($s['storeMoney'])],
            ['회원보유머니', number_format($s['memberMoney'])],
            ['보유 포인트', number_format($s['points'])],
        ];
        $rows2 = [
            ['HonorLink 총잔고', number_format($s['honorlink'])],
            ['금일 (입-출)', number_format($s['todayNet'])],
            ['신규회원 / 신규매장', $s['newMembers'].' / '.$s['newStores']],
            ['실시간 사용자', (string) $s['liveUsers']],
        ];
    @endphp
    @foreach ($rows as [$label, $value])
        <div class="flex justify-between py-0.5">
            <span class="text-zinc-500">{{ __($label) }}</span>
            <span class="font-bold tabular-nums text-zinc-800 dark:text-zinc-100">{{ $value }}</span>
        </div>
    @endforeach
    <flux:separator class="my-1.5" />
    @foreach ($rows2 as [$label, $value])
        <div class="flex justify-between py-0.5">
            <span class="text-zinc-500">{{ __($label) }}</span>
            <span class="font-bold tabular-nums text-zinc-800 dark:text-zinc-100">{{ $value }}</span>
        </div>
    @endforeach
</div>
```

- [ ] **Step 2: action-bar**

Create `resources/views/components/admin/action-bar.blade.php`:
```blade
@php($b = \App\Support\AdminDemoData::actionBadges())

<div class="flex items-stretch gap-1.5">
    @php
        $actions = [
            ['매장충전', 'banknotes', $b['deposit']],
            ['매장환전', 'arrows-right-left', $b['withdraw']],
            ['총판문의', 'chat-bubble-left-ellipsis', $b['inquiry']],
            ['매장 가입 승인', 'user-plus', $b['approval']],
            ['미등록 베팅', 'flag', $b['unregistered']],
        ];
    @endphp
    @foreach ($actions as [$label, $icon, $count])
        <button type="button" class="relative flex flex-col items-center gap-1 border border-zinc-200 px-3 py-1.5 text-[11px] text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-700/40">
            <flux:icon :icon="$icon" class="size-4 text-zinc-400" aria-hidden="true" />
            <span>{{ __($label) }}</span>
            @if ($count > 0)
                <span class="absolute -end-1.5 -top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white">{{ $count }}</span>
            @endif
        </button>
    @endforeach
</div>
```

- [ ] **Step 3: notifications**

Create `resources/views/components/admin/notifications.blade.php`:
```blade
@php($items = \App\Support\AdminDemoData::notifications())

<flux:dropdown position="bottom" align="end">
    <flux:button icon="bell" variant="subtle" size="sm" class="relative" :tooltip="__('Notifications')">
        @if (count($items) > 0)
            <span class="absolute end-1 top-1 size-2 rounded-full bg-rose-500"></span>
        @endif
    </flux:button>
    <flux:menu class="min-w-72">
        @forelse ($items as $item)
            <flux:menu.item>
                <div class="flex w-full items-center justify-between gap-3">
                    <span class="truncate">{{ $item['title'] }}</span>
                    <span class="shrink-0 text-xs text-zinc-400">{{ $item['time'] }}</span>
                </div>
            </flux:menu.item>
        @empty
            <flux:menu.item disabled>{{ __('No notifications') }}</flux:menu.item>
        @endforelse
    </flux:menu>
</flux:dropdown>
```

- [ ] **Step 4: Build + commit**

```bash
npm run build
git add resources/views/components/admin/summary-totals.blade.php resources/views/components/admin/action-bar.blade.php resources/views/components/admin/notifications.blade.php
git commit -m "feat: admin summary-totals, action-bar, notifications

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 4: Admin layout shell (theme-following)

**Files:**
- Create: `resources/views/layouts/admin.blade.php`

**Context:** A full HTML document layout (same head/scripts as the existing `layouts/app/sidebar.blade.php`) but with the admin shell. The sidebar uses `flux:sidebar` (theme-following: light `zinc-50` / dark `zinc-900`, exactly like the existing app sidebar) with the summary block + nav (회원관리 wired; other groups are non-navigable). Header has the action-bar, locale dropdown, notifications, and the existing user menu. Read the existing `resources/views/layouts/app/sidebar.blade.php` first to copy the head includes, locale dropdown, `@persist('toast')`, and `@fluxScripts` exactly.

- [ ] **Step 1: Create the layout**

Create `resources/views/layouts/admin.blade.php`:
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('admin.members') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <x-admin.summary-totals />

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Members')" expandable :expanded="true">
                    <flux:sidebar.item icon="users" :href="route('admin.members')" :current="request()->routeIs('admin.members')" wire:navigate>{{ __('Member List') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="signal" :href="route('admin.members.live')" :current="request()->routeIs('admin.members.live')" wire:navigate>{{ __('Live Users') }}</flux:sidebar.item>
                </flux:sidebar.group>

                {{-- Placeholder groups for later slices (non-navigable this phase) --}}
                <flux:sidebar.group :heading="__('Agents')" expandable :expanded="false">
                    <flux:sidebar.item icon="building-storefront" href="#" class="pointer-events-none opacity-50">{{ __('Coming soon') }}</flux:sidebar.item>
                </flux:sidebar.group>
                <flux:sidebar.group :heading="__('Transactions')" expandable :expanded="false">
                    <flux:sidebar.item icon="banknotes" href="#" class="pointer-events-none opacity-50">{{ __('Coming soon') }}</flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <flux:header class="max-lg:hidden! border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.collapse class="max-lg:hidden" />
            <x-admin.action-bar class="ms-2" />
            <flux:spacer />
            <flux:button :href="route('appearance.edit')" wire:navigate icon="sun" variant="subtle" size="sm" :tooltip="__('Appearance')" />
            <flux:dropdown position="bottom" align="end">
                <flux:button icon="language" icon:trailing="chevron-down" variant="subtle" size="sm" :tooltip="__('Language')" />
                <flux:menu>
                    <flux:menu.item :href="route('locale.switch', 'en')" :icon="app()->getLocale() === 'en' ? 'check' : null">English</flux:menu.item>
                    <flux:menu.item :href="route('locale.switch', 'ko')" :icon="app()->getLocale() === 'ko' ? 'check' : null">한국어</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
            <x-admin.notifications />
            <x-desktop-user-menu />
        </flux:header>

        {{-- Mobile header --}}
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:spacer />
            <x-admin.notifications />
        </flux:header>

        <flux:main>
            {{ $slot }}
        </flux:main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
```

- [ ] **Step 2: Build**

Run: `npm run build`
Expected: success. (No page uses this layout yet — verified in Task 6.)

- [ ] **Step 3: Commit**

```bash
git add resources/views/layouts/admin.blade.php
git commit -m "feat: theme-following admin layout shell

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 5: List scaffold — page-header, filter-bar, table, row-actions

**Files:**
- Create: `resources/views/components/admin/page-header.blade.php`
- Create: `resources/views/components/admin/filter-bar.blade.php`
- Create: `resources/views/components/admin/table.blade.php`
- Create: `resources/views/components/admin/row-actions.blade.php`

**Context:** `filter-bar` binds to **conventional Livewire properties** the host page must declare: `dateFrom`, `dateTo`, `keyword`, `status`, plus a `resetFilters()` method. It accepts a `:statuses` array prop (value=>label) for the status select and an `actions` slot.

- [ ] **Step 1: page-header**

Create `resources/views/components/admin/page-header.blade.php`:
```blade
@props(['title' => ''])

<div class="mb-3 flex flex-col gap-3">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="lg">{{ $title }}</flux:heading>
        @isset($actions)
            <div class="flex items-center gap-2">{{ $actions }}</div>
        @endisset
    </div>
    @isset($tabs)
        <div class="flex gap-0.5">{{ $tabs }}</div>
    @endisset
</div>
```

- [ ] **Step 2: filter-bar**

Create `resources/views/components/admin/filter-bar.blade.php`:
```blade
@props(['statuses' => []])

<div class="mb-3 flex flex-wrap items-end gap-2 border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-800">
    <flux:input type="date" wire:model.live="dateFrom" :label="__('From')" size="sm" class="w-40" />
    <flux:input type="date" wire:model.live="dateTo" :label="__('To')" size="sm" class="w-40" />
    <flux:button wire:click="$set('dateFrom', '{{ now()->toDateString() }}')" size="sm" variant="subtle">{{ __('Today') }}</flux:button>
    <flux:button wire:click="$set('dateFrom', '{{ now()->subDay()->toDateString() }}')" size="sm" variant="subtle">{{ __('Yesterday') }}</flux:button>

    @if (! empty($statuses))
        <flux:select wire:model.live="status" :label="__('Status')" size="sm" class="w-36">
            <flux:select.option value="">{{ __('All') }}</flux:select.option>
            @foreach ($statuses as $value => $label)
                <flux:select.option :value="$value">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
    @endif

    <flux:input wire:model.live.debounce.300ms="keyword" :label="__('Search')" :placeholder="__('Keyword')" icon="magnifying-glass" size="sm" class="w-56" />
    <flux:button wire:click="resetFilters" variant="danger" size="sm">{{ __('Reset') }}</flux:button>

    @isset($actions)
        <div class="ms-auto flex items-end gap-2">{{ $actions }}</div>
    @endisset
</div>
```

- [ ] **Step 3: table + row-actions**

Create `resources/views/components/admin/table.blade.php`:
```blade
{{-- Wide, horizontally-scrollable admin table. Pass <x-slot:head> (tr of <th>) and the rows as the default slot (<tr>…). --}}
<div {{ $attributes->merge(['class' => 'border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800']) }}>
    <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap text-xs">
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
```

Create `resources/views/components/admin/row-actions.blade.php`:
```blade
{{-- Per-row action button group. Just a flex wrapper; host supplies the buttons. --}}
<div {{ $attributes->merge(['class' => 'flex items-center gap-1']) }}>
    {{ $slot }}
</div>
```

Add shared header/cell helper classes by documenting in the table usage (Task 6 uses `<th class="px-3 py-2 text-start font-semibold text-zinc-500">` and `<td class="px-3 py-2">`).

- [ ] **Step 4: Build + commit**

```bash
npm run build
git add resources/views/components/admin/page-header.blade.php resources/views/components/admin/filter-bar.blade.php resources/views/components/admin/table.blade.php resources/views/components/admin/row-actions.blade.php
git commit -m "feat: admin list scaffold (page-header, filter-bar, table, row-actions)

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 6: Member list page + routes

**Files:**
- Modify: `routes/admin.php`
- Create: `resources/views/pages/admin/members/⚡index.blade.php`

- [ ] **Step 1: Add routes**

In `routes/admin.php`, inside the existing `Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(...)` closure, append:
```php
    Route::livewire('members', 'pages::admin.members.index')->name('members');
    Route::livewire('members/create', 'pages::admin.members.form')->name('members.create');
    Route::livewire('members/{id}/edit', 'pages::admin.members.form')->name('members.edit');
    Route::livewire('members/live', 'pages::admin.members.live')->name('members.live');
```

- [ ] **Step 2: Create the member list page**

Create `resources/views/pages/admin/members/⚡index.blade.php`:
```blade
<?php

use App\Support\AdminDemoData;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Member Management')] #[Layout('layouts.admin')] class extends Component {
    use WithPagination;

    public string $dateFrom = '';
    public string $dateTo = '';
    public string $keyword = '';
    public string $status = '';
    public int $perPage = 50;
    public ?int $deletingId = null;

    /** @var array<int, array<string, mixed>> */
    public array $members = [];

    public function mount(): void
    {
        $this->members = AdminDemoData::members();
    }

    public function updatedKeyword(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['dateFrom', 'dateTo', 'keyword', 'status']);
        $this->resetPage();
    }

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        $filtered = collect($this->members)
            ->when($this->status !== '', fn ($c) => $c->where('status', $this->status))
            ->when($this->keyword !== '', fn ($c) => $c->filter(
                fn ($m) => str_contains(strtolower($m['username'].$m['nickname'].$m['phone']), strtolower($this->keyword))
            ))
            ->values();

        $page = $this->getPage();

        return new LengthAwarePaginator(
            $filtered->forPage($page, $this->perPage)->values(),
            $filtered->count(),
            $this->perPage,
            $page,
            ['path' => request()->url()],
        );
    }

    #[Computed]
    public function totals(): array
    {
        $c = collect($this->members);

        return [
            'count' => $c->count(),
            'balance' => $c->sum('balance'),
            'points' => $c->sum('points'),
            'net' => $c->sum('deposit') - $c->sum('withdraw'),
        ];
    }

    public function setStatusTab(string $status): void
    {
        $this->status = $status;
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $this->members = collect($this->members)->map(function ($m) use ($id) {
            if ($m['id'] === $id) {
                $m['status'] = $m['status'] === 'normal' ? 'suspended' : 'normal';
            }

            return $m;
        })->all();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        Flux::modal('delete-member')->show();
    }

    public function deleteMember(): void
    {
        $this->members = collect($this->members)->reject(fn ($m) => $m['id'] === $this->deletingId)->values()->all();
        $this->deletingId = null;
        Flux::modal('delete-member')->close();
        Flux::toast(text: __('Member deleted'), variant: 'success');
    }

    public function statusColor(string $status): string
    {
        return match ($status) {
            'normal' => 'green',
            'suspended' => 'amber',
            default => 'zinc',
        };
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'normal' => __('Normal'),
            'suspended' => __('Suspended'),
            default => __('Withdrawn'),
        };
    }
}; ?>

<div class="flex flex-col">
    <x-admin.page-header :title="__('Member Management')">
        <x-slot:tabs>
            @foreach (['' => __('All'), 'normal' => __('Normal'), 'suspended' => __('Suspended'), 'withdrawn' => __('Withdrawn')] as $value => $label)
                <button type="button" wire:click="setStatusTab('{{ $value }}')"
                    @class([
                        'border border-b-0 px-4 py-1.5 text-xs',
                        'border-accent bg-accent text-[color:var(--color-accent-foreground)]' => $status === $value,
                        'border-zinc-200 bg-white text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' => $status !== $value,
                    ])>{{ $label }}</button>
            @endforeach
        </x-slot:tabs>
    </x-admin.page-header>

    <x-admin.filter-bar :statuses="['normal' => __('Normal'), 'suspended' => __('Suspended'), 'withdrawn' => __('Withdrawn')]">
        <x-slot:actions>
            <flux:button :href="route('admin.members.create')" wire:navigate icon="plus" variant="primary" size="sm">{{ __('Create member') }}</flux:button>
            <flux:select wire:model.live="perPage" size="sm" class="w-28">
                <flux:select.option value="25">25</flux:select.option>
                <flux:select.option value="50">50</flux:select.option>
                <flux:select.option value="100">100</flux:select.option>
            </flux:select>
        </x-slot:actions>
    </x-admin.filter-bar>

    <x-admin.stat-strip class="mb-3">
        <x-admin.stat-cell :label="__('Total members')" :value="number_format($this->totals['count'])" />
        <x-admin.stat-cell :label="__('Total balance')" :value="number_format($this->totals['balance'])" />
        <x-admin.stat-cell :label="__('Total points')" :value="number_format($this->totals['points'])" />
        <x-admin.stat-cell :label="__('Deposit − Withdraw')" :value="number_format($this->totals['net'])" />
    </x-admin.stat-strip>

    <x-admin.table>
        <x-slot:head>
            <tr>
                @foreach (['ID', 'Level / ID', 'Nickname', 'Store', 'Account details', 'Phone', 'Balance', 'Commission', 'Points', 'Deposit', 'Withdraw', 'Difference', 'Login', 'Last access', 'IP', 'Joined', 'Domain', 'Manage'] as $h)
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __($h) }}</th>
                @endforeach
            </tr>
        </x-slot:head>

        @foreach ($this->rows as $m)
            <tr wire:key="member-{{ $m['id'] }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2">{{ $m['id'] }}</td>
                <td class="px-3 py-2"><x-admin.level-badge :level="$m['level']" :id="$m['username']" /></td>
                <td class="px-3 py-2">{{ $m['nickname'] }}</td>
                <td class="px-3 py-2">{{ $m['store'] }}</td>
                <td class="px-3 py-2">{{ $m['bank'] }}</td>
                <td class="px-3 py-2 tabular-nums">{{ $m['phone'] }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ number_format($m['balance']) }}</td>
                <td class="px-3 py-2"><flux:badge size="sm" :color="$m['commissionType'] === 'betting' ? 'indigo' : 'zinc'" inset="top bottom">{{ $m['commissionType'] }}</flux:badge></td>
                <td class="px-3 py-2 text-end tabular-nums">{{ number_format($m['points']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ number_format($m['deposit']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ number_format($m['withdraw']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ number_format($m['deposit'] - $m['withdraw']) }}</td>
                <td class="px-3 py-2"><flux:switch :checked="$m['status'] === 'normal'" wire:click="toggleStatus({{ $m['id'] }})" wire:key="member-switch-{{ $m['id'] }}-{{ $m['status'] === 'normal' ? '1' : '0' }}" /></td>
                <td class="px-3 py-2">{{ $m['lastLogin'] }}</td>
                <td class="px-3 py-2 tabular-nums">{{ $m['ip'] }}</td>
                <td class="px-3 py-2">{{ $m['joinedAt'] }}</td>
                <td class="px-3 py-2">{{ $m['domain'] }}</td>
                <td class="px-3 py-2">
                    <x-admin.row-actions>
                        <flux:button :href="route('admin.members.edit', $m['id'])" wire:navigate size="xs" variant="subtle">{{ __('Edit') }}</flux:button>
                        <flux:button size="xs" variant="subtle" disabled>{{ __('Money') }}</flux:button>
                        <flux:button wire:click="confirmDelete({{ $m['id'] }})" size="xs" variant="danger">{{ __('Delete') }}</flux:button>
                    </x-admin.row-actions>
                </td>
            </tr>
        @endforeach

        <x-slot:footer>
            <flux:pagination :paginator="$this->rows" />
        </x-slot:footer>
    </x-admin.table>

    <flux:modal name="delete-member" class="w-full max-w-sm">
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Delete member') }}</flux:heading>
            <flux:text>{{ __('Are you sure you want to delete this member? This action cannot be undone.') }}</flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close>
                <flux:button wire:click="deleteMember" variant="danger" data-test="confirm-delete">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
```

- [ ] **Step 3: Verify route + render**

```bash
php artisan view:clear
php artisan route:list --path=admin/members
php artisan test --compact 2>&1 | tail -5
```
Expected: routes resolve; existing suite still green.

- [ ] **Step 4: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add routes/admin.php "resources/views/pages/admin/members/⚡index.blade.php"
git commit -m "feat: member list page (18 cols, filters, tabs, demo data)

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 7: Member create/edit form (normal page)

**Files:**
- Create: `resources/views/pages/admin/members/⚡form.blade.php`

**Context:** One Chisel page serves both create (`/admin/members/create`) and edit (`/admin/members/{id}/edit`) via an optional `{id}` route param mounted into the component. Save validates and toasts (no persistence). Sections: 계정 / 계좌정보 / 수수료.

- [ ] **Step 1: Create the form page**

Create `resources/views/pages/admin/members/⚡form.blade.php`:
```blade
<?php

use App\Support\AdminDemoData;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Member Form')] #[Layout('layouts.admin')] class extends Component {
    public ?int $id = null;

    // Account
    public string $type = 'offline';
    public string $upline = '';
    public int $level = 1;
    public string $username = '';
    public string $nickname = '';
    public string $password = '';
    public string $passwordConfirm = '';
    public string $withdrawPin = '';
    public string $email = '';
    public string $messenger = '';
    public string $phone = '';
    public string $memberStatus = 'normal';
    public string $bettingGrade = '';
    public int $balance = 0;
    public int $points = 0;
    public string $memo = '';

    // Bank
    public bool $memberAccountEnabled = true;
    public string $memberBankName = '';
    public string $memberHolder = '';
    public string $memberAccountNo = '';

    // Commission
    public bool $commissionEnabled = true;
    public string $commissionType = 'betting';
    public int $casinoBaccarat = 0;
    public int $casinoSlot = 0;
    public int $casinoLosing = 0;

    public function mount(?int $id = null): void
    {
        $this->id = $id;

        if ($id !== null) {
            $member = collect(AdminDemoData::members())->firstWhere('id', $id);
            if ($member) {
                $this->username = $member['username'];
                $this->nickname = $member['nickname'];
                $this->level = $member['level'];
                $this->phone = $member['phone'];
                $this->balance = $member['balance'];
                $this->points = $member['points'];
                $this->memberStatus = $member['status'];
                $this->commissionType = $member['commissionType'];
            }
        }
    }

    public function save(): void
    {
        $this->validate([
            'username' => ['required', 'string', 'max:50'],
            'nickname' => ['required', 'string', 'max:50'],
            'level' => ['required', 'integer', 'between:1,5'],
            'type' => ['required', 'in:offline,online'],
            'memberStatus' => ['required', 'in:normal,suspended,withdrawn'],
            'commissionType' => ['required', 'in:betting,losing'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
        ]);

        // Frontend-only phase: no persistence.
        Flux::toast(text: $this->id ? __('Member updated') : __('Member created'), variant: 'success');
        $this->redirectRoute('admin.members', navigate: true);
    }
}; ?>

<div class="flex flex-col gap-4">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ $id ? __('Edit member') : __('Create member') }}</flux:heading>
        <div class="flex gap-2">
            <flux:button :href="route('admin.members')" wire:navigate variant="ghost">{{ __('Cancel') }}</flux:button>
            <flux:button wire:click="save" variant="primary">{{ __('Save') }}</flux:button>
        </div>
    </div>

    <form wire:submit="save" class="flex flex-col gap-4">
        {{-- 계정 / Account --}}
        <flux:card>
            <flux:heading size="sm" class="mb-3">{{ __('Account') }}</flux:heading>
            <div class="grid gap-4 md:grid-cols-2">
                <flux:radio.group wire:model="type" :label="__('Type')" variant="segmented">
                    <flux:radio value="offline" :label="__('Offline')" />
                    <flux:radio value="online" :label="__('Online')" />
                </flux:radio.group>
                <flux:select wire:model="memberStatus" :label="__('Member status')">
                    <flux:select.option value="normal">{{ __('Normal') }}</flux:select.option>
                    <flux:select.option value="suspended">{{ __('Suspended') }}</flux:select.option>
                    <flux:select.option value="withdrawn">{{ __('Withdrawn') }}</flux:select.option>
                </flux:select>
                <flux:input wire:model="upline" :label="__('Store (upline)')" :placeholder="__('Recommend code or username')" />
                <flux:select wire:model="level" :label="__('Level')">
                    @foreach (range(1, 5) as $l)
                        <flux:select.option :value="$l">{{ __('Level') }} {{ $l }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="username" :label="__('Username')" />
                <flux:input wire:model="nickname" :label="__('Nickname')" />
                <flux:input wire:model="password" type="password" :label="__('Password')" />
                <flux:input wire:model="passwordConfirm" type="password" :label="__('Confirm password')" />
                <flux:input wire:model="withdrawPin" :label="__('Withdraw password / PIN')" />
                <flux:input wire:model="email" type="email" :label="__('Email')" />
                <flux:input wire:model="phone" :label="__('Phone')" />
                <flux:input wire:model="balance" type="number" :label="__('Balance')" />
                <flux:input wire:model="points" type="number" :label="__('Points')" />
            </div>
            <flux:textarea wire:model="memo" :label="__('Memo')" rows="3" class="mt-4" />
        </flux:card>

        {{-- 계좌정보 / Bank --}}
        <flux:card>
            <div class="mb-3 flex items-center justify-between">
                <flux:heading size="sm">{{ __('Bank account') }}</flux:heading>
                <flux:switch wire:model="memberAccountEnabled" :label="__('Enabled')" align="right" />
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <flux:input wire:model="memberBankName" :label="__('Bank name')" />
                <flux:input wire:model="memberHolder" :label="__('Account holder')" />
                <flux:input wire:model="memberAccountNo" :label="__('Account number')" />
            </div>
        </flux:card>

        {{-- 수수료 / Commission --}}
        <flux:card>
            <div class="mb-3 flex items-center justify-between">
                <flux:heading size="sm">{{ __('Commission') }}</flux:heading>
                <flux:switch wire:model="commissionEnabled" :label="__('Enabled')" align="right" />
            </div>
            <flux:radio.group wire:model="commissionType" :label="__('Commission type')" variant="segmented" class="mb-4">
                <flux:radio value="betting" :label="__('Betting')" />
                <flux:radio value="losing" :label="__('Losing')" />
            </flux:radio.group>
            <div class="grid gap-4 md:grid-cols-3">
                <flux:input wire:model="casinoBaccarat" type="number" :label="__('Casino · Baccarat %')" />
                <flux:input wire:model="casinoSlot" type="number" :label="__('Casino · Slot %')" />
                <flux:input wire:model="casinoLosing" type="number" :label="__('Casino · Losing %')" />
            </div>
        </flux:card>
    </form>
</div>
```

- [ ] **Step 2: Verify routes resolve + render**

```bash
php artisan view:clear
php artisan route:list --path=admin/members
php artisan test --compact 2>&1 | tail -5
```
Expected: `members.create` and `members.edit` resolve; suite green.

- [ ] **Step 3: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add "resources/views/pages/admin/members/⚡form.blade.php"
git commit -m "feat: member create/edit as a normal full page

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 8: Live users page

**Files:**
- Create: `resources/views/pages/admin/members/⚡live.blade.php`

- [ ] **Step 1: Create the page**

Create `resources/views/pages/admin/members/⚡live.blade.php`:
```blade
<?php

use App\Support\AdminDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Live Users')] #[Layout('layouts.admin')] class extends Component {
    /** @var array<int, array<string, mixed>> */
    public array $users = [];

    public function mount(): void
    {
        $this->users = AdminDemoData::liveUsers();
    }
}; ?>

<div class="flex flex-col">
    <x-admin.page-header :title="__('Live Users (Members)')" />

    <x-admin.table>
        <x-slot:head>
            <tr>
                @foreach (['Level / ID', 'Joined', 'IP', 'Domain', 'Status'] as $h)
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __($h) }}</th>
                @endforeach
            </tr>
        </x-slot:head>

        @foreach ($users as $u)
            <tr wire:key="live-{{ $u['username'] }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2"><x-admin.level-badge :level="$u['level']" :id="$u['username']" /></td>
                <td class="px-3 py-2">{{ $u['joinedAt'] }}</td>
                <td class="px-3 py-2 tabular-nums">{{ $u['ip'] }}</td>
                <td class="px-3 py-2">{{ $u['domain'] }}</td>
                <td class="px-3 py-2"><flux:badge color="green" size="sm" inset="top bottom">{{ __('Online') }}</flux:badge></td>
            </tr>
        @endforeach
    </x-admin.table>
</div>
```

- [ ] **Step 2: Verify + commit**

```bash
php artisan view:clear && php artisan route:list --path=admin/members/live
vendor/bin/pint --dirty --format agent
git add "resources/views/pages/admin/members/⚡live.blade.php"
git commit -m "feat: live users (members) page

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 9: Korean translations

**Files:**
- Modify: `lang/ko.json`

- [ ] **Step 1: Add the new keys**

Add these key/value pairs to `lang/ko.json` (insert before the closing `}`; ensure the preceding line ends with a comma). Do not duplicate keys that already exist (e.g. "Search", "Cancel", "Save", "Delete", "Edit", "Status", "Phone", "Level", "Nickname", "Points", "Online" may already be present — skip those):
```json
    "Members": "회원",
    "Member List": "회원목록",
    "Live Users": "현재 접속자",
    "Live Users (Members)": "현재 접속자 (회원)",
    "Member Management": "회원관리",
    "Agents": "매장",
    "Transactions": "입출금",
    "Coming soon": "준비 중",
    "Notifications": "알림",
    "No notifications": "알림이 없습니다",
    "Language": "언어",
    "From": "시작일",
    "To": "종료일",
    "Today": "오늘",
    "Yesterday": "어제",
    "All": "전체",
    "Keyword": "키워드 입력",
    "Reset": "리셋",
    "Create member": "회원생성",
    "Total members": "총 회원",
    "Total balance": "총 잔액",
    "Total points": "총 포인트",
    "Deposit − Withdraw": "충전 − 환전",
    "Level / ID": "레벨/아이디",
    "Store": "매장",
    "Account details": "계정 세부정보",
    "Balance": "잔액",
    "Commission": "수수료",
    "Deposit": "충전",
    "Withdraw": "환전",
    "Difference": "차액",
    "Login": "로그인",
    "Last access": "최근접속",
    "IP": "접근아이피",
    "Joined": "가입일자",
    "Domain": "접근도메인",
    "Manage": "관리",
    "Money": "머니",
    "Normal": "정상",
    "Suspended": "정지",
    "Withdrawn": "탈퇴",
    "Delete member": "회원 삭제",
    "Are you sure you want to delete this member? This action cannot be undone.": "이 회원을 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.",
    "Member deleted": "회원이 삭제되었습니다.",
    "Edit member": "회원 수정",
    "Member created": "회원이 생성되었습니다.",
    "Member updated": "회원이 수정되었습니다.",
    "Account": "계정",
    "Type": "유형",
    "Offline": "오프라인",
    "Online": "온라인",
    "Member status": "회원상태",
    "Store (upline)": "매장",
    "Recommend code or username": "추천 코드 또는 사용자 이름 입력",
    "Username": "아이디",
    "Password": "비밀번호",
    "Confirm password": "비밀번호 확인",
    "Withdraw password / PIN": "환전 비밀번호/핀번호",
    "Email": "이메일 주소",
    "Memo": "메모",
    "Bank account": "계좌정보",
    "Enabled": "활성화",
    "Bank name": "은행 이름",
    "Account holder": "예금주",
    "Account number": "계좌 번호",
    "Commission type": "수수료 유형",
    "Betting": "베팅",
    "Losing": "루징",
    "Casino · Baccarat %": "카지노 · 바카라 %",
    "Casino · Slot %": "카지노 · 슬롯 %",
    "Casino · Losing %": "카지노 · 루징 %"
```

(If a key already exists in the file, leave the existing one and drop the duplicate from this block — JSON cannot have duplicate keys.)

- [ ] **Step 2: Validate JSON + commit**

```bash
php -r "json_decode(file_get_contents('lang/ko.json'), true, 512, JSON_THROW_ON_ERROR); echo 'valid';"
git add lang/ko.json
git commit -m "feat: Korean translations for admin member pages

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 10: Tests

**Files:**
- Create: `tests/Feature/Admin/MemberPagesTest.php`
- Create: `tests/Browser/Admin/MemberTest.php`

- [ ] **Step 1: Feature tests (render + guard + ko)**

Create `tests/Feature/Admin/MemberPagesTest.php`:
```php
<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('renders the member pages', function (string $url, string $see) {
    actingAs($this->user)->get($url)->assertOk()->assertSee($see);
})->with([
    ['/admin/members', 'Member Management'],
    ['/admin/members/create', 'Create member'],
    ['/admin/members/1/edit', 'Edit member'],
    ['/admin/members/live', 'Live Users'],
]);

it('guards member pages behind auth', function (string $url) {
    $this->get($url)->assertRedirect('/login');
})->with([
    '/admin/members',
    '/admin/members/create',
    '/admin/members/live',
]);

it('renders member management in Korean', function () {
    actingAs($this->user)
        ->withSession(['locale' => 'ko'])
        ->get('/admin/members')
        ->assertOk()
        ->assertSee('회원관리')
        ->assertSee('회원생성');
});
```

- [ ] **Step 2: Run feature tests**

Run: `php artisan test --compact --filter=MemberPagesTest`
Expected: PASS. (If a Korean assertion fails because that key was a pre-existing duplicate left untranslated, verify the key exists in `lang/ko.json`.)

- [ ] **Step 3: Browser tests**

Create `tests/Browser/Admin/MemberTest.php`:
```php
<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('loads member pages without JS errors', function () {
    actingAs($this->user);

    foreach (['/admin/members', '/admin/members/create', '/admin/members/live'] as $url) {
        visit($url)->assertNoJavascriptErrors();
    }
});

it('shows the 18-column list with summary stats', function () {
    actingAs($this->user);

    visit('/admin/members')
        ->assertSee('Member Management')
        ->assertSee('Total members')
        ->assertScript('document.querySelectorAll("table thead th").length === 18', true);
});

it('renders the create form sections', function () {
    actingAs($this->user);

    visit('/admin/members/create')
        ->assertSee('Account')
        ->assertSee('Bank account')
        ->assertSee('Commission');
});

it('deletes a member through a Flux modal', function () {
    actingAs($this->user);

    visit('/admin/members')
        ->click('Delete')
        ->assertSee('This action cannot be undone.')
        ->click('[data-test="confirm-delete"]')
        ->assertNoJavascriptErrors();
});
```

- [ ] **Step 4: Build + run full suite**

```bash
npm run build
php artisan test --compact
```
Expected: all green (existing + new). If the 18-column assertion fails, count the `<th>` in the list head and reconcile (must be exactly 18).

- [ ] **Step 5: Commit**

```bash
git add tests/Feature/Admin/MemberPagesTest.php tests/Browser/Admin/MemberTest.php
git commit -m "test: admin member pages (feature + browser smoke)

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

## Final verification
- [ ] `npm run build` succeeds.
- [ ] `php artisan test --compact` — all green.
- [ ] `vendor/bin/pint --test --format agent` — clean.
- [ ] Manual (Herd): `/admin/members` shows the theme-following sidebar with summary block, header action badges + notifications, status tabs, filter bar, stat strip, 18-col scrolling table, status switch, delete modal; light/dark toggle flips the whole shell incl. sidebar; ko locale renders Korean; Create member opens a normal page with the three sections.

## Notes for later slices (not in this plan)
- Agents (28-col list + create-as-page with permission toggles + betting limits), transactions (6 sub-tabs + 대기/완료/취소 workflow via `row-actions`), betting (grouped-commission tables), money/settlement/board/coupons/settings.
- Real backend: models, agent hierarchy, money/ledger (race-safe), auth/permissions, HonorLink, real persistence/search/export, the 머니 balance-adjust action, multi-member create.
