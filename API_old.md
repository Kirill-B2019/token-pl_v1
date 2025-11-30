# API Документация Токен-Платформы

## Общая информация

Базовый URL: `https://cardfly.online/api`

Все API запросы должны содержать соответствующие заголовки аутентификации.

## Аутентификация

### Брокер API
Используйте API ключ брокера в параметре `api_key`.

### Банк API
Используйте `merchant_id` и `api_key` в параметрах запроса.

### Пользовательские эндпоинты (Sanctum)
Передавайте пользовательский токен в заголовке `Authorization: Bearer <token>`.

## API v1 (публичные и пользовательские)

Базовый префикс: `/v1`

### Публичные эндпоинты

1) Получить список токенов

GET `/v1/tokens`

Ответ:
```json
{
  "tokens": [
    {"symbol": "BTC", "name": "Bitcoin", "current_price": "50000.00", "total_supply": "21000000.00000000", "available_supply": "1000000.00000000"}
  ],
  "timestamp": "2025-01-01T12:00:00.000000Z"
}
```

2) Получить токен по символу

GET `/v1/tokens/{symbol}`

Ответ:
```json
{
  "token": {"symbol": "BTC", "name": "Bitcoin", "current_price": "50000.00", "total_supply": "21000000.00000000", "available_supply": "1000000.00000000", "metadata": {}}
}
```

3) Пакеты токенов

GET `/v1/token-packages`

Ответ:
```json
{
  "packages": [
    {"id": 1, "name": "Starter", "description": "...", "token_amount": "100.00000000", "price": "100.00", "discount_percentage": "0.00", "final_price": 100.0, "savings_amount": 0.0}
  ],
  "count": 1
}
```

4) Банки (публичная справка)

GET `/v1/banks`

Ответ:
```json
{ "banks": [{"id":1,"name":"MTS Bank","code":"MTS"}] }
```

5) Брокеры (публичная справка)

GET `/v1/brokers`

Ответ:
```json
{ "brokers": [{"id":1,"name":"Default Broker"}] }
```

### Пользовательские эндпоинты (требуют Bearer токен)

1) Текущий пользователь

GET `/v1/me`

Ответ:
```json
{ "id": 123, "name": "User", "email": "user@example.com", "email_verified_at": null, "created_at": "2025-01-01T12:00:00.000000Z" }
```

2) Балансы пользователя

GET `/v1/me/balances`

Ответ:
```json
{
  "balances": [
    {"token_symbol":"BTC","token_name":"Bitcoin","balance":"1.00000000","locked_balance":"0.00000000","available_balance":1.0}
  ]
}
```

3) Транзакции пользователя

GET `/v1/me/transactions?limit=50&status=completed&type=buy`

Ответ:
```json
{
  "transactions": [
    {"transaction_id":"TXN_ABC","status":"completed","type":"buy","token_symbol":"BTC","amount":"0.10000000","price":"50000.00","total_amount":"5000.00","created_at":"2025-01-01T12:00:00.000000Z","processed_at":"2025-01-01T12:00:01.000000Z"}
  ],
  "count": 1
}
```

### Wallet (TRON) эндпоинты под префиксом v1

Эндпоинты дублируют существующие `/tron/*`, но доступны также под `/v1/wallet/*`.

- POST `/v1/wallet/create` — создать кошелек
- GET `/v1/wallet` — получить кошелек
- POST `/v1/wallet/sync` — синхронизировать баланс
- POST `/v1/wallet/send-trx` — отправить TRX
- POST `/v1/wallet/send-usdt` — отправить USDT
- GET `/v1/wallet/transactions?limit=50` — история транзакций
- GET `/v1/wallet/qr-code` — данные для QR
- POST `/v1/wallet/validate-address` — валидация адреса

#### Примеры (cURL)

```bash
# Публичный список токенов
curl -X GET "https://cardfly.online/api/v1/tokens"

# Профиль текущего пользователя
curl -X GET "https://cardfly.online/api/v1/me" \
  -H "Authorization: Bearer <SANCTUM_TOKEN>"

# Отправка TRX
curl -X POST "https://cardfly.online/api/v1/wallet/send-trx" \
  -H "Authorization: Bearer <SANCTUM_TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "to_address": "TXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
    "amount": 1.25
  }'
```

## Брокер API

### Получить все токены

**GET** `/broker/tokens`

Получает список всех активных токенов.

**Параметры:**
- `api_key` (string, required) - API ключ брокера

**Ответ:**
```json
{
    "tokens": [
        {
            "symbol": "BTC",
            "name": "Bitcoin",
            "current_price": "50000.00",
            "available_supply": "1000000.00000000"
        }
    ],
    "timestamp": "2024-01-01T12:00:00.000000Z"
}
```

