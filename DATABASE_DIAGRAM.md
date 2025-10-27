# Диаграмма структуры базы данных Токен-Платформы

## ER-диаграмма связей между таблицами

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              СИСТЕМНЫЕ ТАБЛИЦЫ LARAVEL                          │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────┐    ┌─────────────────┐    ┌─────────────┐    ┌─────────────┐
│    users    │    │password_reset_  │    │   sessions  │    │    cache    │
│             │    │    tokens       │    │             │    │             │
│ id (PK)     │    │ email (PK)      │    │ id (PK)     │    │ key (PK)    │
│ name        │    │ token           │    │ user_id (FK)│    │ value       │
│ email (UK)  │    │ created_at      │    │ ip_address  │    │ expiration  │
│ password    │    └─────────────────┘    │ user_agent  │    └─────────────┘
│ phone       │                           │ payload     │
│ role        │                           │ last_activity│
│ unique_id   │                           └─────────────┘
│ 2fa_enabled │
│ 2fa_secret  │
│ is_active   │
│ last_login  │
└─────────────┘
       │
       │ 1:N
       ▼
┌─────────────┐
│    jobs     │
│             │
│ id (PK)     │
│ queue       │
│ payload     │
│ attempts    │
│ reserved_at │
│ available_at│
└─────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              ОСНОВНЫЕ БИЗНЕС-ТАБЛИЦЫ                           │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────┐    ┌─────────────────┐    ┌─────────────┐
│    users    │    │   transactions  │    │   tokens    │
│             │    │                 │    │             │
│ id (PK)     │◄───┤ user_id (FK)    │    │ id (PK)     │
│ name        │    │ token_id (FK)    ├───►│ symbol (UK) │
│ email (UK)  │    │ transaction_id  │    │ name        │
│ role        │    │ type            │    │ current_price│
│ unique_id   │    │ status          │    │ total_supply│
│ is_active   │    │ amount          │    │ available_  │
└─────────────┘    │ price           │    │ supply      │
       │            │ total_amount    │    │ is_active   │
       │ 1:N        │ fee             │    │ metadata    │
       ▼            │ payment_method  │    └─────────────┘
