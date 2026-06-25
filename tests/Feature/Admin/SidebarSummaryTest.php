<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('renders the sidebar summary labels in English by default', function () {
    actingAs($this->user);

    get('/dashboard')
        ->assertOk()
        ->assertSee('Total deposits')
        ->assertSee('Pending withdrawals')
        ->assertSee('Live users');
});
