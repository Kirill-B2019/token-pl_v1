<?php
// |KB Сервис для интеграции с платежной системой 2can

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TwoCanPaymentService
{
    protected string $shopId;
    protected string $secretKey;
    protected string $apiUrl;
    protected string $paymentUrl;
    protected string $currency;

    public function __construct()
    {
        $this->shopId = config('twocan.shop_id');
        $this->secretKey = config('twocan.secret_key');
        $this->apiUrl = config('twocan.api_url');
        $this->paymentUrl = config('twocan.payment_url');
        $this->currency = config('twocan.currency');
    }

    /**
     * Создание платежа
     */
    public function createPayment(User $user, float $amount, string $description = 'Пополнение баланса', ?string $cardToken = null): array
    {
        // Валидация суммы
        if ($amount < config('twocan.min_amount') || $amount > config('twocan.max_amount')) {
            throw new \InvalidArgumentException('Сумма платежа вне допустимого диапазона');
        }

        // Генерация уникального ID платежа
        $paymentId = 'payment_' . $user->id . '_' . time() . '_' . Str::random(8);

        // Создание транзакции в базе данных
        $transaction = Transaction::create([
            'transaction_id' => 'txn_' . $user->id . '_' . time(),
            'user_id' => $user->id,
            'token_id' => null, // Для RUB депозитов token_id = null
            'type' => 'deposit',
            'deposit_type' => 'rub',
            'amount' => $amount,
            'price' => 1.00, // Для RUB цена = 1
            'total_amount' => $amount, // Для RUB совпадает с amount
            'status' => 'pending',
            'payment_reference' => $paymentId,
            'metadata' => [
                'payment_system' => '2can',
                'shop_id' => $this->shopId,
            ]
        ]);

        // Подготовка данных для 2can API
        $data = [
            'shop_id' => $this->shopId,
            'amount' => $amount,
            'currency' => $this->currency,
            'payment_id' => $paymentId,
            'description' => $description,
            'success_url' => url(config('twocan.success_url')),
            'fail_url' => url(config('twocan.fail_url')),
            'customer_email' => $user->email,
            'customer_phone' => $user->phone,
        ];

        // Если указан токен карты, добавляем его в запрос
        if ($cardToken) {
            $data['card_token'] = $cardToken;
        }

        // Генерация подписи
        $signature = $this->generateSignature($data);
        $data['signature'] = $signature;

        try {
            // Отправка запроса к 2can API
            $response = Http::timeout(30)->post($this->apiUrl . 'payment/create', $data);

            if ($response->successful()) {
                $result = $response->json();

                // Обновление транзакции с данными от 2can
                $transaction->update([
                    'external_payment_id' => $result['payment_id'] ?? null,
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'twocan_payment_id' => $result['payment_id'] ?? null,
                        'payment_url' => $result['payment_url'] ?? null,
                    ])
                ]);

                return [
                    'success' => true,
                    'payment_id' => $paymentId,
                    'payment_url' => $result['payment_url'] ?? null,
                    'transaction_id' => $transaction->id,
                ];
            } else {
                // Обновление статуса транзакции при ошибке
                $transaction->update(['status' => 'failed']);
                Log::error('2can payment creation failed', [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'response' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'Не удалось создать платеж',
                ];
            }
        } catch (\Exception $e) {
            // Обновление статуса транзакции при исключении
            $transaction->update(['status' => 'failed']);
            Log::error('2can payment creation exception', [
                'user_id' => $user->id,
                'amount' => $amount,
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Ошибка при создании платежа',
            ];
        }
    }

    /**
     * Обработка успешного платежа (webhook/callback)
     */
    public function processPaymentSuccess(Request $request): bool
    {
        $paymentId = $request->input('payment_id');
        $status = $request->input('status');
        $signature = $request->input('signature');

        // Валидация подписи
        if (!$this->validateSignature($request->all(), $signature)) {
            Log::warning('Invalid signature in 2can webhook', ['payment_id' => $paymentId]);
            return false;
        }

        // Поиск транзакции
        $transaction = Transaction::where('payment_reference', $paymentId)->first();
        if (!$transaction) {
            Log::warning('Transaction not found for payment', ['payment_id' => $paymentId]);
            return false;
        }

        if ($status === 'success' && $transaction->status === 'pending') {
            // Обновление статуса транзакции
            $transaction->update(['status' => 'completed']);

            // Пополнение баланса пользователя
            $user = $transaction->user;
            $user->addRubBalance($transaction->amount);

            // Логирование успешного платежа
            Log::info('2can payment completed successfully', [
                'transaction_id' => $transaction->id,
                'user_id' => $user->id,
                'amount' => $transaction->amount,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Обработка неудачного платежа
     */
    public function processPaymentFailure(Request $request): bool
    {
        $paymentId = $request->input('payment_id');
        $status = $request->input('status');

        // Поиск транзакции
        $transaction = Transaction::where('payment_reference', $paymentId)->first();
        if (!$transaction) {
            Log::warning('Transaction not found for failed payment', ['payment_id' => $paymentId]);
            return false;
        }

        if ($transaction->status === 'pending') {
            // Обновление статуса транзакции
            $transaction->update(['status' => 'failed']);

            Log::info('2can payment failed', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'amount' => $transaction->amount,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Генерация подписи для запроса
     */
    protected function generateSignature(array $data): string
    {
        // Удаление signature из данных, если она есть
        unset($data['signature']);

        // Сортировка параметров по ключам
        ksort($data);

        // Создание строки для подписи
        $stringToSign = '';
        foreach ($data as $key => $value) {
            $stringToSign .= $key . '=' . $value . '&';
        }
        $stringToSign .= 'secret_key=' . $this->secretKey;

        // Возврат MD5 хэша
        return md5($stringToSign);
    }

    /**
     * Валидация подписи ответа
     */
    protected function validateSignature(array $data, string $signature): bool
    {
        $expectedSignature = $this->generateSignature($data);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Получение статуса платежа
     */
    public function getPaymentStatus(string $paymentId): ?array
    {
        $data = [
            'shop_id' => $this->shopId,
            'payment_id' => $paymentId,
        ];

        $signature = $this->generateSignature($data);
        $data['signature'] = $signature;

        try {
            $response = Http::timeout(30)->post($this->apiUrl . 'payment/status', $data);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Error getting payment status from 2can', [
                'payment_id' => $paymentId,
                'exception' => $e->getMessage(),
            ]);
        }

        return null;
    }
}
