<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
});

it('freezes the first table column and avoids page overflow on mobile portrait', function () {
    actingAs($this->user);

    visit('/admin/members')
        ->resize(390, 844)
        ->assertNoJavascriptErrors()
        // first column is frozen (sticky) on mobile so the row identifier stays visible
        ->assertScript("getComputedStyle(document.querySelector('.admin-table tbody td:first-child')).position === 'sticky'", true)
        // the page itself must not overflow the viewport — the wide table scrolls internally
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth + 1', true)
        // stat strip stacks to a 2-up grid on mobile
        ->assertScript("getComputedStyle([...document.querySelectorAll('div')].find(d => d.className.includes('grid-cols-2') && d.className.includes('max-lg:grid'))).display === 'grid'", true);
});

it('collapses the filter card behind a toggle on mobile portrait', function () {
    actingAs($this->user);

    $page = visit('/admin/members')->resize(390, 844)->assertNoJavascriptErrors();

    // Filter card hidden by default on mobile.
    $page->assertScript("document.querySelector('[data-filter-bar]').offsetHeight === 0", true)
        // Tapping "Filters" reveals it...
        ->click('[data-test="filters-toggle"]')
        ->assertScript("document.querySelector('[data-filter-bar]').offsetHeight > 0", true)
        // ...as a stacked form with the per-field labels hidden (placeholder-driven).
        ->assertScript("getComputedStyle(document.querySelector('[data-filter-bar]')).flexDirection === 'column'", true)
        ->assertScript("[...document.querySelector('[data-filter-bar]').querySelectorAll('span')].find(s => s.textContent.trim() === 'Date').offsetHeight === 0", true);
});

it('collapses the table commands into a menu on mobile portrait', function () {
    actingAs($this->user);

    // The inline "Export all" utility button is hidden on mobile (it lives in the ⋯ menu).
    visit('/admin/members')->resize(390, 844)
        ->assertNoJavascriptErrors()
        ->assertScript("[...document.querySelectorAll('button')].filter(b => b.textContent.trim() === 'Export all' && b.offsetHeight > 0).length === 0", true);
});

it('enriches the mobile top bar with notifications and language', function () {
    actingAs($this->user);

    // The visible (mobile) header gains bell + appearance + language beyond the old toggle + profile.
    visit('/admin/members')->resize(390, 844)
        ->assertNoJavascriptErrors()
        ->assertScript("[...[...document.querySelectorAll('header')].find(h => h.offsetHeight > 0).querySelectorAll('button')].filter(b => b.offsetHeight > 0).length >= 4", true);
});

it('keeps the filter bar and commands inline on desktop', function () {
    actingAs($this->user);

    // Regression: at lg+ the filter card is always visible with labels, and the toolbar is inline.
    visit('/admin/members')->resize(1440, 900)
        ->assertScript("document.querySelector('[data-filter-bar]').offsetHeight > 0", true)
        ->assertScript("[...document.querySelector('[data-filter-bar]').querySelectorAll('span')].find(s => s.textContent.trim() === 'Date').offsetHeight > 0", true)
        ->assertScript("[...document.querySelectorAll('button')].some(b => b.textContent.trim() === 'Export all' && b.offsetHeight > 0)", true);
});

it('re-applies the frozen columns after a filter reset', function () {
    // Regression: Reset (like any Livewire morph that reuses wire:keys) strips the
    // JS-applied freeze classes/offsets; the adminTable observer must re-apply them.
    actingAs($this->user);

    visit('/admin/members')->resize(1440, 900)
        ->assertScript("document.querySelectorAll('.admin-table thead th.is-stuck').length >= 1", true)
        // narrow with a keyword (one morph)...
        ->fill('input[aria-label="Search"]', 'player')
        ->wait(1)
        // ...then Reset (another morph) — the freeze must survive.
        ->click('[wire\\:click="resetFilters"]')
        ->wait(1)
        ->assertScript("document.querySelectorAll('.admin-table thead th.is-stuck').length >= 1", true);
});

it('freezes the leading columns on desktop too', function () {
    // Selectable tables freeze the checkbox + identifier column(s) on every viewport
    // now, so the row stays identifiable while scrolling wide tables sideways at lg+.
    actingAs($this->user);

    visit('/admin/members')
        ->resize(1440, 900)
        ->assertScript("getComputedStyle(document.querySelector('.admin-table tbody td:first-child')).position === 'sticky'", true);
});
