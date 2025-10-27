@extends('layouts.app')

@section('title', 'Ошибка обработки платежа')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <!-- Error Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>

            <!-- Error Message -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Ошибка обработки платежа</h1>
                <p class="text-gray-600">К сожалению, платеж не был обработан. Попробуйте еще раз или выберите другой способ оплаты.</p>
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
                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-sm">
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

            <!-- Error Details -->
            @if($error)
            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                <h3 class="text-sm font-medium text-red-800 mb-2">Причина ошибки:</h3>
                <p class="text-sm text-red-700">{{ $error }}</p>
            </div>
            @endif

            <!-- Common Solutions -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
                <h3 class="text-sm font-medium text-yellow-800 mb-2">Возможные решения:</h3>
                <ul class="text-sm text-yellow-700 space-y-1">
                    <li>• Проверьте правильность введенных данных карты</li>
                    <li>• Убедитесь, что на карте достаточно средств</li>
                    <li>• Попробуйте другую карту или банк</li>
                    <li>• Проверьте, не заблокирована ли карта банком</li>
                    <li>• Обратитесь в службу поддержки вашего банка</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center space-x-4">
                <a href="{{ route('client.packages') }}" 
                   class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Попробовать снова
                </a>
                <a href="{{ route('client.dashboard') }}" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Перейти в кабинет
                </a>
            </div>

            <!-- Support Contact -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600">
                    Нужна помощь? 
                    <a href="mailto:support@cardfly.online" class="text-blue-600 hover:text-blue-800">
                        Обратитесь в службу поддержки
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
