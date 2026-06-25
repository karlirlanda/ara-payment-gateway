<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Permissions')] class extends Component {
    /** @var array<string, array<int, array{key:string,label:string}>> */
    public array $groups = [];

    public function mount(): void
    {
        $this->groups = [
            'System' => [
                ['key' => 'users.view', 'label' => 'View users'],
                ['key' => 'users.create', 'label' => 'Create users'],
                ['key' => 'users.edit', 'label' => 'Edit users'],
                ['key' => 'users.delete', 'label' => 'Delete users'],
            ],
            'Roles' => [
                ['key' => 'roles.view', 'label' => 'View roles'],
                ['key' => 'roles.assign', 'label' => 'Assign permissions'],
            ],
            'Content' => [
                ['key' => 'content.view', 'label' => 'View content'],
                ['key' => 'content.publish', 'label' => 'Publish content'],
            ],
        ];
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-page-meta :title="__('Permissions')" route="admin.permissions" :breadcrumb="[__('System'), __('Permissions')]" />

    <div>
        <flux:heading size="lg">{{ __('Permissions') }}</flux:heading>
        <flux:text class="mt-1">{{ __('All permissions available to assign to roles, grouped by module.') }}</flux:text>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        @foreach ($groups as $group => $permissions)
            <flux:card class="overflow-hidden p-0">
                <div class="flex items-center gap-2 border-b border-zinc-200 bg-zinc-50 px-4 py-2.5 dark:border-zinc-700 dark:bg-zinc-900/50">
                    <flux:icon icon="rectangle-stack" class="size-4 text-zinc-400" />
                    <span class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ $group }}</span>
                    <flux:badge color="zinc" size="sm" class="ms-auto">{{ count($permissions) }}</flux:badge>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                    @foreach ($permissions as $permission)
                        <div class="flex items-center justify-between gap-4 px-4 py-2.5">
                            <flux:badge color="indigo" size="sm" class="font-mono">{{ $permission['key'] }}</flux:badge>
                            <span class="text-end text-sm text-zinc-600 dark:text-zinc-300">{{ $permission['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        @endforeach
    </div>
</div>
