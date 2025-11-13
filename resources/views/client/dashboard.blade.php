@php($title = 'Кабинет клиента')
<x-layouts.app :title="$title">
    <!-- |KB Клиентский дашборд: балансы, транзакции и пакеты -->
    <div class="space-y-6 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <a href="{{ route('client.packages') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">Купить токены</a>
        </div>

        @php($statusLabels = [
            'pending' => 'В ожидании',
            'processing' => 'В обработке',
            'completed' => 'Завершено',
            'failed' => 'Ошибка',
            'cancelled' => 'Отменено',
        ])
        @php($typeLabels = [
            'buy' => 'Покупка',
            'sell' => 'Продажа',
            'transfer' => 'Перевод',
            'refund' => 'Возврат',
        ])

        @if(session('success'))
            <x-ui.flash :message="session('success')" type="success" />
        @endif
        @if(session('error'))
            <x-ui.flash :message="session('error')" type="error" />
        @endif

        <x-admin.panel title="Балансы токенов">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse($balances as $balance)
                    <div class="rounded-md border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $balance->token->name ?? '—' }}</h3>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ number_format($balance->balance, 6, '.', ' ') }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Заблокировано: {{ number_format($balance->locked_balance, 6, '.', ' ') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">У вас ещё нет токенов.</p>
                @endforelse
            </div>
        </x-admin.panel>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-admin.panel title="Последние транзакции">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                <th class="px-3 py-2">Дата</th>
                                <th class="px-3 py-2">Тип</th>
                                <th class="px-3 py-2">Токен</th>
                                <th class="px-3 py-2">Сумма</th>
                                <th class="px-3 py-2">Статус</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($recentTransactions as $transaction)
                                <tr class="text-zinc-800 dark:text-zinc-100">
                                    <td class="px-3 py-2">{{ $transaction->created_at->format('d.m.Y H:i') }}</td>
                                     <td class="px-3 py-2">{{ $typeLabels[$transaction->type] ?? ucfirst($transaction->type) }}</td>
                                    <td class="px-3 py-2">{{ $transaction->token->symbol ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ number_format($transaction->total_amount, 2, '.', ' ') }}</td>
                                     <td class="px-3 py-2">{{ $statusLabels[$transaction->status] ?? ucfirst($transaction->status) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">Нет транзакций.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.panel>

            <x-admin.panel title="Доступные пакеты">
                <div class="space-y-3">
                    @forelse($packages as $package)
                        <div class="rounded-md border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $package->name }}</h3>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $package->description }}</p>
                                </div>
                                <span class="text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($package->price, 2, '.', ' ') }} ₽</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-300">
                                <span>Токенов: {{ number_format($package->token_amount, 4, '.', ' ') }}</span>
                                <a href="{{ route('client.packages.purchase', $package) }}" class="text-indigo-600 hover:underline">Купить</a>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Пакеты недоступны.</p>
                    @endforelse
                </div>
            </x-admin.panel>
        </div>
    </div>
</x-layouts.app>


