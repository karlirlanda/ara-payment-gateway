<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('renders the transaction sub-tabs', function (string $url, string $see) {
    actingAs($this->user)->get($url)->assertOk()->assertSee('Transactions')->assertSee($see);
})->with([
    ['/admin/transactions/deposit', 'Deposits'],
    ['/admin/transactions/withdraw', 'Withdrawals'],
]);

it('rejects invalid transaction directions', function (string $url) {
    actingAs($this->user)->get($url)->assertNotFound();
})->with([
    '/admin/transactions/transfer',
    '/admin/transactions/refund',
]);

it('guards transaction pages behind auth', function () {
    $this->get('/admin/transactions/deposit')->assertRedirect('/login');
});

it('shows the gateway column on transactions', function () {
    actingAs($this->user)->get('/admin/transactions/deposit')
        ->assertOk()
        ->assertSee('Gateway')
        ->assertSee('GCash');
});
