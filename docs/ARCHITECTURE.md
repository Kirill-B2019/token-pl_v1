# 📊 Архитектура системы

Это подробное описание архитектуры Токен Платформы.

## Общая архитектура

### Микросервисная архитектура

```
┌─────────────────────────────────────────────────────────────┐
│                    Токен Платформа                          │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐          │
│  │   Frontend  │ │   Backend   │ │   Database  │          │
│  │             │ │             │ │             │          │
│  │ • Vue.js    │ │ • Laravel   │ │ • MySQL     │          │
│  │ • Bootstrap │ │ • PHP 8.2   │ │ • Redis     │          │
│  │ • SPA       │ │ • REST API  │ │             │          │
│  └─────────────┘ └─────────────┘ └─────────────┘          │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐          │
│  │  Payments   │ │  Cards      │ │ Monitoring  │          │
│  │             │ │             │ │             │          │
│  │ • 2can      │ │ • Tokenize   │ │ • Logs      │          │
│  │ • Webhook   │ │ • Storage    │ │ • Metrics   │          │
│  │             │ │              │ │             │          │
│  └─────────────┘ └─────────────┘ └─────────────┘          │
└─────────────────────────────────────────────────────────────┘
```

## Детальная архитектура

### 1. Frontend Layer

#### Vue.js SPA
```
public/
├── index.html          # Главная страница
├── css/
│   ├── app.css        # Стили приложения
│   └── vendor.css     # Стили зависимостей
└── js/
    ├── app.js         # Основное приложение Vue
    ├── vendor.js      # Зависимости
    └── chunks/        # Динамические чанки
```

#### Компоненты Vue.js
```
resources/js/
├── components/        # Переиспользуемые компоненты
│   ├── ui/           # UI компоненты
│   ├── forms/        # Формы
│   └── modals/       # Модальные окна
├── pages/            # Страницы приложения
├── router/           # Маршрутизация Vue Router
├── store/            # Vuex store
└── mixins/           # Vue mixins
```

### 2. Backend Layer

#### Laravel Application Structure
```
app/
├── Http/
│   ├── Controllers/      # HTTP контроллеры
│   ├── Middleware/       # HTTP middleware
│   └── Requests/         # Form request классы
├── Models/               # Eloquent модели
├── Services/             # Бизнес-логика сервисы
├── Policies/             # Авторизационные политики
├── Events/               # События
├── Listeners/            # Слушатели событий
├── Jobs/                 # Очередные задачи
├── Mail/                 # Email классы
├── Notifications/        # Уведомления
└── Console/Commands/     # Artisan команды
```

#### API Layer
```
routes/
├── api.php              # API маршруты
├── web.php              # Web маршруты
├── channels.php         # Broadcast маршруты
└── console.php          # Artisan маршруты
```

### 3. Database Layer

#### MySQL Database Schema
```sql
-- Основные таблицы
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('client', 'broker', 'admin') DEFAULT 'client',
    balance_rub DECIMAL(15,2) DEFAULT 0,
    unique_id VARCHAR(10) UNIQUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(255) UNIQUE NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    token_id BIGINT UNSIGNED NULL,
    type ENUM('buy', 'sell', 'transfer', 'refund', 'deposit') NOT NULL,
    deposit_type ENUM('token', 'rub') NULL,
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled'),
    amount DECIMAL(20,8) NOT NULL,
    price DECIMAL(20,8),
    total_amount DECIMAL(20,8),
    fee DECIMAL(20,8) DEFAULT 0,
    payment_method VARCHAR(50),
    payment_reference VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (token_id) REFERENCES tokens(id)
);

CREATE TABLE user_cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    twocan_card_token VARCHAR(255) UNIQUE NOT NULL,
    card_mask VARCHAR(50) NOT NULL,
    card_brand VARCHAR(50),
    card_holder_name VARCHAR(255),
    expiry_month TINYINT NOT NULL,
    expiry_year SMALLINT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### Redis для кеширования
```
Redis Keys:
├── user:{id}:balance         # Баланс пользователя
├── user:{id}:cards          # Карты пользователя
├── system:health            # Статус системы
├── cache:config             # Кешированная конфигурация
└── sessions:{session_id}    # Сессии пользователей
```

### 4. External Services

#### 2can Payment Gateway
```
API Endpoints:
├── POST /api/v1/payment/create      # Создание платежа
├── POST /api/v1/tokenize            # Токенизация карты
├── POST /api/v1/payment/status      # Статус платежа
└── POST /webhook                    # Webhook уведомления

