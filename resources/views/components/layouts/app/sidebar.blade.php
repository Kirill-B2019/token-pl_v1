<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @php
            $user = auth()->user();
            $role = $user?->role;
        @endphp

        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('home.redirect') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group heading="Навигация" class="grid">
                    <flux:navlist.item icon="home" :href="route('home.redirect')" :current="request()->routeIs('home.redirect')" wire:navigate>Главная</flux:navlist.item>
                </flux:navlist.group>

                @if($role === 'admin')
                    <flux:navlist.group heading="Администрирование" class="grid">
                        <flux:navlist.item :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>Панель администратора</flux:navlist.item>
                        <flux:navlist.item :href="route('admin.users')" :current="request()->routeIs('admin.users') || request()->routeIs('admin.users.show')" wire:navigate>Пользователи</flux:navlist.item>
                        <flux:navlist.item :href="route('admin.user-groups.index')" :current="request()->routeIs('admin.user-groups.*')" wire:navigate>Группы пользователей</flux:navlist.item>
                        <flux:navlist.item :href="route('admin.transactions')" :current="request()->routeIs('admin.transactions') || request()->routeIs('admin.transactions.show')" wire:navigate>Транзакции</flux:navlist.item>
                        <flux:navlist.item :href="route('admin.tokens')" :current="request()->routeIs('admin.tokens') || request()->routeIs('admin.tokens.create')" wire:navigate>Токены</flux:navlist.item>
                        <flux:navlist.item :href="route('admin.winners-losers')" :current="request()->routeIs('admin.winners-losers*')" wire:navigate>Победители и проигравшие</flux:navlist.item>
                        <flux:navlist.item :href="route('admin.banks')" :current="request()->routeIs('admin.banks')" wire:navigate>Банки</flux:navlist.item>
                        <flux:navlist.item :href="route('admin.brokers')" :current="request()->routeIs('admin.brokers')" wire:navigate>Брокеры</flux:navlist.item>
                        <flux:navlist.item :href="route('admin.audit-logs')" :current="request()->routeIs('admin.audit-logs')" wire:navigate>Журнал действий</flux:navlist.item>
                        <flux:navlist.item :href="route('admin.settings')" :current="request()->routeIs('admin.settings')" wire:navigate>Настройки системы</flux:navlist.item>
                    </flux:navlist.group>
                @elseif($role === 'broker')
                    <flux:navlist.group heading="Рабочий кабинет" class="grid">
                        <flux:navlist.item :href="route('broker.dashboard')" :current="request()->routeIs('broker.dashboard')" wire:navigate>Панель брокера</flux:navlist.item>
                        <flux:navlist.item :href="route('broker.tokens')" :current="request()->routeIs('broker.tokens')" wire:navigate>Токены</flux:navlist.item>
                        <flux:navlist.item :href="route('broker.reserves')" :current="request()->routeIs('broker.reserves')" wire:navigate>Резервы</flux:navlist.item>
                        <flux:navlist.item :href="route('broker.setup')" :current="request()->routeIs('broker.setup')" wire:navigate>Настройки брокера</flux:navlist.item>
                    </flux:navlist.group>
                @else
                    <flux:navlist.group heading="Личный кабинет" class="grid">
                        <flux:navlist.item :href="route('client.dashboard')" :current="request()->routeIs('client.dashboard')" wire:navigate>Панель клиента</flux:navlist.item>
                        <flux:navlist.item :href="route('client.packages')" :current="request()->routeIs('client.packages*')" wire:navigate>Пакеты токенов</flux:navlist.item>
                        <flux:navlist.item :href="route('client.transactions')" :current="request()->routeIs('client.transactions*')" wire:navigate>Мои транзакции</flux:navlist.item>
                        <flux:navlist.item :href="route('client.wallet')" :current="request()->routeIs('client.wallet*')" wire:navigate>TRON кошелёк</flux:navlist.item>
                        <flux:navlist.item :href="route('client.sell')" :current="request()->routeIs('client.sell*')" wire:navigate>Продажа токенов</flux:navlist.item>
                        <flux:navlist.item :href="route('client.profile')" :current="request()->routeIs('client.profile')" wire:navigate>Профиль</flux:navlist.item>
                    </flux:navlist.group>
                @endif
            </flux:navlist>

            <flux:spacer />

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                    data-test="sidebar-menu-button"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>Настройки профиля</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            Выйти
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>Настройки профиля</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            Выйти
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @stack('scripts')
        @fluxScripts
    </body>
</html>
