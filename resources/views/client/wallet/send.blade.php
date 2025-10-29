@extends('layouts.app')

@section('title', 'Отправить TRON')

@section('content')
<div class="max-w-2xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Отправить TRON</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Отправьте TRX или USDT на другой адрес</p>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <form action="{{ route('client.wallet.send.post') }}" method="POST">
            @csrf
            
            <!-- Currency Selection -->
            <div class="mb-6">
                <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Валюта
                </label>
                <select name="currency" id="currency" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                    <option value="">Выберите валюту</option>
                    <option value="TRX" {{ old('currency') == 'TRX' ? 'selected' : '' }}>TRX</option>
                    <option value="USDT" {{ old('currency') == 'USDT' ? 'selected' : '' }}>USDT</option>
                </select>
                @error('currency')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Recipient Address -->
            <div class="mb-6">
                <label for="to_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Адрес получателя
                </label>
                <input type="text" 
                       name="to_address" 
                       id="to_address"
                       value="{{ old('to_address') }}"
                       placeholder="T..."
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono"
                       required>
                @error('to_address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Введите TRON адрес получателя (начинается с T)
                </p>
            </div>

            <!-- Amount -->
            <div class="mb-6">
                <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Сумма
                </label>
                <div class="relative">
                    <input type="number" 
                           name="amount" 
                           id="amount"
                           value="{{ old('amount') }}"
                           step="0.000001"
                           min="0.000001"
                           placeholder="0.000000"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <span id="currency_symbol" class="text-gray-500 dark:text-gray-400 text-sm font-medium"></span>
                    </div>
                </div>
                @error('amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Доступно: <span id="available_balance">-</span>
                </p>
            </div>

            <!-- Transaction Fee -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Комиссия транзакции</h4>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <p>TRX: <span id="trx_fee">0.1 TRX</span></p>
                    <p>USDT: <span id="usdt_fee">0 USDT</span></p>
                </div>
            </div>

            <!-- Total Amount -->
            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900 rounded-md">
                <h4 class="text-sm font-medium text-blue-700 dark:text-blue-300 mb-2">Общая сумма</h4>
                <div class="text-lg font-semibold text-blue-900 dark:text-blue-100">
                    <span id="total_amount">-</span>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex space-x-4">
                <button type="button" 
                        onclick="validateAddress()"
                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    Проверить адрес
                </button>
                <button type="submit" 
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    Отправить
                </button>
            </div>
        </form>
    </div>

    <!-- Wallet Balance Info -->
    <div class="mt-6 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Баланс кошелька</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">TRX</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($wallet->balance_trx, 6) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">USDT</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($wallet->balance_usdt, 6) }}</p>
            </div>
        </div>
    </div>
</div>

<script>
const wallet = @json($wallet);

document.getElementById('currency').addEventListener('change', function() {
    const currency = this.value;
    const symbol = currency === 'TRX' ? 'TRX' : 'USDT';
    const balance = currency === 'TRX' ? wallet.balance_trx : wallet.balance_usdt;
    
    document.getElementById('currency_symbol').textContent = symbol;
    document.getElementById('available_balance').textContent = `${balance} ${symbol}`;
    
    updateTotalAmount();
});

document.getElementById('amount').addEventListener('input', function() {
    updateTotalAmount();
});

function updateTotalAmount() {
    const currency = document.getElementById('currency').value;
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    
    if (currency === 'TRX') {
        const fee = 0.1;
        const total = amount + fee;
        document.getElementById('total_amount').textContent = `${total} TRX`;
    } else if (currency === 'USDT') {
        const fee = 0;
        const total = amount + fee;
        document.getElementById('total_amount').textContent = `${total} USDT`;
    } else {
        document.getElementById('total_amount').textContent = '-';
    }
}

function validateAddress() {
    const address = document.getElementById('to_address').value;
    
    if (!address) {
        alert('Введите адрес получателя');
        return;
    }
    
    fetch('/api/tron/validate-address', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ address: address })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.is_valid) {
            alert('Адрес валиден ✓');
        } else {
            alert('Неверный адрес ✗');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка проверки адреса');
    });
}

// Initialize currency symbol on page load
document.addEventListener('DOMContentLoaded', function() {
    const currency = document.getElementById('currency').value;
    if (currency) {
        document.getElementById('currency').dispatchEvent(new Event('change'));
    }
});
</script>
@endsection



