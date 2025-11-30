<?php
// |KB Сервис для работы с токенизацией карт через 2can API

namespace App\Services;

use App\Models\User;
use App\Models\UserCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwoCanCardService
{
    protected TwoCanPaymentService $paymentService;

    public function __construct(TwoCanPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Токенизация карты (привязка карты)
     */
    public function tokenizeCard(User $user, array $cardData): array
    {
        // Валидация данных карты
        $this->validateCardData($cardData);

        // Подготовка данных для 2can API
        $data = [
            'shop_id' => config('twocan.shop_id'),
            'number' => preg_replace('/\s+/', '', $cardData['number']), // Удаляем пробелы
            'expiry' => $cardData['expiry'],
            'cvv' => $cardData['cvv'],
            'holder' => $cardData['holder'] ?? null,
        ];

        // Генерация подписи
        $signature = $this->generateSignature($data);
        $data['signature'] = $signature;

        try {
            // Отправка запроса к 2can API для токенизации
            $response = Http::timeout(30)->post(config('twocan.api_url') . 'tokenize', $data);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['card_token'])) {
                    // Сохранение токенизированной карты в базе данных
                    $card = $this->saveCard($user, $result, $cardData);

                    return [
                        'success' => true,
                        'card' => $card,
                        'message' => 'Карта успешно привязана',
                    ];
                } else {
                    Log::warning('2can card tokenization failed - no token received', [
                        'user_id' => $user->id,
                        'response' => $result,
                    ]);

                    return [
                        'success' => false,
                        'error' => 'Не удалось получить токен карты',
                    ];
                }
            } else {
                $errorData = $response->json();
                Log::error('2can card tokenization API error', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                    'response' => $errorData,
                ]);

                return [
                    'success' => false,
                    'error' => $this->getErrorMessage($errorData),
                ];
            }
        } catch (\Exception $e) {
            Log::error('2can card tokenization exception', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Ошибка при привязке карты',
            ];
        }
    }

    /**
     * Удаление привязанной карты
     */
    public function deleteCard(User $user, int $cardId): bool
    {
        $card = $user->cards()->find($cardId);

        if (!$card) {
            return false;
        }

        // Здесь можно отправить запрос к 2can для удаления токена,
        // но обычно токены остаются активными на стороне 2can

        return $card->delete();
    }

    /**
     * Установка карты по умолчанию
     */
    public function setDefaultCard(User $user, int $cardId): bool
    {
        $card = $user->cards()->find($cardId);

        if (!$card) {
            return false;
        }

        return $card->setAsDefault();
    }

    /**
     * Получение активных карт пользователя
     */
    public function getUserCards(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $user->activeCards;
    }

    /**
     * Валидация данных карты
     */
    protected function validateCardData(array $cardData): void
    {
        // Проверка номера карты (алгоритм Луна)
        $number = preg_replace('/\s+/', '', $cardData['number']);
        if (!$this->isValidCardNumber($number)) {
            throw new \InvalidArgumentException('Неверный номер карты');
        }

        // Проверка срока действия
        if (!$this->isValidExpiry($cardData['expiry'])) {
            throw new \InvalidArgumentException('Неверный срок действия карты');
        }

        // Проверка CVV
        if (!preg_match('/^\d{3,4}$/', $cardData['cvv'])) {
            throw new \InvalidArgumentException('Неверный CVV код');
        }
    }

    /**
     * Валидация номера карты по алгоритму Луна
     */
    protected function isValidCardNumber(string $number): bool
    {
        if (!preg_match('/^\d{13,19}$/', $number)) {
            return false;
        }

        $sum = 0;
        $shouldDouble = false;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];

            if ($shouldDouble) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $shouldDouble = !$shouldDouble;
        }

        return $sum % 10 === 0;
    }

    /**
     * Валидация срока действия карты
     */
    protected function isValidExpiry(string $expiry): bool
    {
        if (!preg_match('/^(\d{2})\/(\d{2})$/', $expiry, $matches)) {
            return false;
        }

        $month = (int) $matches[1];
        $year = (int) $matches[2] + 2000;

        if ($month < 1 || $month > 12) {
            return false;
        }

        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');

        if ($year < $currentYear || ($year === $currentYear && $month < $currentMonth)) {
            return false;
        }

        return true;
    }

    /**
     * Сохранение карты в базе данных
     */
    protected function saveCard(User $user, array $twocanData, array $cardData): UserCard
    {
        // Парсинг срока действия
        [$expiryMonth, $expiryYear] = explode('/', $cardData['expiry']);
        $expiryYear = (int) $expiryYear + 2000;

        // Создание маски карты
        $number = preg_replace('/\s+/', '', $cardData['number']);
        $cardMask = substr($number, 0, 6) . str_repeat('*', strlen($number) - 10) . substr($number, -4);

        // Определение бренда карты
        $cardBrand = $this->detectCardBrand($number);

        $card = UserCard::create([
            'user_id' => $user->id,
            'twocan_card_token' => $twocanData['card_token'],
            'card_mask' => $cardMask,
            'card_brand' => $cardBrand,
            'card_holder_name' => $cardData['holder'] ?? null,
            'expiry_month' => (int) $expiryMonth,
            'expiry_year' => $expiryYear,
            'is_active' => true,
            'is_default' => $user->cards()->count() === 0, // Первая карта становится по умолчанию
        ]);

        return $card;
    }

    /**
     * Определение бренда карты
     */
    protected function detectCardBrand(string $number): string
    {
        $number = preg_replace('/\D/', '', $number);

        if (preg_match('/^4/', $number)) {
            return 'Visa';
        } elseif (preg_match('/^5[1-5]/', $number) || preg_match('/^2[2-7]/', $number)) {
            return 'MasterCard';
        } elseif (preg_match('/^3[47]/', $number)) {
            return 'American Express';
        } elseif (preg_match('/^6(?:011|5)/', $number)) {
            return 'Discover';
        } elseif (preg_match('/^35/', $number)) {
            return 'JCB';
        } elseif (preg_match('/^30[0-5]/', $number) || preg_match('/^36/', $number) || preg_match('/^38/', $number)) {
            return 'Diners Club';
        }

        return 'Unknown';
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
        $stringToSign .= 'secret_key=' . config('twocan.secret_key');

        // Возврат MD5 хэша
        return md5($stringToSign);
    }

    /**
     * Получение сообщения об ошибке
     */
    protected function getErrorMessage(?array $errorData): string
    {
        if (!$errorData) {
            return 'Неизвестная ошибка';
        }

        // Здесь можно добавить маппинг кодов ошибок 2can на понятные сообщения
        $errorCode = $errorData['error_code'] ?? null;

        switch ($errorCode) {
            case 'E.1001':
                return 'Неверные данные карты';
            case 'E.1002':
                return 'Карта заблокирована';
            case 'E.1003':
                return 'Недостаточно средств';
            case 'E.1004':
                return 'Срок действия карты истек';
            default:
                return $errorData['error_message'] ?? 'Ошибка при обработке карты';
        }
    }
}
