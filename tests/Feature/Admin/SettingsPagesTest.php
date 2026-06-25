<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('renders the settings pages', function (string $url, string $see) {
    actingAs($this->user)->get($url)->assertOk()->assertSee('Settings')->assertSee($see);
})->with([
    ['/admin/settings/provider', 'Enabled gateways'],
    ['/admin/settings/advanced', 'Welcome message'],
]);

it('guards settings pages behind auth', function () {
    $this->get('/admin/settings/provider')->assertRedirect('/login');
});
