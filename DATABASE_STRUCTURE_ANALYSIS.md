# Анализ структуры базы данных Токен-Платформы

## Обзор

База данных проекта построена на Laravel Migrations и содержит **12 таблиц**, которые обеспечивают полную функциональность системы управления цифровыми токенами.

## Структура таблиц

### 🔐 **Системные таблицы Laravel**

#### 1. `users` - Пользователи системы
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(255) NULL,
    role ENUM('client', 'broker', 'admin') DEFAULT 'client',
    unique_id VARCHAR(10) UNIQUE NOT NULL,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret TEXT NULL,
    two_factor_recovery_codes JSON NULL,
    two_factor_confirmed_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Особенности:**
- **Ролевая система**: client, broker, admin
- **Уникальный ID**: 10-символьный идентификатор
- **Двухфакторная аутентификация**: поддержка 2FA
- **Активность**: контроль активности аккаунтов

#### 2. `password_reset_tokens` - Токены сброса пароля
```sql
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);
```

#### 3. `sessions` - Сессии пользователей
```sql
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL INDEX,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT INDEX
);
```

#### 4. `cache` - Кэш системы
```sql
CREATE TABLE cache (
    key VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL
);
```

#### 5. `jobs` - Очередь задач
```sql
CREATE TABLE jobs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT NOT NULL,
    reserved_at INT NULL,
    available_at INT NOT NULL,
    created_at INT NOT NULL
);
```

### 💰 **Основные бизнес-таблицы**

