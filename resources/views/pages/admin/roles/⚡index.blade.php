<?php

use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Role Management')] class extends Component {
    /** @var array<int, array{id:int,name:string,label:string,users:int}> */
    public array $roles = [];

    /** @var array<string, array<int, string>> module => permission keys */
    public array $modules = [];

    public ?int $assigningRoleId = null;

    /** @var array<int, string> */
    public array $assigned = [];

    public function mount(): void
    {
        $this->roles = [
            ['id' => 1, 'name' => 'admin',  'label' => 'Administrator', 'users' => 2],
            ['id' => 2, 'name' => 'system', 'label' => 'Operator',      'users' => 1],
            ['id' => 3, 'name' => 'user',   'label' => 'Member',        'users' => 1],
        ];

        $this->modules = [
            'System' => ['users.view', 'users.create', 'users.edit', 'users.delete'],
            'Roles' => ['roles.view', 'roles.assign'],
            'Content' => ['content.view', 'content.publish'],
        ];
    }

    public function assign(int $roleId): void
    {
        $this->assigningRoleId = $roleId;
        // demo: admin gets everything, others get view-only
        $role = collect($this->roles)->firstWhere('id', $roleId);
        $all = collect($this->modules)->flatten()->all();
        $this->assigned = ($role['name'] ?? '') === 'admin'
            ? $all
            : array_values(array_filter($all, fn ($p) => str_ends_with($p, '.view')));

        Flux::modal('role-permissions')->show();
    }

    public function saveAssignment(): void
    {
        // Frontend-only phase: no persistence yet.
        Flux::modal('role-permissions')->close();
        Flux::toast(text: __('Permissions updated'), variant: 'success');
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Roles')" route="admin.roles" :breadcrumb="[__('System'), __('Roles')]" />

    <flux:heading size="lg">{{ __('Role Management') }}</flux:heading>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('ID') }}</flux:table.column>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Label') }}</flux:table.column>
            <flux:table.column>{{ __('Users') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($roles as $role)
                <flux:table.row :key="$role['id']">
                    <flux:table.cell>{{ $role['id'] }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="indigo" size="sm" inset="top bottom">{{ $role['name'] }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell variant="strong">{{ $role['label'] }}</flux:table.cell>
                    <flux:table.cell class="tabular-nums">{{ $role['users'] }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button wire:click="assign({{ $role['id'] }})" size="xs" variant="subtle" icon="key">{{ __('Permissions') }}</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:modal name="role-permissions" class="w-full max-w-lg">
        <form wire:submit="saveAssignment" class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Assign permissions') }}</flux:heading>
            <div class="flex flex-col gap-4">
                @foreach ($modules as $module => $permissions)
                    <div>
                        <div class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ $module }}</div>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($permissions as $permission)
                                <flux:checkbox wire:model="assigned" value="{{ $permission }}" :label="$permission" />
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
