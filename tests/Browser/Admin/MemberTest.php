<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('shows status-tab counts and the list toolbar', function () {
    actingAs($this->user);

    visit('/admin/members')
        ->assertSee('Export all')
        ->assertSee('Columns')
        // tab count badges (65 demo members)
        ->assertScript("[...document.querySelectorAll('button')].some((b) => /All\\s*65/.test(b.textContent.replace(/\\s+/g, ' ')))", true);
});

it('switches to the bulk toolbar and bulk-suspends selected members', function () {
    actingAs($this->user);

    visit('/admin/members')
        ->click('[data-flux-main] tbody tr:nth-child(1) input[type=checkbox]')
        ->assertSee('selected')
        ->click('[wire\\:click="bulkSetStatus(\'suspended\')"]')
        ->assertNoJavascriptErrors()
        // selection cleared → toolbar back to idle
        ->assertSee('Export all');
});

it('bulk-deletes selected members through a confirmation modal', function () {
    actingAs($this->user);

    visit('/admin/members')
        ->click('[data-flux-main] tbody tr:nth-child(1) input[type=checkbox]')
        ->click('[wire\\:click="confirmBulkDelete"]')
        ->assertSee('Delete selected members')
        ->click('[data-test="confirm-bulk-delete"]')
        ->assertNoJavascriptErrors();
});

it('hides a column via the Columns control', function () {
    actingAs($this->user);

    visit('/admin/members')
        // Phone is the 7th header (after the checkbox column)
        ->assertScript("document.querySelector('[data-flux-main] table thead th:nth-child(7)').textContent.trim() === 'Phone'", true)
        // toggle the Phone checkbox in the columns menu (rendered in the DOM)
        ->assertScript("(() => { const l = [...document.querySelectorAll('label')].find((x) => x.textContent.trim() === 'Phone'); l.querySelector('input').click(); return true; })()", true)
        ->assertScript("getComputedStyle(document.querySelector('[data-flux-main] table tbody td:nth-child(7)')).display === 'none'", true);
});

it('toggles row density', function () {
    actingAs($this->user);

    visit('/admin/members')
        ->click('[x-on\\:click="toggleDensity()"]:not([role=menuitem])')
        ->assertScript("document.querySelector('[data-flux-main] table').classList.contains('admin-table--compact')", true);
});

it('exports the list without JS errors', function () {
    actingAs($this->user);

    visit('/admin/members')
        ->click('[wire\\:click="export(false)"]:not([role=menuitem])')
        ->assertNoJavascriptErrors();
});

it('opens the member row modals (detail / balance / points) and force-logout', function () {
    actingAs($this->user);

    visit('/admin/members')
        ->click('[wire\\:click="showDetail(1)"]')
        ->assertSee('Member details')
        ->assertNoJavascriptErrors();

    visit('/admin/members')
        ->click('[wire\\:click="showMoney(1)"]')
        ->assertSee('Adjust balance')
        ->assertNoJavascriptErrors();

    visit('/admin/members')
        ->click('[wire\\:click="showPoints(1)"]')
        ->assertSee('Adjust points')
        ->assertNoJavascriptErrors();

    visit('/admin/members')
        ->click('[wire\\:click="forceLogout(1)"]')
        ->assertNoJavascriptErrors();
});

it('loads member pages without JS errors', function () {
    actingAs($this->user);

    foreach (['/admin/members', '/admin/members/create', '/admin/members/live'] as $url) {
        visit($url)->assertNoJavascriptErrors();
    }
});

it('shows the member list (18 data columns + select checkbox) with summary stats', function () {
    actingAs($this->user);

    visit('/admin/members')
        ->assertSee('Member Management')
        ->assertSee('Total members')
        // 18 data columns + the leading select-all checkbox column
        ->assertScript('document.querySelectorAll("table thead th").length === 19', true);
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

it('renders the member page in the unified shell (history pills + summary + action bar)', function () {
    // Regression for the layout merge: member pages share the one app shell, so they
    // get the history-pills strip, the sidebar summary-totals block, and the header
    // action bar — previously these only existed on the (now-deleted) layouts.admin.
    actingAs($this->user);

    visit('/admin/members')
        ->assertNoJavascriptErrors()
        ->assertSee('Total deposits') // sidebar summary-totals block (English by default)
        ->assertSee('Approvals')      // header action-bar badge button
        ->assertScript('document.querySelectorAll("[data-tags-view]").length === 1', true);
});
