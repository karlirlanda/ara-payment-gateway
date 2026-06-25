# Mobile Portrait Polish — Design

**Date:** 2026-06-04
**Status:** Approved (pending spec review)

## Goal

Make the admin list pages comfortable in mobile portrait (≤ phone width) without
changing desktop at all. Three improvements, validated against live samples:

1. **Collapsible filters + table commands** — reclaim the vertical space the
   always-open filter card and the toolbar consume before the table.
2. **Tighter page-title spacing** on mobile.
3. **Richer mobile top bar** — surface notifications + appearance, which today
   only appear on desktop.

All changes are scoped to the mobile breakpoint (`max-lg:` / `lg:hidden`, i.e.
< 1024px) and live in shared components, so every list page benefits at once.

## Decisions (from brainstorming + samples)

- Collapse pattern: **inline toggle**, collapsed by default on mobile.
- Filter labels on mobile: **hidden / placeholder-driven** (inline labels overflow
  the date range on a 390px screen).
- Top bar additions: **notification bell (with count) + appearance toggle + language
  switcher**; the desktop "action badges" strip stays desktop-only.

## Architecture / behaviour

### Breakpoint
`lg` (1024px) is the existing desktop/mobile seam (sidebar, headers, tabs all use
it). Reuse it. Desktop (`lg+`) renders exactly as today.

### 1. Collapsible filters
- **`listTools` Alpine** (already on every list page root) gains:
  - `filtersOpen` (bool, default `false`) — drives the mobile filter visibility.
  - `filterCount` — number of active filters, computed client-side by scanning the
    filter-bar's controls (`input`/`select` with a non-default value). Recomputed on
    `input`/`change` within the bar and on `livewire:navigated`. Generic — no need to
    know each page's prop names.
- **`filter-bar` component**:
  - Prepends a **mobile-only control row** (`lg:hidden`): a `Filters` button (funnel
    icon + count badge when `filterCount > 0`) bound to
    `x-on:click="filtersOpen = !filtersOpen"`.
  - The filter **card** is `max-lg:hidden` by default and shown on mobile when open
    via `:class="{ 'max-lg:!flex': filtersOpen }"`. On `lg+` it is always visible
    (unchanged single labelled row).
  - On mobile the card stacks: `max-lg:flex-col max-lg:items-stretch`; each control
    wrapper becomes `max-lg:w-full`; the two date inputs sit in one row with the `~`;
    Today/Yesterday share a row. Desktop layout/classes are untouched.
  - **Hidden labels on mobile:** each label `<span>` gets `max-lg:hidden`. Inputs
    carry placeholders (`mm/dd/yyyy` is native; status first option reads
    "All statuses"; search already has "Keyword"). Desktop keeps the visible labels.

### 2. Table commands (Refresh / Export all / Columns / Density)
- **`list-toolbar` component** becomes responsive in its **idle** state:
  - Desktop (`lg+`): the four utilities render inline as today.
  - Mobile (`max-lg`): they collapse into a single **`⋯` dropdown** (menu items:
    Refresh, Export all, a Columns sub-section reusing the existing `cols` checklist,
    and Density). Lives where the toolbar already is — the page-header `toolbar` slot,
    pinned to the right of the (horizontally scrolling) tabs row.
  - The **selected/bulk** state is left as-is in this pass (transient; out of scope).

> Note vs the sample: the `⋯` sits at the right end of the tabs row (its current
> home) rather than directly beside the `Filters` toggle. This avoids relocating a
> slot across components and keeps each component owning its concern. Net mobile
> stack: `tabs … ⋯` / `Filters (n)` / collapsible card / stats / table.

### 3. Mobile top bar
- In `layouts/app/sidebar.blade.php`, the mobile header (`flux:header.lg:hidden`)
  gains, before the profile dropdown: `<x-admin.notifications />` (existing bell +
  count + dropdown), the appearance toggle button (`route('appearance.edit')`,
  `icon="sun"`), and the **language switcher** (the same `flux:dropdown` of
  English / 한국어 used in the desktop header, `icon="language"`).
  Order: `☰ … 🔔 ☀ 🌐 profile`.

### 4. Content padding / title spacing
- Add to `resources/css/app.css` (avoids editing the Flux `main` vendor stub):
  ```css
  @media (max-width: 1023px) { [data-flux-main] { padding: 0.5rem; } }
  ```
  Reduces the mobile content padding from `p-6` (24px) to `p-2` (8px) on all sides —
  tighter title spacing and more horizontal room for the tables. Desktop `lg:p-8`
  is unchanged.

## Files

- `resources/js/app.js` — extend `listTools` (`filtersOpen`, `filterCount` + listeners).
- `resources/views/components/admin/filter-bar.blade.php` — mobile control row,
  stacked card, hidden labels; desktop unchanged.
- `resources/views/components/admin/list-toolbar.blade.php` — idle utilities collapse
  to a `⋯` dropdown on mobile; inline on desktop.
- `resources/views/layouts/app/sidebar.blade.php` — mobile header: notifications +
  appearance.
- `resources/css/app.css` — mobile `[data-flux-main]` top padding.

## Testing

Pest browser tests at 390px (`->on()->mobile()` / resize):
- Filter card hidden by default on mobile; tapping `Filters` reveals it; tapping again
  hides it.
- Active-filter count badge appears when a filter is set.
- The `⋯` command menu is present on mobile and exposes Export all / Columns / Density.
- Mobile header shows the notification bell + language switcher.
- Desktop (lg) still shows the filter bar inline with visible labels and the toolbar
  inline (regression guard).
- Existing `ResponsiveTest` + full suite stay green (adjust any assertion that
  expected the filter card visible on mobile).

## Out of scope

Desktop layout, the table itself, the toolbar's selected/bulk state on mobile, and
any data/behaviour. Pure mobile layout, visibility, and the two header icons.

## Cleanup

Remove the temporary `public/mobile-samples/` preview folder before finalizing.
