# 👨‍💻 Руководство разработчика

Это техническое руководство для разработчиков, работающих с Токен Платформой.

## Архитектура

### Общая структура

```
├── app/
│   ├── Http/Controllers/     # Контроллеры
│   ├── Models/              # Модели Eloquent
│   ├── Services/            # Бизнес-логика
│   ├── Policies/            # Политики авторизации
│   └── Console/Commands/    # Artisan команды
├── database/
│   ├── migrations/          # Миграции БД
│   ├── seeders/            # Сиды
│   └── factories/          # Фабрики для тестов
├── resources/
│   ├── views/              # Blade шаблоны
│   ├── js/                 # Vue.js компоненты
│   └── lang/               # Локализация
├── routes/                  # Определение маршрутов
├── tests/                   # Тесты
└── config/                  # Конфигурационные файлы
```

## Модели данных

### User
```php
// Основная модель пользователя
class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password', 'phone',
        'role', 'unique_id', 'balance_rub'
    ];

    // Связи
    public function balances()        // UserBalance[]
    public function transactions()     // Transaction[]
    public function cards()           // UserCard[]
    public function defaultCard()     // UserCard|null
    public function userGroups()      // UserGroup[]

    // Методы баланса
    public function addRubBalance(float $amount): bool
    public function subtractRubBalance(float $amount): bool
    public function hasEnoughRubBalance(float $amount): bool
}
```

### UserBalance
```php
// Баланс пользователя по конкретному токену
class UserBalance extends Model
{
    protected $fillable = [
        'user_id', 'token_id', 'balance',
        'locked_balance', 'total_purchased', 'total_sold'
    ];

    protected $casts = [
        'balance' => 'decimal:8',
        'locked_balance' => 'decimal:8'
    ];

    // Методы
    public function addBalance(float $amount): bool
    public function subtractBalance(float $amount): bool
    public function lockBalance(float $amount): bool
    public function unlockBalance(float $amount): bool
    public function hasEnoughBalance(float $amount): bool
    public function getAvailableBalanceAttribute(): float
}
```

### UserCard
```php
// Привязанная карта пользователя
class UserCard extends Model
{
    protected $fillable = [
        'user_id', 'twocan_card_token', 'card_mask',
        'card_brand', 'card_holder_name', 'expiry_month',
        'expiry_year', 'is_active', 'is_default'
    ];

    // Методы
    public function isExpired(): bool
    public function setAsDefault(): bool
    public function getMaskedNumberAttribute(): string
    public function getFormattedExpiryAttribute(): string
}
```

### Transaction
```php
// Транзакция (операция)
class Transaction extends Model
{
    protected $fillable = [
        'transaction_id', 'user_id', 'token_id',
        'type', 'deposit_type', 'status', 'amount',
        'price', 'total_amount', 'fee', 'payment_reference'
    ];

    // Типы транзакций
    const TYPES = [
        'buy', 'sell', 'transfer', 'refund', 'deposit'
    ];

    // Типы депозитов
    const DEPOSIT_TYPES = [
        'token', 'rub'
    ];

    // Статусы
    const STATUSES = [
        'pending', 'processing', 'completed', 'failed', 'cancelled'
    ];
}
```

## Сервисы

### TwoCanPaymentService
```php
class TwoCanPaymentService
{
    // Создание платежа
    public function createPayment(User $user, float $amount, string $description, ?string $cardToken = null): array

    // Обработка успешного платежа (webhook)
    public function processPaymentSuccess(Request $request): bool

    // Обработка неудачного платежа
    public function processPaymentFailure(Request $request): bool

    // Получение статуса платежа
    public function getPaymentStatus(string $paymentId): ?array
}
```

### TwoCanCardService
```php
class TwoCanCardService
{
    // Токенизация карты
    public function tokenizeCard(User $user, array $cardData): array

    // Удаление карты
    public function deleteCard(User $user, int $cardId): bool

    // Установка карты по умолчанию
    public function setDefaultCard(User $user, int $cardId): bool

    // Получение карт пользователя
    public function getUserCards(User $user): Collection
}
```

## Контроллеры

### TwoCanPaymentController
```php
class TwoCanPaymentController extends Controller
{
    public function showTopUpForm(): View
    public function createPayment(Request $request): RedirectResponse
    public function paymentSuccess(Request $request): View
    public function paymentFail(Request $request): View
    public function webhook(Request $request) // Webhook endpoint
}
```

### CardController
```php
class CardController extends Controller
{
    public function index(): View
    public function showAttachForm(): View
    public function attachCard(Request $request): RedirectResponse
    public function setDefault(Request $request, int $cardId): RedirectResponse
    public function delete(int $cardId): RedirectResponse
}
```

## API Endpoints

### Аутентификация
```
POST   /login
POST   /register
POST   /logout
GET    /user
```

### Платежи
```
GET    /client/balance/topup          # Форма пополнения
POST   /client/balance/topup          # Создание платежа
GET    /client/payment/success        # Успешный платеж
GET    /client/payment/fail           # Неудачный платеж
POST   /client/payment/webhook        # Webhook от 2can
```

