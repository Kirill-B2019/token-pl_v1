@extends('layouts.app')

@section('title', 'Покупка токенов - ' . $package->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Покупка токенов</h1>
            
            <!-- Package Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h2 class="text-lg font-semibold text-gray-800">{{ $package->name }}</h2>
                <div class="mt-2 space-y-1">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Количество токенов:</span> {{ number_format($package->token_amount, 8) }}
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Цена за токен:</span> {{ number_format($package->price_per_token, 2) }} RUB
                    </p>
                    <p class="text-lg font-bold text-green-600">
                        <span class="font-medium">Итого к оплате:</span> {{ number_format($package->final_price, 2) }} RUB
                    </p>
                </div>
            </div>

            <!-- Payment Form -->
            <form method="POST" action="{{ route('client.packages.purchase.store', $package) }}" class="space-y-6">
                @csrf
                
                <!-- Bank Selection -->
                <div>
                    <label for="bank_code" class="block text-sm font-medium text-gray-700 mb-2">
                        Выберите банк для оплаты
                    </label>
                    <select name="bank_code" id="bank_code" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
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
                <div id="commission-info" class="hidden bg-yellow-50 border border-yellow-200 rounded-md p-3">
                    <p class="text-sm text-yellow-800">
                        <span class="font-medium">Комиссия банка:</span> <span id="commission-rate"></span>%
                        <br>
                        <span class="font-medium">Сумма комиссии:</span> <span id="commission-amount"></span> RUB
                        <br>
                        <span class="font-medium">Итого с комиссией:</span> <span id="total-with-commission"></span> RUB
                    </p>
                </div>

                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                        Способ оплаты
                    </label>
                    <select name="payment_method" id="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
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
                            <label for="card_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Номер карты
                            </label>
                            <input type="text" name="card_number" id="card_number" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="1234 5678 9012 3456" maxlength="19" required>
                            @error('card_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Expiry Date -->
                        <div>
                            <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Срок действия
                            </label>
                            <input type="text" name="expiry_date" id="expiry_date" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="MM/YY" maxlength="5" required>
                            @error('expiry_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- CVV -->
                        <div>
                            <label for="cvv" class="block text-sm font-medium text-gray-700 mb-2">
                                CVV код
                            </label>
                            <input type="text" name="cvv" id="cvv" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="123" maxlength="4" required>
                            @error('cvv')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cardholder Name -->
                        <div class="md:col-span-2">
                            <label for="cardholder_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Имя держателя карты
                            </label>
                            <input type="text" name="cardholder_name" id="cardholder_name" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="IVAN IVANOV" required>
                            @error('cardholder_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Безопасность платежей</h3>
                            <div class="mt-2 text-sm text-blue-700">
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
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('client.packages') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Отмена
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Оплатить
                    </button>
                </div>
            </form>
        </div>
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
@endsection


