<?php

use App\Support\PlayerDemoData;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Cashier')] #[Layout('layouts.player')] class extends Component {
    /** Active tab: "deposit" or "withdraw". */
    #[Url]
    public string $tab = 'deposit';

    /** Selected provider key (gcash | maya | gotyme). */
    public string $providerKey = 'gcash';

    /** Amount in PHP (₱). */
    public ?int $amount = null;

    /** Destination account for withdrawals (mock). */
    public string $account = '';

    /** Current wallet balance, mirrored from the session. */
    public int $balance = 0;

    /** Reference shown in the deposit confirmation modal. */
    public string $pendingReference = '';

    public function mount(): void
    {
        if (! in_array($this->tab, ['deposit', 'withdraw'], true)) {
            $this->tab = 'deposit';
        }

        $this->balance = (int) session('player.demo.balance', PlayerDemoData::STARTING_BALANCE);
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['deposit', 'withdraw'], true) ? $tab : 'deposit';
        $this->resetErrorBag();
    }

    public function selectProvider(string $key): void
    {
        if (PlayerDemoData::provider($key) !== null) {
            $this->providerKey = $key;
        }
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /** @return array{key:string, name:string, tagline:string, icon:string, color:string, account:string, min:int, max:int} */
    public function provider(): array
    {
        return PlayerDemoData::provider($this->providerKey) ?? PlayerDemoData::providers()[0];
    }

    /**
     * Validate the amount against the selected provider's limits.
     * Returns the matched provider on success, or null after flagging an error.
     *
     * @return array{key:string, name:string, tagline:string, icon:string, color:string, account:string, min:int, max:int}|null
     */
    protected function validatedProvider(): ?array
    {
        $provider = $this->provider();

        if ($this->amount === null || $this->amount < $provider['min']) {
            $this->addError('amount', __('Minimum amount is ₱:min.', ['min' => number_format($provider['min'])]));

            return null;
        }

        if ($this->amount > $provider['max']) {
            $this->addError('amount', __('Maximum amount is ₱:max.', ['max' => number_format($provider['max'])]));

            return null;
        }

        return $provider;
    }

    /**
     * Step 1 of a deposit — open the (mock) payment confirmation modal.
     */
    public function proceedDeposit(): void
    {
        $this->resetErrorBag();

        if ($this->validatedProvider() === null) {
            return;
        }

        $this->pendingReference = 'DEP-'.now()->format('Ymd').'-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        Flux::modal('deposit-confirm')->show();
    }

    /**
     * Step 2 of a deposit — simulate the provider confirming payment and credit the wallet.
     */
    public function confirmDeposit(): void
    {
        $provider = $this->validatedProvider();

        if ($provider === null) {
            Flux::modal('deposit-confirm')->close();

            return;
        }

        $amount = (int) $this->amount;
        $this->balance += $amount;
        $this->persistBalance();
        $this->recordTransaction('deposit', $provider['name'], $amount, 'completed', $this->pendingReference);

        Flux::modal('deposit-confirm')->close();
        Flux::toast(
            heading: __('Deposit successful'),
            text: __(':provider deposit of ₱:amount credited.', ['provider' => $provider['name'], 'amount' => number_format($amount)]),
            variant: 'success',
        );

        $this->amount = null;
    }

    /**
     * Submit a withdrawal request — debits the wallet and queues it for (mock) approval.
     */
    public function submitWithdraw(): void
    {
        $this->resetErrorBag();

        $provider = $this->validatedProvider();

        if ($provider === null) {
            return;
        }

        if (trim($this->account) === '') {
            $this->addError('account', __('Enter the destination account.'));

            return;
        }

        if ($this->amount > $this->balance) {
            $this->addError('amount', __('Amount exceeds your wallet balance.'));

            return;
        }

        $amount = (int) $this->amount;
        $this->balance -= $amount;
        $this->persistBalance();

        $reference = 'WDL-'.now()->format('Ymd').'-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $this->recordTransaction('withdraw', $provider['name'], $amount, 'pending', $reference);

        Flux::toast(
            heading: __('Withdrawal requested'),
            text: __('₱:amount to :provider is pending approval.', ['amount' => number_format($amount), 'provider' => $provider['name']]),
            variant: 'success',
        );

        $this->amount = null;
        $this->account = '';
    }

    protected function persistBalance(): void
    {
        $player = session('player.demo', PlayerDemoData::profile());
        $player['balance'] = $this->balance;
        session(['player.demo' => $player]);
    }

    protected function recordTransaction(string $direction, string $provider, int $amount, string $status, string $reference): void
    {
        $transactions = session('player.transactions', PlayerDemoData::seedTransactions());

        array_unshift($transactions, [
            'id' => (int) (collect($transactions)->max('id') ?? 1000) + 1,
            'direction' => $direction,
            'provider' => $provider,
            'amount' => $amount,
            'status' => $status,
            'reference' => $reference,
            'time' => now()->format('Y-m-d H:i'),
        ]);

        session(['player.transactions' => $transactions]);
    }
}; ?>

@php
    $providers = \App\Support\PlayerDemoData::providers();
    $quickAmounts = \App\Support\PlayerDemoData::QUICK_AMOUNTS;
    $isDeposit = $tab === 'deposit';
    $provider = $this->provider();
    $colorRing = [
        'sky' => 'border-sky-400 bg-sky-400/10',
        'emerald' => 'border-emerald-400 bg-emerald-400/10',
        'amber' => 'border-amber-400 bg-amber-400/10',
    ];
    $colorIcon = [
        'sky' => 'text-sky-400',
        'emerald' => 'text-emerald-400',
        'amber' => 'text-amber-400',
    ];
@endphp

