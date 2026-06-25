<?php

use App\Support\AdminDemoData;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Support Tickets')] class extends Component {
    public string $status = '';
    public ?int $openId = null;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public function mount(): void
    {
        $this->items = AdminDemoData::tickets();
    }

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function rows(): array
    {
        return collect($this->items)
            ->when($this->status !== '', fn ($c) => $c->where('status', $this->status))
            ->values()
            ->all();
    }

    /** @return array<string, mixed>|null */
    #[Computed]
    public function current(): ?array
    {
        return collect($this->items)->firstWhere('id', $this->openId);
    }

    /** @return array<int, string> */
    #[Computed]
    public function cannedResponses(): array
    {
        return AdminDemoData::cannedResponses();
    }

    public function viewTicket(int $id): void
    {
        $this->openId = $id;
        Flux::modal('ticket-detail')->show();
    }

    public function sendCanned(): void
    {
        Flux::modal('ticket-detail')->close();
        Flux::toast(text: __('Reply sent'), variant: 'success');
    }

    public function resolve(int $id): void
    {
        $this->items = collect($this->items)->map(function ($t) use ($id) {
            if ($t['id'] === $id) {
                $t['status'] = 'closed';
            }

            return $t;
        })->all();

        Flux::modal('ticket-detail')->close();
        Flux::toast(text: __('Ticket resolved'), variant: 'success');
    }
}; ?>

@php
    $priorityMeta = [
        'urgent' => ['color' => 'red', 'label' => __('Urgent')],
        'high' => ['color' => 'amber', 'label' => __('High')],
        'normal' => ['color' => 'blue', 'label' => __('Normal')],
        'low' => ['color' => 'zinc', 'label' => __('Low')],
    ];
    $statusMeta = [
        'open' => ['color' => 'green', 'label' => __('Open')],
        'in_progress' => ['color' => 'amber', 'label' => __('In Progress')],
        'closed' => ['color' => 'zinc', 'label' => __('Closed')],
    ];
@endphp

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Support Tickets')" route="admin.tickets" :breadcrumb="[__('Engagement'), __('Support Tickets')]" />

    <x-admin.page-header :title="__('Support & Ticketing')">
        <x-slot:actions>
            <div class="w-40">
                <flux:select wire:model.live="status" size="sm" :aria-label="__('Status')">
                    <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
                    <flux:select.option value="open">{{ __('Open') }}</flux:select.option>
                    <flux:select.option value="in_progress">{{ __('In Progress') }}</flux:select.option>
                    <flux:select.option value="closed">{{ __('Closed') }}</flux:select.option>
                </flux:select>
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table :selectable="false" stick-first>
        <x-slot:head>
            <tr>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Ticket') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Player') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Subject') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Brand') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Priority') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Status') }}</th>
                <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Updated') }}</th>
                <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Manage') }}</th>
            </tr>
        </x-slot:head>

        @forelse ($this->rows as $t)
            <tr wire:key="ticket-{{ $t['id'] }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="px-3 py-2 font-mono text-xs text-zinc-500">#{{ $t['id'] }}</td>
                <td class="px-3 py-2 font-semibold text-zinc-800 dark:text-zinc-100">{{ $t['player'] }}</td>
                <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">{{ $t['subject'] }}</td>
                <td class="px-3 py-2"><flux:badge color="zinc" size="sm" inset="top bottom">{{ $t['brand'] }}</flux:badge></td>
                <td class="px-3 py-2"><flux:badge :color="$priorityMeta[$t['priority']]['color']" size="sm" inset="top bottom">{{ $priorityMeta[$t['priority']]['label'] }}</flux:badge></td>
                <td class="px-3 py-2"><flux:badge :color="$statusMeta[$t['status']]['color']" size="sm" inset="top bottom">{{ $statusMeta[$t['status']]['label'] }}</flux:badge></td>
                <td class="px-3 py-2 tabular-nums text-zinc-500">{{ $t['updated'] }}</td>
                <td class="px-3 py-2 text-end">
                    <flux:button wire:click="viewTicket({{ $t['id'] }})" size="xs" variant="subtle" icon="chat-bubble-left-right">{{ __('Open') }}</flux:button>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="px-3 py-8 text-center text-zinc-400">{{ __('No data found') }}</td></tr>
        @endforelse
    </x-admin.table>

    {{-- Ticket detail modal: conversation thread + account snapshot + canned responses --}}
    <flux:modal name="ticket-detail" class="w-full max-w-2xl">
        @if ($this->current)
            <div class="flex flex-col gap-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:heading size="lg">#{{ $this->current['id'] }} · {{ $this->current['subject'] }}</flux:heading>
                        <flux:text class="mt-0.5">{{ $this->current['player'] }} · {{ $this->current['brand'] }}</flux:text>
                    </div>
                    <flux:badge :color="$priorityMeta[$this->current['priority']]['color']" size="sm">{{ $priorityMeta[$this->current['priority']]['label'] }}</flux:badge>
                </div>

                {{-- Account snapshot --}}
                <div class="grid grid-cols-2 gap-3 border border-zinc-200 bg-zinc-50 p-3 text-sm dark:border-zinc-700 dark:bg-zinc-900/40">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wide text-zinc-400">{{ __('Current balance') }}</div>
                        <div class="font-semibold tabular-nums text-zinc-800 dark:text-zinc-100">₱{{ number_format($this->current['balance']) }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wide text-zinc-400">{{ __('Last transaction') }}</div>
                        <div class="font-medium text-zinc-700 dark:text-zinc-200">{{ $this->current['lastTx'] }}</div>
                    </div>
                </div>

                {{-- Conversation thread --}}
                <div class="flex max-h-60 flex-col gap-2 overflow-y-auto">
                    @foreach ($this->current['thread'] as $msg)
                        <div @class([
                            'max-w-[80%] rounded-lg px-3 py-2 text-sm',
                            'self-end bg-accent text-[color:var(--color-accent-foreground)]' => $msg['from'] === 'agent',
                            'self-start bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' => $msg['from'] !== 'agent',
                        ])>
                            <div>{{ $msg['body'] }}</div>
                            <div class="mt-1 text-[10px] opacity-70">{{ $msg['from'] === 'agent' ? __('Support') : $this->current['player'] }} · {{ $msg['time'] }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Canned responses --}}
                <div class="flex flex-col gap-1.5">
                    <div class="text-[10px] font-bold uppercase tracking-wide text-zinc-400">{{ __('Canned responses') }}</div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($this->cannedResponses as $reply)
                            <flux:button wire:click="sendCanned" size="xs" variant="subtle" class="max-w-full">
                                <span class="truncate">{{ \Illuminate\Support\Str::limit($reply, 38) }}</span>
                            </flux:button>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close><flux:button variant="ghost">{{ __('Close') }}</flux:button></flux:modal.close>
                    <flux:button wire:click="resolve({{ $this->current['id'] }})" variant="primary" icon="check">{{ __('Resolve ticket') }}</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