┌─────────────┐    │ payment_ref     │           │
│user_balances│    │ metadata        │           │ 1:N
│             │    │ processed_at    │           ▼
│ user_id (FK)│    └─────────────────┘    ┌─────────────┐
│ token_id(FK)│                           │user_balances│
│ balance     │                           │             │
│ locked_bal  │                           │ user_id (FK)│
│ total_purch │                           │ token_id(FK)│
│ total_sold  │                           │ balance     │
│ UNIQUE(user │                           │ locked_bal  │
│ _id,token_id│                           │ total_purch │
└─────────────┘                           │ total_sold  │
       ▲                                  │ UNIQUE(user │
       │                                  │ _id,token_id│
       └──────────────────────────────────┘             │
                                                        └─────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              ИНТЕГРАЦИОННЫЕ ТАБЛИЦЫ                            │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────┐    ┌─────────────┐
│   brokers   │    │    banks    │
│             │    │             │
│ id (PK)     │    │ id (PK)     │
│ name        │    │ name        │
│ api_key (UK)│    │ code (UK)   │
│ api_secret  │    │ api_endpoint│
│ exchange_url│    │ merchant_id │
│ reserve_bal │    │ api_key    │
│ min_thresh  │    │ api_secret │
│ is_active   │    │ commission │
│ settings    │    │ is_active  │
└─────────────┘    │ settings   │
                   └─────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              ДОПОЛНИТЕЛЬНЫЕ ТАБЛИЦЫ                            │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│token_packages│    │winner_losers│    │ audit_logs  │
│             │    │             │    │             │
│ id (PK)     │    │ id (PK)     │    │ id (PK)     │
│ name        │    │ user_id (FK)│    │ event       │
│ description │    │ type        │    │ entity_type │
│ token_amount│    │ amount      │    │ entity_id   │
│ price       │    │ token_amount│    │ user_id (FK)│
│ discount_%  │    │ token_id(FK)│    │ ip_address  │
│ is_active   │    │ status      │    │ user_agent  │
│ sort_order  │    │ processed_at│    │ old_values  │
└─────────────┘    │ metadata    │    │ new_values  │
                   └─────────────┘    │ metadata    │
                            │         └─────────────┘
                            │                 │
                            │ 1:N             │ 1:N
                            ▼                 ▼
                   ┌─────────────┐    ┌─────────────┐
                   │    users    │    │    users    │
                   │             │    │             │
                   │ id (PK)     │    │ id (PK)     │
                   │ name        │    │ name        │
                   │ email (UK)  │    │ email (UK)  │
                   │ role        │    │ role        │
                   │ unique_id   │    │ unique_id   │
                   │ is_active   │    │ is_active   │
                   └─────────────┘    └─────────────┘
                            │                 │
                            │ 1:N             │ 1:N
                            ▼                 ▼
                   ┌─────────────┐    ┌─────────────┐
                   │   tokens    │    │   tokens    │
                   │             │    │             │
                   │ id (PK)     │    │ id (PK)     │
                   │ symbol (UK) │    │ symbol (UK) │
                   │ name        │    │ name        │
                   │ current_price│    │ current_price│
                   │ total_supply│    │ total_supply│
                   │ available_  │    │ available_  │
                   │ supply      │    │ supply      │
                   └─────────────┘    └─────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              КЛЮЧЕВЫЕ СВЯЗИ                                    │
└─────────────────────────────────────────────────────────────────────────────────┘

1. users (1) ──────── (N) transactions
   ├─ CASCADE DELETE при удалении пользователя
   └─ Основная связь для всех операций

2. tokens (1) ──────── (N) transactions  
   ├─ CASCADE DELETE при удалении токена
   └─ Связь токена с транзакциями

3. users (1) ──────── (N) user_balances
   ├─ UNIQUE(user_id, token_id)
   └─ Баланс пользователя по токенам

4. tokens (1) ──────── (N) user_balances
   ├─ UNIQUE(user_id, token_id) 
   └─ Баланс токена у пользователей

5. users (1) ──────── (N) winner_losers
   ├─ CASCADE DELETE при удалении пользователя
   └─ Результаты игр пользователя

6. tokens (1) ──────── (N) winner_losers
   ├─ CASCADE DELETE при удалении токена
   └─ Результаты игр по токену

7. users (1) ──────── (N) audit_logs
   ├─ SET NULL при удалении пользователя
   └─ Аудит действий пользователя

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              ИНДЕКСЫ И ОПТИМИЗАЦИЯ                             │
└─────────────────────────────────────────────────────────────────────────────────┘

УНИКАЛЬНЫЕ ИНДЕКСЫ:
├─ users.email (UNIQUE)
├─ users.unique_id (UNIQUE)  
├─ transactions.transaction_id (UNIQUE)
├─ brokers.api_key (UNIQUE)
├─ banks.code (UNIQUE)
└─ user_balances(user_id, token_id) (UNIQUE)

СОСТАВНЫЕ ИНДЕКСЫ:
├─ audit_logs(entity_type, entity_id)
├─ audit_logs(user_id, created_at)
├─ sessions.user_id
└─ sessions.last_activity

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              ТИПЫ ДАННЫХ И ОГРАНИЧЕНИЯ                         │
└─────────────────────────────────────────────────────────────────────────────────┘

ФИНАНСОВЫЕ ПОЛЯ (DECIMAL):
├─ tokens.current_price (20,8)
├─ tokens.total_supply (20,8)
├─ tokens.available_supply (20,8)
├─ transactions.amount (20,8)
├─ transactions.price (20,8)
├─ transactions.total_amount (20,8)
├─ transactions.fee (20,8)
├─ user_balances.balance (20,8)
├─ user_balances.locked_balance (20,8)
├─ user_balances.total_purchased (20,8)
├─ user_balances.total_sold (20,8)
├─ brokers.reserve_balance (20,8)
├─ brokers.min_reserve_threshold (20,8)
├─ banks.commission_rate (5,4)
├─ token_packages.token_amount (20,8)
├─ token_packages.price (20,8)
├─ token_packages.discount_percentage (5,2)
├─ winner_losers.amount (20,8)
└─ winner_losers.token_amount (20,8)

ENUM ПОЛЯ:
├─ users.role: 'client', 'broker', 'admin'
├─ transactions.type: 'buy', 'sell', 'transfer', 'refund'
├─ transactions.status: 'pending', 'processing', 'completed', 'failed', 'cancelled'
├─ winner_losers.type: 'winner', 'loser'
└─ winner_losers.status: 'pending', 'processed', 'paid'

JSON ПОЛЯ:
├─ tokens.metadata
├─ transactions.metadata
├─ brokers.settings
├─ banks.settings
├─ winner_losers.metadata
├─ audit_logs.old_values
├─ audit_logs.new_values
├─ audit_logs.metadata
└─ users.two_factor_recovery_codes

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              БЕЗОПАСНОСТЬ И АУДИТ                              │
└─────────────────────────────────────────────────────────────────────────────────┘

ШИФРОВАНИЕ:
├─ brokers.api_secret (AES-256)
├─ banks.api_key (AES-256)
├─ banks.api_secret (AES-256)
└─ users.password (bcrypt)

АУДИТ:
├─ audit_logs.event (тип события)
├─ audit_logs.entity_type (тип сущности)
├─ audit_logs.entity_id (ID сущности)
├─ audit_logs.user_id (пользователь)
├─ audit_logs.ip_address (IP адрес)
├─ audit_logs.user_agent (браузер)
├─ audit_logs.old_values (старые значения)
├─ audit_logs.new_values (новые значения)
└─ audit_logs.metadata (дополнительные данные)

КОНТРОЛЬ ДОСТУПА:
├─ users.role (ролевая система)
├─ users.is_active (активность аккаунта)
├─ brokers.is_active (активность брокера)
├─ banks.is_active (активность банка)
└─ tokens.is_active (активность токена)
