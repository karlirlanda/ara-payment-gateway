<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('loads every settings page without JS errors', function () {
    actingAs($this->user);

    foreach ([
        '/admin/settings/provider',
        '/admin/settings/advanced',
    ] as $url) {
        visit($url)->assertNoJavascriptErrors();
    }
});

it('shows the enabled-gateway switches on gateway settings', function () {
    actingAs($this->user);

    visit('/admin/settings/provider')
        ->assertSee('Enabled gateways')
        ->assertSee('GCash')
        ->assertSee('GoTyme');
});

it('saves gateway settings without errors', function () {
    actingAs($this->user);

    visit('/admin/settings/provider')
        ->click('Save')
        ->assertNoJavascriptErrors()
        ->assertSee('Settings saved');
});
