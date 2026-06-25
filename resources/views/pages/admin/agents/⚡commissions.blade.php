<?php

use App\Support\AdminDemoData;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Commission Settings')] class extends Component {
    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public ?int $editingId = null;
    public string $form_type = 'loss';
    public float $form_rate = 1.0;

    public function mount(): void
    {
        $this->items = AdminDemoData::agentCommissions();
    }

    public function edit(int $id): void
    {
        $row = collect($this->items)->firstWhere('id', $id);
        if (! $row) {
            return;
        }
        $this->editingId = $id;
        $this->form_type = $row['type'];
        $this->form_rate = (float) $row['rate'];
        Flux::modal('commission-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'form_type' => ['required', 'in:turnover,loss'],
            'form_rate' => ['required', 'numeric', 'min:0', 'max:10'],
        ]);

        $this->items = collect($this->items)->map(function ($a) use ($validated) {
            if ($a['id'] === $this->editingId) {
                $a['type'] = $validated['form_type'];
                $a['rate'] = $validated['form_rate'];
            }

            return $a;
        })->all();

        Flux::modal('commission-form')->close();
        Flux::toast(text: __('Commission updated'), variant: 'success');
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Commission Settings')" route="admin.agents.commissions" :breadcrumb="[__('Agents'), __('Commission Settings')]" />

    <x-admin.page-header :title="__('Commission Settings')" />

    <p class="text-sm text-zinc-500">{{ __('Set the rolling rate per agent. Changes apply to all downline players automatically.') }}</p>

    <x-admin.table :selectable="false" stick-first>
        <x-slot:head><tr>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Agent') }}</th>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Level') }}</th>
            <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Type') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Rolling Rate') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Players') }}</th>
            <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Manage') }}</th>
        </tr></x-slot:head>
        @foreach ($this->items as $a)
            <tr wire:key="agc-{{ $a['id'] }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2 font-mono text-xs font-semibold">{{ $a['username'] }}</td>
                <td class="px-3 py-2"><flux:badge :color="$a['level'] === 'Head' ? 'indigo' : 'zinc'" size="sm" inset="top bottom">{{ $a['level'] }}</flux:badge></td>
                <td class="px-3 py-2">{{ $a['type'] === 'turnover' ? __('Turnover') : __('Loss rebate') }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ $a['rate'] }}%</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ number_format($a['players']) }}</td>
                <td class="px-3 py-2 text-end">
                    <flux:button wire:click="edit({{ $a['id'] }})" size="xs" variant="subtle" icon="pencil-square">{{ __('Edit') }}</flux:button>
                </td>
            </tr>
        @endforeach
    </x-admin.table>

    <flux:modal name="commission-form" class="w-full max-w-sm">
        <form wire:submit="save" class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Edit commission') }}</flux:heading>
            <flux:select wire:model="form_type" :label="__('Commission type')">
                <flux:select.option value="loss">{{ __('Loss rebate') }}</flux:select.option>
                <flux:select.option value="turnover">{{ __('Turnover') }}</flux:select.option>
            </flux:select>
            <flux:input type="number" step="0.1" wire:model="form_rate" :label="__('Rolling rate (%)')" />
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
