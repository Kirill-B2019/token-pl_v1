@php($title = 'Кабинет клиента')
<x-layouts.app :title="$title">
    <!-- |KB Дашборд клиента: балансы, последние транзакции и пакеты -->
    <div class="space-y-6 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('client.balance.topup') }}" class="rounded-md bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-500">Пополнить баланс</a>
                <a href="{{ route('client.packages') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">Купить токены</a>
            </div>
        </div>

        @if(session('success'))
            <x-ui.flash :message="session('success')" type="success" />
        @endif
        @if(session('error'))
            <x-ui.flash :message="session('error')" type="error" />
        @endif

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

        <x-admin.panel title="Балансы">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <!-- Баланс в рублях -->
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 shadow-sm dark:border-green-700 dark:bg-green-900/20">
                    <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-2">Баланс в рублях</h3>
                    <div class="text-2xl font-bold text-green-900 dark:text-green-100 mb-2">{{ auth()->user()->formatted_rub_balance }}</div>
                    <div class="text-sm text-green-600 dark:text-green-400">Доступно для оплаты</div>
                </div>

                <!-- Балансы токенов -->
                @forelse($balances as $balance)
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">{{ $balance->token->name ?? '—' }}</h3>
                        <div class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">{{ number_format($balance->balance, 6, '.', ' ') }}</div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">Заблокировано: {{ number_format($balance->locked_balance, 6, '.', ' ') }}</div>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 col-span-full">У вас ещё нет токенов.</p>
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
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $transaction->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="px-3 py-2">{{ $typeLabels[$transaction->type] ?? ucfirst($transaction->type) }}</td>
                                    <td class="px-3 py-2">{{ $transaction->token->symbol ?? '—' }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ number_format($transaction->total_amount, 2, '.', ' ') }}</td>
                                    <td class="px-3 py-2">
                                        <span class="rounded-full bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $statusLabels[$transaction->status] ?? ucfirst($transaction->status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">Нет транзакций.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.panel>

            <x-admin.panel title="Доступные пакеты">
                <div class="space-y-4">
                    @forelse($packages as $package)
                        <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-zinc-900 dark:text-white">{{ $package->name }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $package->description }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-zinc-900 dark:text-white">{{ number_format($package->price, 2, '.', ' ') }} ₽</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Токенов: {{ number_format($package->token_amount, 4, '.', ' ') }}</div>
                                    <a href="{{ route('client.packages.purchase', $package) }}" class="mt-2 inline-flex rounded-md border border-indigo-600 px-3 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-600 hover:text-white">Купить</a>
                                </div>
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