Data Flow:
1. Frontend → Laravel → 2can API
2. 2can → Webhook → Laravel → Database
3. Laravel → Email/SMS notifications
```

#### Email Service (SMTP/Mailgun)
```
Mail Templates:
├── welcome.blade.php        # Приветственное письмо
├── payment-success.blade.php # Успешный платеж
├── payment-fail.blade.php    # Неудачный платеж
└── security-alert.blade.php  # Безопасность
```

## Архитектурные паттерны

### MVC Pattern

#### Model Layer
```php
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    // Relationships
    public function balances() { return $this->hasMany(UserBalance::class); }
    public function cards() { return $this->hasMany(UserCard::class); }

    // Business logic
    public function addRubBalance(float $amount): bool
    public function hasEnoughRubBalance(float $amount): bool
}
```

#### View Layer
```blade
{{-- resources/views/client/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="grid gap-6 lg:grid-cols-2">
    <!-- Balance Cards -->
    <div class="card">
        <h3>Баланс в рублях</h3>
        <p class="text-2xl">{{ auth()->user()->formatted_rub_balance }}</p>
    </div>

    <!-- Token Balances -->
    @foreach($balances as $balance)
    <div class="card">
        <h3>{{ $balance->token->name }}</h3>
        <p>{{ number_format($balance->balance, 6) }}</p>
    </div>
    @endforeach
</div>
@endsection
```

#### Controller Layer
```php
class TwoCanPaymentController extends Controller
{
    public function createPayment(Request $request): RedirectResponse
    {
        // Validation
        $request->validate([...]);

        // Business logic via service
        $result = $this->paymentService->createPayment(
            auth()->user(),
            $request->amount,
            'Payment description',
            $request->card_token
        );

        // Response
        return $result['success']
            ? redirect($result['payment_url'])
            : back()->with('error', $result['error']);
    }
}
```

### Service Layer Pattern

```php
interface PaymentServiceInterface
{
    public function createPayment(User $user, float $amount, string $description): array;
    public function processWebhook(Request $request): bool;
}

class TwoCanPaymentService implements PaymentServiceInterface
{
    public function __construct(
        private HttpClient $http,
        private Logger $logger
    ) {}

    public function createPayment(User $user, float $amount, string $description): array
    {
        try {
            // API call to 2can
            $response = $this->http->post('/api/v1/payment/create', [
                'amount' => $amount,
                'currency' => 'RUB',
                // ...
            ]);

            // Create transaction record
            $transaction = Transaction::create([...]);

            // Log success
            $this->logger->info('Payment created', ['transaction_id' => $transaction->id]);

            return ['success' => true, 'transaction' => $transaction];
        } catch (Exception $e) {
            // Log error
            $this->logger->error('Payment creation failed', [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => 'Payment creation failed'];
        }
    }
}
```

### Repository Pattern

```php
interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): bool;
}

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(private User $model) {}

    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }
}
```

### Observer Pattern

```php
class UserObserver
{
    public function created(User $user): void
    {
        // Send welcome email
        $user->notify(new WelcomeNotification());

        // Log user creation
        AuditLog::create([
            'action' => 'user_created',
            'model_type' => User::class,
            'model_id' => $user->id,
            'user_id' => auth()->id(),
        ]);
    }

    public function updated(User $user): void
    {
        // Check for sensitive field changes
        if ($user->wasChanged('email')) {
            // Send email verification
            $user->sendEmailVerificationNotification();
        }

        if ($user->wasChanged('password')) {
            // Log password change
            AuditLog::create([
                'action' => 'password_changed',
                'model_type' => User::class,
                'model_id' => $user->id,
            ]);
        }
    }
}

// Register observer in AppServiceProvider
User::observe(UserObserver::class);
```

## Безопасность

### Authentication & Authorization

#### JWT Tokens (для API)
```php
class ApiAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->bearerToken();
            $payload = JWT::decode($token, config('jwt.secret'), ['HS256']);

            // Set authenticated user
            auth()->loginUsingId($payload->sub);

            return $next($request);
        } catch (Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
```

#### Role-Based Access Control (RBAC)
```php
class TransactionPolicy
{
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['client', 'broker']);
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $user->isAdmin();
    }
}
```

#### Data Encryption
```php
class EncryptedCardToken extends CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): string
    {
        return decrypt($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return encrypt($value);
    }
}

