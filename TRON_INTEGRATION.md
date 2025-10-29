# TRON Wallet Integration Documentation

## Обзор

Интеграция TRON кошельков в CardFly Token Platform позволяет пользователям создавать, управлять и использовать кошельки для работы с TRX и USDT токенами в сети TRON.

## Архитектура

### Компоненты системы

1. **TronWallet Model** - Модель для хранения данных кошельков
2. **TronWalletService** - Сервис для работы с TRON API
3. **TronWalletController** - Контроллер для API и веб-интерфейса
4. **Database Migration** - Миграция для создания таблицы кошельков
5. **Blade Templates** - Шаблоны для веб-интерфейса
6. **Configuration** - Конфигурация TRON интеграции

## Установка и настройка

### 1. Выполнение миграций

```bash
php artisan migrate
```

### 2. Запуск сидеров

```bash
php artisan db:seed --class=TronWalletSeeder
```

### 3. Настройка .env

```env
# TRON Configuration
TRON_NETWORK=mainnet
TRON_API_URL=https://api.trongrid.io
TRON_API_KEY=your_tron_api_key_here
TRON_API_TIMEOUT=30
TRON_RETRY_ATTEMPTS=3

# Wallet Settings
TRON_AUTO_CREATE_WALLET=true
TRON_SYNC_INTERVAL=300
TRON_MIN_TRX_BALANCE=1.0
TRON_MIN_USDT_BALANCE=0.0

# Token Contracts
TRON_USDT_CONTRACT=TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t

# Transaction Limits
TRON_MIN_AMOUNT_TRX=0.000001
TRON_MIN_AMOUNT_USDT=0.000001
TRON_MAX_AMOUNT_TRX=1000000
TRON_MAX_AMOUNT_USDT=1000000
TRON_FEE_TRX=0.1
TRON_FEE_USDT=0.0

# Security Settings
TRON_ENCRYPT_PRIVATE_KEYS=true
TRON_ENCRYPT_MNEMONIC=true
TRON_REQUIRE_CONFIRMATION=true
TRON_MAX_DAILY_TRANSACTIONS=10
TRON_MAX_DAILY_AMOUNT_TRX=1000
TRON_MAX_DAILY_AMOUNT_USDT=10000

# Notifications
TRON_WEBHOOK_URL=https://cardfly.online/api/tron/webhook
TRON_WEBHOOK_SECRET=your_webhook_secret
TRON_NOTIFY_ON_TRANSACTION=true
TRON_NOTIFY_ON_BALANCE_CHANGE=true

# Cache Settings
TRON_BALANCE_CACHE_TTL=60
TRON_PRICE_CACHE_TTL=300
TRON_TRANSACTION_CACHE_TTL=600

# Logging
TRON_LOG_TRANSACTIONS=true
TRON_LOG_BALANCE_CHANGES=true
TRON_LOG_API_CALLS=false

# Development
TRON_USE_TESTNET=false
TRON_MOCK_TRANSACTIONS=false
TRON_DEBUG_MODE=false
```

## API Endpoints

### Создание кошелька

```http
POST /api/tron/wallet/create
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Кошелек TRON успешно создан",
    "wallet": {
        "id": 1,
        "address": "T9yD14Nj9j7xAB4dbGeiX9h8unkKHxuWwb",
        "balance_trx": 0,
        "balance_usdt": 0,
        "is_active": true,
        "created_at": "2025-01-15T10:30:00Z"
    }
}
```

### Получение информации о кошельке

```http
GET /api/tron/wallet
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "wallet": {
        "id": 1,
        "address": "T9yD14Nj9j7xAB4dbGeiX9h8unkKHxuWwb",
        "balance_trx": 100.5,
        "balance_usdt": 50.25,
        "total_balance_usd": 150.75,
        "is_active": true,
        "last_sync_at": "2025-01-15T10:30:00Z",
        "created_at": "2025-01-15T10:30:00Z"
    }
}
```

### Синхронизация баланса

```http
POST /api/tron/wallet/sync
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Баланс успешно обновлен",
    "balance": {
        "trx": 100.5,
        "usdt": 50.25,
        "total_usd": 150.75
    }
}
```

### Отправка TRX

```http
POST /api/tron/wallet/send-trx
Authorization: Bearer {token}
Content-Type: application/json

{
    "to_address": "T9yD14Nj9j7xAB4dbGeiX9h8unkKHxuWwb",
    "amount": 10.5
}
```

