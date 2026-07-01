<?php

use Illuminate\Support\Facades\Route;

/*
 * Frontend player portal (proposal demo).
 *
 * Purely frontend: a mock demo login seeds a session flag; the wallet,
 * deposits and withdrawals are all in-memory mock data (no DB, no real
 * provider APIs). Mirrors the GCash / Maya / GoTyme providers from the
 * admin build. English-only.
 */
Route::prefix('play')->name('player.')->group(function () {
    // Guest — mock demo login.
    Route::livewire('login', 'pages::player.login')->name('login');

    // Clear the mock demo session.
    Route::post('logout', function () {
        session()->forget('player.demo');

        return redirect()->route('player.login');
    })->name('logout');

    // Authenticated by the mock player session.
    Route::middleware('player')->group(function () {
        Route::livewire('/', 'pages::player.lobby')->name('lobby');
        Route::livewire('cashier', 'pages::player.cashier')->name('cashier');
        Route::livewire('history', 'pages::player.history')->name('history');
    });
});
