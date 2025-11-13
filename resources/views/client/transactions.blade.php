@php($title = 'История транзакций')
<x-layouts.app :title="$title">
    <!-- |KB История транзакций клиента -->
    <div class="space-y-6 p-6">
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

         <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <a href="{{ route('client.dashboard') }}" class="text-sm text-indigo-600 hover:underline">Назад к кабинету</a>
        </div>

        <div class="overflow-x-auto rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr class="text-left text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        <th class="px-4 py-3">Дата</th>
                        <th class="px-4 py-3">Тип</th>
                        <th class="px-4 py-3">Токен</th>
                        <th class="px-4 py-3">Сумма</th>
                        <th class="px-4 py-3">Статус</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($transactions as $transaction)
                        <tr class="text-zinc-800 dark:text-zinc-100">
                            <td class="px-4 py-3">{{ $transaction->created_at->format('d.m.Y H:i') }}</td>
                             <td class="px-4 py-3">{{ $typeLabels[$transaction->type] ?? ucfirst($transaction->type) }}</td>
                            <td class="px-4 py-3">{{ $transaction->token->symbol ?? '—' }}</td>
                            <td class="px-4 py-3">{{ number_format($transaction->total_amount, 2, '.', ' ') }}</td>
                             <td class="px-4 py-3">{{ $statusLabels[$transaction->status] ?? ucfirst($transaction->status) }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('client.transactions.show', $transaction) }}" class="text-sm text-indigo-600 hover:underline">Подробнее</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">Транзакции отсутствуют.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $transactions->links() }}
        </div>
    </div>
</x-layouts.app>