**Response:**
```json
{
    "success": true,
    "transaction_id": "TXN_1642248600_abc123",
    "amount": 10.5,
    "to_address": "T9yD14Nj9j7xAB4dbGeiX9h8unkKHxuWwb"
}
```

### Отправка USDT

```http
POST /api/tron/wallet/send-usdt
Authorization: Bearer {token}
Content-Type: application/json

{
    "to_address": "T9yD14Nj9j7xAB4dbGeiX9h8unkKHxuWwb",
    "amount": 25.0
}
```

**Response:**
```json
{
    "success": true,
    "transaction_id": "USDT_1642248600_def456",
    "amount": 25.0,
    "to_address": "T9yD14Nj9j7xAB4dbGeiX9h8unkKHxuWwb"
}
```

### История транзакций

```http
GET /api/tron/wallet/transactions?limit=50
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "transactions": {
        "data": [
            {
                "txid": "abc123def456...",
                "timestamp": "2025-01-15T10:30:00Z",
                "type": "send",
                "amount": 10.5,
                "currency": "TRX",
                "to_address": "T9yD14Nj9j7xAB4dbGeiX9h8unkKHxuWwb",
                "status": "completed"
            }
        ],
        "meta": {
            "total": 1,
            "page": 1,
            "limit": 50
        }
    },
    "wallet_address": "T9yD14Nj9j7xAB4dbGeiX9h8unkKHxuWwb"
}
```

### Получение QR кода

```http
GET /api/tron/wallet/qr-code
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "qr_data": {
        "address": "T9yD14Nj9j7xAB4dbGeiX9h8unkKHxuWwb",
        "type": "TRON",
        "label": "CardFly Wallet",
        "amount": null
    }
}
```

### Валидация адреса

```http
POST /api/tron/validate-address
Authorization: Bearer {token}
Content-Type: application/json

{
    "address": "T9yD14Nj9j7xAB4dbGeiX9h8unkKHxuWwb"
}
```

**Response:**
```json
{
    "success": true,
    "is_valid": true,
    "address": "T9yD14Nj9j7xAB4dbGeiX9h8unkKHxuWwb"
}
```

## Веб-интерфейс

### Маршруты

- `GET /client/wallet` - Главная страница кошелька
- `POST /client/wallet/create` - Создание кошелька
- `GET /client/wallet/send` - Форма отправки транзакций
- `POST /client/wallet/send` - Обработка отправки транзакций
- `GET /client/wallet/history` - История транзакций

### Функции

1. **Создание кошелька** - Автоматическая генерация нового кошелька
2. **Просмотр баланса** - Отображение TRX и USDT балансов
3. **Отправка транзакций** - Форма для отправки TRX/USDT
4. **История транзакций** - Просмотр всех транзакций
5. **QR код** - Генерация QR кода для адреса
6. **Валидация адресов** - Проверка корректности TRON адресов

## Безопасность

### Шифрование

- Приватные ключи шифруются с помощью Laravel Crypt
- Мнемонические фразы также шифруются
- Используется AES-256-CBC шифрование

### Валидация

- Проверка формата TRON адресов (T + 33 символа)
- Валидация сумм транзакций
- Проверка баланса перед отправкой

### Ограничения

- Максимальное количество транзакций в день
- Максимальные суммы транзакций
- Требование подтверждения для больших сумм

## Мониторинг и логирование

### Логи

- Все транзакции логируются в audit_logs
- Ошибки API запросов записываются в Laravel logs
- Изменения балансов отслеживаются

### Метрики

- Количество созданных кошельков
- Общий объем транзакций
- Статистика по валютам

## Тестирование

### Unit тесты

```bash
php artisan test --filter=TronWalletTest
```

### Интеграционные тесты

```bash
php artisan test --filter=TronWalletIntegrationTest
```

## Развертывание

### Продакшен

1. Настройте TRON API ключи
2. Включите шифрование приватных ключей
3. Настройте webhook для уведомлений
4. Включите логирование транзакций

### Мониторинг

1. Настройте алерты на ошибки API
2. Мониторьте балансы кошельков
3. Отслеживайте производительность синхронизации

## Поддержка

При возникновении проблем:

1. Проверьте логи системы
2. Убедитесь в правильности настроек в .env
3. Проверьте доступность TRON API
4. Обратитесь в поддержку TRON Foundation

## Обновления

Для обновления интеграции:

1. Обновите код из репозитория
2. Выполните миграции: `php artisan migrate`
3. Очистите кэш: `php artisan config:cache`
4. Перезапустите веб-сервер



