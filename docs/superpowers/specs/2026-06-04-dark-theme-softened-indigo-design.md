# Dark Theme Refresh — "Softened Indigo (A2)" Design

**Date:** 2026-06-04
**Status:** Approved

## Goal

Lift the dark-mode surfaces one notch — clearer card elevation, softer row/border
contrast — while keeping the existing indigo identity. Light mode is untouched.

## Background

The current dark theme renders near-black: cards/tables on `zinc-800` (`#262626`),
sidebar/body on `zinc-900`/`zinc-950` (`#171717` / `#0a0a0a`), indigo-400 (`#818cf8`)
accent, sharp 2px corners. It reads dark and high-contrast; borders and rows feel
harsh against the near-black.

Four candidate palettes (softened-indigo, slate+sky, warm-emerald, graphite+violet)
were rendered live in the app and reviewed via a temporary preview page. The user
chose **A · Softened Indigo** at the **A2 ("a bit lighter")** brightness level.

## Approach

Every dark surface already derives from four neutral CSS custom properties that Flux
components and our own admin CSS reference (`bg-zinc-800`, `border-zinc-700`, etc.,
which compile to `var(--color-zinc-*)`). Overriding just those four variables —
**scoped to `.dark` only** — lifts every dark surface uniformly with a single edit,
and leaves light mode completely unchanged.

The override lives in the existing `.dark` block inside `@layer theme` in
`resources/css/app.css`, alongside the current `--color-accent` override (which
already proves this cascade works).

### Token values

| Token | Used for | Today | A2 |
|-------|----------|-------|----|
| `--color-zinc-700` | borders / dividers (dark) | `#404040` | `#434956` |
| `--color-zinc-800` | cards, table, header surface | `#262626` | `#2a2d37` |
| `--color-zinc-900` | sidebar / body panels | `#171717` | `#20222b` |
| `--color-zinc-950` | deepest body background | `#0a0a0a` | `#191b22` |

- **Accent unchanged:** indigo-400 `#818cf8` (dark), indigo-600 `#4f46e5` (light).
- **Light mode unchanged:** the global `@theme` `--color-zinc-*` values stay as-is;
  only the `.dark` scope is overridden.
- **No new files, no token renames.** The frozen-column and sort-caret rules in
  `app.css` already use `var(--color-zinc-700/800/900)`, so they follow automatically.

## Out of scope

- Light theme, accent hue, corner radius, typography — all unchanged.
- Per-component restyling — this is a pure token shift.

## Verification

This is a cosmetic token change, so there is no meaningful unit test. Verification is:

1. `npm run build` succeeds.
2. Headed dark-mode screenshots (dashboard, members list, an open modal) confirm the
   surfaces are lifted and card elevation reads clearly.
3. A light-mode sanity screenshot confirms light mode is visually unchanged.
4. The full Pest suite stays green (CSS-only change — no behavioural impact).

## Cleanup

Remove the temporary `public/dark-samples/` preview folder before finalizing.
