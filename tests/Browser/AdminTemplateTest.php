<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('loads every admin page without JS errors', function () {
    actingAs($this->user);

    $urls = [
        '/dashboard',
        '/admin/users', '/admin/roles', '/admin/permissions',
        '/admin/members', '/admin/members/live',
        '/admin/gateways', '/admin/transactions/deposit', '/admin/transactions/withdraw',
        '/admin/withdrawals/approvals', '/admin/reconciliation',
        '/admin/settings/provider', '/admin/settings/advanced',
    ];

    foreach ($urls as $url) {
        $page = visit($url);
        $page->assertNoJavascriptErrors();
    }
});

it('shows the gateway provider cards and integration flow', function () {
    actingAs($this->user);

    visit('/admin/gateways')
        ->assertSee('GCash')
        ->assertSee('Maya')
        ->assertSee('GoTyme')
        ->assertSee('Integration Flow');
});

it('approves a pending withdrawal from the approvals queue', function () {
    actingAs($this->user);

    visit('/admin/withdrawals/approvals')
        ->assertSee('Withdrawal Approvals')
        ->click('Approve')
        ->assertSee('Withdrawal approved');
});

it('shows the dashboard KPI cards and recent-activity widgets', function () {
    actingAs($this->user);

    visit('/dashboard')
        // KPI strip (driven by AdminDemoData::summary())
        ->assertSee('Total deposits')
        ->assertSee('Pending withdrawals')
        // recent-activity widgets
        ->assertSee('Deposits')
        ->assertSee('Member signups');
});

it('adds a history tab when visiting users', function () {
    actingAs($this->user);

    // The tabs store captures the current page on load, so the tab appears in the strip
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

    // flux:switch renders as <ui-switch role="switch" data-flux-switch …>.
    // Target user-1's switch directly via its wire:key attribute.
    visit('/admin/users')
        ->click('[wire\\:key="user-switch-1-1"]')
        ->assertNoJavascriptErrors();
});

it('closes a history pill without navigating to it', function () {
    // Regression: the close (×) button used to be nested inside the wire:navigate
    // anchor, so clicking it navigated to the tab ("opened" it) instead of removing it.
    actingAs($this->user);

    $page = visit('/admin/users')    // adds the Users tab
        ->navigate('/admin/roles')   // adds Roles; current page is /admin/roles
        ->click('[aria-label="Close Users"]');

    // Must NOT have navigated to /admin/users, and the Users tab must be gone.
    $page->assertScript('window.location.pathname === "/admin/roles"', true);
    $page->assertScript('JSON.parse(localStorage.getItem("admin-tabs") || "[]").some(t => t.path === "/admin/users")', false);
});

it('deletes a user through a Flux modal rather than a native confirm dialog', function () {
    actingAs($this->user);

    // Clicking a row's Delete opens an in-DOM Flux modal (a native confirm() could not
    // be asserted with assertSee). Confirming removes the row.
    // user 1 (admin) has a unique phone number we can assert on (the nickname "admin"
    // also appears as other users' role badge, so we can't assert on that).
    $page = visit('/admin/users')
        ->assertSee('13577728948')
        ->click('Delete')                              // first row's Delete button
        ->assertSee('This action cannot be undone.');  // Flux modal body is in the DOM

    $page->click('[data-test="confirm-delete"]')
        ->assertDontSee('13577728948');
});

it('renders the history strip inside the main content area, not a phantom grid column', function () {
    // Regression: Flux lays <body> out as a CSS grid (sidebar/header/main/aside).
    // The strip used to be a body-level element with no grid-area, so it auto-placed
    // into the reserved `aside` column and shrank `main` as pills accumulated. It must
    // now live inside <flux:main> (grid-area: main).
    actingAs($this->user);

    visit('/dashboard')
        ->assertNoJavascriptErrors()
        ->assertScript('document.querySelector("[data-flux-main] [data-tags-view]") !== null', true)
        ->assertScript('document.querySelectorAll("[data-tags-view]").length === 1', true);
});

it('keeps the history strip intact after a confirm-password round trip and back', function () {
    // Regression: navigating to a page on the auth layout (no tags-view) and back used
    // to orphan the persisted x-for clones (flood of "tab is not defined") and double
    // the pills. Visiting /settings/security redirects through Fortify's
    // /user/confirm-password (auth layout), reproducing the transition.
    actingAs($this->user);

    $page = visit('/admin/users')
        ->navigate('/admin/roles')
        ->navigate('/settings/security') // 302 -> /user/confirm-password (auth layout)
        ->back();                        // back into the app layout

    $page->assertNoJavascriptErrors();

    // Tabs must not have duplicated: stored count equals the unique-path count.
    $page->assertScript(
        'JSON.parse(localStorage.getItem("admin-tabs") || "[]").length === '.
        'new Set(JSON.parse(localStorage.getItem("admin-tabs") || "[]").map(t => t.path)).size',
        true
    );
    $page->assertScript('document.querySelectorAll("[data-tags-view]").length === 1', true);
});