// In model
protected $casts = [
    'twocan_card_token' => EncryptedCardToken::class,
];
```

## Производительность

### Caching Strategy

#### Multi-layer Caching
```php
class CacheManager
{
    public function getUserBalance(int $userId): float
    {
        return Cache::tags(['user', "user.{$userId}"])
            ->remember("user.{$userId}.balance", 3600, function () use ($userId) {
                return User::find($userId)->balance_rub;
            });
    }

    public function invalidateUserCache(int $userId): void
    {
        Cache::tags(["user.{$userId}"])->flush();
    }
}
```

#### Cache Invalidation Patterns
```php
class UserObserver
{
    public function updated(User $user): void
    {
        if ($user->wasChanged('balance_rub')) {
            Cache::tags(["user.{$user->id}"])->flush();
        }
    }
}
```

### Database Optimization

#### Indexing Strategy
```sql
-- Primary keys (auto-indexed)
-- Foreign keys (auto-indexed)

-- Composite indexes
CREATE INDEX idx_transactions_user_status ON transactions (user_id, status);
CREATE INDEX idx_transactions_created_at ON transactions (created_at);
CREATE INDEX idx_user_cards_user_active ON user_cards (user_id, is_active);

-- Partial indexes
CREATE INDEX idx_active_cards ON user_cards (user_id) WHERE is_active = true;
```

#### Query Optimization
```php
class TransactionRepository
{
    public function getUserTransactions(int $userId, array $filters = []): Collection
    {
        return Transaction::where('user_id', $userId)
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['type']), fn($q) => $q->where('type', $filters['type']))
            ->when(isset($filters['date_from']), fn($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
            ->when(isset($filters['date_to']), fn($q) => $q->whereDate('created_at', '<=', $filters['date_to']))
            ->with(['token:id,name,symbol'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }
}
```

### Queue System

#### Job Processing
```php
class ProcessPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $userId,
        public float $amount,
        public string $paymentId
    ) {}

    public function handle(): void
    {
        // Process payment logic
        $user = User::find($this->userId);
        $user->addRubBalance($this->amount);

        // Send notification
        $user->notify(new PaymentProcessed($this->amount));

        // Log success
        Log::info('Payment processed via queue', [
            'user_id' => $this->userId,
            'amount' => $this->amount,
            'payment_id' => $this->paymentId,
        ]);
    }

    public function failed(Exception $exception): void
    {
        Log::error('Payment processing failed', [
            'user_id' => $this->userId,
            'amount' => $this->amount,
            'exception' => $exception->getMessage(),
        ]);
    }
}
```

## Масштабируемость

### Horizontal Scaling

#### Database Sharding
```php
class ShardManager
{
    public function getShardForUser(int $userId): string
    {
        // Simple sharding by user ID
        $shardNumber = $userId % 4; // 4 shards
        return "shard_{$shardNumber}";
    }

    public function getConnectionForUser(int $userId): string
    {
        return $this->getShardForUser($userId);
    }
}
```

#### Load Balancing
```nginx
upstream laravel_backend {
    ip_hash;  # Session stickiness
    server app1:8000;
    server app2:8000;
    server app3:8000;
}

