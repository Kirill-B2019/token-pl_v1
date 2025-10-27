# Интеграция с МТС эквайрингом

## Обзор

Система интегрирована с МТС Банком для обработки платежей клиентов при покупке токенов. Интеграция включает в себя:

- Обработку платежей через МТС API
- Обработку возвратов
- Webhook уведомления
- Проверку статуса транзакций

## Настройка

### 1. Конфигурация в .env файле

Добавьте следующие переменные в ваш `.env` файл:

```env
# MTS Bank Configuration
MTS_API_URL=https://api.mtsbank.ru/api/v1
MTS_MERCHANT_ID=your_mts_merchant_id
MTS_API_KEY=your_mts_api_key
MTS_API_SECRET=your_mts_api_secret
MTS_WEBHOOK_SECRET=your_mts_webhook_secret

# MTS Bank Settings
MTS_TIMEOUT=30
MTS_RETRY_ATTEMPTS=3
MTS_CURRENCY=RUB
MTS_MIN_AMOUNT=1
MTS_MAX_AMOUNT=1000000

# MTS Bank URLs
MTS_SUCCESS_URL=https://cardfly.online/client/payment/success
MTS_FAIL_URL=https://cardfly.online/client/payment/fail
MTS_WEBHOOK_URL=https://cardfly.online/api/mts/webhook
MTS_NOTIFICATION_URL=https://cardfly.online/api/mts/notification

# MTS Bank Commission Settings
MTS_COMMISSION_RATE=0.022
MTS_MIN_COMMISSION=10
MTS_MAX_COMMISSION=1000

# MTS Bank Security Settings
MTS_VERIFY_SIGNATURE=true
MTS_ALLOWED_IPS=127.0.0.1,::1
MTS_LOG_REQUESTS=true
```

### 2. Настройка базы данных

МТС Банк уже добавлен в сидер `BankSeeder.php`. Для активации выполните:

```bash
php artisan db:seed --class=BankSeeder
```

### 3. Настройка webhook URL в МТС

В личном кабинете МТС Банка укажите следующие URL:

- **Webhook URL**: `https://cardfly.online/api/mts/webhook`
- **Success URL**: `https://cardfly.online/client/payment/success`
- **Fail URL**: `https://cardfly.online/client/payment/fail`

## API Endpoints

### Обработка платежа

**POST** `/api/mts/payment`

```json
{
    "merchant_id": "MERCHANT_MTS_001",
    "api_key": "your_api_key",
    "transaction_id": "TXN_1234567890",
    "amount": 1000.00,
    "currency": "RUB",
    "card_number": "4111111111111111",
    "expiry_date": "12/25",
    "cvv": "123",
    "cardholder_name": "John Doe",
    "description": "Payment for tokens"
}
```

**Ответ успешного платежа:**
```json
{
    "success": true,
    "transaction_id": "TXN_1234567890",
    "mts_transaction_id": "MTS_9876543210",
    "mts_order_id": "ORDER_1234567890",
    "amount": 1000.00,
    "currency": "RUB",
    "status": "approved",
    "timestamp": "2024-01-01T12:00:00.000000Z"
}
```

### Обработка возврата

**POST** `/api/mts/refund`

```json
{
    "merchant_id": "MERCHANT_MTS_001",
    "api_key": "your_api_key",
    "original_transaction_id": "TXN_1234567890",
    "refund_amount": 500.00,
    "reason": "Customer request"
}
```

### Проверка статуса транзакции

**GET** `/api/mts/status`

Параметры:
- `merchant_id` - ID мерчанта
- `api_key` - API ключ
- `transaction_id` - ID транзакции

### Webhook уведомления

**POST** `/api/mts/webhook`

МТС Банк отправляет уведомления о изменении статуса транзакций:

```json
{
    "orderId": "ORDER_1234567890",
    "status": "APPROVED",
    "transactionId": "MTS_9876543210",
    "amount": 1000.00,
    "signature": "calculated_signature"
}
```

## Использование в клиентском интерфейсе

### Выбор банка при покупке

В форме покупки токенов добавьте выбор банка:

```html
<div class="form-group">
    <label for="bank_code">Выберите банк для оплаты:</label>
    <select name="bank_code" id="bank_code" class="form-control" required>
        <option value="MTS">МТС Банк (комиссия 2.2%)</option>
        <option value="SBER">Сбербанк (комиссия 2.5%)</option>
        <option value="VTB">ВТБ (комиссия 3.0%)</option>
        <option value="ALFA">Альфа-Банк (комиссия 2.8%)</option>
    </select>
</div>
```

### Обработка результатов платежа

Система автоматически перенаправляет пользователя на соответствующие страницы:

- **Успешная оплата**: `/client/payment/success?transaction_id=TXN_1234567890`
- **Неуспешная оплата**: `/client/payment/fail?transaction_id=TXN_1234567890&error=Payment declined`

## Безопасность

### Проверка подписи webhook

Все webhook уведомления проверяются на подлинность с помощью подписи:

```php
private function verifyWebhookSignature(Request $request): bool
{
    $bank = Bank::where('code', 'MTS')->first();
    $signature = $request->signature;
    $data = $request->except('signature');
    
    ksort($data);
    $signatureString = http_build_query($data) . $bank->api_secret;
    $expectedSignature = hash('sha256', $signatureString);
    
    return hash_equals($expectedSignature, $signature);
}
```

### Шифрование API ключей

API ключи и секреты хранятся в зашифрованном виде в базе данных:

```php
// В модели Bank
public function getApiKeyAttribute($value)
{
    return $value ? Crypt::decryptString($value) : null;
}

public function setApiKeyAttribute($value)
{
    $this->attributes['api_key'] = $value ? Crypt::encryptString($value) : null;
}
```

## Логирование и аудит

Все операции с МТС Банком логируются:

- Запросы к API
- Ответы от API
- Webhook уведомления
- Ошибки обработки

Логи сохраняются в таблице `audit_logs` и файлах логов Laravel.

## Тестирование

### Тестовые данные

Для тестирования используйте тестовые карты МТС:

- **Успешная оплата**: `4111111111111111`
- **Отклоненная оплата**: `4000000000000002`
- **Недостаточно средств**: `4000000000009995`

### Тестовые суммы

- Минимальная сумма: 1 RUB
- Максимальная сумма: 1,000,000 RUB

## Мониторинг

### Проверка статуса интеграции

```bash
# Проверка подключения к МТС API
php artisan tinker
>>> Http::get('https://api.mtsbank.ru/api/v1/health')
```

### Логи ошибок

```bash
# Просмотр логов МТС
tail -f storage/logs/laravel.log | grep MTS
```

## Поддержка

При возникновении проблем:

1. Проверьте логи системы
2. Убедитесь в правильности настроек в .env
3. Проверьте доступность МТС API
4. Обратитесь в поддержку МТС Банка

## Обновления

Для обновления интеграции:

1. Обновите код из репозитория
2. Выполните миграции: `php artisan migrate`
3. Очистите кэш: `php artisan config:cache`
4. Перезапустите веб-сервер