### Получить баланс токена

**GET** `/broker/token-balance`

Получает информацию о балансе конкретного токена.

**Параметры:**
- `api_key` (string, required) - API ключ брокера
- `token_symbol` (string, required) - Символ токена

**Ответ:**
```json
{
    "token_symbol": "BTC",
    "available_supply": "1000000.00000000",
    "current_price": "50000.00",
    "timestamp": "2024-01-01T12:00:00.000000Z"
}
```

### Перевести токены

**POST** `/broker/transfer`

Переводит токены пользователю.

**Параметры:**
- `api_key` (string, required) - API ключ брокера
- `user_id` (integer, required) - ID пользователя
- `token_symbol` (string, required) - Символ токена
- `amount` (float, required) - Количество токенов
- `transaction_id` (string, required) - Уникальный ID транзакции

**Ответ:**
```json
{
    "success": true,
    "transaction_id": "TXN_ABC123DEF456",
    "amount": "0.01000000",
    "token_symbol": "BTC",
    "timestamp": "2024-01-01T12:00:00.000000Z"
}
```

### Получить статус транзакции

**GET** `/broker/transaction-status`

Получает статус транзакции.

**Параметры:**
- `api_key` (string, required) - API ключ брокера
- `transaction_id` (string, required) - ID транзакции

**Ответ:**
```json
{
    "transaction_id": "TXN_ABC123DEF456",
    "status": "completed",
    "type": "transfer",
    "amount": "0.01000000",
    "created_at": "2024-01-01T12:00:00.000000Z",
    "processed_at": "2024-01-01T12:00:01.000000Z"
}
```

### Обновить цену токена

**POST** `/broker/update-price`

Обновляет цену токена.

**Параметры:**
- `api_key` (string, required) - API ключ брокера
- `token_symbol` (string, required) - Символ токена
- `price` (float, required) - Новая цена

**Ответ:**
```json
{
    "success": true,
    "token_symbol": "BTC",
    "old_price": "50000.00",
    "new_price": "51000.00",
    "timestamp": "2024-01-01T12:00:00.000000Z"
}
```

## Банк API

### Обработать платеж

**POST** `/bank/payment`

Обрабатывает платеж по карте.

**Параметры:**
- `merchant_id` (string, required) - ID мерчанта
- `api_key` (string, required) - API ключ
- `transaction_id` (string, required) - ID транзакции
- `amount` (float, required) - Сумма платежа
- `currency` (string, required) - Валюта (3 символа)
- `card_number` (string, required) - Номер карты
- `expiry_date` (string, required) - Срок действия (MM/YY)
- `cvv` (string, required) - CVV код
- `cardholder_name` (string, required) - Имя держателя карты

**Ответ успешного платежа:**
```json
{
    "success": true,
    "transaction_id": "CARD_1234567890",
    "bank_transaction_id": "BANK_9876543210",
    "amount": "100.00",
    "currency": "USD",
    "status": "approved",
    "timestamp": "2024-01-01T12:00:00.000000Z"
}
```

**Ответ отклоненного платежа:**
```json
{
    "success": false,
    "transaction_id": "CARD_1234567890",
    "error": "Payment declined by bank",
    "status": "declined",
    "timestamp": "2024-01-01T12:00:00.000000Z"
}
```

## МТС Банк API

### Обработать платеж (МТС)

**POST** `/mts/payment`

Проксирует платеж через МТС Банк.

**Параметры:**
- `merchant_id` (string, required)
- `api_key` (string, required)
- `transaction_id` (string, required)
- `amount` (float, required)
- `currency` (string, required)
- `card_number` (string, required)
- `expiry_date` (string, required)
- `cvv` (string, required)
- `cardholder_name` (string, required)
- `description` (string, optional)

**Успех/Ошибка:** как в разделе «Банк API», дополнительные поля: `mts_transaction_id`, `mts_order_id`.

### Возврат (МТС)

**POST** `/mts/refund`

**Параметры:**
- `merchant_id` (string, required)
- `api_key` (string, required)
- `original_transaction_id` (string, required)
- `refund_amount` (float, required)
- `reason` (string, required)

**Ответ:** как «Обработать возврат», дополнительно `mts_refund_id`.

### Статус транзакции (МТС)

**GET** `/mts/status`

**Параметры:**
- `merchant_id` (string, required)
- `api_key` (string, required)
- `transaction_id` (string, required)

**Ответ:** статус транзакции, при наличии `mts_order_id` делается запрос в МТС и статус синхронизируется.

### Webhook (МТС)

**POST** `/mts/webhook`

