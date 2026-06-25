# Admin Template UI (Frontend) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Recreate the vue-element-admin look (dark/light themeable sidebar, indigo accent, sharp edges, Pretendard font, a persistent "history tabs" strip, RBAC admin pages, dashboard stat cards) inside this Livewire Flux starter kit — frontend only, on demo data.

**Architecture:** Restyle the existing Flux sidebar layout via CSS theme tokens, add a persistent desktop header + an Alpine-powered tags-view strip, and build Users/Roles/Permissions as Chisel single-file Livewire components holding in-memory demo arrays. No database, no authorization, no new Composer/NPM dependency this phase.

**Tech Stack:** Laravel 13, Livewire 4 (Chisel SFC pages), Flux Free 2, Tailwind v4, Alpine (bundled with Livewire, incl. `persist` plugin), Pest 4 browser tests, Pretendard (self-hosted woff2).

**Spec:** `docs/superpowers/specs/2026-06-02-admin-template-ui-design.md`

**Conventions discovered (follow these):**
- Active layout is `resources/views/layouts/app/sidebar.blade.php` (wrapped by `layouts/app.blade.php` → `<x-layouts::app.sidebar>` → `<flux:main>`).
- Chisel pages live in `resources/views/pages/...` as `new class extends Component { ... }` SFCs with `#[Title('...')]`, registered with `Route::livewire('url', 'pages::dot.path')->name('...')`.
- Run `vendor/bin/pint --dirty --format agent` after PHP edits.
- This dir is NOT a git repo. **Before Task 1**, run `git init` so the per-task commits work. If the user declined git, skip every "Commit" step.

---

### Task 0: Initialize git (one-time)

**Files:** none

- [ ] **Step 1: Init repo and baseline commit**

```bash
cd /Users/alvinmanaros/Documents/Code/Laravel/ara-laravel-template
git init
printf '/.superpowers/\n' >> .gitignore
git add -A
git commit -m "chore: baseline before admin template UI"
```
Expected: repo created, initial commit succeeds. (If user declined git, skip this task and all later Commit steps.)

---

### Task 1: Self-host Pretendard font

**Files:**
- Create: `public/fonts/PretendardVariable.woff2`
- Modify: `resources/css/app.css`

- [ ] **Step 1: Download the Pretendard variable woff2**

```bash
mkdir -p public/fonts
curl -fsSL -o public/fonts/PretendardVariable.woff2 \
  https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/woff2/PretendardVariable.woff2
ls -la public/fonts/PretendardVariable.woff2
```
Expected: file exists, size ~1MB.

- [ ] **Step 2: Add @font-face and set the font token**

In `resources/css/app.css`, immediately after the `@import` lines (after line 7) add:

```css
@font-face {
    font-family: 'Pretendard';
    font-weight: 100 900;
    font-display: swap;
    font-style: normal;
    src: url('/fonts/PretendardVariable.woff2') format('woff2-variations');
}
```

Then replace the existing `--font-sans:` line inside `@theme { … }` with:

```css
    --font-sans: 'Pretendard', ui-sans-serif, system-ui, -apple-system, 'Apple SD Gothic Neo', 'Malgun Gothic', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
```

- [ ] **Step 3: Build and verify the font loads**

