@php($title = 'Транзакция ' . $transaction->transaction_id)
<x-layouts.app :title="$title">
    <!-- |KB Детали транзакции клиента -->
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
            <a href="{{ route('client.transactions') }}" class="text-sm text-indigo-600 hover:underline">Назад к истории</a>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <x-admin.panel title="Основная информация" class="lg:col-span-2">
                <dl class="grid gap-4 text-sm text-zinc-700 dark:text-zinc-200 md:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase text-zinc-500 dark:text-zinc-400">Тип</dt>
                         <dd>{{ $typeLabels[$transaction->type] ?? ucfirst($transaction->type) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-zinc-500 dark:text-zinc-400">Статус</dt>
                         <dd>{{ $statusLabels[$transaction->status] ?? ucfirst($transaction->status) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-zinc-500 dark:text-zinc-400">Токен</dt>
                        <dd>{{ $transaction->token->name ?? '—' }} ({{ $transaction->token->symbol ?? '—' }})</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-zinc-500 dark:text-zinc-400">Сумма</dt>
                        <dd>{{ number_format($transaction->amount, 6, '.', ' ') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-zinc-500 dark:text-zinc-400">Итого</dt>
                        <dd>{{ number_format($transaction->total_amount, 2, '.', ' ') }} ₽</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-zinc-500 dark:text-zinc-400">Комиссия</dt>
                        <dd>{{ number_format($transaction->fee, 2, '.', ' ') }} ₽</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-zinc-500 dark:text-zinc-400">Метод оплаты</dt>
                        <dd>{{ $transaction->payment_method ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-zinc-500 dark:text-zinc-400">Создано</dt>
                        <dd>{{ $transaction->created_at->format('d.m.Y H:i') }}</dd>
                    </div>
                </dl>
            </x-admin.panel>

            @if($transaction->metadata)
                <x-admin.panel title="Детали платежа">
                    <pre class="max-h-64 overflow-auto rounded bg-black/5 p-3 text-xs text-zinc-700 dark:bg-white/10 dark:text-zinc-300">{{ json_encode($transaction->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </x-admin.panel>
            @endif
        </div>
    </div>
</x-layouts.app>


