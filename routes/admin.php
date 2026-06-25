<?php

use Illuminate\Support\Facades\Route;

/*
 * NOTE: These admin routes are gated by 'auth' + 'verified' only.
 * Role/permission authorization is intentionally DEFERRED to the backend phase
 * (spatie/laravel-permission). Do NOT ship to production without adding an
 * admin authorization middleware (e.g. ->middleware('can:access-admin')) here.
 */
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('users', 'pages::admin.users.index')->name('users');
    Route::livewire('roles', 'pages::admin.roles.index')->name('roles');
    Route::livewire('permissions', 'pages::admin.permissions.index')->name('permissions');

    Route::livewire('members', 'pages::admin.members.index')->name('members');
    Route::livewire('members/create', 'pages::admin.members.form')->name('members.create');
    Route::livewire('members/{id}/edit', 'pages::admin.members.form')->name('members.edit');
    Route::livewire('members/live', 'pages::admin.members.live')->name('members.live');

    // Payment gateways — provider overview (GCash / Maya / GoTyme).
    Route::livewire('gateways', 'pages::admin.gateways.index')->name('gateways');

    // Transactions — gateway deposits and withdrawals.
    Route::livewire('transactions/{direction}', 'pages::admin.transactions.index')
        ->whereIn('direction', ['deposit', 'withdraw'])->name('transactions');

    // Withdrawals — admin approval queue (approve / reject pending withdrawals).
    Route::livewire('withdrawals/approvals', 'pages::admin.withdrawals.approvals')->name('withdrawals.approvals');

    // Reconciliation — daily reconciliation against provider transaction logs.
    Route::livewire('reconciliation', 'pages::admin.reconciliation.index')->name('reconciliation');

    // Settings — gateway (provider) settings and advanced settings.
    Route::livewire('settings/provider', 'pages::admin.settings.provider')->name('settings.provider');
    Route::livewire('settings/advanced', 'pages::admin.settings.advanced')->name('settings.advanced');
});
