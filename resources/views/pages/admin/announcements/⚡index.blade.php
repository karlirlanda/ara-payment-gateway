<?php

use App\Support\AdminDemoData;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Announcements & Popups')] class extends Component {
    public string $tab = 'announcements';

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    // New announcement modal state.
    public string $form_title = '';
    public string $form_body = '';
    public string $form_audience = 'All brands';
    public string $form_scheduledAt = '';
    public bool $form_pinned = false;

    public function mount(): void
    {
        $this->items = AdminDemoData::announcements();
    }

    /** @return array<int, array<string, mixed>> */
    #[Computed]
    public function popups(): array
    {
        return AdminDemoData::popups();
    }

    public function createAnnouncement(): void
    {
        $this->reset(['form_title', 'form_body', 'form_audience', 'form_scheduledAt', 'form_pinned']);
        $this->form_audience = 'All brands';
        Flux::modal('announcement-form')->show();
    }

    public function saveAnnouncement(): void
    {
        $validated = $this->validate([
            'form_title' => ['required', 'string', 'max:120'],
            'form_body' => ['required', 'string', 'max:500'],
            'form_audience' => ['required', 'string', 'max:60'],
            'form_scheduledAt' => ['required', 'date'],
        ]);

        array_unshift($this->items, [
            'id' => (collect($this->items)->max('id') ?? 0) + 1,
            'title' => $validated['form_title'],
            'audience' => $validated['form_audience'],
            'scheduledAt' => $validated['form_scheduledAt'],
            'pinned' => $this->form_pinned,
            'status' => 'scheduled',
            'body' => $validated['form_body'],
        ]);

        Flux::modal('announcement-form')->close();
        Flux::toast(text: __('Announcement scheduled'), variant: 'success');
    }

    public function togglePin(int $id): void
    {
        $this->items = collect($this->items)->map(function ($a) use ($id) {
            if ($a['id'] === $id) {
                $a['pinned'] = ! $a['pinned'];
            }

            return $a;
        })->all();
    }
}; ?>

@php
    $statusMeta = [
        'published' => ['color' => 'green', 'label' => __('Published')],
        'scheduled' => ['color' => 'blue', 'label' => __('Scheduled')],
        'draft' => ['color' => 'zinc', 'label' => __('Draft')],
        'active' => ['color' => 'green', 'label' => __('Active')],
    ];
    $tabs = [
        ['key' => 'announcements', 'label' => __('Announcements')],
        ['key' => 'popups', 'label' => __('Popups')],
    ];
@endphp

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Announcements & Popups')" route="admin.announcements" :breadcrumb="[__('Engagement'), __('Announcements')]" />

    <x-admin.page-header :title="__('Announcements & Popups')">
        <x-slot:tabs>
            @foreach ($tabs as $t)
                <button type="button" wire:click="$set('tab', '{{ $t['key'] }}')"
                    @class([
                        'border border-b-0 px-4 py-1.5 text-xs',
                        'border-accent bg-accent text-[color:var(--color-accent-foreground)]' => $tab === $t['key'],
                        'border-zinc-200 bg-white text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' => $tab !== $t['key'],
                    ])>{{ $t['label'] }}</button>
            @endforeach
        </x-slot:tabs>
        <x-slot:actions>
            @if ($tab === 'announcements')
                <flux:button wire:click="createAnnouncement" icon="plus" variant="primary" size="sm">{{ __('New announcement') }}</flux:button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    @if ($tab === 'announcements')
        <x-admin.table :selectable="false" stick-first>
            <x-slot:head>
                <tr>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Title') }}</th>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Audience') }}</th>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Scheduled') }}</th>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Status') }}</th>
                    <th class="px-3 py-2 text-end font-semibold text-zinc-500">{{ __('Pinned') }}</th>
                </tr>
            </x-slot:head>

            @foreach ($this->items as $a)
                <tr wire:key="ann-{{ $a['id'] }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                    <td class="px-3 py-2">
                        <div class="flex items-center gap-2">
                            @if ($a['pinned'])<flux:icon icon="bookmark" variant="solid" class="size-3.5 text-accent" />@endif
                            <span class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $a['title'] }}</span>
                        </div>
                        <div class="mt-0.5 max-w-md truncate text-xs text-zinc-400">{{ $a['body'] }}</div>
                    </td>
                    <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">{{ $a['audience'] }}</td>
                    <td class="px-3 py-2 tabular-nums text-zinc-500">{{ $a['scheduledAt'] }}</td>
                    <td class="px-3 py-2"><flux:badge :color="$statusMeta[$a['status']]['color']" size="sm" inset="top bottom">{{ $statusMeta[$a['status']]['label'] }}</flux:badge></td>
                    <td class="px-3 py-2 text-end">
                        <flux:switch :checked="$a['pinned']" wire:click="togglePin({{ $a['id'] }})" wire:key="pin-{{ $a['id'] }}-{{ $a['pinned'] ? '1' : '0' }}" />
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @else
        <x-admin.table :selectable="false" stick-first>
            <x-slot:head>
                <tr>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Title') }}</th>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Audience') }}</th>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Frequency cap') }}</th>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Window') }}</th>
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __('Status') }}</th>
                </tr>
            </x-slot:head>

            @foreach ($this->popups as $p)
                <tr wire:key="popup-{{ $p['id'] }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                    <td class="px-3 py-2 font-semibold text-zinc-800 dark:text-zinc-100">{{ $p['title'] }}</td>
                    <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">{{ $p['audience'] }}</td>
                    <td class="px-3 py-2">{{ $p['frequency'] }}</td>
                    <td class="px-3 py-2 tabular-nums text-zinc-500">{{ $p['start'] }} → {{ $p['end'] }}</td>
                    <td class="px-3 py-2"><flux:badge :color="$statusMeta[$p['status']]['color']" size="sm" inset="top bottom">{{ $statusMeta[$p['status']]['label'] }}</flux:badge></td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif

    {{-- New announcement modal --}}
    <flux:modal name="announcement-form" class="w-full max-w-lg">
        <form wire:submit="saveAnnouncement" class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('New announcement') }}</flux:heading>
            <flux:input wire:model="form_title" :label="__('Title')" />
            <flux:textarea wire:model="form_body" :label="__('Body')" rows="4" />
            <div class="grid grid-cols-2 gap-3">
                <flux:input wire:model="form_audience" :label="__('Audience')" placeholder="All brands" />
                <flux:input type="datetime-local" wire:model="form_scheduledAt" :label="__('Schedule')" />
            </div>
            <flux:checkbox wire:model="form_pinned" :label="__('Pin to top')" />
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Schedule') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
