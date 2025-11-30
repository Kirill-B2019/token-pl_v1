@php($title = 'Покупка токенов - ' . $package->name)
<x-layouts.app :title="$title">
    <!-- |KB Форма покупки пакета токенов -->
    <div class="flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-2xl rounded-lg border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="mb-6 text-2xl font-bold text-zinc-900 dark:text-white">Покупка токенов</h1>

            <!-- Package Info -->
            <div class="mb-6 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $package->name }}</h2>
                <div class="mt-2 space-y-1">
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">
                        <span class="font-medium">Количество токенов:</span> {{ number_format($package->token_amount, 8) }}
                    </p>
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">
                        <span class="font-medium">Цена за токен:</span> {{ number_format($package->price_per_token, 2) }} ₽
                    </p>
                    <p class="text-lg font-bold text-green-600 dark:text-green-400">
                        <span class="font-medium">Итого к оплате:</span> {{ number_format($package->final_price, 2) }} ₽
                    </p>
                </div>
            </div>

            <!-- Payment Form -->
            <form method="POST" action="{{ route('client.packages.purchase.store', $package) }}" class="space-y-6">
                @csrf

                <!-- Bank Selection -->
                <div>
                    <label for="bank_code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
                        Выберите банк для оплаты
                    </label>
                    <select name="bank_code" id="bank_code" class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                        <option value="">-- Выберите банк --</option>
                        <option value="MTS" data-commission="2.2">МТС Банк (комиссия 2.2%)</option>
                        <option value="SBER" data-commission="2.5">Сбербанк (комиссия 2.5%)</option>
                        <option value="VTB" data-commission="3.0">ВТБ (комиссия 3.0%)</option>
                        <option value="ALFA" data-commission="2.8">Альфа-Банк (комиссия 2.8%)</option>
                    </select>
                    @error('bank_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Commission Display -->
                <div id="commission-info" class="hidden rounded-md border border-yellow-200 bg-yellow-50 p-3 dark:border-yellow-800 dark:bg-yellow-900/20">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <span class="font-medium">Комиссия банка:</span> <span id="commission-rate"></span>%<br>
                        <span class="font-medium">Сумма комиссии:</span> <span id="commission-amount"></span> ₽<br>
                        <span class="font-medium">Итого с комиссией:</span> <span id="total-with-commission"></span> ₽
                    </p>
                </div>

                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
                        Способ оплаты
                    </label>
                    <select name="payment_method" id="payment_method" class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" required>
                        <option value="card">Банковская карта</option>
                        <option value="apple_pay">Apple Pay</option>
                        <option value="google_pay">Google Pay</option>
                    </select>
                    @error('payment_method')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Card Details -->
                <div id="card-details">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Card Number -->
                        <div class="md:col-span-2">
                            <label for="card_number" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
                                Номер карты
                            </label>
                            <input type="text" name="card_number" id="card_number"
                                   class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                   placeholder="1234 5678 9012 3456" maxlength="19" required>
                            @error('card_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Expiry Date -->
                        <div>
                            <label for="expiry_date" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
                                Срок действия
                            </label>
                            <input type="text" name="expiry_date" id="expiry_date"
                                   class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                   placeholder="MM/YY" maxlength="5" required>
                            @error('expiry_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- CVV -->
                        <div>
                            <label for="cvv" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
                                CVV код
                            </label>
                            <input type="text" name="cvv" id="cvv"
                                   class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                   placeholder="123" maxlength="4" required>
                            @error('cvv')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cardholder Name -->
                        <div class="md:col-span-2">
                            <label for="cardholder_name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-2">
                                Имя держателя карты
                            </label>
                            <input type="text" name="cardholder_name" id="cardholder_name"
                                   class="w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                   placeholder="IVAN IVANOV" required>
                            @error('cardholder_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="rounded-md border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Безопасность платежей</h3>
                            <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Все платежи защищены SSL-шифрованием</li>
                                    <li>Данные карты не сохраняются на наших серверах</li>
                                    <li>Обработка платежей через защищенные банковские системы</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-4">
                    <a href="{{ route('client.packages') }}"
                       class="rounded-md border border-zinc-300 px-6 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800">
                        Отмена
                    </a>
                    <button type="submit"
                            class="rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-500">
                        Оплатить
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const bankSelect = document.getElementById('bank_code');
        const commissionInfo = document.getElementById('commission-info');
        const commissionRate = document.getElementById('commission-rate');
        const commissionAmount = document.getElementById('commission-amount');
        const totalWithCommission = document.getElementById('total-with-commission');
        const packagePrice = {{ $package->final_price }};

        // Format card number
        const cardNumberInput = document.getElementById('card_number');
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });

        // Format expiry date
        const expiryInput = document.getElementById('expiry_date');
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });

        // Format CVV
        const cvvInput = document.getElementById('cvv');
        cvvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });

        // Format cardholder name
        const cardholderInput = document.getElementById('cardholder_name');
        cardholderInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });

        // Calculate commission
        bankSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const commission = parseFloat(selectedOption.dataset.commission) || 0;

            if (commission > 0) {
                const commissionAmountValue = (packagePrice * commission / 100).toFixed(2);
                const totalWithCommissionValue = (packagePrice + parseFloat(commissionAmountValue)).toFixed(2);

                commissionRate.textContent = commission;
                commissionAmount.textContent = commissionAmountValue;
                totalWithCommission.textContent = totalWithCommissionValue;

                commissionInfo.classList.remove('hidden');
            } else {
                commissionInfo.classList.add('hidden');
            }
        });

        // Show/hide card details based on payment method
        const paymentMethodSelect = document.getElementById('payment_method');
        const cardDetails = document.getElementById('card-details');

        paymentMethodSelect.addEventListener('change', function() {
            if (this.value === 'card') {
                cardDetails.style.display = 'block';
            } else {
                cardDetails.style.display = 'none';
            }
        });
    });
    </script>
</x-layouts.app>



