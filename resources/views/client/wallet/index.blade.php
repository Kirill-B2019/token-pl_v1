@extends('layouts.app')

@section('title', 'TRON Кошелек')

@section('content')
<div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">TRON Кошелек</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Управляйте своими TRX и USDT токенами</p>
    </div>

    @if (!$wallet)
        <!-- Create Wallet Section -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 mb-4">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Создать TRON кошелек</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Создайте новый кошелек для работы с TRON блокчейном. Кошелек будет автоматически сгенерирован и привязан к вашему аккаунту.
                </p>
                <form action="{{ route('client.wallet.create') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                        Создать кошелек
                    </button>
                </form>
            </div>
        </div>
    @else
        <!-- Wallet Dashboard -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Balance Cards -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                            <span class="text-red-600 dark:text-red-400 font-bold text-sm">T</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">TRX Баланс</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($wallet->balance_trx, 6) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                            <span class="text-green-600 dark:text-green-400 font-bold text-sm">U</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">USDT Баланс</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($wallet->balance_usdt, 6) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 dark:text-blue-400 font-bold text-sm">$</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Общий баланс (USD)</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">${{ number_format($wallet->total_balance_usd, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wallet Address -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Адрес кошелька</h3>
            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <input type="text" 
                           value="{{ $wallet->address }}" 
                           readonly 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm">
                </div>
                <button onclick="copyToClipboard('{{ $wallet->address }}')" 
                        class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                    Копировать
                </button>
                <button onclick="showQRCode()" 
                        class="bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-300 px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                    QR код
                </button>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Действия</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('client.wallet.send') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md text-center transition duration-150 ease-in-out">
                    Отправить
                </a>
                <a href="{{ route('client.wallet.history') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-3 px-4 rounded-md text-center transition duration-150 ease-in-out">
                    История
                </a>
                <button onclick="syncBalance()" 
                        class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-md transition duration-150 ease-in-out">
                    Обновить баланс
                </button>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Последние транзакции</h3>
            <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                <p>Транзакции будут отображаться здесь после их выполнения</p>
                <a href="{{ route('client.wallet.history') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                    Посмотреть полную историю
                </a>
            </div>
        </div>
    @endif
</div>

<!-- QR Code Modal -->
<div id="qrModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-sm w-full">
            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">QR код адреса</h3>
                <div id="qrCode" class="mb-4"></div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $wallet->address ?? '' }}</p>
                <button onclick="closeQRModal()" 
                        class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-md">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Скопировано!';
        button.classList.add('bg-green-100', 'text-green-700');
        setTimeout(() => {
            button.textContent = originalText;
            button.classList.remove('bg-green-100', 'text-green-700');
        }, 2000);
    });
}

function showQRCode() {
    // Simple QR code generation (in production use proper QR library)
    const qrDiv = document.getElementById('qrCode');
    qrDiv.innerHTML = '<div class="w-48 h-48 bg-gray-200 dark:bg-gray-700 mx-auto flex items-center justify-center"><span class="text-gray-500 dark:text-gray-400">QR код</span></div>';
    document.getElementById('qrModal').classList.remove('hidden');
}

function closeQRModal() {
    document.getElementById('qrModal').classList.add('hidden');
}

function syncBalance() {
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Обновление...';
    button.disabled = true;
    
    fetch('/api/tron/wallet/sync', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Ошибка обновления баланса: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка обновления баланса');
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}
</script>
@endsection


