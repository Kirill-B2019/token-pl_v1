# 📚 API Документация

Токен Платформа предоставляет REST API для интеграции с внешними системами.

## Базовая информация

### Базовый URL
```
https://cardfly.online/api/v1
```

### Аутентификация
API использует Bearer токены для аутентификации.

```bash
# Получение токена
curl -X POST https://cardfly.online/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Использование токена
curl -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  https://cardfly.online/api/v1/user
```

### Формат ответа
```json
{
  "success": true,
  "data": {},
  "message": "Optional message",
  "errors": []
}
```

### Коды ответов
- `200` - Успешный запрос
- `201` - Ресурс создан
- `400` - Ошибка валидации
- `401` - Не авторизован
- `403` - Доступ запрещен
- `404` - Ресурс не найден
- `422` - Ошибка валидации
- `500` - Внутренняя ошибка сервера

## Аутентификация

### POST /api/v1/login
Аутентификация пользователя.

**Запрос:**
```json
{
  "email": "user@example.com",
  "password": "password",
  "remember": false
}
```

**Ответ:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "client"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
    "token_type": "Bearer"
  }
}
```

### POST /api/v1/register
Регистрация нового пользователя.

**Запрос:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+79001234567"
}
```

### POST /api/v1/logout
Выход из системы.

**Заголовки:**
```
Authorization: Bearer YOUR_TOKEN
```

### GET /api/v1/user
Получение информации о текущем пользователе.

**Ответ:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "phone": "+79001234567",
    "role": "client",
    "balance_rub": 1500.50,
    "unique_id": "ABC123DEF",
    "is_active": true,
    "last_login_at": "2024-01-15T10:30:00Z"
  }
}
```

## Балансы

### GET /api/v1/balances
Получение всех балансов пользователя.

**Ответ:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "token": {
        "id": 1,
        "symbol": "BTC",
        "name": "Bitcoin"
      },
      "balance": "0.50000000",
      "locked_balance": "0.10000000",
      "available_balance": 0.4,
      "total_purchased": "0.60000000",
      "total_sold": "0.10000000"
    }
  ]
}
```

### GET /api/v1/balances/{token_id}
Получение баланса по конкретному токену.

## Транзакции

### GET /api/v1/transactions
Получение списка транзакций пользователя.

**Параметры запроса:**
- `page` - номер страницы (по умолчанию: 1)
- `per_page` - количество элементов на странице (по умолчанию: 15)
- `type` - фильтр по типу (`buy`, `sell`, `transfer`, `refund`, `deposit`)
- `status` - фильтр по статусу (`pending`, `processing`, `completed`, `failed`, `cancelled`)
- `date_from` - дата начала (Y-m-d)
- `date_to` - дата окончания (Y-m-d)

**Ответ:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "transaction_id": "txn_1_1642156800",
        "type": "deposit",
        "deposit_type": "rub",
        "status": "completed",
        "amount": "1000.00",
        "price": "1.00",
        "total_amount": "1000.00",
        "fee": "0.00",
        "payment_method": "card",
        "payment_reference": "payment_1_1642156800_abc123",
        "created_at": "2024-01-15T10:30:00Z",
        "processed_at": "2024-01-15T10:31:00Z"
      }
    ],
    "per_page": 15,
    "total": 1
  }
}
```

### GET /api/v1/transactions/{id}
Получение детальной информации о транзакции.

## Платежи

### POST /api/v1/payments/topup
Создание платежа для пополнения баланса.

**Запрос:**
```json
{
  "amount": 1000.00,
  "card_token": "card_token_123", // опционально, токен привязанной карты
  "description": "Пополнение баланса"
}
```

**Ответ:**
```json
{
  "success": true,
  "data": {
    "payment_id": "payment_1_1642156800_abc123",
    "payment_url": "https://2can.ru/payment/abc123",
    "amount": 1000.00,
    "currency": "RUB"
  }
}
```

### GET /api/v1/payments/status/{payment_id}
Получение статуса платежа.

**Ответ:**
```json
{
  "success": true,
  "data": {
    "payment_id": "payment_1_1642156800_abc123",
    "status": "completed",
    "amount": 1000.00,
    "processed_at": "2024-01-15T10:31:00Z"
  }
}
```

## Управление картами

### GET /api/v1/cards
Получение списка привязанных карт пользователя.

**Ответ:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "card_mask": "411111******1111",
      "card_brand": "Visa",
      "card_holder_name": "JOHN DOE",
      "expiry_month": 12,
      "expiry_year": 2025,
      "formatted_expiry": "12/2025",
      "is_default": true,
      "is_active": true,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

### POST /api/v1/cards
Привязка новой карты.

**Запрос:**
```json
{
  "number": "4111111111111111",
  "expiry": "12/25",
  "cvv": "123",
  "holder": "JOHN DOE"
}
```

**Ответ:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "card_mask": "411111******1111",
    "card_brand": "Visa",
    "is_default": false,
    "message": "Карта успешно привязана"
  }
}
```

