@php($title = 'Транзакция ' . $transaction->transaction_id)
<x-layouts.app :title="$title">
    <!-- |KB Детализация отдельной транзакции -->
    <div class="space-y-6 p-6">
        @php($statusLabels = [
            'pending' => 'В ожидании',
            'processing' => 'В обработке',
            'completed' => 'Завершено',
            'failed' => 'Ошибка',
            'cancelled' => 'Отменено',
        ])

         <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
                 <p class="text-sm text-zinc-500 dark:text-zinc-400">Статус: {{ $statusLabels[$transaction->status] ?? ucfirst($transaction->status) }} • Тип: {{ $transaction->type === 'buy' ? 'Покупка' : ($transaction->type === 'sell' ? 'Продажа' : ($transaction->type === 'transfer' ? 'Перевод' : ($transaction->type === 'refund' ? 'Возврат' : $transaction->type))) }}</p>
            </div>
            <a href="{{ route('admin.transactions') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Назад к списку</a>
        </div>

        @if(session('success'))
            <x-ui.flash :message="session('success')" type="success" />
        @endif
        @if(session('error'))
            <x-ui.flash :message="session('error')" type="error" />
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <x-admin.panel title="Основные данные" class="lg:col-span-2">
                <dl class="grid gap-4 text-sm text-zinc-700 dark:text-zinc-200 md:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase text-zinc-500 dark:text-zinc-400">Пользователь</dt>
                        <dd>{{ $transaction->user->name ?? '—' }} (ID: {{ $transaction->user_id }})</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-zinc-500 dark:text-zinc-400">Токен</dt>
                        <dd>{{ $transaction->token->name ?? '—' }} ({{ $transaction->token->symbol ?? '—' }})</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-zinc-500 dark:text-зinc-400">Сумма</dt>
                        <dd>{{ number_format($transaction->amount, 8, '.', ' ') }} • Всего: {{ number_format($transaction->total_amount, 2, '.', ' ') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-зinc-500 dark:text-зinc-400">Комиссия</dt>
                        <dd>{{ number_format($transaction->fee, 2, '.', ' ') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-зinc-500 dark:text-зinc-400">Метод оплаты</dt>
                        <dd>{{ $transaction->payment_method ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-зinc-500 dark:text-zinc-400">Ссылочный код</dt>
                        <dd>{{ $transaction->payment_reference ?? '—' }}</dd>
                    </div>
                </dl>
            </x-admin.panel>

            <x-admin.panel title="Действия">
                <div class="space-y-2 text-sm">
                     <p class="text-zinc-600 dark:text-zinc-300">Статус: {{ $statusLabels[$transaction->status] ?? ucfirst($transaction->status) }}</p>
                    <p class="text-zinc-600 dark:text-zinc-300">Создана: {{ $transaction->created_at->format('d.m.Y H:i') }}</p>
                    <p class="text-зинk-600 dark:text-зинk-300">Обработана: {{ optional($transaction->processed_at)->format('d.m.Y H:i') ?? '—' }}</p>
                </div>

                @if($transaction->status === 'pending')
                    <form action="{{ route('admin.transactions.cancel', $transaction) }}" method="POST" class="mt-4 space-y-2">
                        @csrf
                        <button type="submit" class="w-full rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-500">Отменить транзакцию</button>
                    </form>
                @endif
            </x-admin.panel>
        </div>

        @if($transaction->metadata)
            <x-admin.panel title="Метаданные">
                <pre class="overflow-x-auto rounded bg-black/5 p-4 text-xs text-zinc-700 dark:bg-white/10 dark:text-zinc-300">{{ json_encode($transaction->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </x-admin.panel>
        @endif
    </div>
</x-layouts.app>


