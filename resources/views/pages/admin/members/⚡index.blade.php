<?php

use App\Support\AdminDemoData;
use Flux\Flux;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Member Management')] class extends Component {
    use WithPagination;

    public string $dateFrom = '';
    public string $dateTo = '';
    public string $keyword = '';
    public string $status = '';
    public int $perPage = 50;
    public ?int $deletingId = null;

    /** Selected row ids (drives the bulk-action toolbar). @var array<int, string> */
    public array $selected = [];

    public bool $selectPage = false;

    /** @var array<int, array<string, mixed>> */
    public array $members = [];

    public function mount(): void
    {
        $this->members = AdminDemoData::members();
    }

    public function updatedKeyword(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function resetFilters(): void
    {
        $this->reset(['dateFrom', 'dateTo', 'keyword', 'status']);
        $this->resetPage();
        $this->clearSelection();
    }

    public function refreshList(): void
    {
        $this->members = AdminDemoData::members();
        $this->clearSelection();
        Flux::toast(text: __('List refreshed'), variant: 'success');
    }

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        $filtered = collect($this->members)
            ->when($this->status !== '', fn ($c) => $c->where('status', $this->status))
            ->when($this->keyword !== '', fn ($c) => $c->filter(
                fn ($m) => str_contains(strtolower($m['username'].$m['nickname'].$m['phone']), strtolower($this->keyword))
            ))
            ->values();

        $page = $this->getPage();

        return new LengthAwarePaginator(
            $filtered->forPage($page, $this->perPage)->values(),
            $filtered->count(),
            $this->perPage,
            $page,
            ['path' => request()->url()],
        );
    }

    #[Computed]
    public function totals(): array
    {
        $c = collect($this->members);

        return [
            'count' => $c->count(),
            'balance' => $c->sum('balance'),
            'points' => $c->sum('points'),
            'net' => $c->sum('deposit') - $c->sum('withdraw'),
        ];
    }

    #[Computed]
    public function statusCounts(): array
    {
        $c = collect($this->members);

        return [
            '' => $c->count(),
            'normal' => $c->where('status', 'normal')->count(),
            'suspended' => $c->where('status', 'suspended')->count(),
            'withdrawn' => $c->where('status', 'withdrawn')->count(),
        ];
    }

    /** Ids on the current page (for the header select-all). @return array<int, string> */
    #[Computed]
    public function pageIds(): array
    {
        return collect($this->rows->items())->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    public function setStatusTab(string $status): void
    {
        $this->status = $status;
        $this->resetPage();
        $this->clearSelection();
    }

    // ── Bulk selection + actions ─────────────────────────────────────────────
    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectPage = false;
    }

    public function updatedSelectPage(bool $value): void
    {
        $this->selected = $value ? $this->pageIds : [];
    }

    public function confirmBulkForceLogout(): void
    {
        Flux::modal('bulk-force-logout')->show();
    }

    public function bulkForceLogout(): void
    {
        $n = count($this->selected);
        $this->clearSelection();
        Flux::modal('bulk-force-logout')->close();
        Flux::toast(text: trans_choice(':count member forced to log out|:count members forced to log out', $n, ['count' => $n]), variant: 'success');
    }

    public string $pendingBulkStatus = '';

    public function confirmBulkSetStatus(string $status): void
    {
        $this->pendingBulkStatus = $status;
        Flux::modal('bulk-set-status')->show();
    }

    public function bulkSetStatus(): void
    {
        $status = $this->pendingBulkStatus;
        $ids = array_map('intval', $this->selected);
        $this->members = collect($this->members)->map(function ($m) use ($ids, $status) {
            if (in_array($m['id'], $ids, true) && $m['status'] !== 'withdrawn') {
                $m['status'] = $status;
            }

            return $m;
        })->all();

        $n = count($this->selected);
        $this->clearSelection();
        Flux::modal('bulk-set-status')->close();
        Flux::toast(text: trans_choice(':count member updated|:count members updated', $n, ['count' => $n]), variant: 'success');
    }

    public function confirmBulkDelete(): void
    {
        Flux::modal('bulk-delete')->show();
    }

    public function bulkDelete(): void
    {
        $ids = array_map('intval', $this->selected);
        $n = count($ids);
        $this->members = collect($this->members)->reject(fn ($m) => in_array($m['id'], $ids, true))->values()->all();
        $this->clearSelection();
        Flux::modal('bulk-delete')->close();
        Flux::toast(text: trans_choice(':count member deleted|:count members deleted', $n, ['count' => $n]), variant: 'success');
    }

    /** Stream the filtered (or selected) rows as a CSV — real download of in-memory demo data. */
    public function export(bool $selectedOnly = false)
    {
        $rows = collect($this->members)
            ->when($this->status !== '', fn ($c) => $c->where('status', $this->status))
            ->when($this->keyword !== '', fn ($c) => $c->filter(
                fn ($m) => str_contains(strtolower($m['username'].$m['nickname'].$m['phone']), strtolower($this->keyword))
            ))
            ->when($selectedOnly, fn ($c) => $c->whereIn('id', array_map('intval', $this->selected)))
            ->values();

        $columns = ['id', 'username', 'nickname', 'store', 'phone', 'balance', 'points', 'deposit', 'withdraw', 'status', 'joinedAt'];

        return response()->streamDownload(function () use ($rows, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($rows as $m) {
                fputcsv($out, array_map(fn ($k) => $m[$k] ?? '', $columns));
            }
            fclose($out);
        }, 'members-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function toggleStatus(int $id): void
    {
        $this->members = collect($this->members)->map(function ($m) use ($id) {
            if ($m['id'] === $id && $m['status'] !== 'withdrawn') {
                $m['status'] = $m['status'] === 'normal' ? 'suspended' : 'normal';
            }

            return $m;
        })->all();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        Flux::modal('delete-member')->show();
    }

    public function deleteMember(): void
    {
        $this->members = collect($this->members)->reject(fn ($m) => $m['id'] === $this->deletingId)->values()->all();
        $this->deletingId = null;
        Flux::modal('delete-member')->close();
        Flux::toast(text: __('Member deleted'), variant: 'success');
    }

    // ── Row interactions ────────────────────────────────────────────────────
    public ?int $activeId = null;

    public int $adjustAmount = 0;

    public int $adjustCurrent = 0;

    public string $adjustKey = 'balance';

    public string $adjustTitle = '';

    public string $pendingMode = 'add';

    public function showDetail(int $id): void
    {
        $this->activeId = $id;
        Flux::modal('member-detail')->show();
    }

    public function showMoney(int $id): void
    {
        $this->openAdjust($id, 'balance', __('Adjust balance'));
    }

    public function showPoints(int $id): void
    {
        $this->openAdjust($id, 'points', __('Adjust points'));
    }

    private function openAdjust(int $id, string $key, string $title): void
    {
        $this->activeId = $id;
        $this->adjustKey = $key;
        $this->adjustTitle = $title;
        $this->adjustAmount = 0;
        $this->adjustCurrent = (int) (collect($this->members)->firstWhere('id', $id)[$key] ?? 0);
        Flux::modal('member-adjust')->show();
    }

    public function confirmAdjust(string $mode): void
    {
        $this->pendingMode = $mode;
        Flux::modal('member-adjust-confirm')->show();
    }

    public function doAdjust(): void
    {
        $delta = $this->pendingMode === 'subtract' ? -$this->adjustAmount : $this->adjustAmount;
        $key = $this->adjustKey;

        $this->members = collect($this->members)->map(function ($m) use ($key, $delta) {
            if ($m['id'] === $this->activeId) {
                $m[$key] = max(0, $m[$key] + $delta);
            }

            return $m;
        })->all();

        Flux::modal('member-adjust-confirm')->close();
        Flux::modal('member-adjust')->close();
        Flux::toast(text: $key === 'points' ? __('Points updated') : __('Balance updated'), variant: 'success');
    }

    public ?int $forceLogoutId = null;

    public function confirmForceLogout(int $id): void
    {
        $this->forceLogoutId = $id;
        Flux::modal('force-logout-member')->show();
    }

    public function forceLogout(): void
    {
        $this->forceLogoutId = null;
        Flux::modal('force-logout-member')->close();
        Flux::toast(text: __('Member forced to log out'), variant: 'success');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function activeMember(): ?array
    {
        return collect($this->members)->firstWhere('id', $this->activeId);
    }

}; ?>

<div class="flex flex-col" x-data="listTools('members')">
    <x-page-meta :title="__('Members')" route="admin.members" :breadcrumb="[__('Members'), __('Member List')]" />

    <x-admin.page-header :title="__('Member Management')">
        <x-slot:tabs>
            @foreach (['' => __('All'), 'normal' => __('Normal'), 'suspended' => __('Suspended'), 'withdrawn' => __('Withdrawn')] as $value => $label)
                <button type="button" wire:click="setStatusTab('{{ $value }}')"
                    @class([
                        'flex items-center gap-1.5 border border-b-0 px-4 py-1.5 text-xs',
                        'border-accent bg-accent text-[color:var(--color-accent-foreground)]' => $status === $value,
                        'border-zinc-200 bg-white text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' => $status !== $value,
                    ])>
                    {{ $label }}
                    <span @class([
                        'rounded-full px-1.5 text-[10px] tabular-nums',
                        'bg-white/25' => $status === $value,
                        'bg-zinc-100 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-300' => $status !== $value,
                    ])>{{ $this->statusCounts[$value] }}</span>
                </button>
            @endforeach
        </x-slot:tabs>
        <x-slot:actions>
            <flux:button :href="route('admin.members.create')" wire:navigate icon="plus" variant="primary" size="sm">{{ __('Create member') }}</flux:button>
            <flux:select wire:model.live="perPage" size="sm" class="w-28">
                <flux:select.option value="25">25</flux:select.option>
                <flux:select.option value="50">50</flux:select.option>
                <flux:select.option value="100">100</flux:select.option>
            </flux:select>
        </x-slot:actions>
        <x-slot:toolbar>
            <x-admin.list-toolbar :selected-count="count($selected)">
                <x-slot:bulk>
                    <flux:button wire:click="confirmBulkForceLogout" size="sm" variant="subtle">{{ __('Force logout') }}</flux:button>
                    <flux:button wire:click="confirmBulkSetStatus('suspended')" size="sm" variant="subtle">{{ __('Suspend') }}</flux:button>
                    <flux:button wire:click="confirmBulkSetStatus('normal')" size="sm" variant="subtle">{{ __('Activate') }}</flux:button>
                    <flux:button wire:click="export(true)" size="sm" variant="subtle">{{ __('Export selected') }}</flux:button>
                    <flux:button wire:click="confirmBulkDelete" size="sm" variant="danger">{{ __('Delete') }}</flux:button>
                </x-slot:bulk>
            </x-admin.list-toolbar>
        </x-slot:toolbar>
    </x-admin.page-header>

    <x-admin.filter-bar :statuses="['normal' => __('Normal'), 'suspended' => __('Suspended'), 'withdrawn' => __('Withdrawn')]" />

    <x-admin.stat-strip class="mb-2">
        <x-admin.stat-cell :label="__('Total members')" :value="number_format($this->totals['count'])" />
        <x-admin.stat-cell :label="__('Total balance')" :value="number_format($this->totals['balance'])" />
        <x-admin.stat-cell :label="__('Total points')" :value="number_format($this->totals['points'])" />
        <x-admin.stat-cell :label="__('Deposit − Withdraw')" :value="number_format($this->totals['net'])" />
    </x-admin.stat-strip>

    <x-admin.table :stick="3">
        <x-slot:head>
            <tr>
                <th class="admin-col-select w-px px-3 text-center align-middle font-semibold">
                    <input type="checkbox" wire:model.live="selectPage" class="size-3.5 cursor-pointer accent-[var(--color-accent)]" aria-label="{{ __('Select all') }}" />
                </th>
                @foreach (['ID', 'Level / ID', 'Nickname', 'Store', 'Account details', 'Phone', 'Balance', 'Commission', 'Points', 'Deposit', 'Withdraw', 'Difference', 'Login', 'Last access', 'IP', 'Joined', 'Domain', 'Manage'] as $h)
                    <th class="px-3 py-2 text-start font-semibold text-zinc-500">{{ __($h) }}</th>
                @endforeach
            </tr>
        </x-slot:head>

        @foreach ($this->rows as $m)
            @php
                $difference = $m['deposit'] - $m['withdraw'];
                [$bankName, $bankAccount, $bankHolder] = array_pad(array_map('trim', explode('/', (string) $m['bank'])), 3, '');
                // Mask all but the first 4 digits; a per-row reveal toggle shows the full number.
                $bankAccountMasked = strlen($bankAccount) > 4
                    ? substr($bankAccount, 0, 4).str_repeat('*', strlen($bankAccount) - 4)
                    : $bankAccount;
                // Group every 4 characters for readability: "78696001" -> "7869 6001".
                $bankAccountGrouped = trim(chunk_split($bankAccount, 4, ' '));
                $bankAccountMaskedGrouped = trim(chunk_split($bankAccountMasked, 4, ' '));
                $lastAccess = \Illuminate\Support\Carbon::parse($m['lastLogin']);
            @endphp
            <tr wire:key="member-{{ $m['id'] }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                <td class="admin-col-select w-px px-3 text-center align-middle">
                    <input type="checkbox" value="{{ $m['id'] }}" wire:model.live="selected" class="size-3.5 cursor-pointer accent-[var(--color-accent)]" aria-label="{{ __('Select row') }}" />
                </td>
                <td class="px-3 py-2">{{ $m['id'] }}</td>
                <td class="px-3 py-2">
                    <button type="button" wire:click="showDetail({{ $m['id'] }})" class="cursor-pointer text-start hover:text-accent" title="{{ __('View details') }}">
                        <x-admin.level-badge :level="$m['level']" :id="$m['username']" />
                    </button>
                </td>
                <td class="px-3 py-2">{{ $m['nickname'] }}</td>
                <td class="px-3 py-2">
                    @if (($m['storeLevel'] ?? 0) > 0)
                        <x-admin.level-badge :level="$m['storeLevel']" :id="$m['store']" />
                    @else
                        <span class="text-zinc-400">{{ $m['store'] }}</span>
                    @endif
                </td>
                <td class="px-3 py-2">
                    <div class="flex items-center gap-2" x-data="{ revealed: false, full: @js($bankAccountGrouped), masked: @js($bankAccountMaskedGrouped) }">
                        <span class="flex size-7 shrink-0 items-center justify-center rounded bg-zinc-100 text-zinc-500 dark:bg-zinc-700/60 dark:text-zinc-300">
                            <flux:icon icon="building-library" variant="micro" class="size-4" />
                        </span>
                        <div class="flex flex-col leading-tight">
                            <span class="flex items-center gap-1">
                                <span class="tabular-nums font-medium text-zinc-800 dark:text-zinc-100" x-text="revealed ? full : masked"></span>
                                <button type="button" x-on:click="revealed = !revealed"
                                    class="cursor-pointer text-zinc-400 hover:text-accent"
                                    x-bind:aria-label="revealed ? '{{ __('Hide account number') }}' : '{{ __('Show account number') }}'"
                                    x-bind:title="revealed ? '{{ __('Hide account number') }}' : '{{ __('Show account number') }}'">
                                    <flux:icon icon="eye" variant="micro" class="size-3.5" x-show="!revealed" />
                                    <flux:icon icon="eye-slash" variant="micro" class="size-3.5" x-show="revealed" x-cloak />
                                </button>
                            </span>
                            <span class="text-xs text-zinc-400">{{ $bankName }}<span class="px-1 text-zinc-300 dark:text-zinc-600">·</span>{{ $bankHolder }}</span>
                        </div>
                    </div>
                </td>
                <td class="px-3 py-2 tabular-nums">{{ $m['phone'] }}</td>
                <td class="px-3 py-2 text-end">
                    <button type="button" wire:click="showMoney({{ $m['id'] }})" title="{{ __('Adjust balance') }}"
                        class="group ms-auto inline-flex cursor-pointer items-center gap-1 rounded px-1.5 py-0.5 font-medium text-zinc-800 hover:bg-emerald-50 hover:text-emerald-700 dark:text-zinc-100 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-400">
                        <span class="tabular-nums">{{ number_format($m['balance']) }}</span>
                        <flux:icon icon="pencil-square" variant="micro" class="size-3 text-emerald-500 opacity-0 transition group-hover:opacity-100" />
                    </button>
                </td>
                <td class="px-3 py-2"><flux:badge size="sm" :color="$m['commissionType'] === 'turnover' ? 'indigo' : 'zinc'" inset="top bottom">{{ $m['commissionType'] }}</flux:badge></td>
                <td class="px-3 py-2 text-end">
                    <button type="button" wire:click="showPoints({{ $m['id'] }})" title="{{ __('Adjust points') }}"
                        class="group ms-auto inline-flex cursor-pointer items-center gap-1 rounded px-1.5 py-0.5 font-medium text-zinc-800 hover:bg-violet-50 hover:text-violet-700 dark:text-zinc-100 dark:hover:bg-violet-500/10 dark:hover:text-violet-400">
                        <span class="tabular-nums">{{ number_format($m['points']) }}</span>
                        <flux:icon icon="pencil-square" variant="micro" class="size-3 text-violet-500 opacity-0 transition group-hover:opacity-100" />
                    </button>
                </td>
                <td class="px-3 py-2 text-end tabular-nums">{{ number_format($m['deposit']) }}</td>
                <td class="px-3 py-2 text-end tabular-nums">{{ number_format($m['withdraw']) }}</td>
                <td @class([
                    'px-3 py-2 text-end tabular-nums',
                    'font-semibold text-rose-600 dark:text-rose-400' => $difference < 0,
                ])>{{ number_format($difference) }}</td>
                <td class="px-3 py-2"><flux:switch :checked="$m['status'] === 'normal'" :disabled="$m['status'] === 'withdrawn'" wire:click="toggleStatus({{ $m['id'] }})" wire:key="member-switch-{{ $m['id'] }}-{{ $m['status'] }}" /></td>
                <td class="px-3 py-2">
                    <div class="flex flex-col leading-tight">
                        <span class="tabular-nums">{{ $m['lastLogin'] }}</span>
                        <span class="text-xs text-zinc-400" title="{{ $lastAccess->toDayDateTimeString() }}">{{ $lastAccess->diffForHumans() }}</span>
                    </div>
                </td>
                <td class="px-3 py-2 tabular-nums">{{ $m['ip'] }}</td>
                <td class="px-3 py-2">{{ $m['joinedAt'] }}</td>
                <td class="px-3 py-2">{{ $m['domain'] }}</td>
                <td class="px-3 py-2">
                    <x-admin.row-actions>
                        <flux:button :href="route('admin.members.profile', $m['id'])" wire:navigate size="xs" variant="primary">{{ __('Profile') }}</flux:button>
                        <flux:button :href="route('admin.members.edit', $m['id'])" wire:navigate size="xs" variant="subtle">{{ __('Edit') }}</flux:button>
                        <flux:button wire:click="confirmForceLogout({{ $m['id'] }})" size="xs" variant="subtle">{{ __('Force logout') }}</flux:button>
                        <flux:button wire:click="confirmDelete({{ $m['id'] }})" size="xs" variant="danger">{{ __('Delete') }}</flux:button>
                    </x-admin.row-actions>
                </td>
            </tr>
        @endforeach

        <x-slot:footer>
            <flux:pagination :paginator="$this->rows" />
        </x-slot:footer>
    </x-admin.table>

    <flux:modal name="delete-member" class="w-full max-w-sm">
        @php $deletingMember = collect($this->members)->firstWhere('id', $deletingId); @endphp
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Delete member') }}</flux:heading>
            @if ($deletingMember)
                <div class="flex items-center border border-zinc-200 bg-zinc-50 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900/50">
                    <x-admin.level-badge :level="$deletingMember['level']" :id="$deletingMember['username']" />
                </div>
            @endif
            <flux:text>{{ __('Are you sure you want to delete this member? This action cannot be undone.') }}</flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close>
                <flux:button wire:click="deleteMember" variant="danger" data-test="confirm-delete">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="force-logout-member" class="w-full max-w-sm">
        @php $forceLogoutMember = collect($this->members)->firstWhere('id', $forceLogoutId); @endphp
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Force logout') }}</flux:heading>
            @if ($forceLogoutMember)
                <div class="flex items-center border border-zinc-200 bg-zinc-50 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-900/50">
                    <x-admin.level-badge :level="$forceLogoutMember['level']" :id="$forceLogoutMember['username']" />
                </div>
            @endif
            <flux:text>{{ __('End this member\'s active session and force them to log out?') }}</flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close>
                <flux:button wire:click="forceLogout" variant="primary" data-test="confirm-force-logout">{{ __('Force logout') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="bulk-set-status" class="w-full max-w-sm">
        @php $isSuspend = $pendingBulkStatus === 'suspended'; @endphp
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">{{ $isSuspend ? __('Suspend') : __('Activate') }}</flux:heading>
            <flux:text>{{ $isSuspend
                ? trans_choice('Suspend :count selected member?|Suspend :count selected members?', count($selected), ['count' => count($selected)])
                : trans_choice('Activate :count selected member?|Activate :count selected members?', count($selected), ['count' => count($selected)]) }}</flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close>
                <flux:button wire:click="bulkSetStatus" :variant="$isSuspend ? 'danger' : 'primary'" data-test="confirm-bulk-set-status">{{ $isSuspend ? __('Suspend') : __('Activate') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="bulk-force-logout" class="w-full max-w-sm">
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Force logout') }}</flux:heading>
            <flux:text>{{ trans_choice('End the active session of :count selected member?|End the active sessions of :count selected members?', count($selected), ['count' => count($selected)]) }}</flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close>
                <flux:button wire:click="bulkForceLogout" variant="primary" data-test="confirm-bulk-force-logout">{{ __('Force logout') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="bulk-delete" class="w-full max-w-sm">
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Delete selected members') }}</flux:heading>
            <flux:text>{{ trans_choice('Delete :count selected member? This cannot be undone.|Delete :count selected members? This cannot be undone.', count($selected), ['count' => count($selected)]) }}</flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close>
                <flux:button wire:click="bulkDelete" variant="danger" data-test="confirm-bulk-delete">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Read-only member detail (opened by clicking the level/ID) --}}
    <flux:modal name="member-detail" class="w-full max-w-lg">
        @php $am = $this->activeMember(); @endphp
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">{{ __('Member details') }}</flux:heading>
            @if ($am)
                <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><div class="text-xs text-zinc-400">{{ __('Level / ID') }}</div><x-admin.level-badge :level="$am['level']" :id="$am['username']" /></div>
                    <div><div class="text-xs text-zinc-400">{{ __('Nickname') }}</div>{{ $am['nickname'] }}</div>
                    <div><div class="text-xs text-zinc-400">{{ __('Store') }}</div>{{ $am['store'] }}</div>
                    <div><div class="text-xs text-zinc-400">{{ __('Phone') }}</div><span class="tabular-nums">{{ $am['phone'] }}</span></div>
                    <div class="col-span-2">
                        <div class="text-xs text-zinc-400">{{ __('Account details') }}</div>
                        @php [$amBankName, $amBankAccount, $amBankHolder] = array_pad(array_map('trim', explode('/', (string) $am['bank'])), 3, ''); @endphp
                        <div class="mt-1 flex items-center gap-2">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded bg-zinc-100 text-zinc-500 dark:bg-zinc-700/60 dark:text-zinc-300">
                                <flux:icon icon="building-library" variant="mini" class="size-4" />
                            </span>
                            <div class="flex flex-col leading-tight">
                                <span class="tabular-nums font-semibold">{{ trim(chunk_split($amBankAccount, 4, ' ')) }}</span>
                                <span class="text-xs text-zinc-400">{{ $amBankName }}<span class="px-1 text-zinc-300 dark:text-zinc-600">·</span>{{ $amBankHolder }}</span>
                            </div>
                        </div>
                    </div>
                    <div><div class="text-xs text-zinc-400">{{ __('Balance') }}</div><span class="tabular-nums font-semibold">{{ number_format($am['balance']) }}</span></div>
                    <div><div class="text-xs text-zinc-400">{{ __('Points') }}</div><span class="tabular-nums font-semibold">{{ number_format($am['points']) }}</span></div>
                    <div><div class="text-xs text-zinc-400">{{ __('Deposit') }}</div><span class="tabular-nums">{{ number_format($am['deposit']) }}</span></div>
                    <div><div class="text-xs text-zinc-400">{{ __('Withdraw') }}</div><span class="tabular-nums">{{ number_format($am['withdraw']) }}</span></div>
                    <div><div class="text-xs text-zinc-400">{{ __('Last access') }}</div>{{ $am['lastLogin'] }}</div>
                    <div><div class="text-xs text-zinc-400">{{ __('IP') }}</div><span class="tabular-nums">{{ $am['ip'] }}</span></div>
                    <div><div class="text-xs text-zinc-400">{{ __('Joined') }}</div>{{ $am['joinedAt'] }}</div>
                    <div><div class="text-xs text-zinc-400">{{ __('Domain') }}</div>{{ $am['domain'] }}</div>
                </div>
                <div class="flex justify-end gap-2">
                    <flux:modal.close><flux:button variant="ghost">{{ __('Close') }}</flux:button></flux:modal.close>
                    <flux:button :href="route('admin.members.profile', $am['id'])" wire:navigate variant="primary">{{ __('View profile') }}</flux:button>
                    <flux:button :href="route('admin.members.edit', $am['id'])" wire:navigate variant="filled">{{ __('Edit') }}</flux:button>
                </div>
            @endif
        </div>
    </flux:modal>

    {{-- Balance + points adjust (opened by clicking the balance / points cells) --}}
    <x-admin.adjust-modal name="member-adjust" :title="$adjustTitle" />
</div>