<div class="flex flex-col gap-5">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-black text-white">{{ __('Cashier') }}</h1>
        <div class="rounded-full border border-amber-400/30 bg-amber-400/10 px-3 py-1.5 text-sm font-bold tabular-nums text-amber-300">
            ₱{{ number_format($balance) }}
        </div>
    </div>

    {{-- Tabs --}}
    <div class="grid grid-cols-2 gap-1 rounded-xl border border-white/10 bg-zinc-900/60 p-1">
        <button type="button" wire:click="setTab('deposit')"
            @class([
                'flex items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-semibold transition-colors',
                'bg-amber-400 text-zinc-950' => $isDeposit,
                'text-zinc-400' => ! $isDeposit,
            ])>
            <flux:icon icon="arrow-down-tray" class="size-4" /> {{ __('Deposit') }}
        </button>
        <button type="button" wire:click="setTab('withdraw')"
            @class([
                'flex items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-semibold transition-colors',
                'bg-amber-400 text-zinc-950' => ! $isDeposit,
                'text-zinc-400' => $isDeposit,
            ])>
            <flux:icon icon="arrow-up-tray" class="size-4" /> {{ __('Withdraw') }}
        </button>
    </div>

    <div class="rounded-2xl border border-white/10 bg-zinc-900/60 p-5">
        {{-- Provider picker --}}
        <p class="mb-2 text-sm font-semibold text-zinc-300">{{ __('Select payment method') }}</p>
        <div class="grid grid-cols-3 gap-2.5">
            @foreach ($providers as $p)
                <button type="button" wire:click="selectProvider('{{ $p['key'] }}')"
                    @class([
                        'flex flex-col items-center gap-1.5 rounded-xl border p-3 text-center transition-colors',
                        ($colorRing[$p['color']] ?? 'border-amber-400 bg-amber-400/10') => $providerKey === $p['key'],
                        'border-white/10 bg-zinc-950/40 hover:border-white/25' => $providerKey !== $p['key'],
                    ])>
                    <flux:icon :icon="$p['icon']" @class(['size-6', $colorIcon[$p['color']] ?? 'text-amber-400']) />
                    <span class="text-xs font-bold text-white">{{ $p['name'] }}</span>
                </button>
            @endforeach
        </div>

        <p class="mt-2 text-xs text-zinc-500">{{ $provider['tagline'] }} · {{ __('Limit ₱:min – ₱:max', ['min' => number_format($provider['min']), 'max' => number_format($provider['max'])]) }}</p>

        <flux:separator class="my-5" variant="subtle" />

        {{-- Amount --}}
        <div class="flex flex-col gap-3">
            <flux:input wire:model="amount" type="number" :label="__('Amount (₱)')" placeholder="0" />

            <div class="flex flex-wrap gap-2">
                @foreach ($quickAmounts as $qa)
                    <button type="button" wire:click="setAmount({{ $qa }})"
                        class="rounded-lg border border-white/15 bg-zinc-950/40 px-3 py-1.5 text-sm font-semibold text-zinc-200 hover:border-amber-400 hover:text-amber-300">
                        ₱{{ number_format($qa) }}
                    </button>
                @endforeach
            </div>

            @if (! $isDeposit)
                <flux:input wire:model="account" :label="__('Destination account / number')" :placeholder="$provider['account']" />
            @endif

            @error('amount') <flux:text class="text-sm text-red-400">{{ $message }}</flux:text> @enderror
            @error('account') <flux:text class="text-sm text-red-400">{{ $message }}</flux:text> @enderror

            @if ($isDeposit)
                <flux:button wire:click="proceedDeposit" variant="primary" class="mt-1 w-full" data-test="deposit-proceed">
                    {{ __('Proceed to payment') }}
                </flux:button>
            @else
                <flux:button wire:click="submitWithdraw" variant="primary" class="mt-1 w-full" data-test="withdraw-submit">
                    {{ __('Request withdrawal') }}
                </flux:button>
            @endif
        </div>
    </div>

    <p class="text-center text-xs text-zinc-500">{{ __('All transactions are simulated — no real money is moved.') }}</p>

    {{-- Deposit confirmation modal (mock QR / reference) --}}
    <flux:modal name="deposit-confirm" class="w-full max-w-sm">
        <div class="flex flex-col items-center gap-4 text-center">
            <flux:heading size="lg">{{ __('Scan to pay with :provider', ['provider' => $provider['name']]) }}</flux:heading>

            {{-- Fake QR --}}
            <div class="grid grid-cols-8 gap-0.5 rounded-xl bg-white p-3">
                @for ($i = 0; $i < 64; $i++)
                    <span @class(['size-3', 'bg-zinc-900' => ($i * 7 + 3) % 3 !== 0, 'bg-white' => ($i * 7 + 3) % 3 === 0])></span>
                @endfor
            </div>

            <div class="w-full rounded-xl border border-white/10 bg-zinc-950/40 p-3 text-sm">
                <div class="flex justify-between"><span class="text-zinc-400">{{ __('Amount') }}</span><span class="font-bold tabular-nums text-amber-300">₱{{ number_format((int) $amount) }}</span></div>
                <div class="mt-1 flex justify-between"><span class="text-zinc-400">{{ __('Reference') }}</span><span class="font-mono text-xs text-zinc-300">{{ $pendingReference }}</span></div>
                <div class="mt-1 flex justify-between"><span class="text-zinc-400">{{ __('Pay to') }}</span><span class="tabular-nums text-zinc-300">{{ $provider['account'] }}</span></div>
            </div>

            <div class="flex w-full gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost" class="flex-1">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="confirmDeposit" variant="primary" class="flex-1" data-test="deposit-confirm">
                    {{ __("I've paid") }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