```bash
npm run build
```
Expected: build succeeds, no errors. (Manual visual check happens in Task 11's browser test.)

- [ ] **Step 4: Commit**

```bash
git add public/fonts/PretendardVariable.woff2 resources/css/app.css
git commit -m "feat: self-host Pretendard font"
```

---

### Task 2: Indigo accent, sharp edges, density tokens

**Files:**
- Modify: `resources/css/app.css`

- [ ] **Step 1: Set the indigo accent tokens**

In `resources/css/app.css`, replace the three `--color-accent*` lines inside `@theme { … }` with:

```css
    --color-accent: #4f46e5;            /* indigo-600 */
    --color-accent-content: #4f46e5;
    --color-accent-foreground: #ffffff;
```

And replace the `.dark { … }` block inside `@layer theme` with:

```css
    .dark {
        --color-accent: #818cf8;        /* indigo-400 — pops on dark */
        --color-accent-content: #818cf8;
        --color-accent-foreground: #1e1b4b;
    }
```

- [ ] **Step 2: Sharpen all corners via Tailwind v4 radius tokens**

Inside the same `@theme { … }` block, add these lines (Tailwind v4 maps every `rounded-*` utility to these vars; `rounded-full` uses a literal 9999px and is intentionally left alone so avatars stay round):

```css
    --radius-xs: 2px;
    --radius-sm: 2px;
    --radius-md: 2px;
    --radius-lg: 2px;
    --radius-xl: 2px;
    --radius-2xl: 2px;
    --radius-3xl: 2px;
    --radius-4xl: 2px;
```

- [ ] **Step 3: Add density rule**

At the end of `resources/css/app.css` add:

```css
@layer base {
    body {
        font-size: 0.8125rem;   /* 13px admin-dense base */
    }
}
```

- [ ] **Step 4: Build**

```bash
npm run build
```
Expected: build succeeds.

- [ ] **Step 5: Commit**

```bash
git add resources/css/app.css
git commit -m "feat: indigo accent, sharp edges, dense typography"
```

---

### Task 3: Persistent desktop header with breadcrumb + tools

**Files:**
- Modify: `resources/views/layouts/app/sidebar.blade.php`
- Create: `resources/views/components/page-meta.blade.php`

**Context:** The current sidebar layout only renders a header on mobile (`lg:hidden`). We add a persistent desktop header bar (collapse toggle · breadcrumb · spacer · fullscreen · appearance toggle · profile) above the page content. Breadcrumb data comes from an Alpine store populated by a hidden `<x-page-meta>` element each page renders (also used by the tags-view in Task 4).

- [ ] **Step 1: Create the page-meta component**

Create `resources/views/components/page-meta.blade.php`:

```blade
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
```

- [ ] **Step 2: Add the persistent desktop header to the sidebar layout**

In `resources/views/layouts/app/sidebar.blade.php`, find the line `{{ $slot }}` (line 91) and replace it with:

```blade
        {{-- Persistent desktop top bar --}}
        <flux:header class="max-lg:hidden! border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:sidebar.collapse class="max-lg:hidden" />

            {{-- Plain Alpine-driven breadcrumb (Flux components don't clone reliably inside x-for) --}}
            <nav class="ms-2 flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400" x-data>
                <a :href="'{{ route('dashboard') }}'" wire:navigate class="flex items-center gap-1 hover:text-accent">
                    <flux:icon icon="home" class="size-3.5" />
                    <span>{{ __('Home') }}</span>
                </a>
                <template x-for="(crumb, i) in $store.nav.crumbs" :key="i">
                    <span class="flex items-center gap-1.5">
                        <span class="text-zinc-300 dark:text-zinc-600">/</span>
                        <span x-text="crumb" :class="i === $store.nav.crumbs.length - 1 ? 'text-zinc-800 dark:text-zinc-200' : ''"></span>
                    </span>
                </template>
            </nav>

            <flux:spacer />

            <flux:button x-data icon="arrows-pointing-out" variant="subtle" size="sm" x-on:click="document.documentElement.requestFullscreen?.()" :tooltip="__('Fullscreen')" />
            <flux:button :href="route('appearance.edit')" wire:navigate icon="sun" variant="subtle" size="sm" :tooltip="__('Appearance')" />

            <x-desktop-user-menu />
        </flux:header>

        {{ $slot }}
```

- [ ] **Step 3: Register the `nav` Alpine store**

Open `resources/js/app.js` and append:

```js
document.addEventListener('alpine:init', () => {
    Alpine.store('nav', { crumbs: [] });
});
```

- [ ] **Step 4: Build and sanity check**

```bash
npm run build
```
Expected: build succeeds (breadcrumb will be empty until Task 4 wires the store on navigation; that is fine).

- [ ] **Step 5: Commit**

```bash
git add resources/views/layouts/app/sidebar.blade.php resources/views/components/page-meta.blade.php resources/js/app.js
git commit -m "feat: persistent desktop header with breadcrumb + tools"
```

---

### Task 4: Tags-view (history tabs) strip

**Files:**
- Create: `resources/views/partials/tags-view.blade.php`
- Modify: `resources/views/layouts/app/sidebar.blade.php`
- Modify: `resources/views/dashboard.blade.php` (add `<x-page-meta>` so Dashboard registers its pinned tab)

**Context:** Pure Alpine + Blade. State persists to localStorage so tabs survive a hard reload. The strip lives in `@persist('tags-view')` so it is not torn down on `wire:navigate` swaps. On `livewire:navigated` it reads `#page-meta` and upserts the page; it also updates the `nav` store's breadcrumb.

- [ ] **Step 1: Create the tags-view partial**

Create `resources/views/partials/tags-view.blade.php`:

```blade
<div
    class="max-lg:hidden flex items-stretch gap-1 overflow-x-auto border-b border-zinc-200 bg-zinc-50 px-3 py-1.5 dark:border-zinc-700 dark:bg-zinc-900"
    x-data="tagsView"
    x-init="init()"
    @contextmenu.away="closeMenu()"
>
    <template x-for="tab in tabs" :key="tab.path">
        <div class="group relative flex shrink-0">
            <a
                :href="tab.path.startsWith('/') ? tab.path : '/' + tab.path"
                wire:navigate
                @contextmenu.prevent="openMenu($event, tab)"
                class="flex items-center gap-1.5 border px-2.5 py-1 text-xs transition-colors"
                :class="isActive(tab)
                    ? 'border-accent bg-accent text-[color:var(--color-accent-foreground)]'
                    : 'border-zinc-200 bg-white text-zinc-600 hover:text-accent dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300'"
            >
                <span class="size-1.5 rounded-full" :class="isActive(tab) ? 'bg-[color:var(--color-accent-foreground)]' : 'bg-zinc-400'"></span>
                <span x-text="tab.title"></span>
                <button
                    x-show="!tab.affix"
                    @click.prevent.stop="closeTab(tab)"
                    class="ms-1 rounded-full px-1 leading-none opacity-60 hover:bg-black/10 hover:opacity-100 dark:hover:bg-white/10"
                >&times;</button>
            </a>
        </div>
    </template>

    {{-- Right-click context menu --}}
    <div
        x-show="menu.open"
        x-cloak
        :style="`position:fixed; left:${menu.x}px; top:${menu.y}px; z-index:50`"
        class="min-w-36 border border-zinc-200 bg-white py-1 text-xs shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
        @click.outside="closeMenu()"
    >
        <button class="block w-full px-3 py-1.5 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700" @click="refreshTab()">{{ __('Refresh') }}</button>
        <button class="block w-full px-3 py-1.5 text-left hover:bg-zinc-100 disabled:opacity-40 dark:hover:bg-zinc-700" :disabled="menu.tab?.affix" @click="closeTab(menu.tab); closeMenu()">{{ __('Close') }}</button>
        <button class="block w-full px-3 py-1.5 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700" @click="closeOthers(menu.tab); closeMenu()">{{ __('Close Others') }}</button>
        <button class="block w-full px-3 py-1.5 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700" @click="closeAll(); closeMenu()">{{ __('Close All') }}</button>
    </div>
</div>

@script
<script>
Alpine.data('tagsView', () => ({
    tabs: Alpine.$persist([{ title: 'Dashboard', path: '/dashboard', route: 'dashboard', affix: true }]).as('admin-tabs'),
    menu: { open: false, x: 0, y: 0, tab: null },

    init() {
        this.capture();
        document.addEventListener('livewire:navigated', () => this.capture());
    },

    capture() {
        const el = document.getElementById('page-meta');
        if (! el) return;
        const path = '/' + (el.dataset.path || '').replace(/^\/+/, '');
        const tab = {
            title: el.dataset.title || 'Untitled',
            path,
            route: el.dataset.route || '',
            affix: path === '/dashboard',
        };
        if (tab.title && ! this.tabs.some(t => t.path === tab.path)) {
            this.tabs.push(tab);
        }
        // update breadcrumb store
        let crumbs = [];
        try { crumbs = JSON.parse(el.dataset.breadcrumb || '[]'); } catch (e) {}
        Alpine.store('nav').crumbs = crumbs;
    },

    isActive(tab) {
        return window.location.pathname.replace(/\/+$/, '') === tab.path.replace(/\/+$/, '');
    },

    closeTab(tab) {
        if (! tab || tab.affix) return;
        const wasActive = this.isActive(tab);
        const idx = this.tabs.findIndex(t => t.path === tab.path);
        this.tabs = this.tabs.filter(t => t.path !== tab.path);
        if (wasActive) {
            const next = this.tabs[idx] || this.tabs[idx - 1] || this.tabs[0];
            if (next) Livewire.navigate(next.path);
        }
    },

    closeOthers(tab) {
        this.tabs = this.tabs.filter(t => t.affix || t.path === tab.path);
    },

    closeAll() {
        this.tabs = this.tabs.filter(t => t.affix);
        const dash = this.tabs[0];
        if (dash) Livewire.navigate(dash.path);
    },

    openMenu(e, tab) {
        this.menu = { open: true, x: e.clientX, y: e.clientY, tab };
    },
    closeMenu() { this.menu.open = false; },
    refreshTab() { this.closeMenu(); Livewire.navigate(window.location.pathname); },
}));
</script>
@endscript
```

- [ ] **Step 2: Mount the strip in the layout, persisted**

In `resources/views/layouts/app/sidebar.blade.php`, directly after the closing `</flux:header>` you added in Task 3 (and before `{{ $slot }}`), insert:

```blade
        @persist('tags-view')
            @include('partials.tags-view')
        @endpersist
```

- [ ] **Step 3: Give the Dashboard a page-meta tag (pins its tab + sets breadcrumb)**

In `resources/views/dashboard.blade.php`, immediately after the opening `<x-layouts::app :title="__('Dashboard')">` line, add:

```blade
    <x-page-meta title="Dashboard" route="dashboard" :breadcrumb="[]" />
```

- [ ] **Step 4: Build**

```bash
npm run build
```
Expected: build succeeds.

- [ ] **Step 5: Commit**

```bash
git add resources/views/partials/tags-view.blade.php resources/views/layouts/app/sidebar.blade.php resources/views/dashboard.blade.php
git commit -m "feat: persistent tags-view history strip"
```

---

### Task 5: Stat-card component + dashboard

**Files:**
- Create: `resources/views/components/stat-card.blade.php`
- Modify: `resources/views/dashboard.blade.php`

- [ ] **Step 1: Create the stat-card component**

Create `resources/views/components/stat-card.blade.php`:

```blade
@props([
    'label' => '',
    'value' => '0',
    'sublabel' => '',
    'icon' => 'chart-bar',
    'color' => 'indigo',   // indigo | emerald | violet
])

@php
    $palette = [
        'emerald' => 'bg-emerald-500',
        'indigo'  => 'bg-indigo-500',
        'violet'  => 'bg-violet-500',
    ];
    $bg = $palette[$color] ?? $palette['indigo'];
@endphp

<div {{ $attributes->merge(['class' => "relative overflow-hidden p-4 text-white $bg"]) }}>
    <flux:icon :icon="$icon" class="absolute -right-2 -bottom-2 size-20 opacity-15" />
    <div class="text-xs font-medium opacity-90">{{ $label }}</div>
    <div class="mt-1 text-3xl font-bold tabular-nums">{{ $value }}</div>
    <div class="mt-1 text-[11px] opacity-80">{{ $sublabel }}</div>
</div>
```

- [ ] **Step 2: Replace the dashboard placeholders with stat cards**

In `resources/views/dashboard.blade.php`, replace the entire `<div class="grid auto-rows-min gap-4 md:grid-cols-3"> … </div>` block (the three placeholder cards) with:

```blade
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <x-stat-card :label="__('Activity')" value="128" :sublabel="__('Total activities')" icon="bolt" color="emerald" />
            <x-stat-card :label="__('Members')" value="2,304" :sublabel="__('Registered members')" icon="users" color="indigo" />
            <x-stat-card :label="__('Products')" value="57" :sublabel="__('Catalog items')" icon="cube" color="violet" />
        </div>
```

- [ ] **Step 3: Build**

```bash
npm run build
```
Expected: build succeeds.

- [ ] **Step 4: Commit**

```bash
git add resources/views/components/stat-card.blade.php resources/views/dashboard.blade.php
git commit -m "feat: dashboard stat cards"
```

---

### Task 6: Admin routes file + Users page (table, modal, toggle, pagination on demo data)

**Files:**
- Create: `routes/admin.php`
- Modify: `routes/web.php`
- Create: `resources/views/pages/admin/users/⚡index.blade.php`

**Context:** Chisel SFC page holding a demo array. Pagination uses a `LengthAwarePaginator` built from the array in a computed property so `<flux:pagination>` works. The Add/Edit modal is a `<flux:modal>` toggled with `Flux::modal()`.

- [ ] **Step 1: Create the admin routes file**

Create `routes/admin.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('users', 'pages::admin.users.index')->name('users');
    Route::livewire('roles', 'pages::admin.roles.index')->name('roles');
    Route::livewire('permissions', 'pages::admin.permissions.index')->name('permissions');
});
```

- [ ] **Step 2: Require it from web.php**

In `routes/web.php`, after the line `require __DIR__.'/settings.php';` add:

```php
require __DIR__.'/admin.php';
```

- [ ] **Step 3: Create the Users page**

Create `resources/views/pages/admin/users/⚡index.blade.php`:

```blade
<?php

use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('User Management')] class extends Component {
    use WithPagination;

    public string $search = '';

    /** @var array<int, array{id:int,nickname:string,login:string,phone:string,role:string,enabled:bool}> */
    public array $users = [];

    // Modal form state
    public ?int $editingId = null;
    public string $form_nickname = '';
    public string $form_login = '';
    public string $form_phone = '';
    public string $form_role = 'user';

    public function mount(): void
    {
        $this->users = [
            ['id' => 1, 'nickname' => 'admin',  'login' => 'admin',  'phone' => '13577728948', 'role' => 'admin',  'enabled' => true],
            ['id' => 2, 'nickname' => 'test',   'login' => 'test',   'phone' => '18908775870', 'role' => 'admin',  'enabled' => true],
            ['id' => 3, 'nickname' => 'operator','login' => 'system','phone' => '13708779424', 'role' => 'system', 'enabled' => true],
            ['id' => 4, 'nickname' => 'xpyzwm', 'login' => 'xpyzwm', 'phone' => '13577712345', 'role' => 'user',   'enabled' => false],
        ];
    }

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        $filtered = collect($this->users)
            ->when($this->search !== '', fn ($c) => $c->filter(
                fn ($u) => str_contains(strtolower($u['nickname'].$u['login'].$u['phone']), strtolower($this->search))
            ))
            ->values();

        $perPage = 10;
        $page = $this->getPage();

        return new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => request()->url()],
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $this->users = collect($this->users)->map(function ($u) use ($id) {
            if ($u['id'] === $id) {
                $u['enabled'] = ! $u['enabled'];
            }

            return $u;
        })->all();
    }

    public function createUser(): void
    {
        $this->reset(['editingId', 'form_nickname', 'form_login', 'form_phone', 'form_role']);
        $this->form_role = 'user';
        Flux::modal('user-form')->show();
    }

    public function editUser(int $id): void
    {
        $user = collect($this->users)->firstWhere('id', $id);
        if (! $user) {
            return;
        }
        $this->editingId = $user['id'];
        $this->form_nickname = $user['nickname'];
        $this->form_login = $user['login'];
        $this->form_phone = $user['phone'];
        $this->form_role = $user['role'];
        Flux::modal('user-form')->show();
    }

    public function saveUser(): void
    {
        $validated = $this->validate([
            'form_nickname' => ['required', 'string', 'max:50'],
            'form_login' => ['required', 'string', 'max:50'],
            'form_phone' => ['required', 'string', 'max:20'],
            'form_role' => ['required', 'in:admin,system,user'],
        ]);

        if ($this->editingId) {
            $this->users = collect($this->users)->map(function ($u) use ($validated) {
                if ($u['id'] === $this->editingId) {
                    $u['nickname'] = $validated['form_nickname'];
                    $u['login'] = $validated['form_login'];
                    $u['phone'] = $validated['form_phone'];
                    $u['role'] = $validated['form_role'];
                }

                return $u;
            })->all();
        } else {
            $this->users[] = [
                'id' => (collect($this->users)->max('id') ?? 0) + 1,
                'nickname' => $validated['form_nickname'],
                'login' => $validated['form_login'],
                'phone' => $validated['form_phone'],
                'role' => $validated['form_role'],
                'enabled' => true,
            ];
        }

        Flux::modal('user-form')->close();
        Flux::toast(text: __('Saved'), variant: 'success');
    }

    public function deleteUser(int $id): void
    {
        $this->users = collect($this->users)->reject(fn ($u) => $u['id'] === $id)->values()->all();
    }

    public function roleColor(string $role): string
    {
        return match ($role) {
            'admin' => 'indigo',
            'system' => 'amber',
            default => 'zinc',
        };
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta title="Users" route="admin.users" :breadcrumb="['System', 'Users']" />

    <div class="flex items-center justify-between gap-3">
        <flux:heading size="lg">{{ __('User Management') }}</flux:heading>
        <div class="flex items-center gap-2">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" size="sm" :placeholder="__('Search')" class="w-56" />
            <flux:button wire:click="createUser" icon="plus" variant="primary" size="sm">{{ __('Add user') }}</flux:button>
        </div>
    </div>

    <flux:table :paginate="$this->rows">
        <flux:table.columns>
            <flux:table.column>{{ __('ID') }}</flux:table.column>
            <flux:table.column>{{ __('Nickname') }}</flux:table.column>
            <flux:table.column>{{ __('Login') }}</flux:table.column>
            <flux:table.column>{{ __('Phone') }}</flux:table.column>
            <flux:table.column>{{ __('Role') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->rows as $user)
                <flux:table.row :key="$user['id']">
                    <flux:table.cell>{{ $user['id'] }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ $user['nickname'] }}</flux:table.cell>
                    <flux:table.cell>{{ $user['login'] }}</flux:table.cell>
                    <flux:table.cell class="tabular-nums">{{ $user['phone'] }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$this->roleColor($user['role'])" size="sm" inset="top bottom">{{ $user['role'] }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:switch :checked="$user['enabled']" wire:click="toggleStatus({{ $user['id'] }})" />
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button wire:click="editUser({{ $user['id'] }})" size="xs" variant="subtle">{{ __('Edit') }}</flux:button>
                        <flux:button size="xs" variant="subtle">{{ __('Reset password') }}</flux:button>
                        <flux:button wire:click="deleteUser({{ $user['id'] }})" wire:confirm="{{ __('Delete this user?') }}" size="xs" variant="danger">{{ __('Delete') }}</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Add / Edit modal --}}
    <flux:modal name="user-form" class="w-full max-w-md">
        <form wire:submit="saveUser" class="flex flex-col gap-4">
            <flux:heading size="lg">{{ $editingId ? __('Edit user') : __('Add user') }}</flux:heading>
            <flux:input wire:model="form_nickname" :label="__('Nickname')" />
            <flux:input wire:model="form_login" :label="__('Login')" />
            <flux:input wire:model="form_phone" :label="__('Phone')" />
            <flux:select wire:model="form_role" :label="__('Role')">
                <flux:select.option value="admin">admin</flux:select.option>
                <flux:select.option value="system">system</flux:select.option>
                <flux:select.option value="user">user</flux:select.option>
            </flux:select>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
```

- [ ] **Step 4: Verify the route resolves**

```bash
php artisan route:list --path=admin
```
Expected: lists `admin/users`, `admin/roles`, `admin/permissions` (roles/permissions pages come in Task 7 — their routes appear now but 500 until created; that is expected mid-plan).

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add routes/admin.php routes/web.php "resources/views/pages/admin/users/⚡index.blade.php"
git commit -m "feat: admin users page (demo data)"
```

---

### Task 7: Admin Roles page (table + permission-assignment UI)

**Files:**
- Create: `resources/views/pages/admin/roles/⚡index.blade.php`

- [ ] **Step 1: Create the Roles page**

Create `resources/views/pages/admin/roles/⚡index.blade.php`:

```blade
<?php

use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Role Management')] class extends Component {
    /** @var array<int, array{id:int,name:string,label:string,users:int}> */
    public array $roles = [];

    /** @var array<string, array<int, string>> module => permission keys */
    public array $modules = [];

    public ?int $assigningRoleId = null;

    /** @var array<int, string> */
    public array $assigned = [];

    public function mount(): void
    {
        $this->roles = [
            ['id' => 1, 'name' => 'admin',  'label' => 'Administrator', 'users' => 2],
            ['id' => 2, 'name' => 'system', 'label' => 'Operator',      'users' => 1],
            ['id' => 3, 'name' => 'user',   'label' => 'Member',        'users' => 1],
        ];

        $this->modules = [
            'System' => ['users.view', 'users.create', 'users.edit', 'users.delete'],
            'Roles' => ['roles.view', 'roles.assign'],
            'Content' => ['content.view', 'content.publish'],
        ];
    }

    public function assign(int $roleId): void
    {
        $this->assigningRoleId = $roleId;
        // demo: admin gets everything, others get view-only
        $role = collect($this->roles)->firstWhere('id', $roleId);
        $all = collect($this->modules)->flatten()->all();
        $this->assigned = ($role['name'] ?? '') === 'admin'
            ? $all
            : array_values(array_filter($all, fn ($p) => str_ends_with($p, '.view')));

        Flux::modal('role-permissions')->show();
    }

    public function saveAssignment(): void
    {
        // Frontend-only phase: no persistence yet.
        Flux::modal('role-permissions')->close();
        Flux::toast(text: __('Permissions updated'), variant: 'success');
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta title="Roles" route="admin.roles" :breadcrumb="['System', 'Roles']" />

    <flux:heading size="lg">{{ __('Role Management') }}</flux:heading>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('ID') }}</flux:table.column>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Label') }}</flux:table.column>
            <flux:table.column>{{ __('Users') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($roles as $role)
                <flux:table.row :key="$role['id']">
                    <flux:table.cell>{{ $role['id'] }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="indigo" size="sm" inset="top bottom">{{ $role['name'] }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell variant="strong">{{ $role['label'] }}</flux:table.cell>
                    <flux:table.cell class="tabular-nums">{{ $role['users'] }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button wire:click="assign({{ $role['id'] }})" size="xs" variant="subtle" icon="key">{{ __('Permissions') }}</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:modal name="role-permissions" class="w-full max-w-lg">
        <form wire:submit="saveAssignment" class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Assign permissions') }}</flux:heading>
            <div class="flex flex-col gap-4">
                @foreach ($modules as $module => $permissions)
                    <div>
                        <div class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ $module }}</div>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($permissions as $permission)
                                <flux:checkbox wire:model="assigned" value="{{ $permission }}" :label="$permission" />
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
```

- [ ] **Step 2: Verify it loads**

```bash
php artisan route:list --path=admin/roles
```
Expected: `admin/roles` listed.

- [ ] **Step 3: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add "resources/views/pages/admin/roles/⚡index.blade.php"
git commit -m "feat: admin roles page with permission-assignment UI (demo)"
```

---

### Task 8: Admin Permissions page (grouped display)

**Files:**
- Create: `resources/views/pages/admin/permissions/⚡index.blade.php`

- [ ] **Step 1: Create the Permissions page**

Create `resources/views/pages/admin/permissions/⚡index.blade.php`:

```blade
<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Permissions')] class extends Component {
    /** @var array<string, array<int, array{key:string,label:string}>> */
    public array $groups = [];

    public function mount(): void
    {
        $this->groups = [
            'System' => [
                ['key' => 'users.view', 'label' => 'View users'],
                ['key' => 'users.create', 'label' => 'Create users'],
                ['key' => 'users.edit', 'label' => 'Edit users'],
                ['key' => 'users.delete', 'label' => 'Delete users'],
            ],
            'Roles' => [
                ['key' => 'roles.view', 'label' => 'View roles'],
                ['key' => 'roles.assign', 'label' => 'Assign permissions'],
            ],
            'Content' => [
                ['key' => 'content.view', 'label' => 'View content'],
                ['key' => 'content.publish', 'label' => 'Publish content'],
            ],
        ];
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta title="Permissions" route="admin.permissions" :breadcrumb="['System', 'Permissions']" />

    <flux:heading size="lg">{{ __('Permissions') }}</flux:heading>

    @foreach ($groups as $group => $permissions)
        <flux:card class="p-0">
            <div class="border-b border-zinc-200 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:border-zinc-700">{{ $group }}</div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Key') }}</flux:table.column>
                    <flux:table.column>{{ __('Description') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($permissions as $permission)
                        <flux:table.row :key="$permission['key']">
                            <flux:table.cell>
                                <flux:badge color="zinc" size="sm" inset="top bottom">{{ $permission['key'] }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $permission['label'] }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @endforeach
</div>
```

- [ ] **Step 2: Verify all admin pages load**

```bash
php artisan route:list --path=admin
```
Expected: `admin/users`, `admin/roles`, `admin/permissions` all listed and resolvable.

- [ ] **Step 3: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add "resources/views/pages/admin/permissions/⚡index.blade.php"
git commit -m "feat: admin permissions page (demo)"
```

---

### Task 9: Wire the sidebar navigation (System group)

**Files:**
- Modify: `resources/views/layouts/app/sidebar.blade.php`

**Context:** Now that the admin routes exist, add the `System` nav group. No permission gating this phase (all items visible).

- [ ] **Step 1: Add the System group**

In `resources/views/layouts/app/sidebar.blade.php`, inside the first `<flux:sidebar.nav>` (the one containing the Platform group, around lines 13-19), after the closing `</flux:sidebar.group>` of Platform, add:

```blade
                <flux:sidebar.group :heading="__('System')" expandable :expanded="request()->routeIs('admin.*')">
                    <flux:sidebar.item icon="users" :href="route('admin.users')" :current="request()->routeIs('admin.users')" wire:navigate>
                        {{ __('Users') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shield-check" :href="route('admin.roles')" :current="request()->routeIs('admin.roles')" wire:navigate>
                        {{ __('Roles') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="key" :href="route('admin.permissions')" :current="request()->routeIs('admin.permissions')" wire:navigate>
                        {{ __('Permissions') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
```

- [ ] **Step 2: Build + manual smoke**

```bash
npm run build
php artisan route:list --path=admin
```
Expected: build succeeds; routes resolve. Open the app (Herd) and confirm the sidebar shows Dashboard + System group; clicking each item navigates and adds a history tab.

- [ ] **Step 3: Commit**

```bash
git add resources/views/layouts/app/sidebar.blade.php
git commit -m "feat: sidebar System navigation group"
```

---

### Task 10: Browser smoke tests

**Files:**
- Create: `tests/Browser/AdminTemplateTest.php`

**Context:** Pest 4 browser tests. They need an authenticated, verified user. Use the existing `User` factory. No DB assertions about admin data (it is in-memory demo data) — only UI behavior + no JS console errors.

- [ ] **Step 1: Write the failing tests**

Create `tests/Browser/AdminTemplateTest.php`:

```php
<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('loads every admin page without JS errors', function () {
    actingAs($this->user);

    foreach (['/dashboard', '/admin/users', '/admin/roles', '/admin/permissions'] as $url) {
        $page = visit($url);
        $page->assertNoJavascriptErrors();
    }
});

it('shows dashboard stat cards', function () {
    actingAs($this->user);

    visit('/dashboard')
        ->assertSee('Members')
        ->assertSee('2,304');
});

it('adds a history tab when visiting users', function () {
    actingAs($this->user);

    // tagsView.init() runs capture() on load, so the tab appears in the strip
    visit('/admin/users')
        ->assertSee('User Management')   // page heading
        ->assertSee('Users');            // tab label in the persisted strip
});

it('opens the add-user modal', function () {
    actingAs($this->user);

    visit('/admin/users')
        ->click('Add user')
        ->assertSee('Nickname');
});

it('toggles a user status switch without error', function () {
    actingAs($this->user);

    $page = visit('/admin/users');
    $page->assertNoJavascriptErrors();
});
```

- [ ] **Step 2: Run to verify they fail (before app verified) or pass**

```bash
php artisan test --compact --filter=AdminTemplateTest
```
Expected: tests run. If any fail, fix the referenced UI (selectors/text) until green. (Browser tests require a working build — run `npm run build` first if assets are stale.)

- [ ] **Step 3: Make green, then commit**

```bash
npm run build
php artisan test --compact --filter=AdminTemplateTest
```
Expected: PASS.

```bash
git add tests/Browser/AdminTemplateTest.php
git commit -m "test: admin template UI browser smoke tests"
```

---

## Final verification

- [ ] `npm run build` succeeds.
- [ ] `php artisan test --compact` — all green.
- [ ] `vendor/bin/pint --test --format agent` — clean (or run `vendor/bin/pint` to fix).
- [ ] Manual (Herd): light/dark toggle flips the whole UI incl. sidebar; indigo accent on active nav/buttons/tabs; corners are sharp; Korean text renders in Pretendard; history tabs add on navigation, close, pin Dashboard, right-click menu works, and survive a hard refresh.

## Notes for the later backend phase (not in this plan)
- Swap each page's in-memory `$users`/`$roles`/`$groups` arrays for Eloquent + spatie/laravel-permission queries.
- Add `@can(...)` gating to sidebar items and route middleware.
- Persist role→permission assignment and user CRUD.