server {
    listen 80;
    server_name cardfly.online;

    location / {
        proxy_pass http://laravel_backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Microservices Considerations

#### Service Boundaries
```
Core Services:
├── User Service          # Управление пользователями
├── Payment Service       # Обработка платежей
├── Card Service          # Управление картами
├── Transaction Service   # История транзакций
├── Notification Service  # Отправка уведомлений
└── Audit Service         # Логирование действий
```

#### API Gateway Pattern
```php
class ApiGateway
{
    public function handle(Request $request): Response
    {
        // Authentication
        $user = $this->authenticate($request);

        // Route to appropriate service
        $service = $this->getServiceForRoute($request->path());

        // Call service
        return $service->handle($request, $user);
    }
}
```

## Мониторинг и наблюдаемость

### Metrics Collection

#### Application Metrics
```php
class MetricsCollector
{
    public static function incrementCounter(string $name, array $labels = []): void
    {
        // Prometheus counter
        // or custom metrics storage
    }

    public static function recordHistogram(string $name, float $value, array $labels = []): void
    {
        // Response time, query time, etc.
    }

    public static function setGauge(string $name, float $value, array $labels = []): void
    {
        // Current values: active users, queue size, etc.
    }
}

// Usage in middleware
class MetricsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        $duration = (microtime(true) - $start) * 1000;

        MetricsCollector::recordHistogram(
            'http_request_duration_ms',
            $duration,
            [
                'method' => $request->method(),
                'route' => $request->route()?->getName(),
                'status' => $response->status(),
            ]
        );

        return $response;
    }
}
```

#### Distributed Tracing
```php
class TracingService
{
    public function startSpan(string $name, array $attributes = []): Span
    {
        // Create span for distributed tracing
        // Integration with Jaeger, Zipkin, etc.
    }

    public function traceDatabaseQuery(string $sql, array $bindings): void
    {
        $span = $this->startSpan('database.query');
        $span->setAttribute('db.statement', $sql);
        $span->setAttribute('db.bindings', json_encode($bindings));
        // Record execution time
        $span->end();
    }
}
```

## Disaster Recovery

### Backup Strategy
```bash
# Database backup
mysqldump --single-transaction --routines --triggers token_platform > backup.sql

# File system backup
tar -czf files_backup.tar.gz storage/ public/uploads/

# Incremental backups
rsync -av --delete --link-dest=/backups/last /current /backups/$(date +%Y%m%d_%H%M%S)
```

### Recovery Procedures
```bash
# 1. Stop application
php artisan down

# 2. Restore database
mysql token_platform < backup.sql

# 3. Restore files
tar -xzf files_backup.tar.gz

# 4. Clear caches
php artisan optimize:clear

# 5. Start application
php artisan up
```

## Compliance и регуляции

### GDPR Compliance
```php
class GdprCompliance
{
    public function exportUserData(int $userId): array
    {
        $user = User::with(['transactions', 'cards'])->find($userId);

        return [
            'personal_data' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'created_at' => $user->created_at,
            ],
            'transactions' => $user->transactions->map(function ($transaction) {
                return Arr::except($transaction->toArray(), ['payment_reference']);
            }),
            'cards' => $user->cards->map(function ($card) {
                return Arr::only($card->toArray(), ['card_mask', 'card_brand', 'created_at']);
            }),
        ];
    }

    public function deleteUserData(int $userId): bool
    {
        // Anonymize instead of delete for audit purposes
        User::where('id', $userId)->update([
            'name' => 'Deleted User',
            'email' => "deleted_{$userId}@anonymized.local",
            'phone' => null,
        ]);

        // Remove sensitive data
        UserCard::where('user_id', $userId)->delete();
        AuditLog::where('user_id', $userId)->delete();

        return true;
    }
}
```

### PCI DSS for Card Data
```php
class PciCompliance
{
    public function tokenizeCardData(array $cardData): string
    {
        // Remove sensitive data from application
        // Store only token reference
        $token = $this->callTokenizationService($cardData);

        // Log tokenization (without sensitive data)
        AuditLog::create([
            'action' => 'card_tokenized',
            'user_id' => auth()->id(),
            'model_type' => UserCard::class,
            'details' => ['card_brand' => $this->detectCardBrand($cardData['number'])],
        ]);

        return $token;
    }

    public function validatePciCompliance(): array
    {
        $issues = [];

        // Check for unencrypted card data
        if (DB::table('user_cards')->whereNotNull('card_number')->exists()) {
            $issues[] = 'Unencrypted card numbers found in database';
        }

        // Check token expiry
        $expiredTokens = UserCard::where('is_active', true)
            ->where(function ($query) {
                $query->where('expiry_year', '<', date('Y'))
                      ->orWhere(function ($q) {
                          $q->where('expiry_year', '=', date('Y'))
                            ->where('expiry_month', '<', date('m'));
                      });
            })->count();

        if ($expiredTokens > 0) {
            $issues[] = "{$expiredTokens} expired card tokens found";
        }

        return $issues;
    }
}
```

## Заключение

Архитектура Токен Платформы построена с учетом:

- **Масштабируемости**: Горизонтальное масштабирование компонентов
- **Надежности**: Многоуровневая отказоустойчивость
- **Безопасности**: Защита данных и соответствие стандартам
- **Производительности**: Оптимизация запросов и кеширование
- **Поддерживаемости**: Четкое разделение ответственности

Система спроектирована для обработки высоких нагрузок и обеспечения надежной работы финансовых операций.
