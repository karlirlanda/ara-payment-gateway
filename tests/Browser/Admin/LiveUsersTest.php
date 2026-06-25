<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('renders the live monitoring layout', function () {
    actingAs($this->user);

    visit('/admin/members/live')
        ->assertNoJavascriptErrors()
        ->assertSee('LIVE')               // live indicator pill
        ->assertSee('Online now')         // KPI strip
        ->assertSee('Session')            // rich table columns
        ->assertSee('Last seen')
        ->assertSee('Amount')
        ->assertSee('Login time')
        ->assertSee('Shared')             // shared-IP collusion flag (demo data shares IPs)
        ->assertSee('Force logout');
});

it('force-logs-out a live user through a confirmation modal', function () {
    actingAs($this->user);

    visit('/admin/members/live')
        ->click('[wire\\:click="confirmForceLogout(\'player001\')"]')
        ->assertSee('End this member')
        ->click('[data-test="confirm-force-logout"]')
        ->assertNoJavascriptErrors();
});

it('freezes the user column on mobile portrait', function () {
    actingAs($this->user);

    visit('/admin/members/live')
        ->resize(390, 844)
        ->assertScript("getComputedStyle(document.querySelector('.admin-table--stick-first tbody td:first-child')).position === 'sticky'", true);
});

it('pauses and resumes auto-refresh', function () {
    actingAs($this->user);

    visit('/admin/members/live')
        ->assertSee('Pause')
        ->click('[wire\\:click="$toggle(\'autoRefresh\')"]')
        ->assertSee('Resume')
        ->assertNoJavascriptErrors();
});
