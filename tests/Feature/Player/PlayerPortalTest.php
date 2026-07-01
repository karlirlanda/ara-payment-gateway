<?php

use App\Support\PlayerDemoData;
use Livewire\Livewire;

/** Seed an authenticated mock player session. */
function asPlayer(): void
{
    session([
        'player.demo' => PlayerDemoData::profile(),
        'player.transactions' => PlayerDemoData::seedTransactions(),
    ]);
}

it('redirects guests from the player area to the login', function () {
    $this->get('/play')->assertRedirect(route('player.login'));
    $this->get('/play/cashier')->assertRedirect(route('player.login'));
    $this->get('/play/history')->assertRedirect(route('player.login'));
});

it('shows the demo login with credentials hint', function () {
    $this->get('/play/login')
        ->assertOk()
        ->assertSee('Demo login')
        ->assertSee(PlayerDemoData::DEMO_USERNAME);
});

it('signs in with the preset demo credentials and reaches the lobby', function () {
    Livewire::test('pages::player.login')
        ->set('username', PlayerDemoData::DEMO_USERNAME)
        ->set('password', PlayerDemoData::DEMO_PASSWORD)
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('player.lobby'));

    expect(session('player.demo'))->not->toBeNull();
});

it('rejects invalid demo credentials', function () {
    Livewire::test('pages::player.login')
        ->set('username', 'wrong')
        ->set('password', 'nope')
        ->call('login')
        ->assertHasErrors('username');

    expect(session()->has('player.demo'))->toBeFalse();
});

it('renders the lobby for a signed-in player', function () {
    asPlayer();

    $this->get('/play')
        ->assertOk()
        ->assertSee('ARA')
        ->assertSee('Wallet balance');
});

it('renders the cashier and history pages through the player layout', function () {
    asPlayer();

    $this->get('/play/cashier')
        ->assertOk()
        ->assertSee('Cashier')
        ->assertSee('GCash')
        ->assertSee('GoTyme');

    $this->get('/play/history')
        ->assertOk()
        ->assertSee('Transaction History');
});

it('credits the wallet and records a transaction on a confirmed deposit', function () {
    asPlayer();
    $start = PlayerDemoData::STARTING_BALANCE;

    Livewire::test('pages::player.cashier')
        ->set('providerKey', 'gcash')
        ->set('amount', 1000)
        ->call('proceedDeposit')
        ->assertHasNoErrors()
        ->call('confirmDeposit')
        ->assertSet('balance', $start + 1000);

    expect(session('player.demo.balance'))->toBe($start + 1000);
    expect(session('player.transactions')[0])
        ->direction->toBe('deposit')
        ->provider->toBe('GCash')
        ->amount->toBe(1000)
        ->status->toBe('completed');
});

it('enforces the provider minimum on deposits', function () {
    asPlayer();

    Livewire::test('pages::player.cashier')
        ->set('providerKey', 'gcash')
        ->set('amount', 10)
        ->call('proceedDeposit')
        ->assertHasErrors('amount');
});

it('debits the wallet and queues a pending withdrawal', function () {
    asPlayer();
    $start = PlayerDemoData::STARTING_BALANCE;

    Livewire::test('pages::player.cashier')
        ->set('tab', 'withdraw')
        ->set('providerKey', 'maya')
        ->set('amount', 2000)
        ->set('account', '0998 123 4567')
        ->call('submitWithdraw')
        ->assertHasNoErrors()
        ->assertSet('balance', $start - 2000);

    expect(session('player.transactions')[0])
        ->direction->toBe('withdraw')
        ->status->toBe('pending');
});

it('rejects withdrawals greater than the wallet balance', function () {
    asPlayer();

    Livewire::test('pages::player.cashier')
        ->set('tab', 'withdraw')
        ->set('providerKey', 'maya')
        ->set('amount', PlayerDemoData::STARTING_BALANCE + 5000)
        ->set('account', '0998 123 4567')
        ->call('submitWithdraw')
        ->assertHasErrors('amount');
});

it('clears the demo session on logout', function () {
    asPlayer();

    $this->post(route('player.logout'))->assertRedirect(route('player.login'));

    expect(session()->has('player.demo'))->toBeFalse();
});

it('exposes the three providers matching the proposal', function () {
    expect(collect(PlayerDemoData::providers())->pluck('name')->all())
        ->toBe(['GCash', 'Maya', 'GoTyme']);
});
