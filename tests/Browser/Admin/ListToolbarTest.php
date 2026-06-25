<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

// The utility toolbar (Refresh / Export all / Columns / Density) is rolled out to
// every admin list page via the shared <x-admin.list-toolbar> component + trait.
$pages = [
    'members live' => '/admin/members/live',
    'deposits' => '/admin/transactions/deposit',
    'withdrawals' => '/admin/transactions/withdraw',
    'withdrawal approvals' => '/admin/withdrawals/approvals',
    'reconciliation' => '/admin/reconciliation',
];

it('shows the utility toolbar on every list page', function (string $url) {
    actingAs($this->user);

    visit($url)
        ->assertNoJavascriptErrors()
        ->assertSee('Export all')
        ->assertSee('Columns');
})->with($pages);

it('toggles density and exports without errors from a list page', function () {
    actingAs($this->user);

    visit('/admin/transactions/deposit')
        ->click('[x-on\\:click="toggleDensity()"]:not([role=menuitem])')
        ->assertScript("document.querySelector('[data-flux-main] table').classList.contains('admin-table--compact')", true)
        ->click('[wire\\:click="export(false)"]:not([role=menuitem])')
        ->assertNoJavascriptErrors();
});