### PATCH /api/v1/cards/{id}/default
Установка карты по умолчанию.

**Ответ:**
```json
{
  "success": true,
  "message": "Карта установлена по умолчанию"
}
```

### DELETE /api/v1/cards/{id}
Удаление привязанной карты.

**Ответ:**
```json
{
  "success": true,
  "message": "Карта успешно удалена"
}
```

## Токены

### GET /api/v1/tokens
Получение списка доступных токенов.

**Ответ:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "symbol": "BTC",
      "name": "Bitcoin",
      "current_price": "45000.50000000",
      "total_supply": "21000000.00000000",
      "available_supply": "19000000.00000000",
      "is_active": true
    }
  ]
}
```

### GET /api/v1/tokens/{id}
Получение информации о конкретном токене.

## Статистика

### GET /api/v1/stats/balances
Получение общей статистики балансов пользователя.

**Ответ:**
```json
{
  "success": true,
  "data": {
    "total_balance_rub": 1500.50,
    "total_tokens": 5,
    "total_locked": 250.00,
    "total_available": 1250.50
  }
}
```

### GET /api/v1/stats/transactions
Статистика транзакций по периодам.

**Параметры:**
- `period` - период (`day`, `week`, `month`, `year`)
- `date_from` - дата начала
- `date_to` - дата окончания

**Ответ:**
```json
{
  "success": true,
  "data": {
    "total_transactions": 25,
    "successful_transactions": 23,
    "failed_transactions": 2,
    "total_amount": 15000.00,
    "by_type": {
      "deposit": 10,
      "buy": 8,
      "sell": 5,
      "transfer": 2
    },
    "by_status": {
      "completed": 23,
      "failed": 2
    }
  }
}
```

## Webhook'и

### POST /api/v1/webhooks/2can
Webhook endpoint для обработки платежей от 2can.

**Заголовки:**
```
X-2can-Signature: signature_hash
Content-Type: application/json
```

**Тело запроса:**
```json
{
  "payment_id": "payment_1_1642156800_abc123",
  "status": "success",
  "amount": 1000.00,
  "currency": "RUB",
  "signature": "hash_signature"
}
```

**Ответ:**
```json
{
  "status": "success"
}
```

## Ошибки

### Формат ошибок

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Распространенные ошибки

#### 400 Bad Request
```json
{
  "success": false,
  "message": "Invalid request data",
  "errors": {
    "amount": ["The amount must be between 10 and 50000."]
  }
}
```

#### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

#### 403 Forbidden
```json
{
  "success": false,
  "message": "Access denied"
}
```

#### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found"
}
```

#### 422 Unprocessable Entity
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "card_token": ["The selected card token is invalid."]
  }
}
```

## SDK и примеры

### PHP SDK

```php
<?php

class TokenPlatformAPI {
    private $baseUrl;
    private $token;

    public function __construct($baseUrl, $token) {
        $this->baseUrl = $baseUrl;
        $this->token = $token;
    }

    public function getUser() {
        return $this->request('GET', '/user');
    }

    public function createPayment($amount, $cardToken = null) {
        return $this->request('POST', '/payments/topup', [
            'amount' => $amount,
            'card_token' => $cardToken
        ]);
    }

    private function request($method, $endpoint, $data = null) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/api/v1' . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ]);

        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

// Использование
$api = new TokenPlatformAPI('https://cardfly.online', 'your_token');
$user = $api->getUser();
$payment = $api->createPayment(1000.00);
```

### JavaScript SDK

```javascript
class TokenPlatformAPI {
    constructor(baseUrl, token) {
        this.baseUrl = baseUrl;
        this.token = token;
    }

    async request(method, endpoint, data = null) {
        const config = {
            method,
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Content-Type': 'application/json'
            }
        };

        if (data) {
            config.body = JSON.stringify(data);
        }

        const response = await fetch(`${this.baseUrl}/api/v1${endpoint}`, config);
        return await response.json();
    }

    async getUser() {
        return await this.request('GET', '/user');
    }

    async createPayment(amount, cardToken = null) {
        return await this.request('POST', '/payments/topup', {
            amount,
            card_token: cardToken
        });
    }

    async getBalances() {
        return await this.request('GET', '/balances');
    }

    async getTransactions(params = {}) {
        const query = new URLSearchParams(params);
        return await this.request('GET', `/transactions?${query}`);
    }
}

// Использование
const api = new TokenPlatformAPI('https://cardfly.online', 'your_token');
const user = await api.getUser();
const balances = await api.getBalances();
const transactions = await api.getTransactions({ page: 1, per_page: 10 });
```

## Ограничения

### Rate Limiting
- **Аутентифицированные запросы**: 60 в минуту
- **Неаутентифицированные запросы**: 30 в минуту
- **Платежные операции**: 10 в минуту

### Размер данных
- Максимальный размер запроса: 1 MB
- Максимальное количество элементов в списке: 100

### Таймауты
- Стандартные запросы: 30 секунд
- Платежные операции: 60 секунд

## Версионирование

API использует версионирование через URL:
- `v1` - текущая версия (активна)

