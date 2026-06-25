# Admin Foundation + Member Management — Design Spec

**Date:** 2026-06-03
**Status:** Approved for planning
**Scope of this spec:** First slice only — the **layout foundation** plus the **member-management** vertical. Frontend-only, demo data. Other sections and the real backend are explicitly out of scope (see §8).

## 1. Goal

Recreate the **DREAMS solution** betting/gaming admin (`dev-admin.armjr.cloud`) on top of our Livewire Flux template, **adapting** its structure to our Flux / Pretendard / indigo design system (light + dark) rather than pixel-cloning its dense dark theme. This first slice delivers the reusable **layout foundation** and the **member-management** pages, proving the pattern every later section will reuse.

The reference system is hierarchical: HQ(본사) → sub-HQ(부본사) → branch(지사) → distributor(총판) → store(매장) → member(회원). This slice covers **members (회원)** only.

## 2. Decisions (locked)

| Decision | Choice |
|---|---|
| First slice | Foundation (layout shell) **+** Member management vertical |
| Fidelity | **Adapt** to our Flux template — keep DREAMS *structure* (sidebar summary block, header action badges, filter bar, dense wide tables, status-workflow row actions), render in our indigo/Pretendard/sharp-edge system |
| Build mode | **Frontend-first** — UI on in-memory demo data; real backend deferred per slice |
| Sidebar | **Follows the Flux light/dark theme** (light `zinc-50` in light mode, dark `zinc-900` in dark mode) — the whole shell, sidebar included, honors the appearance toggle. Summary block + nav badges retained. |
| History-tabs strip | **Not** included in the admin shell (DREAMS has none). Remains in the generic app layout; may be added to admin later if desired |
| Create member/agent | **Normal full pages**, not popups (the prod app opens `/player/form` & `/agent/form` in new tabs) |
| Member URLs | Our own clean scheme `/admin/members/*` (not prod's `/player/management`) |

## 3. Foundation — layout shell & reusable components

A dedicated admin layout, parallel to the existing app layout (the existing demo pages — Users/Roles/content/chat — keep working unchanged and can retire once real sections land).

### 3.1 Admin layout — `resources/views/layouts/admin.blade.php`
Body grid: sidebar + header + main. The **entire shell follows the Flux light/dark theme** via the existing appearance toggle. Pages opt in via `#[Layout('layouts.admin')]`.

- **Sidebar (theme-following — light `zinc-50` / dark `zinc-900`, per Flux):** the summary block and nav badge colors adapt to the active theme (e.g. accent values on dark vs light).
  - Brand row.
  - `<x-admin.summary-totals>` — compact totals block: 매장보유머니, 회원보유머니, 보유 포인트, HonorLink 총잔고, 금일(입-출), 신규회원/신규매장, 실시간 사용자. Values from `AdminDemoData::summary()`.
  - Nav menu with **badge support** (numeric count + warning "!"): groups 대시보드, 대시보드 2, 회원관리, 매장 관리, 배팅관리, 입출금 내역, 머니관리, 정산, 게시판, 쿠폰, 설정. **This slice wires only the 회원관리 items**; other groups render (matching prod structure) but their items are non-navigable placeholders.
- **Header:**
  - `<x-admin.action-bar>` — 5 action buttons with red count badges: 매장충전, 매장환전, 총판문의, 매장 가입 승인, 미등록 베팅. Counts from `AdminDemoData::actionBadges()`; click = placeholder (no nav this slice).
  - Existing **locale dropdown** (en/ko).
  - `<x-admin.notifications>` — bell with demo dropdown list.
  - Avatar dropdown (extend existing `x-desktop-user-menu` with 비밀번호 변경 / 핀 변경 placeholder items + logout).

### 3.2 List-page scaffold components (`resources/views/components/admin/`)
- `<x-admin.page-header>` — page title + `tabs` slot + `actions` slot.
- `<x-admin.filter-bar>` — date-range (from~to inputs + **오늘/어제** quick buttons), **status** select, **keyword** input, **검색** (primary) + **리셋** (danger) buttons, and an `actions` slot (for +생성 buttons and a page-size select). Two-way binds to the host Livewire component's filter properties.
- `<x-admin.stat-strip>` — horizontal row of stat cells; each cell shows **label + value + optional count** (e.g. `총 입금 / 5,640,000원 / (50)`).
- `<x-admin.table>` — wrapper: horizontal scroll, sticky header row, optional **sticky last (관리) column**, dense rows. Slots for `columns` and `rows`. Reuses `flux:badge`, `flux:switch`.
- `<x-admin.row-actions>` — a button group for per-row actions, supporting **status-workflow buttons** (e.g. 대기 / 완료 / 취소 on pending rows) and plain actions (수정 / 머니 / 삭제).
- `<x-admin.level-badge :level :name>` — colored level chip (1–5) + id, matching the prod 레벨/아이디 cell.

## 4. Member management slice

Chisel single-file Livewire pages, demo data, following the hardened template conventions (Flux modals, `wire:key` switches, `page-meta`-style title/breadcrumb, ko translations).

### 4.1 Routes (`routes/admin.php`)
- `/admin/members` → list (status tabs 전체/정상/정지/탈퇴 via a `status` filter)
- `/admin/members/create` → create form (normal page)
- `/admin/members/{id}/edit` → edit form (normal page)
- `/admin/members/live` → 현재 접속자 (회원) — online members

### 4.2 Member list (`pages/admin/members/⚡index.blade.php`)
- Demo array of ~30 members. **18 columns**: ☐, 레벨/아이디, 닉네임, 매장, 계정 세부정보, 전화번호, 잔액, 수수료 유형, 포인트, 충전, 환전, 차액, 로그인(switch), 최근접속, 접근아이피, 가입일자, 접근도메인, 관리.
- `<x-admin.filter-bar>`: date-range, status, keyword; `updatedX` resets pagination.
- Pagination via `LengthAwarePaginator` (mirrors the existing Users page) with a **결과보기 50** page-size select.
- `<x-admin.stat-strip>`: 총 회원 (count), 총 잔액, 총 포인트, 충전-환전 차액.
- Status `flux:switch` with the desync-proof `wire:key="member-status-{id}-{0|1}"`.
- `<x-admin.row-actions>`: 수정 (link to edit), 머니 (placeholder — opens a stub modal), 삭제 (Flux confirm modal, `data-test="confirm-delete"`).
- Status tabs switch the `status` filter.

### 4.3 Member form (`pages/admin/members/⚡create.blade.php` + `⚡edit.blade.php`)
Normal full page (shared partial), sections from the prod `/player/form`:
- **계정 / Account:** 유형 (오프라인/온라인 radio), 매장 (upline — text), 레벨 (select), 아이디, 닉네임, 비밀번호 + 확인, 환전 비밀번호/핀번호, 이메일, 메신저, 휴대폰 번호, 상태 / 회원유형 / 회원분류 / 회원상태 (selects), 베팅 등급 (select), 잔액, 포인트, 메모 (textarea — `flux:editor` is Pro, so a styled textarea).
- **계좌정보 / Bank:** 회원계좌 + 입금계좌 (은행 이름 / 예금주 / 계좌 번호) each with a disable toggle.
- **수수료 / Commission:** 수수료 유형 (베팅/루징 radio), then per-game % blocks for 파워볼 (싱글/멀티플), 카지노 (바카라/슬롯/루징), 공배팅 — each with enable toggle + % input.
- Save = `$this->validate(...)` then `Flux::toast`. **No persistence.**

### 4.4 Live users (`pages/admin/members/⚡live.blade.php`)
Demo table of online members: 레벨/아이디, 가입일자, 접근아이피, 접근도메인, 로그인상태 (+ 로그아웃 action button, placeholder).

### 4.5 Translations
All new strings added to `lang/ko.json` (English defaults inline). Titles/breadcrumbs use `__()`.

## 5. Demo data
`app/Support/AdminDemoData.php` — static methods returning arrays, consumed by components + pages:
- `summary()` — sidebar totals
- `actionBadges()` — header action counts
- `notifications()` — bell items
- `members()` — ~30 member rows
- `liveUsers()` — online members

## 6. File structure
- `resources/views/layouts/admin.blade.php` (+ `partials/admin-sidebar.blade.php`, `partials/admin-header.blade.php`)
- `resources/views/components/admin/{summary-totals,action-bar,notifications,page-header,filter-bar,stat-strip,table,row-actions,level-badge}.blade.php`
- `resources/views/pages/admin/members/{⚡index,⚡create,⚡edit,⚡live}.blade.php`
- `app/Support/AdminDemoData.php`
- `routes/admin.php` (member routes)
- `lang/ko.json` (additions)
- `tests/Browser/Admin/MemberTest.php`, `tests/Feature/Admin/MemberPagesTest.php`

## 7. Testing
- **Browser smoke (Pest):** each member route loads with no JS errors; filter bar + 18-col table render; status tabs switch; status switch toggles; delete Flux modal opens/confirms; create page renders all three sections (계정/계좌정보/수수료); list paginates.
- **Feature (Pest):** each route renders server-side (heading present) + redirects to `/login` when unauthenticated; ko locale renders Korean for member strings.
- Run `vendor/bin/pint --dirty` before finalizing.

## 8. Out of scope / deferred
- **Other sections** (each its own later spec): 매장관리(agents) incl. create-as-page, 입출금(transactions, 6 sub-tabs, 대기/완료/취소 workflow), 배팅관리(casino/slot grouped-commission tables), 머니관리, 정산, 게시판, 쿠폰, 설정.
- **Real backend (all deferred):** Eloquent models, the agent hierarchy, money/ledger + concurrency-safe balance ops, auth/permissions, HonorLink integration, real summary/badge numbers, the 머니 (balance adjust) action, multi-member create, real persistence/search/export, IP-block & activity logs.
- The existing template demo pages (Users/Roles/content/chat) are left intact; retire later.

## 9. Reference
- Prod scan screenshots: `prod-01…prod-10` (dashboard, member list, member create form, agent list, agent create form, summary, betting-slot, agent-deposit empty + populated). **Contain real-looking PII — do not commit.**
- Key prod URLs: `/player/management/all` (member list, 18 col), `/player/form` (member create), `/agents/all` (28 col), `/agent/form` (agent create), `/summary` (dashboard 2), `/agent/transaction/deposit` (workflow rows).
