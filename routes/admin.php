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
    Route::livewire('activity-logs', 'pages::admin.activity.index')->name('activity-logs');

    Route::livewire('members', 'pages::admin.members.index')->name('members');
    Route::livewire('members/create', 'pages::admin.members.form')->name('members.create');
    Route::livewire('members/{id}/edit', 'pages::admin.members.form')->name('members.edit');
    Route::livewire('members/live', 'pages::admin.members.live')->name('members.live');
    Route::livewire('members/{id}/profile', 'pages::admin.members.profile')->name('members.profile');

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

    // Reports — sales & revenue analytics (catalog, grouped sales report, daily chart + detail reports).
    Route::livewire('reports', 'pages::admin.reports.index')->name('reports');
    Route::livewire('reports/sales', 'pages::admin.reports.sales')->name('reports.sales');
    Route::livewire('reports/daily', 'pages::admin.reports.daily')->name('reports.daily');
    Route::livewire('reports/period', 'pages::admin.reports.period')->name('reports.period');
    Route::livewire('reports/profit-loss', 'pages::admin.reports.pl')->name('reports.pl');
    Route::livewire('reports/coupon-usage', 'pages::admin.reports.coupon-usage')->name('reports.coupon-usage');
    Route::livewire('reports/player-activity', 'pages::admin.reports.player-activity')->name('reports.player-activity');
    Route::livewire('reports/agent-commission', 'pages::admin.reports.agent-commission')->name('reports.agent-commission');
    Route::livewire('reports/brand-comparison', 'pages::admin.reports.brand-comparison')->name('reports.brand-comparison');

    // Agents — hierarchy, commission settings, transactions, performance.
    Route::livewire('agents', 'pages::admin.agents.index')->name('agents');
    Route::livewire('agents/commissions', 'pages::admin.agents.commissions')->name('agents.commissions');
    Route::livewire('agents/transactions', 'pages::admin.agents.transactions')->name('agents.transactions');
    Route::livewire('agents/performance', 'pages::admin.agents.performance')->name('agents.performance');

    // Accounting — settlement, revenue summary, balance sheet, commission ledger.
    Route::livewire('accounting/settlement', 'pages::admin.accounting.settlement')->name('accounting.settlement');
    Route::livewire('accounting/revenue', 'pages::admin.accounting.revenue')->name('accounting.revenue');
    Route::livewire('accounting/balance-sheet', 'pages::admin.accounting.balance-sheet')->name('accounting.balance-sheet');
    Route::livewire('accounting/commission-ledger', 'pages::admin.accounting.commission-ledger')->name('accounting.commission-ledger');

    // Engagement — coupons & events, support tickets, announcements & popups.
    Route::livewire('coupons', 'pages::admin.coupons.index')->name('coupons');
    Route::livewire('tickets', 'pages::admin.tickets.index')->name('tickets');
    Route::livewire('announcements', 'pages::admin.announcements.index')->name('announcements');
});
