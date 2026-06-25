<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('shows the environment badge outside production', function () {
    // The test suite runs in the "testing" environment — i.e. not production.
    actingAs($this->user);

    get('/dashboard')
        ->assertOk()
        ->assertSee('data-test="env-badge"', false);
});

it('hides the environment badge in production', function () {
    app()->detectEnvironment(fn () => 'production');
    actingAs($this->user);

    get('/dashboard')
        ->assertOk()
        ->assertDontSee('data-test="env-badge"', false);
});
