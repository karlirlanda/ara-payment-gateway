# Admin Template UI — Design Spec

**Date:** 2026-06-02
**Status:** Approved for planning
**Scope of this spec:** Frontend only. Backend (real RBAC, persistence) is a separate later phase.

## 1. Goal

Recreate the *look and feel* of the `wmhello/laravel_template_with_vue` admin panel
(a vue-element-admin / Element UI fork) inside this project — the official Livewire
Flux starter kit (Laravel 13, Livewire 4, Flux 2, Fortify, Chisel file-based pages) —
using the existing stack (Flux Free + Livewire + Tailwind v4). No Vue, no Element UI.

The result is a reusable design template for future projects, tuned for a Korean client
(Korean-capable font, data-dense layout, sharp edges).

This phase delivers the **complete frontend**: theme, layout shell, the history-tabs
strip, the RBAC admin **pages as UI** (on demo data), and dashboard stat cards.
Real authorization, persistence, and data come in a later backend phase.

## 2. Design decisions (locked)

| Decision | Choice | Rationale |
|---|---|---|
| Sidebar across themes | **Follows the theme** (light in light mode, dark in dark mode) — Flux-native | Cleaner/modern, fully themeable; user preference |
| Accent color | **Indigo** — `#4f46e5` (light), `#818cf8` (dark) | Premium/corporate read for an enterprise Korean client |
| Font | **Pretendard**, self-hosted | De-facto modern Korean web font; excellent Hangul + Latin |
| Edges | **Sharp** (~2px / square) | Matches the source template's flat aesthetic |
| Density | Base 13px, tables 12px, tight row padding | Data-dense admin feel |
| Tags-view (history tabs) | **Full faithful clone** (pin, context menu, persist across reload) | User's priority feature |
| RBAC (this phase) | **UI only**, on hardcoded demo data | Frontend-first; backend deferred |
| Sidebar menu (this phase) | Static in Blade, all items visible | Permission-gating deferred to backend phase |

### Deferred to backend phase (explicitly NOT in this spec)
- spatie/laravel-permission, roles/permissions migrations, seeders
- Laravel policies / real authorization
- Permission-driven menu filtering (`@can` gating)
- Real persistence for users/roles/permissions
- Excel user import, article/content management, websocket chat, WeChat,
  low-code generator (all out of scope entirely)

## 3. Build approach

**Design-foundation first** (chosen over backend-first / vertical-slices):
theme tokens → layout shell → tags-view → admin pages (demo data) → dashboard cards → tests.
The template's value is the consistent shell + tags-view + theme, so that is built
solidly first; backend slots into the finished UI later without re-doing it.

## 4. Section detail

### 4.1 Theme & design tokens — `resources/css/app.css`

Single source of truth for re-skinning.

- **Accent**: override Flux tokens
  - Light: `--color-accent` / `--color-accent-content` = `#4f46e5`, `--color-accent-foreground` = white
  - Dark: `--color-accent` / `--color-accent-content` = `#818cf8`, foreground = dark
  - Recolors active nav, primary buttons, active tab, links, focus rings automatically.
- **Font**: `@font-face` for Pretendard (woff2 self-hosted in `public/fonts/`, no npm/composer dep).
  `--font-sans: 'Pretendard', ui-sans-serif, system-ui, 'Apple SD Gothic Neo', sans-serif, …`
- **Sharp edges**: a CSS layer setting `border-radius` to a `--radius-sharp` token (~2px) on
  Flux controls, cards, badges, and tabs. One variable → easy to dial roundness back up.
- **Density**: base `font-size: 13px`; table cells `12px`; tighter vertical padding on table rows
  and menu items.

### 4.2 Layout shell — `resources/views/layouts/app/sidebar.blade.php` (+ partials)

Keeps Flux components; themed + restructured.

- **Sidebar** (theme-following): brand header, collapsible. Nav groups:
  - `Dashboard`
  - `System` group → `Users`, `Roles`, `Permissions`
  - All items visible this phase (no permission gating yet).
