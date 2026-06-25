<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('renders the payment gateway pages', function (string $url, string $see) {
    actingAs($this->user)->get($url)->assertOk()->assertSee($see);
})->with([
    ['/admin/gateways', 'GCash'],
    ['/admin/gateways', 'GoTyme'],
    ['/admin/withdrawals/approvals', 'Withdrawal Approvals'],
    ['/admin/reconciliation', 'Daily Reconciliation'],
]);

it('shows the integration flow on the gateways overview', function () {
    actingAs($this->user)->get('/admin/gateways')
        ->assertOk()
        ->assertSee('Integration Flow')
        ->assertSee('Reconciliation');
});

it('lists pending withdrawals on the approvals queue', function () {
    actingAs($this->user)->get('/admin/withdrawals/approvals')
        ->assertOk()
        ->assertSee('Approve')
        ->assertSee('Reject');
});

it('guards the payment pages behind auth', function (string $url) {
    $this->get($url)->assertRedirect('/login');
})->with([
    '/admin/gateways',
    '/admin/withdrawals/approvals',
    '/admin/reconciliation',
]);
