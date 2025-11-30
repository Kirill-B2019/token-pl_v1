@php($title = 'Отправить TRON')
<x-layouts.app :title="$title">
    <!-- |KB Форма отправки TRX/USDT -->
    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $title }}</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Отправьте TRX или USDT на другой адрес</p>
        </div>

        @if(session('success'))
            <x-ui.flash :message="session('success')" type="success" />
        @endif
        @if(session('error'))
            <x-ui.flash :message="session('error')" type="error" />
        @endif

        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <form action="{{ route('client.wallet.send.post') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Currency Selection -->
                <div>
                    <label for="currency" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
                        Валюта
                    </label>
                    <select name="currency" id="currency"
                            class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                        <option value="">Выберите валюту</option>
                        <option value="TRX" {{ old('currency') == 'TRX' ? 'selected' : '' }}>TRX</option>
                        <option value="USDT" {{ old('currency') == 'USDT' ? 'selected' : '' }}>USDT</option>
                    </select>
                    @error('currency')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Recipient Address -->
                <div>
                    <label for="to_address" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
                        Адрес получателя
                    </label>
                    <input type="text"
                           name="to_address"
                           id="to_address"
                           value="{{ old('to_address') }}"
                           placeholder="T..."
                           class="w-full rounded-md border border-zinc-300 px-3 py-2 font-mono text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                    @error('to_address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Введите TRON адрес получателя (начинается с T)
                    </p>
                </div>

                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
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
                               class="w-full rounded-md border border-zinc-300 px-3 py-2 pr-12 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <span id="currency_symbol" class="text-sm font-medium text-zinc-500 dark:text-zinc-400"></span>
                        </div>
                    </div>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Доступно: <span id="available_balance">-</span>
                    </p>
                </div>

                <!-- Transaction Fee -->
                <div class="rounded-md border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <h4 class="mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">Комиссия транзакции</h4>
                    <div class="text-sm text-zinc-600 dark:text-zinc-300">
                        <p>TRX: <span id="trx_fee">0.1 TRX</span></p>
                        <p>USDT: <span id="usdt_fee">0 USDT</span></p>
                    </div>
                </div>

                <!-- Total Amount -->
                <div class="rounded-md border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900">
                    <h4 class="mb-2 text-sm font-medium text-blue-700 dark:text-blue-200">Общая сумма</h4>
                    <div class="text-lg font-semibold text-blue-900 dark:text-blue-100">
                        <span id="total_amount">-</span>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4">
                    <button type="button"
                            onclick="validateAddress()"
                            class="flex-1 rounded-md bg-zinc-600 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-500">
                        Проверить адрес
                    </button>
                    <button type="submit"
                            class="flex-1 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500">
                        Отправить
                    </button>
                </div>
            </form>
        </div>

        <!-- Wallet Balance Info -->
        <x-admin.panel title="Баланс кошелька">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">TRX</p>
                    <p class="text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($wallet->balance_trx, 6) }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">USDT</p>
                    <p class="text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($wallet->balance_usdt, 6) }}</p>
                </div>
            </div>
        </x-admin.panel>
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
</x-layouts.app>



