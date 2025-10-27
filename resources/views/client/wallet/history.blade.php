@extends('layouts.app')

@section('title', 'История транзакций TRON')

@section('content')
<div class="max-w-6xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">История транзакций</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Все транзакции вашего TRON кошелька</p>
    </div>

    <!-- Wallet Info -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Адрес кошелька</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-mono">{{ $wallet->address }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500 dark:text-gray-400">Общий баланс</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">${{ number_format($wallet->total_balance_usd, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="currency_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Валюта
                </label>
                <select id="currency_filter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">Все валюты</option>
                    <option value="TRX">TRX</option>
                    <option value="USDT">USDT</option>
                </select>
            </div>
            <div>
                <label for="type_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Тип
                </label>
                <select id="type_filter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">Все типы</option>
                    <option value="send">Отправка</option>
                    <option value="receive">Получение</option>
                </select>
            </div>
            <div>
                <label for="status_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Статус
                </label>
                <select id="status_filter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">Все статусы</option>
                    <option value="completed">Завершено</option>
                    <option value="pending">В обработке</option>
                    <option value="failed">Ошибка</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="applyFilters()" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    Применить
                </button>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Транзакции</h3>
        </div>
        
        @if(empty($transactions) || !isset($transactions['data']) || empty($transactions['data']))
            <div class="text-center py-12">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Нет транзакций</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">
                    Транзакции будут отображаться здесь после их выполнения
                </p>
                <a href="{{ route('client.wallet.send') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    Отправить транзакцию
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Дата
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Тип
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Сумма
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Адрес
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Статус
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                ID транзакции
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($transactions['data'] as $transaction)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($transaction['timestamp'] ?? $transaction['created_at'] ?? now())->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ ($transaction['type'] ?? 'unknown') === 'send' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                        {{ $transaction['type'] ?? 'unknown' === 'send' ? 'Отправка' : 'Получение' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ number_format($transaction['amount'] ?? 0, 6) }} {{ $transaction['currency'] ?? 'TRX' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-mono">
                                    {{ Str::limit($transaction['to_address'] ?? $transaction['from_address'] ?? 'N/A', 20) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ ($transaction['status'] ?? 'unknown') === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                           (($transaction['status'] ?? 'unknown') === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                           'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                        {{ $transaction['status'] ?? 'unknown' === 'completed' ? 'Завершено' : 
                                           (($transaction['status'] ?? 'unknown') === 'pending' ? 'В обработке' : 'Ошибка') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-mono">
                                    {{ Str::limit($transaction['txid'] ?? $transaction['transaction_id'] ?? 'N/A', 16) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if(isset($transactions['meta']) && $transactions['meta']['total'] > 50)
        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-300">
                Показано {{ count($transactions['data']) }} из {{ $transactions['meta']['total'] }} транзакций
            </div>
            <div class="flex space-x-2">
                <button class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 px-3 py-1 rounded text-sm">
                    Предыдущая
                </button>
                <button class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 px-3 py-1 rounded text-sm">
                    Следующая
                </button>
            </div>
        </div>
    @endif
</div>

<script>
function applyFilters() {
    const currency = document.getElementById('currency_filter').value;
    const type = document.getElementById('type_filter').value;
    const status = document.getElementById('status_filter').value;
    
    // Build query parameters
    const params = new URLSearchParams();
    if (currency) params.append('currency', currency);
    if (type) params.append('type', type);
    if (status) params.append('status', status);
    
    // Reload page with filters
    const url = new URL(window.location);
    url.search = params.toString();
    window.location.href = url.toString();
}

// Load filters from URL on page load
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('currency')) {
        document.getElementById('currency_filter').value = urlParams.get('currency');
    }
    if (urlParams.get('type')) {
        document.getElementById('type_filter').value = urlParams.get('type');
    }
    if (urlParams.get('status')) {
        document.getElementById('status_filter').value = urlParams.get('status');
    }
});
</script>
@endsection


