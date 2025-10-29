@extends('layouts.app')

@section('title', 'Платеж успешно обработан')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <!-- Success Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            <!-- Success Message -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Платеж успешно обработан!</h1>
                <p class="text-gray-600">Ваши токены будут зачислены на баланс в течение нескольких минут.</p>
            </div>

            @if($transaction)
            <!-- Transaction Details -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Детали транзакции</h2>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">ID транзакции:</span>
                        <span class="font-mono text-sm">{{ $transaction->payment_reference }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Сумма:</span>
                        <span class="font-semibold">{{ number_format($transaction->total_amount, 2) }} RUB</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Количество токенов:</span>
                        <span class="font-semibold">{{ number_format($transaction->amount, 8) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Статус:</span>
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Дата:</span>
                        <span>{{ $transaction->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    @if(isset($transaction->metadata['bank_code']))
                    <div class="flex justify-between">
                        <span class="text-gray-600">Банк:</span>
                        <span>{{ $transaction->metadata['bank_code'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Next Steps -->
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                <h3 class="text-sm font-medium text-blue-800 mb-2">Что дальше?</h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Токены будут зачислены на ваш баланс автоматически</li>
                    <li>• Вы получите уведомление на email о зачислении</li>
                    <li>• Проверить баланс можно в личном кабинете</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center space-x-4">
                <a href="{{ route('client.dashboard') }}" 
                   class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Перейти в кабинет
                </a>
                @if($transaction)
                <a href="{{ route('client.transactions.show', $transaction) }}" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Подробности транзакции
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection



