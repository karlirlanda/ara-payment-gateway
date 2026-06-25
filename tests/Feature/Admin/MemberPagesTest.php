<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('renders the member pages', function (string $url, string $see) {
    actingAs($this->user)->get($url)->assertOk()->assertSee($see);
})->with([
    ['/admin/members', 'Member Management'],
    ['/admin/members/create', 'Create member'],
    ['/admin/members/1/edit', 'Edit member'],
    ['/admin/members/live', 'Live Users'],
]);

it('guards member pages behind auth', function (string $url) {
    $this->get($url)->assertRedirect('/login');
})->with([
    '/admin/members',
    '/admin/members/create',
    '/admin/members/live',
]);

it('renders member management in Korean', function () {
    actingAs($this->user)
        ->withSession(['locale' => 'ko'])
        ->get('/admin/members')
        ->assertOk()
        ->assertSee('회원관리')
        ->assertSee('회원생성');
});
