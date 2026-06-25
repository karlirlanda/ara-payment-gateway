<?php

use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('User Management')] class extends Component {
    use WithPagination;

    public string $search = '';

    /** @var array<int, array{id:int,nickname:string,login:string,phone:string,role:string,enabled:bool}> */
    public array $users = [];

    // Modal form state
    public ?int $editingId = null;
    public string $form_nickname = '';
    public string $form_login = '';
    public string $form_phone = '';
    public string $form_role = 'user';

    // Delete confirmation state
    public ?int $deletingId = null;

    public function mount(): void
    {
        $this->users = [
            ['id' => 1, 'nickname' => 'admin',  'login' => 'admin',  'phone' => '13577728948', 'role' => 'admin',  'enabled' => true],
            ['id' => 2, 'nickname' => 'test',   'login' => 'test',   'phone' => '18908775870', 'role' => 'admin',  'enabled' => true],
            ['id' => 3, 'nickname' => 'operator','login' => 'system','phone' => '13708779424', 'role' => 'system', 'enabled' => true],
            ['id' => 4, 'nickname' => 'xpyzwm', 'login' => 'xpyzwm', 'phone' => '13577712345', 'role' => 'user',   'enabled' => false],
        ];
    }

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        $filtered = collect($this->users)
            ->when($this->search !== '', fn ($c) => $c->filter(
                fn ($u) => str_contains(strtolower($u['nickname'].$u['login'].$u['phone']), strtolower($this->search))
            ))
            ->values();

        $perPage = 10;
        $page = $this->getPage();

        return new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => request()->url()],
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $this->users = collect($this->users)->map(function ($u) use ($id) {
            if ($u['id'] === $id) {
                $u['enabled'] = ! $u['enabled'];
            }

            return $u;
        })->all();
    }

    public function createUser(): void
    {
        $this->reset(['editingId', 'form_nickname', 'form_login', 'form_phone', 'form_role']);
        Flux::modal('user-form')->show();
    }

    public function editUser(int $id): void
    {
        $user = collect($this->users)->firstWhere('id', $id);
        if (! $user) {
            return;
        }
        $this->editingId = $user['id'];
        $this->form_nickname = $user['nickname'];
        $this->form_login = $user['login'];
        $this->form_phone = $user['phone'];
        $this->form_role = $user['role'];
        Flux::modal('user-form')->show();
    }

    public function saveUser(): void
    {
        $validated = $this->validate([
            'form_nickname' => ['required', 'string', 'max:50'],
            'form_login' => ['required', 'string', 'max:50'],
            'form_phone' => ['required', 'string', 'max:20'],
            'form_role' => ['required', 'in:admin,system,user'],
        ]);

        if ($this->editingId !== null) {
            $this->users = collect($this->users)->map(function ($u) use ($validated) {
                if ($u['id'] === $this->editingId) {
                    $u['nickname'] = $validated['form_nickname'];
                    $u['login'] = $validated['form_login'];
                    $u['phone'] = $validated['form_phone'];
                    $u['role'] = $validated['form_role'];
                }

                return $u;
            })->all();
        } else {
            $this->users[] = [
                'id' => (collect($this->users)->max('id') ?? 0) + 1,
                'nickname' => $validated['form_nickname'],
                'login' => $validated['form_login'],
                'phone' => $validated['form_phone'],
                'role' => $validated['form_role'],
                'enabled' => true,
            ];
        }

        Flux::modal('user-form')->close();
        Flux::toast(text: __('Saved'), variant: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        Flux::modal('delete-user')->show();
    }

    public function deleteUser(): void
    {
        $this->users = collect($this->users)->reject(fn ($u) => $u['id'] === $this->deletingId)->values()->all();
        $this->deletingId = null;
        Flux::modal('delete-user')->close();
        Flux::toast(text: __('User deleted'), variant: 'success');
    }

    public function roleColor(string $role): string
    {
        return match ($role) {
            'admin' => 'indigo',
            'system' => 'amber',
            default => 'zinc',
        };
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-page-meta :title="__('Users')" route="admin.users" :breadcrumb="[__('System'), __('Users')]" />

    <div class="flex items-center justify-between gap-3">
        <flux:heading size="lg">{{ __('User Management') }}</flux:heading>
        <div class="flex items-center gap-2">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" size="sm" :placeholder="__('Search')" class="w-56" />
            <flux:button wire:click="createUser" icon="plus" variant="primary" size="sm">{{ __('Add user') }}</flux:button>
        </div>
    </div>

    <flux:table :paginate="$this->rows">
        <flux:table.columns>
            <flux:table.column>{{ __('ID') }}</flux:table.column>
            <flux:table.column>{{ __('Nickname') }}</flux:table.column>
            <flux:table.column>{{ __('Login') }}</flux:table.column>
            <flux:table.column>{{ __('Phone') }}</flux:table.column>
            <flux:table.column>{{ __('Role') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->rows as $user)
                <flux:table.row :key="$user['id']">
                    <flux:table.cell>{{ $user['id'] }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ $user['nickname'] }}</flux:table.cell>
                    <flux:table.cell>{{ $user['login'] }}</flux:table.cell>
                    <flux:table.cell class="tabular-nums">{{ $user['phone'] }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$this->roleColor($user['role'])" size="sm" inset="top bottom">{{ $user['role'] }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:switch
                            :checked="$user['enabled']"
                            wire:click="toggleStatus({{ $user['id'] }})"
                            wire:key="user-switch-{{ $user['id'] }}-{{ $user['enabled'] ? '1' : '0' }}"
                        />
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button wire:click="editUser({{ $user['id'] }})" size="xs" variant="subtle">{{ __('Edit') }}</flux:button>
                        {{-- TODO(backend phase): wire:click="resetPassword({{ $user['id'] }})" --}}
                        <flux:button size="xs" variant="subtle" disabled>{{ __('Reset password') }}</flux:button>
                        <flux:button wire:click="confirmDelete({{ $user['id'] }})" size="xs" variant="danger">{{ __('Delete') }}</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Add / Edit modal --}}
    <flux:modal name="user-form" class="w-full max-w-md">
        <form wire:submit="saveUser" class="flex flex-col gap-4">
            <flux:heading size="lg">{{ $editingId ? __('Edit user') : __('Add user') }}</flux:heading>
            <flux:input wire:model="form_nickname" :label="__('Nickname')" />
            <flux:input wire:model="form_login" :label="__('Login')" />
            <flux:input wire:model="form_phone" :label="__('Phone')" />
            <flux:select wire:model="form_role" :label="__('Role')">
                <flux:select.option value="admin">admin</flux:select.option>
                <flux:select.option value="system">system</flux:select.option>
                <flux:select.option value="user">user</flux:select.option>
            </flux:select>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete confirmation modal (replaces the native browser confirm dialog) --}}
    <flux:modal name="delete-user" class="w-full max-w-sm">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-1">
                <flux:heading size="lg">{{ __('Delete user') }}</flux:heading>
                <flux:text>{{ __('Are you sure you want to delete this user? This action cannot be undone.') }}</flux:text>
                @if ($deletingId)
                    <flux:text class="mt-1 font-medium text-zinc-800 dark:text-zinc-200">
                        {{ collect($users)->firstWhere('id', $deletingId)['nickname'] ?? '' }}
                    </flux:text>
                @endif
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deleteUser" variant="danger" data-test="confirm-delete">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