- **Persistent desktop header** (new — starter kit only had a mobile header):
  sidebar collapse toggle · breadcrumb (left) · spacer · fullscreen toggle ·
  appearance (light/dark) toggle · profile dropdown (right).
- **Tags-view strip** directly under the header (see 4.3).
- **Breadcrumb** derived from the current route.

### 4.3 Tags-view (history-tabs strip)

Pure Alpine + Blade. No backend, no new dependency.

- **State**: Alpine component `tagsView` backed by `Alpine.$persist(...)` → localStorage
  (this is what survives a hard reload, not just SPA swaps).
- **Capture**: layout renders the current page's tab metadata (title, route name, path)
  in a small element; on `livewire:navigated` the store upserts that page (de-duplicated).
  Each admin page declares its tab title via a `@push`/section.
- **No-flicker persistence**: strip wrapped in `@persist('tags-view')` so the DOM node and
  Alpine state are not torn down on navigation.
- **Behaviors**:
  - **Dashboard pinned** — always present, no close button.
  - **Click tab** → navigate to its path (`wire:navigate`).
  - **× per tab** → remove; if active, navigate to neighbor tab.
  - **Right-click → context menu**: Refresh · Close · Close Others · Close All
    (Close-Others / Close-All keep the pinned Dashboard).
  - **Active tab** → indigo background + leading dot.
  - Horizontal scroll on overflow.

### 4.4 RBAC admin pages (frontend UI, demo data)

Interactivity via **Livewire components holding in-memory demo arrays** (collections as
component state), so modals/toggles/search/pagination feel real and the data source can be
swapped for the real backend later without UI changes.

- **Users** — data table: ID, nickname, login, phone, role **badge**, avatar, status
  **toggle switch**, actions (Edit / Reset password / Delete-in-red). Toolbar `+ Add user`.
  Pagination. Add/Edit opens a `flux:modal` with form fields. Search/filter box.
- **Roles** — table of roles + a role→permission assignment screen (checkbox grid/tree, UI
  only, no persistence).
- **Permissions** — table listing permissions grouped by module (display only).

### 4.5 Dashboard stat cards

Reusable `<x-stat-card>` Blade component: full-bleed colored panel (green / blue / purple),
big number, label, faint watermark icon. Static numbers this phase. Rendered on the
dashboard page.

### 4.6 Structure & tests

- **Pages**: Chisel file-based routing under `resources/views/pages/admin/...`, matching
  existing project conventions (sibling-file structure under `resources/views/pages/`).
- **Tests (Pest browser smoke)** — frontend behavior only, no DB assertions:
  - each admin route loads with no JS console errors
  - tags-view: visiting a page adds a tab; close removes it; tab persists across reload
  - modal opens and closes
  - status toggle flips
- Run `vendor/bin/pint --dirty` before finalizing (project convention).

## 5. Files touched / created (anticipated)

- `resources/css/app.css` (edit — tokens, font, radius, density)
- `public/fonts/pretendard-*.woff2` (new — self-hosted font)
- `resources/views/layouts/app/sidebar.blade.php` (edit — sidebar + header + tags-view)
- `resources/views/layouts/app/header.blade.php` (edit/extend)
- `resources/views/partials/tags-view.blade.php` (new)
- `resources/views/components/stat-card.blade.php` (new)
- `resources/views/pages/admin/users/…`, `roles/…`, `permissions/…` (new, Chisel pages)
- `app/Livewire/Admin/…` (new — Users/Roles/Permissions components with demo arrays)
- `resources/views/components/admin/…` (new — shared table/badge/toolbar partials as needed)
- `tests/Browser/Admin/…` (new — Pest browser smoke tests)

## 6. Out of scope / non-goals
- Any real database, migration, model persistence, or authorization (later phase).
- Any feature outside layout shell, tags-view, RBAC pages, dashboard cards.
- Multi-language i18n beyond what the starter kit already provides.