#### 6. `tokens` - Цифровые токены
```sql
CREATE TABLE tokens (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    symbol VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    current_price DECIMAL(20,8) NOT NULL,
    total_supply DECIMAL(20,8) NOT NULL,
    available_supply DECIMAL(20,8) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Особенности:**
- **Высокая точность**: DECIMAL(20,8) для криптовалют
- **Метаданные**: JSON поле для дополнительной информации
- **Управление предложением**: total_supply vs available_supply

#### 7. `transactions` - Транзакции
```sql
CREATE TABLE transactions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    transaction_id VARCHAR(255) UNIQUE NOT NULL,
    user_id BIGINT NOT NULL,
    token_id BIGINT NOT NULL,
    type ENUM('buy', 'sell', 'transfer', 'refund') NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') NOT NULL,
    amount DECIMAL(20,8) NOT NULL,
    price DECIMAL(20,8) NOT NULL,
    total_amount DECIMAL(20,8) NOT NULL,
    fee DECIMAL(20,8) DEFAULT 0,
    payment_method VARCHAR(255) NULL,
    payment_reference VARCHAR(255) NULL,
    metadata JSON NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (token_id) REFERENCES tokens(id) ON DELETE CASCADE
);
```

**Особенности:**
- **Уникальный ID транзакции**: для внешних систем
- **Типы операций**: покупка, продажа, перевод, возврат
- **Статусы**: полный жизненный цикл транзакции
- **Метаданные**: JSON для дополнительной информации

#### 8. `user_balances` - Балансы пользователей
```sql
CREATE TABLE user_balances (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    token_id BIGINT NOT NULL,
    balance DECIMAL(20,8) DEFAULT 0,
    locked_balance DECIMAL(20,8) DEFAULT 0,
    total_purchased DECIMAL(20,8) DEFAULT 0,
    total_sold DECIMAL(20,8) DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY unique_user_token (user_id, token_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (token_id) REFERENCES tokens(id) ON DELETE CASCADE
);
```

**Особенности:**
- **Уникальная связка**: user_id + token_id
- **Заблокированный баланс**: для pending транзакций
- **Статистика**: общие суммы покупок/продаж

### 🏦 **Интеграционные таблицы**

#### 9. `brokers` - Брокеры
```sql
CREATE TABLE brokers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    api_key VARCHAR(255) UNIQUE NOT NULL,
    api_secret VARCHAR(255) NOT NULL,
    exchange_url VARCHAR(255) NOT NULL,
    reserve_balance DECIMAL(20,8) DEFAULT 0,
    min_reserve_threshold DECIMAL(20,8) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    settings JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Особенности:**
- **API интеграция**: ключи для внешних систем
- **Резервный баланс**: управление ликвидностью
- **Настройки**: JSON для конфигурации

#### 10. `banks` - Банки-эквайеры
```sql
CREATE TABLE banks (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255) UNIQUE NOT NULL,
    api_endpoint VARCHAR(255) NOT NULL,
    merchant_id VARCHAR(255) NOT NULL,
    api_key VARCHAR(255) NOT NULL,
    api_secret VARCHAR(255) NOT NULL,
    commission_rate DECIMAL(5,4) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    settings JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Особенности:**
- **Уникальный код**: для идентификации банка
- **Комиссия**: процент от суммы транзакции
- **Мерчант ID**: для API интеграции

### 📦 **Дополнительные таблицы**

#### 11. `token_packages` - Пакеты токенов
```sql
CREATE TABLE token_packages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    token_amount DECIMAL(20,8) NOT NULL,
    price DECIMAL(20,8) NOT NULL,
    discount_percentage DECIMAL(5,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Особенности:**
- **Скидки**: процентная скидка на пакеты
- **Сортировка**: порядок отображения
- **Гибкая структура**: для различных предложений

#### 12. `winner_losers` - Победители/Проигравшие
```sql
CREATE TABLE winner_losers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    type ENUM('winner', 'loser') NOT NULL,
    amount DECIMAL(20,8) NOT NULL,
    token_amount DECIMAL(20,8) NOT NULL,
    token_id BIGINT NOT NULL,
    status ENUM('pending', 'processed', 'paid') NOT NULL,
    processed_at TIMESTAMP NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (token_id) REFERENCES tokens(id) ON DELETE CASCADE
);
```

**Особенности:**
- **Тип результата**: победитель или проигравший
- **Статус выплаты**: контроль процесса выплат
- **Метаданные**: дополнительная информация

#### 13. `audit_logs` - Логи аудита
```sql
CREATE TABLE audit_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event VARCHAR(255) NOT NULL,
    entity_type VARCHAR(255) NOT NULL,
    entity_id BIGINT NOT NULL,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_user_time (user_id, created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

**Особенности:**
- **Полный аудит**: все изменения в системе
- **Индексы**: для быстрого поиска
- **Метаданные**: дополнительная информация

## Связи между таблицами

### 🔗 **Основные связи**

1. **users** → **transactions** (1:м)
   - Один пользователь может иметь множество транзакций
   - CASCADE DELETE при удалении пользователя

2. **tokens** → **transactions** (1:м)
   - Один токен может участвовать в множестве транзакций
   - CASCADE DELETE при удалении токена

3. **users** → **user_balances** (1:м)
   - Один пользователь может иметь балансы по разным токенам
   - Уникальная связка user_id + token_id

4. **tokens** → **user_balances** (1:м)
   - Один токен может иметь балансы у разных пользователей

5. **users** → **winner_losers** (1:м)
   - Один пользователь может иметь множество записей побед/поражений

6. **tokens** → **winner_losers** (1:м)
   - Один токен может участвовать в множестве игр

7. **users** → **audit_logs** (1:м)
   - Один пользователь может иметь множество записей аудита
   - SET NULL при удалении пользователя

## Индексы и оптимизация

### 📊 **Индексы для производительности**

1. **Уникальные индексы:**
   - `users.email` - быстрый поиск по email
   - `users.unique_id` - поиск по уникальному ID
   - `transactions.transaction_id` - поиск транзакций
   - `brokers.api_key` - аутентификация брокеров
   - `banks.code` - идентификация банков

2. **Составные индексы:**
   - `user_balances(user_id, token_id)` - уникальная связка
   - `audit_logs(entity_type, entity_id)` - поиск по сущности
   - `audit_logs(user_id, created_at)` - поиск по пользователю и времени

3. **Индексы для сессий:**
   - `sessions.user_id` - поиск сессий пользователя
   - `sessions.last_activity` - очистка старых сессий

## Сидеры и тестовые данные

### 🌱 **Сидеры для инициализации**

1. **DatabaseSeeder** - основной сидер
   - Создает пользователей: admin, broker, client
   - Запускает все остальные сидеры

2. **TokenSeeder** - токены
   - Bitcoin (BTC) - 50,000 USD
   - Ethereum (ETH) - 3,000 USD  
   - Tether (USDT) - 1.00 USD

3. **TokenPackageSeeder** - пакеты токенов
   - Стартовый пакет - 0.01 BTC за 500 RUB
   - Базовый пакет - 0.05 BTC за 2,400 RUB (5% скидка)
   - Премиум пакет - 0.1 BTC за 4,500 RUB (10% скидка)
   - VIP пакет - 0.5 BTC за 20,000 RUB (20% скидка)

4. **BankSeeder** - банки-эквайеры
   - Сбербанк (2.5% комиссия)
   - ВТБ (3.0% комиссия)
   - Альфа-Банк (2.8% комиссия)
   - МТС Банк (2.2% комиссия)

## Особенности архитектуры

### 🔒 **Безопасность**

1. **Шифрование чувствительных данных:**
   - API ключи и секреты в таблицах `brokers` и `banks`
   - Пароли пользователей (bcrypt)

2. **Аудит всех операций:**
   - Таблица `audit_logs` для отслеживания изменений
   - IP адреса и User Agent для безопасности

3. **Контроль доступа:**
   - Ролевая система в таблице `users`
   - Активность аккаунтов

### 📈 **Масштабируемость**

1. **Высокая точность для финансовых операций:**
   - DECIMAL(20,8) для токенов и сумм
   - Поддержка микротранзакций

2. **Гибкая структура:**
   - JSON поля для метаданных
   - Настраиваемые параметры

3. **Оптимизация запросов:**
   - Индексы для часто используемых полей
   - Составные индексы для сложных запросов

### 🔄 **Жизненный цикл данных**

1. **Транзакции:**
   - pending → processing → completed/failed/cancelled
   - Отслеживание времени обработки

2. **Балансы:**
   - Автоматическое обновление при транзакциях
   - Блокировка средств для pending операций

3. **Аудит:**
   - Immutable логи всех изменений
   - Полная история операций

## Заключение

База данных спроектирована с учетом:

✅ **Высокой производительности** - оптимизированные индексы  
✅ **Безопасности** - шифрование и аудит  
✅ **Масштабируемости** - гибкая структура  
✅ **Надежности** - внешние ключи и ограничения  
✅ **Соответствия требованиям** - полная функциональность системы  

Структура готова к продакшену и может обрабатывать большие объемы транзакций с высокой точностью и безопасностью.
