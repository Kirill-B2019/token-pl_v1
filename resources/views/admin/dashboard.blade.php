@php($title = 'Администратор: дашборд')
<x-layouts.app :title="$title">
    <!-- |KB Админский дашборд: статистика, последние транзакции и логи -->
    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.users') }}" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500">Пользователи</a>
                <a href="{{ route('admin.transactions') }}" class="rounded-md bg-zinc-800 px-3 py-2 text-sm font-medium text-white hover:bg-zinc-700">Транзакции</a>
                <a href="{{ route('admin.tokens.create') }}" class="rounded-md border border-indigo-600 px-3 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-600 hover:text-white">Добавить токен</a>
            </div>
        </div>

        @if (session('success'))
            <x-ui.flash :message="session('success')" type="success" />
        @endif
        @if (session('error'))
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

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <x-admin.stat-card title="Всего пользователей" :value="$stats['total_users']" :subtitle="'Активных: ' . $stats['active_users']" />
            <x-admin.stat-card title="Транзакции (всего)" :value="$stats['total_transactions']" :subtitle="'Ожидают: ' . $stats['pending_transactions'] . ' • Завершено: ' . $stats['completed_transactions']" />
            <x-admin.stat-card title="Токены" :value="$stats['total_tokens']" :subtitle="'Активных: ' . $stats['active_tokens']" />
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-admin.panel title="Последние транзакции">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                <th class="px-3 py-2">Дата</th>
                                <th class="px-3 py-2">Пользователь</th>
                                <th class="px-3 py-2">Токен</th>
                                <th class="px-3 py-2">Тип</th>
                                <th class="px-3 py-2">Статус</th>
                                <th class="px-3 py-2">Сумма</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse($recentTransactions as $transaction)
                                <tr class="text-sm text-zinc-700 dark:text-zinc-200">
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $transaction->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="px-3 py-2">{{ $transaction->user->name ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $transaction->token->symbol ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $typeLabels[$transaction->type] ?? ucfirst($transaction->type) }}</td>
                                    <td class="px-3 py-2">
                                        <span class="rounded-full bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $statusLabels[$transaction->status] ?? ucfirst($transaction->status) }}</span>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ number_format($transaction->total_amount, 2, '.', ' ') }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <a href="{{ route('admin.transactions.show', $transaction) }}" class="text-indigo-600 hover:underline">Подробнее</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">Транзакции отсутствуют.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.panel>

            <x-admin.panel title="Журнал действий">
                <div class="space-y-4">
                    @forelse($recentAuditLogs as $log)
                        <div class="rounded-md border border-zinc-200 bg-zinc-50 p-3 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                <span>{{ strtoupper($log->event) }}</span>
                                <span>{{ $log->created_at->format('d.m.Y H:i') }}</span>
                            </div>
                            <div class="mt-1 text-sm text-zinc-800 dark:text-zinc-100">
                                {{ $log->user->name ?? 'Система' }} • {{ $log->entity_type }} #{{ $log->entity_id }}
                            </div>
                            @if($log->metadata)
                                <pre class="mt-2 overflow-x-auto rounded bg-black/5 p-2 text-xs text-zinc-600 dark:bg-white/10 dark:text-zinc-300">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Журнал событий пока пуст.</p>
                    @endforelse
                </div>
            </x-admin.panel>
        </div>
    </div>
</x-layouts.app>


