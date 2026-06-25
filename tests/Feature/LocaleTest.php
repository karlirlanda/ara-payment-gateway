<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('renders the admin UI in English by default', function () {
    actingAs($this->user)
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('User Management')
        ->assertSee('Nickname');
});

it('renders the admin UI in Korean when the session locale is ko', function () {
    actingAs($this->user)
        ->withSession(['locale' => 'ko'])
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('사용자 관리')   // "User Management"
        ->assertSee('닉네임')        // "Nickname"
        ->assertDontSee('Nickname'); // English column header is gone (not present in the page <title>)
});

it('persists the chosen locale through the switch route', function () {
    actingAs($this->user)
        ->get('/locale/ko')
        ->assertRedirect();

    expect(session('locale'))->toBe('ko');
});

it('rejects unsupported locales with a 404', function () {
    actingAs($this->user)
        ->get('/locale/fr')
        ->assertNotFound();
});
