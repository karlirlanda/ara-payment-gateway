{{--
    Money/points adjust modal + confirmation step. The operation IS the button: two
    explicit, colour-coded actions (green Add / red Subtract). Clicking one does NOT
    apply immediately — it opens a confirmation modal summarising the change. The
    action buttons stay disabled until a positive amount is entered, so the previewed
    result never just mirrors the current value.

    Renders two modals: "{{ $name }}" (the editor) and "{{ $name }}-confirm".

    The host Livewire component must expose:
      - public int $adjustAmount, public int $adjustCurrent, public string $adjustTitle,
        public string $pendingMode
      - confirmAdjust(string $mode)  — sets pendingMode + opens "{{ $name }}-confirm"
      - doAdjust()                   — applies the change and closes both modals
--}}
@props(['name', 'title' => ''])

<flux:modal name="{{ $name }}" class="w-full max-w-sm">
    <div class="flex flex-col gap-4">
        <flux:heading size="lg">{{ $title }}</flux:heading>

        <div class="flex items-center justify-between border border-zinc-200 bg-zinc-50 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900/50">
            <span class="text-sm text-zinc-500">{{ __('Current') }}</span>
            <span class="text-base font-bold tabular-nums text-zinc-800 dark:text-zinc-100" x-text="(Number($wire.adjustCurrent) || 0).toLocaleString()"></span>
        </div>

        {{-- Text input with thousands separators; the digits sync to the integer $wire.adjustAmount. --}}
        <div x-data="{
            get formatted() {
                const n = Number($wire.adjustAmount) || 0;
                return n === 0 ? '' : n.toLocaleString();
            },
            onInput(event) {
                const digits = event.target.value.replace(/[^0-9]/g, '');
                const value = digits ? parseInt(digits, 10) : 0;
                $wire.adjustAmount = value;
                event.target.value = value === 0 ? '' : value.toLocaleString();
            },
        }">
            <flux:input type="text" inputmode="numeric" x-bind:value="formatted" x-on:input="onInput"
                :label="__('Amount to add or subtract')" :placeholder="__('Enter an amount')" />
        </div>

        <div class="flex flex-wrap gap-1.5">
            @foreach ([10000, 50000, 100000, 1000000] as $chip)
                <button type="button" x-on:click="$wire.adjustAmount = (Number($wire.adjustAmount) || 0) + {{ $chip }}"
                    class="border border-zinc-200 px-2 py-1 text-xs tabular-nums text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-700/40">+{{ number_format($chip) }}</button>
            @endforeach
            <button type="button" x-on:click="$wire.adjustAmount = 0"
                class="border border-zinc-200 px-2 py-1 text-xs text-zinc-500 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-700/40">{{ __('Clear') }}</button>
        </div>

        {{-- Colour legend --}}
        <div class="flex items-center gap-4 text-xs text-zinc-500">
            <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-emerald-500"></span>{{ __('Green = add (increase)') }}</span>
            <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-rose-500"></span>{{ __('Red = subtract (decrease)') }}</span>
        </div>

        {{-- Each action is its own colour-coded button; disabled until a positive amount is entered. --}}
        <div class="grid grid-cols-2 gap-2">
            <button type="button" wire:click="confirmAdjust('add')" x-bind:disabled="!((Number($wire.adjustAmount) || 0) > 0)"
                class="flex flex-col items-center gap-0.5 border border-emerald-500 bg-emerald-50 px-3 py-2 text-emerald-700 hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:bg-emerald-500/20">
                <span class="flex items-center gap-1 text-sm font-semibold"><flux:icon icon="plus" class="size-3.5" />{{ __('Add') }} <span class="tabular-nums" x-show="(Number($wire.adjustAmount) || 0) > 0" x-text="(Number($wire.adjustAmount) || 0).toLocaleString()"></span></span>
                <span class="text-xs tabular-nums opacity-80" x-show="(Number($wire.adjustAmount) || 0) > 0" x-text="'→ ' + ((Number($wire.adjustCurrent) || 0) + (Number($wire.adjustAmount) || 0)).toLocaleString()"></span>
            </button>
            <button type="button" wire:click="confirmAdjust('subtract')" x-bind:disabled="!((Number($wire.adjustAmount) || 0) > 0)"
                class="flex flex-col items-center gap-0.5 border border-rose-500 bg-rose-50 px-3 py-2 text-rose-700 hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500/20">
                <span class="flex items-center gap-1 text-sm font-semibold"><flux:icon icon="minus" class="size-3.5" />{{ __('Subtract') }} <span class="tabular-nums" x-show="(Number($wire.adjustAmount) || 0) > 0" x-text="(Number($wire.adjustAmount) || 0).toLocaleString()"></span></span>
                <span class="text-xs tabular-nums opacity-80" x-show="(Number($wire.adjustAmount) || 0) > 0" x-text="'→ ' + Math.max(0, (Number($wire.adjustCurrent) || 0) - (Number($wire.adjustAmount) || 0)).toLocaleString()"></span>
            </button>
        </div>

        <flux:modal.close class="self-end">
            <flux:button variant="ghost" size="sm">{{ __('Cancel') }}</flux:button>
        </flux:modal.close>
    </div>
</flux:modal>

{{-- Confirmation step --}}
<flux:modal name="{{ $name }}-confirm" class="w-full max-w-sm">
    <div class="flex flex-col gap-4">
        <flux:heading size="lg">{{ __('Confirm adjustment') }}</flux:heading>

        <div class="flex flex-col gap-1.5 border border-zinc-200 px-3 py-3 text-sm dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <span class="text-zinc-500">{{ __('Operation') }}</span>
                <span class="font-semibold text-emerald-600 dark:text-emerald-400" x-show="$wire.pendingMode === 'add'">＋ {{ __('Add') }}</span>
                <span class="font-semibold text-rose-600 dark:text-rose-400" x-show="$wire.pendingMode === 'subtract'">－ {{ __('Subtract') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-zinc-500">{{ __('Amount') }}</span>
                <span class="font-semibold tabular-nums" x-text="(Number($wire.adjustAmount) || 0).toLocaleString()"></span>
            </div>
            <flux:separator variant="subtle" class="my-1" />
            <div class="flex items-center justify-between">
                <span class="text-zinc-500">{{ __('Current') }}</span>
                <span class="tabular-nums" x-text="(Number($wire.adjustCurrent) || 0).toLocaleString()"></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-zinc-500">{{ __('New value') }}</span>
                <span class="text-base font-bold tabular-nums"
                    :class="$wire.pendingMode === 'subtract' ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'"
                    x-text="($wire.pendingMode === 'subtract' ? Math.max(0, (Number($wire.adjustCurrent) || 0) - (Number($wire.adjustAmount) || 0)) : (Number($wire.adjustCurrent) || 0) + (Number($wire.adjustAmount) || 0)).toLocaleString()"></span>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close>
            <button type="button" wire:click="doAdjust"
                class="inline-flex items-center px-4 py-1.5 text-sm font-semibold text-white"
                :class="$wire.pendingMode === 'subtract' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                x-text="$wire.pendingMode === 'subtract' ? '{{ __('Confirm subtract') }}' : '{{ __('Confirm add') }}'"></button>
        </div>
    </div>
</flux:modal>
