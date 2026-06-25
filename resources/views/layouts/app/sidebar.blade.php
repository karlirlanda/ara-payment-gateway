<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                {{-- Hidden on desktop while expanded (we collapse via the top bar), but
                     present when collapsed on desktop so Flux's hover-swap reveals the
                     expand toggle instead of leaving the brand area blank. --}}
                <flux:sidebar.collapse class="lg:hidden in-[[data-flux-sidebar-collapsed-desktop]]:flex!" />
            </flux:sidebar.header>

            <x-admin.summary-totals />

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" icon="squares-2x2" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Members')" icon="user-group" expandable :expanded="request()->routeIs('admin.members*')">
                    <flux:sidebar.item icon="user-group" :href="route('admin.members')" :current="request()->routeIs('admin.members')" wire:navigate>
                        {{ __('Member List') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="signal" :href="route('admin.members.live')" :current="request()->routeIs('admin.members.live')" wire:navigate>
                        {{ __('Live Users') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Gateways')" icon="credit-card" expandable :expanded="request()->routeIs('admin.gateways')">
                    <flux:sidebar.item icon="credit-card" :href="route('admin.gateways')" :current="request()->routeIs('admin.gateways')" wire:navigate>
                        {{ __('Providers') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Transactions')" icon="banknotes" expandable :expanded="request()->routeIs('admin.transactions')">
                    <flux:sidebar.item icon="arrow-down-circle" :href="route('admin.transactions', 'deposit')" :current="request()->routeIs('admin.transactions') && request()->route('direction') === 'deposit'" wire:navigate>{{ __('Deposits') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="arrow-up-circle" :href="route('admin.transactions', 'withdraw')" :current="request()->routeIs('admin.transactions') && request()->route('direction') === 'withdraw'" wire:navigate>{{ __('Withdrawals') }}</flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Approvals')" icon="check-badge" expandable :expanded="request()->routeIs('admin.withdrawals.approvals')">
                    <flux:sidebar.item icon="check-badge" :href="route('admin.withdrawals.approvals')" :current="request()->routeIs('admin.withdrawals.approvals')" wire:navigate>
                        {{ __('Withdrawal Approvals') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Reconciliation')" icon="scale" expandable :expanded="request()->routeIs('admin.reconciliation')">
                    <flux:sidebar.item icon="scale" :href="route('admin.reconciliation')" :current="request()->routeIs('admin.reconciliation')" wire:navigate>
                        {{ __('Daily Reconciliation') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Reports')" icon="chart-bar" expandable :expanded="request()->routeIs('admin.reports*')">
                    <flux:sidebar.item icon="table-cells" :href="route('admin.reports')" :current="request()->routeIs('admin.reports')" wire:navigate>{{ __('All Reports') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="document-chart-bar" :href="route('admin.reports.sales')" :current="request()->routeIs('admin.reports.sales')" wire:navigate>{{ __('Sales Report') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="chart-bar-square" :href="route('admin.reports.daily')" :current="request()->routeIs('admin.reports.daily')" wire:navigate>{{ __('Daily Sales') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="calendar-days" :href="route('admin.reports.period')" :current="request()->routeIs('admin.reports.period')" wire:navigate>{{ __('Period Sales') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="scale" :href="route('admin.reports.pl')" :current="request()->routeIs('admin.reports.pl')" wire:navigate>{{ __('Profit & Loss') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="ticket" :href="route('admin.reports.coupon-usage')" :current="request()->routeIs('admin.reports.coupon-usage')" wire:navigate>{{ __('Coupon Usage') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="user-group" :href="route('admin.reports.player-activity')" :current="request()->routeIs('admin.reports.player-activity')" wire:navigate>{{ __('Player Activity') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="briefcase" :href="route('admin.reports.agent-commission')" :current="request()->routeIs('admin.reports.agent-commission')" wire:navigate>{{ __('Agent Commission') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="building-storefront" :href="route('admin.reports.brand-comparison')" :current="request()->routeIs('admin.reports.brand-comparison')" wire:navigate>{{ __('Brand Comparison') }}</flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Agents')" icon="briefcase" expandable :expanded="request()->routeIs('admin.agents*')">
                    <flux:sidebar.item icon="users" :href="route('admin.agents')" :current="request()->routeIs('admin.agents')" wire:navigate>{{ __('List & Hierarchy') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="adjustments-horizontal" :href="route('admin.agents.commissions')" :current="request()->routeIs('admin.agents.commissions')" wire:navigate>{{ __('Commission Settings') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" :href="route('admin.agents.transactions')" :current="request()->routeIs('admin.agents.transactions')" wire:navigate>{{ __('Agent Transactions') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="trophy" :href="route('admin.agents.performance')" :current="request()->routeIs('admin.agents.performance')" wire:navigate>{{ __('Performance') }}</flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Accounting')" icon="calculator" expandable :expanded="request()->routeIs('admin.accounting.*')">
                    <flux:sidebar.item icon="calendar-days" :href="route('admin.accounting.settlement')" :current="request()->routeIs('admin.accounting.settlement')" wire:navigate>{{ __('Daily Settlement') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="chart-pie" :href="route('admin.accounting.revenue')" :current="request()->routeIs('admin.accounting.revenue')" wire:navigate>{{ __('Revenue Summary') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="scale" :href="route('admin.accounting.balance-sheet')" :current="request()->routeIs('admin.accounting.balance-sheet')" wire:navigate>{{ __('Balance Sheet') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="book-open" :href="route('admin.accounting.commission-ledger')" :current="request()->routeIs('admin.accounting.commission-ledger')" wire:navigate>{{ __('Commission Ledger') }}</flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Engagement')" icon="megaphone" expandable :expanded="request()->routeIs('admin.coupons') || request()->routeIs('admin.tickets') || request()->routeIs('admin.announcements')">
                    <flux:sidebar.item icon="ticket" :href="route('admin.coupons')" :current="request()->routeIs('admin.coupons')" wire:navigate>{{ __('Coupons & Events') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="lifebuoy" :href="route('admin.tickets')" :current="request()->routeIs('admin.tickets')" wire:navigate>{{ __('Support Tickets') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="megaphone" :href="route('admin.announcements')" :current="request()->routeIs('admin.announcements')" wire:navigate>{{ __('Announcements') }}</flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Settings')" icon="cog-6-tooth" expandable :expanded="request()->routeIs('admin.settings.*')">
                    <flux:sidebar.item icon="credit-card" :href="route('admin.settings.provider')" :current="request()->routeIs('admin.settings.provider')" wire:navigate>{{ __('Gateway settings') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="adjustments-horizontal" :href="route('admin.settings.advanced')" :current="request()->routeIs('admin.settings.advanced')" wire:navigate>{{ __('Advanced settings') }}</flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('System')" icon="shield-check" expandable :expanded="request()->routeIs('admin.users') || request()->routeIs('admin.roles') || request()->routeIs('admin.permissions') || request()->routeIs('admin.activity-logs')">
                    <flux:sidebar.item icon="users" :href="route('admin.users')" :current="request()->routeIs('admin.users')" wire:navigate>
                        {{ __('Users') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shield-check" :href="route('admin.roles')" :current="request()->routeIs('admin.roles')" wire:navigate>
                        {{ __('Roles') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="key" :href="route('admin.permissions')" :current="request()->routeIs('admin.permissions')" wire:navigate>
                        {{ __('Permissions') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('admin.activity-logs')" :current="request()->routeIs('admin.activity-logs')" wire:navigate>
                        {{ __('Activity Logs') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="lock-closed">
                    {{ __('Confidential · .ARA Inc · 2026') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <x-env-badge class="ms-2" />

            <flux:spacer />

            <x-admin.notifications />

            <flux:button :href="route('appearance.edit')" wire:navigate icon="sun" variant="subtle" size="sm" :tooltip="__('Appearance')" />

            <flux:dropdown position="bottom" align="end">
                <flux:button icon="language" variant="subtle" size="sm" :tooltip="__('Language')" />
                <flux:menu>
                    <flux:menu.item :href="route('locale.switch', 'en')" :icon="app()->getLocale() === 'en' ? 'check' : null">English</flux:menu.item>
                    <flux:menu.item :href="route('locale.switch', 'ko')" :icon="app()->getLocale() === 'ko' ? 'check' : null">한국어</flux:menu.item>
                </flux:menu>
            </flux:dropdown>

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{-- Persistent desktop top bar --}}
        <flux:header class="max-lg:hidden! border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.collapse class="max-lg:hidden" />

            <x-env-badge class="ms-2" />

            {{-- Plain Alpine-driven breadcrumb (Flux components don't clone reliably inside x-for) --}}
            <nav aria-label="{{ __('Breadcrumb') }}" class="ms-2 flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400" x-data>
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-1 hover:text-accent">
                    <flux:icon icon="home" class="size-3.5" />
                    <span>{{ __('Home') }}</span>
                </a>
                <template x-for="(crumb, i) in $store.nav.crumbs" :key="i">
                    <span class="flex items-center gap-1.5">
                        <span class="text-zinc-300 dark:text-zinc-600">/</span>
                        <span x-text="crumb" :class="i === $store.nav.crumbs.length - 1 ? 'text-zinc-800 dark:text-zinc-200' : ''"></span>
                    </span>
                </template>
            </nav>

            <flux:spacer />

            <x-admin.action-bar />

            <flux:spacer />

            <flux:button x-data icon="arrows-pointing-out" variant="subtle" size="sm" x-on:click="document.documentElement.requestFullscreen?.()" :tooltip="__('Fullscreen')" />
            <flux:button :href="route('appearance.edit')" wire:navigate icon="sun" variant="subtle" size="sm" :tooltip="__('Appearance')" />

            <flux:dropdown position="bottom" align="end">
                <flux:button icon="language" variant="subtle" size="sm" :tooltip="__('Language')" />
                <flux:menu>
                    <flux:menu.item :href="route('locale.switch', 'en')" :icon="app()->getLocale() === 'en' ? 'check' : null">English</flux:menu.item>
                    <flux:menu.item :href="route('locale.switch', 'ko')" :icon="app()->getLocale() === 'ko' ? 'check' : null">한국어</flux:menu.item>
                </flux:menu>
            </flux:dropdown>

            <x-admin.notifications />

            <x-desktop-user-menu />
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
