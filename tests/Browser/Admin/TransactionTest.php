<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('loads every transaction sub-tab without JS errors', function () {
    actingAs($this->user);

    foreach ([
        '/admin/transactions/deposit',
        '/admin/transactions/withdraw',
    ] as $url) {
        visit($url)->assertNoJavascriptErrors();
    }
});

it('shows deposits with the gateway column', function () {
    actingAs($this->user);

    visit('/admin/transactions/deposit')
        ->assertSee('Deposit amount')
        ->assertSee('Gateway')
        ->assertScript('document.querySelectorAll("table thead th").length === 10', true);
});

it('advances a pending transaction through the workflow', function () {
    actingAs($this->user);

    visit('/admin/transactions/deposit')
        // pending row offers Complete + Cancel
        ->assertSee('Complete')
        // complete row #1 (wire:click selector is unique per row+action)
        ->click('[wire\\:click="setStatus(1, \'completed\')"]')
        ->assertNoJavascriptErrors()
        // the completed row now offers the revert action instead
        ->assertScript("document.querySelector('[wire\\\\:click=\"setStatus(1, \\'pending\\')\"]') !== null", true);
});

it('keeps transactions within the viewport on mobile portrait', function () {
    actingAs($this->user);

    visit('/admin/transactions/withdraw')
        ->resize(390, 844)
        ->assertNoJavascriptErrors()
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth + 1', true)
        ->assertScript("getComputedStyle(document.querySelector('.admin-table tbody td:first-child')).position === 'sticky'", true);
});
