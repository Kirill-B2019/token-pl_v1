@php($title = 'Платеж успешно обработан')
<x-layouts.app :title="$title">
    <!-- |KB Страница успешного платежа -->
    <div class="flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-2xl rounded-lg border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <!-- Success Icon -->
            <div class="mb-6 flex justify-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/40">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            <!-- Success Message -->
            <div class="mb-6 text-center">
                <h1 class="mb-2 text-2xl font-bold text-zinc-900 dark:text-white">Платеж успешно обработан!</h1>
                <p class="text-zinc-600 dark:text-zinc-300">
                    @if($transaction && $transaction->deposit_type === 'rub')
                        Средства будут зачислены на ваш баланс в рублях в течение нескольких минут.
                    @else
                        Ваши токены будут зачислены на баланс в течение нескольких минут.
                    @endif
                </p>
            </div>

            @if($transaction)
            @php($statusLabels = [
                'pending' => 'В ожидании',
                'processing' => 'В обработке',
                'completed' => 'Завершено',
                'failed' => 'Ошибка',
                'cancelled' => 'Отменено',
            ])

            <!-- Transaction Details -->
            <div class="mb-6 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">Детали транзакции</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-300">ID транзакции:</span>
                        <span class="font-mono text-sm text-zinc-900 dark:text-white">{{ $transaction->payment_reference }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-300">Сумма:</span>
                        <span class="font-semibold text-zinc-900 dark:text-white">
                            {{ number_format($transaction->amount, 2) }} ₽
                        </span>
                    </div>
                    @if($transaction->deposit_type !== 'rub')
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-300">Количество токенов:</span>
                        <span class="font-semibold text-zinc-900 dark:text-white">{{ number_format($transaction->amount, 8) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-300">Статус:</span>
                        <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900/40 dark:text-green-200">
                            {{ $statusLabels[$transaction->status] ?? ucfirst($transaction->status) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-300">Дата:</span>
                        <span class="text-zinc-900 dark:text-white">{{ $transaction->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    @if(isset($transaction->metadata['bank_code']))
                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-300">Банк:</span>
                        <span class="text-zinc-900 dark:text-white">{{ $transaction->metadata['bank_code'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Next Steps -->
            <div class="mb-6 rounded-md border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                <h3 class="mb-2 text-sm font-medium text-blue-800 dark:text-blue-200">Что дальше?</h3>
                <ul class="space-y-1 text-sm text-blue-700 dark:text-blue-300">
                    @if($transaction && $transaction->deposit_type === 'rub')
                        <li>• Средства будут зачислены на ваш баланс в рублях автоматически</li>
                        <li>• Вы получите уведомление на email о зачислении</li>
                        <li>• Проверить баланс можно в личном кабинете</li>
                    @else
                        <li>• Токены будут зачислены на ваш баланс автоматически</li>
                        <li>• Вы получите уведомление на email о зачислении</li>
                        <li>• Проверить баланс можно в личном кабинете</li>
                    @endif
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center gap-4">
                <a href="{{ route('client.dashboard') }}"
                   class="rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-500">
                    Перейти в кабинет
                </a>
                @if($transaction)
                <a href="{{ route('client.transactions.show', $transaction) }}"
                   class="rounded-md border border-zinc-300 px-6 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800">
                    Подробности транзакции
                </a>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>