**Параметры (body):** `orderId`, `status`, `transactionId`, `amount`, `signature` (все required)

Проверяется подпись. При валидности обновляется статус транзакции.

### Обработать возврат

**POST** `/bank/refund`

Обрабатывает возврат средств.

**Параметры:**
- `merchant_id` (string, required) - ID мерчанта
- `api_key` (string, required) - API ключ
- `original_transaction_id` (string, required) - ID оригинальной транзакции
- `refund_amount` (float, required) - Сумма возврата
- `reason` (string, required) - Причина возврата

**Ответ:**
```json
{
    "success": true,
    "refund_transaction_id": "REF_1234567890",
    "original_transaction_id": "CARD_1234567890",
    "refund_amount": "50.00",
    "status": "approved",
    "timestamp": "2024-01-01T12:00:00.000000Z"
}
```

### Проверить транзакцию

**GET** `/bank/verify`

Проверяет статус транзакции.

**Параметры:**
- `merchant_id` (string, required) - ID мерчанта
- `api_key` (string, required) - API ключ
- `transaction_id` (string, required) - ID транзакции

**Ответ:**
```json
{
    "transaction_id": "CARD_1234567890",
    "status": "completed",
    "amount": "100.00",
    "currency": "USD",
    "created_at": "2024-01-01T12:00:00.000000Z",
    "processed_at": "2024-01-01T12:00:01.000000Z"
}
```

### Получить историю транзакций

**GET** `/bank/history`

Получает историю транзакций.

**Параметры:**
- `merchant_id` (string, required) - ID мерчанта
- `api_key` (string, required) - API ключ
- `from_date` (date, optional) - Дата начала (YYYY-MM-DD)
- `to_date` (date, optional) - Дата окончания (YYYY-MM-DD)
- `limit` (integer, optional) - Лимит записей (1-100, по умолчанию 50)

**Ответ:**
```json
{
    "transactions": [
        {
            "transaction_id": "TXN_ABC123DEF456",
            "payment_reference": "CARD_1234567890",
            "type": "buy",
            "status": "completed",
            "total_amount": "100.00",
            "created_at": "2024-01-01T12:00:00.000000Z",
            "processed_at": "2024-01-01T12:00:01.000000Z"
        }
    ],
    "count": 1,
    "timestamp": "2024-01-01T12:00:00.000000Z"
}
```

## Биржа API

### Получить цену токена

**GET** `/exchange/price`

Получает текущую цену токена с биржи.

**Параметры:**
- `api_key` (string, required) - API ключ брокера
- `token_symbol` (string, required) - Символ токена

**Ответ:**
```json
{
    "token_symbol": "BTC",
    "price": "50000.00",
    "timestamp": "2024-01-01T12:00:00.000000Z"
}
```

### Разместить ордер на покупку

**POST** `/exchange/buy`

Размещает ордер на покупку токенов.

**Параметры:**
- `api_key` (string, required) - API ключ брокера
- `token_symbol` (string, required) - Символ токена
- `amount` (float, required) - Количество токенов
- `price` (float, required) - Цена за единицу

**Ответ:**
```json
{
    "success": true,
    "order_id": "EXCH_1234567890",
    "token_symbol": "BTC",
    "amount": "0.01000000",
    "price": "50000.00",
    "total": "500.00",
    "timestamp": "2024-01-01T12:00:00.000000Z"
}
```

### Разместить ордер на продажу

**POST** `/exchange/sell`

Размещает ордер на продажу токенов.

**Параметры:**
- `api_key` (string, required) - API ключ брокера
- `token_symbol` (string, required) - Символ токена
- `amount` (float, required) - Количество токенов
- `price` (float, required) - Цена за единицу

**Ответ:**
```json
{
    "success": true,
    "order_id": "EXCH_1234567890",
    "token_symbol": "BTC",
    "amount": "0.01000000",
    "price": "50000.00",
    "total": "500.00",
    "timestamp": "2024-01-01T12:00:00.000000Z"
}
```

### Получить статус ордера

**GET** `/exchange/order-status`

Получает статус ордера.

**Параметры:**
- `api_key` (string, required) - API ключ брокера
- `order_id` (string, required) - ID ордера

**Ответ:**
```json
{
    "order_id": "EXCH_1234567890",
    "status": "filled",
    "filled_amount": "0.01000000",
    "remaining_amount": "0.00000000",
    "average_price": "50000.00",
    "timestamp": "2024-01-01T12:00:00.000000Z"
}
```

### Отменить ордер

**POST** `/exchange/cancel-order`

Отменяет ордер.

**Параметры:**
- `api_key` (string, required) - API ключ брокера
- `order_id` (string, required) - ID ордера

