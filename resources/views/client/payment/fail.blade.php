@php($title = 'Ошибка обработки платежа')
<x-layouts.app :title="$title">
    <!-- |KB Страница ошибки платежа -->
    <div class="flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-2xl rounded-lg border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <!-- Error Icon -->
            <div class="mb-6 flex justify-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/40">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>

            <!-- Error Message -->
            <div class="mb-6 text-center">
                <h1 class="mb-2 text-2xl font-bold text-zinc-900 dark:text-white">Ошибка обработки платежа</h1>
                <p class="text-zinc-600 dark:text-zinc-300">К сожалению, платеж не был обработан. Попробуйте еще раз или выберите другой способ оплаты.</p>
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
                        <span class="rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800 dark:bg-red-900/40 dark:text-red-200">
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

            <!-- Error Details -->
            @if($error)
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                <h3 class="mb-2 text-sm font-medium text-red-800 dark:text-red-200">Причина ошибки:</h3>
                <p class="text-sm text-red-700 dark:text-red-300">{{ $error }}</p>
            </div>
            @endif

            <!-- Common Solutions -->
            <div class="mb-6 rounded-md border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                <h3 class="mb-2 text-sm font-medium text-yellow-800 dark:text-yellow-200">Возможные решения:</h3>
                <ul class="space-y-1 text-sm text-yellow-700 dark:text-yellow-300">
                    <li>• Проверьте правильность введенных данных карты</li>
                    <li>• Убедитесь, что на карте достаточно средств</li>
                    <li>• Попробуйте другую карту или банк</li>
                    <li>• Проверьте, не заблокирована ли карта банком</li>
                    <li>• Обратитесь в службу поддержки вашего банка</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="mb-8 flex justify-center gap-4">
                <a href="{{ route('client.balance.topup') }}"
                   class="rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-500">
                    Попробовать снова
                </a>
                <a href="{{ route('client.dashboard') }}"
                   class="rounded-md border border-zinc-300 px-6 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800">
                    Перейти в кабинет
                </a>
            </div>

            <!-- Support Contact -->
            <div class="text-center">
                <p class="text-sm text-zinc-600 dark:text-zinc-300">
                    Нужна помощь?
                    <a href="mailto:support@cardfly.online" class="text-blue-600 hover:underline">
                        Обратитесь в службу поддержки
                    </a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