### Управление картами
```
GET    /client/cards                  # Список карт
GET    /client/cards/attach           # Форма привязки
POST   /client/cards/attach           # Привязка карты
PATCH  /client/cards/{id}/default     # По умолчанию
DELETE /client/cards/{id}             # Удаление
```

## Разработка новых функций

### 1. Создание новой модели

```bash
php artisan make:model NewModel -m
```

### 2. Создание миграции

```bash
php artisan make:migration create_new_table
```

### 3. Создание контроллера

```bash
php artisan make:controller NewController
```

### 4. Создание сервиса

```bash
php artisan make:class Services/NewService
```

### 5. Создание политики

```bash
php artisan make:policy NewPolicy --model=NewModel
```

### 6. Создание тестов

```bash
php artisan make:test NewTest
```

## Работа с базой данных

### Миграции

```php
// Создание таблицы
Schema::create('new_table', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

// Добавление колонки
Schema::table('existing_table', function (Blueprint $table) {
    $table->string('new_column')->nullable();
});
```

### Сиды

```php
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(10)->create();
        Token::factory(5)->create();
    }
}
```

### Фабрики

```php
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => 'client',
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }
}
```

## Тестирование

### Структура тестов

```
tests/
├── Feature/          # Feature тесты (HTTP)
├── Unit/            # Unit тесты (классы/методы)
└── TestCase.php     # Базовый класс
```

### Примеры тестов

```php
class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_be_created()
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'email' => $user->email,
        ]);
    }

    /** @test */
    public function user_can_add_balance()
    {
        $user = User::factory()->create(['balance_rub' => 100]);

        $user->addRubBalance(50);

        $this->assertEquals(150, $user->fresh()->balance_rub);
    }
}
```

### Запуск тестов

```bash
# Все тесты
php artisan test

# Конкретный тест
php artisan test --filter=UserTest

# С покрытием
php artisan test --coverage
```

## Работа с платежной системой 2can

### Конфигурация

```env
TWOCAN_SHOP_ID=1337
TWOCAN_SECRET_KEY=your_secret_key
TWOCAN_API_URL=https://2can.ru/api/v1/
TWOCAN_CURRENCY=RUB
TWOCAN_MIN_AMOUNT=10
TWOCAN_MAX_AMOUNT=50000
```

### Создание платежа

```php
$service = app(TwoCanPaymentService::class);
$result = $service->createPayment($user, 100.00, 'Описание платежа', $cardToken);

if ($result['success']) {
    // Перенаправление на 2can
    return redirect($result['payment_url']);
}
```

### Webhook обработка

```php
public function webhook(Request $request)
{
    $service = app(TwoCanPaymentService::class);

    if ($service->processPaymentSuccess($request)) {
        return response()->json(['status' => 'success']);
    }

    return response()->json(['status' => 'error'], 400);
}
```

## Безопасность

### Политики авторизации

```php
class UserPolicy
{
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->isAdmin();
    }
}
```

### Валидация запросов

```php
class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ];
    }
}
```

## Производительность

### Кеширование

```php
// Кеширование данных
$userBalances = Cache::remember(
    "user.{$user->id}.balances",
    3600,
    fn() => $user->balances()->get()
);
```

### Очереди

```php
// Отправка email в очередь
dispatch(new SendWelcomeEmail($user));

// Создание очереди
php artisan make:job SendWelcomeEmail
```

## Логирование

```php
// Логирование действий
Log::info('User created payment', [
    'user_id' => $user->id,
    'amount' => $amount,
    'payment_id' => $paymentId,
]);

// Логирование ошибок
Log::error('Payment failed', [
    'user_id' => $user->id,
    'error' => $error,
    'trace' => $exception->getTraceAsString(),
]);
```

## Развертывание

### Production настройки

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
REDIS_HOST=127.0.0.1
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
```

### Оптимизация

```bash
# Оптимизация автозагрузчика
composer install --optimize-autoloader --no-dev

# Кеширование конфигурации
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Оптимизация для production
php artisan optimize
```

## Мониторинг

### Логи

```bash
# Просмотр логов
tail -f storage/logs/laravel.log

# Логи по дате
tail -f storage/logs/laravel-2024-01-15.log
```

### Метрики

- Response time
- Error rate
- Database queries
- Memory usage
- CPU usage

## Troubleshooting

### Распространенные проблемы

#### Ошибка подключения к БД
```bash
# Проверка подключения
php artisan tinker
>>> DB::connection()->getPdo()
```

#### Ошибки платежей
```bash
# Проверка логов платежей
tail -f storage/logs/laravel.log | grep "2can"
```

#### Проблемы с кешем
```bash
# Очистка всего кеша
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Debug режим

```php
// В контроллере
dd($variable); // Dump and die

// В blade шаблоне
{{ dd($variable) }}

// Логирование
Log::debug('Debug message', ['data' => $data]);
```