**Ответ:**
```json
{
    "success": true,
    "order_id": "EXCH_1234567890",
    "message": "Order cancelled successfully",
    "timestamp": "2024-01-01T12:00:00.000000Z"
}
```

## TRON Wallet API

Все эндпоинты требуют заголовок `Authorization: Bearer <token>`.

### Создать кошелёк

**POST** `/tron/wallet/create`

**Ответ:**
```json
{
  "success": true,
  "message": "Кошелек TRON успешно создан",
  "wallet": {
    "id": 1,
    "address": "T...",
    "balance_trx": 0,
    "balance_usdt": 0,
    "is_active": true,
    "created_at": "2025-01-01T12:00:00.000000Z"
  }
}
```

### Получить кошелёк

**GET** `/tron/wallet`

Возвращает адрес и балансы TRX/USDT, `total_balance_usd`, `last_sync_at`.

### Синхронизировать баланс

**POST** `/tron/wallet/sync`

Синхронизирует балансы с сетью и возвращает актуальные значения.

### Отправить TRX

**POST** `/tron/wallet/send-trx`

**Параметры:**
- `to_address` (string, required, 34 символа)
- `amount` (float, required, ≥ 0.000001)

**Ответ (успех):** `{ "success": true, "transaction_id": "TXN_...", "amount": 1.23, "to_address": "T..." }`

### Отправить USDT

**POST** `/tron/wallet/send-usdt`

Аналогично TRX, но для токена USDT (TRC20).

### История транзакций

**GET** `/tron/wallet/transactions?limit=50`

Возвращает массив транзакций кошелька из TronGrid (или пустой массив при ошибке).

### QR-код и валидация адреса

**GET** `/tron/wallet/qr-code` — данные для QR.

**POST** `/tron/validate-address` — проверка адреса, `{ "is_valid": true|false }`.

## Коды ошибок

### HTTP статус коды
- `200` - Успешный запрос
- `400` - Неверные параметры запроса
- `401` - Неверная аутентификация
- `403` - Доступ запрещен
- `404` - Ресурс не найден
- `409` - Конфликт (например, дублирующаяся транзакция)
- `500` - Внутренняя ошибка сервера

### Коды ошибок API
- `INVALID_API_KEY` - Неверный API ключ
- `INVALID_MERCHANT_CREDENTIALS` - Неверные учетные данные мерчанта
- `TOKEN_NOT_FOUND` - Токен не найден
- `INSUFFICIENT_TOKEN_SUPPLY` - Недостаточно токенов
- `INSUFFICIENT_BALANCE` - Недостаточно средств
- `TRANSACTION_NOT_FOUND` - Транзакция не найдена
- `TRANSACTION_ALREADY_EXISTS` - Транзакция уже существует
- `ORDER_NOT_FOUND` - Ордер не найден
- `PAYMENT_DECLINED` - Платеж отклонен
- `INVALID_CARD_DETAILS` - Неверные данные карты

## Примеры использования

### Пример запроса на перевод токенов (cURL)

```bash
curl -X POST "https://cardfly.online/api/broker/transfer" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your_broker_api_key",
    "user_id": 123,
    "token_symbol": "BTC",
    "amount": 0.01,
    "transaction_id": "TXN_1234567890"
  }'
```

### Пример запроса на обработку платежа (cURL)

```bash
curl -X POST "https://cardfly.online/api/bank/payment" \
  -H "Content-Type: application/json" \
  -d '{
    "merchant_id": "MERCHANT_001",
    "api_key": "your_bank_api_key",
    "transaction_id": "CARD_1234567890",
    "amount": 100.00,
    "currency": "USD",
    "card_number": "4111111111111111",
    "expiry_date": "12/25",
    "cvv": "123",
    "cardholder_name": "John Doe"
  }'
```

### Пример запроса на получение цены токена (cURL)

```bash
curl -X GET "https://cardfly.online/api/exchange/price?api_key=your_broker_api_key&token_symbol=BTC"
```

## Лимиты и ограничения

- Максимальное количество запросов: 1000 в минуту на API ключ
- Максимальный размер запроса: 10 MB
- Таймаут запроса: 30 секунд
- Максимальное количество токенов в одном переводе: 1000
- Минимальная сумма платежа: 0.01 USD
- Максимальная сумма платежа: 100,000 USD

## Безопасность

- Все API запросы должны использовать HTTPS
- API ключи должны храниться в безопасном месте
- Рекомендуется использовать IP whitelist для API ключей
- Все чувствительные данные должны быть зашифрованы
- Регулярно обновляйте API ключи

## Поддержка

Для получения поддержки по API обращайтесь к администратору системы или создавайте тикет в системе поддержки.
