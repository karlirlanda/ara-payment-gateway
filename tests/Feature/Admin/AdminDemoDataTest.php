<?php

use App\Support\AdminDemoData;

it('exposes payment summary totals', function () {
    expect(AdminDemoData::summary())
        ->toHaveKeys(['totalDeposits', 'totalWithdrawals', 'todayNet', 'pendingWithdrawals', 'members', 'newMembers', 'liveUsers', 'reconVariance']);
});

it('exposes header action badge counts', function () {
    $badges = AdminDemoData::actionBadges();
    expect($badges)->toHaveKeys(['deposit', 'withdraw', 'approval']);
    expect($badges['deposit'])->toBeInt();
});

it('provides enough demo members (multiple pages) with the required fields', function () {
    $members = AdminDemoData::members();
    expect($members)->toHaveCount(65);
    expect($members[0])->toHaveKeys([
        'id', 'level', 'username', 'nickname', 'store', 'storeLevel', 'gateway', 'bank', 'phone', 'balance',
        'commissionType', 'points', 'deposit', 'withdraw', 'status', 'lastLogin',
        'ip', 'joinedAt', 'domain',
    ]);
});

it('assigns every member a known payment gateway', function () {
    foreach (AdminDemoData::members() as $member) {
        expect($member['gateway'])->toBeIn(AdminDemoData::GATEWAYS);
    }
});

it('includes net-negative members so the red difference styling is exercised', function () {
    $hasNegative = collect(AdminDemoData::members())
        ->contains(fn ($m) => $m['deposit'] - $m['withdraw'] < 0);

    expect($hasNegative)->toBeTrue();
});

it('exposes the three proposal gateways with overview fields', function () {
    $gateways = AdminDemoData::gateways();
    expect($gateways)->toHaveCount(3);
    expect(collect($gateways)->pluck('name')->all())->toBe(['GCash', 'Maya', 'GoTyme']);
    expect($gateways[0])->toHaveKeys(['name', 'operator', 'license', 'users', 'deposit', 'withdraw', 'fee', 'segment', 'todayVolume', 'todayCount']);
});

it('exposes daily reconciliation rows per gateway', function () {
    $rows = AdminDemoData::reconciliation();
    expect($rows)->not->toBeEmpty();
    expect($rows[0])->toHaveKeys(['gateway', 'date', 'platformCount', 'platformAmount', 'providerCount', 'providerAmount', 'variance', 'status']);
});

it('provides gateway transactions with a reference and at least one pending row', function () {
    $rows = AdminDemoData::transactions('withdraw');
    expect($rows[0])->toHaveKeys(['id', 'level', 'username', 'gateway', 'amount', 'account', 'reference', 'appliedAt', 'processedAt', 'status']);
    expect(collect($rows)->where('status', 'pending'))->not->toBeEmpty();
});

it('provides notifications and live users', function () {
    expect(AdminDemoData::notifications())->not->toBeEmpty();
    expect(AdminDemoData::liveUsers())->not->toBeEmpty();
});
