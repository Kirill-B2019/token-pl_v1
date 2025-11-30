@php($title = 'TRON Кошелек')
<x-layouts.app :title="$title">
    <!-- |KB Главная страница TRON кошелька -->
    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Управляйте своими TRX и USDT токенами</p>
        </div>

        @if(session('success'))
            <x-ui.flash :message="session('success')" type="success" />
        @endif
        @if(session('error'))
            <x-ui.flash :message="session('error')" type="error" />
        @endif

        @if (!$wallet)
            <!-- Create Wallet Section -->
            <x-admin.panel title="Создать TRON кошелек">
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="mb-2 text-lg font-medium text-zinc-900 dark:text-white">Создать TRON кошелек</h3>
                    <p class="mb-6 text-zinc-600 dark:text-zinc-300">
                        Создайте новый кошелек для работы с TRON блокчейном. Кошелек будет автоматически сгенерирован и привязан к вашему аккаунту.
                    </p>
                    <form action="{{ route('client.wallet.create') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500">
                            Создать кошелек
                        </button>
                    </form>
                </div>
            </x-admin.panel>
        @else
            <!-- Wallet Dashboard -->
            <div class="grid gap-4 md:grid-cols-3">
                <!-- Balance Cards -->
                <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 dark:bg-red-900">
                            <span class="font-bold text-red-600 dark:text-red-400">T</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">TRX Баланс</p>
                            <p class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ number_format($wallet->balance_trx, 6) }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                            <span class="font-bold text-green-600 dark:text-green-400">U</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">USDT Баланс</p>
                            <p class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ number_format($wallet->balance_usdt, 6) }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                            <span class="font-bold text-blue-600 dark:text-blue-400">$</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Общий баланс (USD)</p>
                            <p class="text-2xl font-semibold text-zinc-900 dark:text-white">${{ number_format($wallet->total_balance_usd, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wallet Address -->
            <x-admin.panel title="Адрес кошелька">
                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <input type="text"
                               value="{{ $wallet->address }}"
                               readonly
                               class="w-full rounded-md border border-zinc-300 bg-zinc-50 px-3 py-2 text-sm font-mono text-zinc-900 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                    </div>
                    <button onclick="copyToClipboard('{{ $wallet->address }}')"
                            class="rounded-md border border-zinc-300 bg-zinc-100 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-200 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-600">
                        Копировать
                    </button>
                    <button onclick="showQRCode()"
                            class="rounded-md bg-blue-100 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-300 dark:hover:bg-blue-800">
                        QR код
                    </button>
                </div>
            </x-admin.panel>

            <!-- Action Buttons -->
            <x-admin.panel title="Действия">
                <div class="grid gap-4 md:grid-cols-3">
                    <a href="{{ route('client.wallet.send') }}"
                       class="rounded-md bg-blue-600 px-4 py-3 text-center text-sm font-medium text-white hover:bg-blue-500">
                        Отправить
                    </a>
                    <a href="{{ route('client.wallet.history') }}"
                       class="rounded-md bg-zinc-600 px-4 py-3 text-center text-sm font-medium text-white hover:bg-zinc-500">
                        История
                    </a>
                    <button onclick="syncBalance()"
                            class="rounded-md bg-green-600 px-4 py-3 text-sm font-medium text-white hover:bg-green-500">
                        Обновить баланс
                    </button>
                </div>
            </x-admin.panel>

            <!-- Recent Transactions -->
            <x-admin.panel title="Последние транзакции">
                <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                    <p class="mb-2">Транзакции будут отображаться здесь после их выполнения</p>
                    <a href="{{ route('client.wallet.history') }}" class="text-blue-600 hover:underline dark:text-blue-400">
                        Посмотреть полную историю
                    </a>
                </div>
            </x-admin.panel>
        @endif
    </div>

    <!-- QR Code Modal -->
    <div id="qrModal" class="fixed inset-0 z-50 hidden bg-zinc-600 bg-opacity-50">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="w-full max-w-sm rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-center">
                    <h3 class="mb-4 text-lg font-medium text-zinc-900 dark:text-white">QR код адреса</h3>
                    <div id="qrCode" class="mb-4"></div>
                    <p class="mb-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $wallet->address ?? '' }}</p>
                    <button onclick="closeQRModal()"
                            class="rounded-md bg-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-400 dark:bg-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-500">
                        Закрыть
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = 'Скопировано!';
            button.classList.add('bg-green-100', 'text-green-700', 'dark:bg-green-900', 'dark:text-green-200');
            setTimeout(() => {
                button.textContent = 'Копировать';
                button.classList.remove('bg-green-100', 'text-green-700', 'dark:bg-green-900', 'dark:text-green-200');
            }, 2000);
        });
    }

    function showQRCode() {
        const qrDiv = document.getElementById('qrCode');
        qrDiv.innerHTML = '<div class="mx-auto flex h-48 w-48 items-center justify-center bg-zinc-200 dark:bg-zinc-700"><span class="text-zinc-500 dark:text-zinc-400">QR код</span></div>';
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
</x-layouts.app>



