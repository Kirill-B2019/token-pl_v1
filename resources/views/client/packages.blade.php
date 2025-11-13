@php($title = 'Пакеты токенов')
<x-layouts.app :title="$title">
    <!-- |KB Список пакетов для покупки токенов -->
    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <a href="{{ route('client.dashboard') }}" class="text-sm text-indigo-600 hover:underline">Назад к кабинету</a>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($packages as $package)
                <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $package->name }}</h2>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $package->description }}</p>
                        </div>
                        @if($package->discount_percentage)
                            <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800 dark:bg-green-900/40 dark:text-green-200">-{{ $package->discount_percentage }}%</span>
                        @endif
                    </div>
                    <dl class="mt-4 space-y-2 text-sm text-zinc-700 dark:text-zinc-200">
                        <div class="flex justify-between">
                            <dt>Количество токенов</dt>
                            <dd>{{ number_format($package->token_amount, 4, '.', ' ') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>Цена</dt>
                            <dd>{{ number_format($package->price, 2, '.', ' ') }} ₽</dd>
                        </div>
                    </dl>
                    <a href="{{ route('client.packages.purchase', $package) }}" class="mt-4 inline-flex w-full justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Купить</a>
                </div>
            @empty
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Пакеты недоступны.</p>
            @endforelse
        </div>

        <x-admin.panel title="Доступные токены">
            <div class="grid gap-4 md:grid-cols-3">
                @forelse($tokens as $token)
                    <div class="rounded-md border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $token->name }}</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $token->symbol }}</p>
                        <p class="mt-2 text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($token->current_price, 2, '.', ' ') }} ₽</p>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Токены недоступны.</p>
                @endforelse
            </div>
        </x-admin.panel>
    </div>
</x-layouts.app>


