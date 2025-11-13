@php($title = 'Кабинет брокера')
<x-layouts.app :title="$title">
    <!-- |KB Брокерский дашборд: заявки, резервы и статусы токенов -->
    <div class="space-y-6 p-6">
        @php($typeLabels = [
            'buy' => 'Покупка',
            'sell' => 'Продажа',
            'transfer' => 'Перевод',
            'refund' => 'Возврат',
        ])

         <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('broker.tokens') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">Токены</a>
                <a href="{{ route('broker.reserves') }}" class="rounded-md border border-indigo-600 px-3 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-600 hover:text-white">Резервы</a>
            </div>
        </div>

        @if(session('success'))
            <x-ui.flash :message="session('success')" type="success" />
        @endif
        @if(session('error'))
            <x-ui.flash :message="session('error')" type="error" />
        @endif

        @if(!$broker)
            <x-admin.panel title="Настройка аккаунта">
                <p class="text-sm text-zinc-600 dark:text-zinc-300">Заполните данные брокера для работы с заявками.</p>
                <a href="{{ route('broker.setup') }}" class="mt-3 inline-flex rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Перейти к настройке</a>
            </x-admin.panel>
        @else
            <div class="grid gap-4 md:grid-cols-3">
                <x-admin.stat-card title="Ожидающих заявок" :value="$pendingTransactions->total()" />
                <x-admin.stat-card title="Всего токенов" :value="$tokens->count()" />
                <x-admin.stat-card title="Низкий резерв" :value="$lowReserveTokens->count()" />
            </div>

            <x-admin.panel title="Ожидающие транзакции">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                <th class="px-3 py-2">Дата</th>
                                <th class="px-3 py-2">Пользователь</th>
                                <th class="px-3 py-2">Тип</th>
                                <th class="px-3 py-2">Сумма</th>
                                <th class="px-3 py-2">Токен</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($pendingTransactions as $transaction)
                                <tr class="text-zinc-800 dark:text-zinc-100">
                                    <td class="px-3 py-2">{{ $transaction->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="px-3 py-2">{{ $transaction->user->name ?? '—' }}</td>
                                     <td class="px-3 py-2">{{ $typeLabels[$transaction->type] ?? ucfirst($transaction->type) }}</td>
                                    <td class="px-3 py-2">{{ number_format($transaction->total_amount, 2, '.', ' ') }}</td>
                                    <td class="px-3 py-2">{{ $transaction->token->symbol ?? '—' }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <form action="{{ route('broker.transactions.process', $transaction) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-sm text-indigo-600 hover:underline">Обработать</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">Нет заявок.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $pendingTransactions->links() }}
                </div>
            </x-admin.panel>

            <x-admin.panel title="Резервы токенов">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @forelse($tokens as $token)
                        <div class="rounded-md border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $token->name }} ({{ $token->symbol }})</h3>
                            <p class="mt-2 text-xl font-semibold text-zinc-900 dark:text-white">{{ number_format($token->available_supply, 4, '.', ' ') }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Всего: {{ number_format($token->total_supply, 4, '.', ' ') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Нет токенов.</p>
                    @endforelse
                </div>
            </x-admin.panel>
        @endif
    </div>
</x-layouts.app>


