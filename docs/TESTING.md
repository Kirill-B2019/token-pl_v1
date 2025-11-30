# 🧪 Тестирование

Это руководство по тестированию Токен Платформы.

## Обзор тестирования

### Типы тестов

1. **Unit тесты** - тестирование отдельных классов и методов
2. **Feature тесты** - тестирование HTTP endpoints и пользовательских сценариев
3. **Integration тесты** - тестирование взаимодействия компонентов
4. **E2E тесты** - сквозное тестирование через браузер

### Структура тестов

```
tests/
├── Feature/          # HTTP и функциональные тесты
│   ├── TwoCanPaymentTest.php
│   └── AuthTest.php
├── Unit/            # Unit тесты классов
│   ├── Services/
│   └── Models/
├── TestCase.php     # Базовый класс
└── CreatesApplication.php
```

## Настройка тестирования

### Конфигурация PHPUnit

```xml
<!-- phpunit.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
    </testsuites>
</phpunit>
```

### Тестовая база данных

```php
// tests/TestCase.php
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Запуск миграций для тестов
        $this->artisan('migrate:fresh');

        // Сиды для тестов (опционально)
        $this->seed();
    }

    protected function tearDown(): void
    {
        // Очистка после тестов
        $this->artisan('migrate:rollback');

        parent::tearDown();
    }
}
```

## Unit тестирование

### Тестирование моделей

```php
<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_add_rub_balance()
    {
        $user = User::factory()->create(['balance_rub' => 100]);

        $result = $user->addRubBalance(50);

        $this->assertTrue($result);
        $this->assertEquals(150, $user->fresh()->balance_rub);
    }

    /** @test */
    public function user_cannot_subtract_more_than_balance()
    {
        $user = User::factory()->create(['balance_rub' => 100]);

        $result = $user->subtractRubBalance(150);

        $this->assertFalse($result);
        $this->assertEquals(100, $user->fresh()->balance_rub);
    }

    /** @test */
    public function user_has_correct_formatted_balance()
    {
        $user = User::factory()->create(['balance_rub' => 1234.56]);

        $this->assertEquals('1 234.56 ₽', $user->formatted_rub_balance);
    }
}
```

### Тестирование сервисов

```php
<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\TwoCanPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TwoCanPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TwoCanPaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TwoCanPaymentService::class);
    }

    /** @test */
    public function can_create_payment_request()
    {
        Http::fake([
            config('twocan.api_url') . 'payment/create' => Http::response([
                'success' => true,
                'payment_id' => 'test_payment_123',
                'payment_url' => 'https://2can.ru/payment/test_payment_123',
            ], 200)
        ]);

        $user = User::factory()->create();
        $result = $this->service->createPayment($user, 100.00);

        $this->assertTrue($result['success']);
        $this->assertEquals('test_payment_123', $result['payment_id']);
        $this->assertStringContains('2can.ru', $result['payment_url']);
    }

    /** @test */
    public function validates_payment_amount()
    {
        $user = User::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->service->createPayment($user, 5.00); // Ниже минимума
    }
}
```

## Feature тестирование

### Тестирование аутентификации

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    /** @test */
    public function user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_cannot_login_with_wrong_password()
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        $this->assertGuest();
    }
}
```

### Тестирование API

```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_get_profile_via_api()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                        ->getJson('/api/v1/user');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_api()
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401);
    }
}
```

## Тестирование платежей

### Тестирование 2can интеграции

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TwoCanPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock HTTP клиент для всех тестов
        Http::fake();
    }

    /** @test */
    public function user_can_view_balance_topup_form()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('client.balance.topup'));

        $response->assertStatus(200);
        $response->assertViewIs('client.balance.topup');
        $response->assertViewHas(['user', 'minAmount', 'maxAmount']);
    }

    /** @test */
    public function payment_form_validation_works()
    {
        $user = User::factory()->create();

        // Test valid amount
        $response = $this->actingAs($user)->post(route('client.balance.topup.submit'), [
            'amount' => 100.00,
            '_token' => csrf_token(),
        ]);

        $response->assertStatus(302); // Redirect to 2can
    }

    /** @test */
    public function payment_form_validates_minimum_amount()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('client.balance.topup.submit'), [
            'amount' => 5.00, // Below minimum
            '_token' => csrf_token(),
        ]);

        $response->assertSessionHasErrors('amount');
    }

    /** @test */
    public function card_form_validation_works()
    {
        $user = User::factory()->create();

        // Test invalid card number format
        $response = $this->actingAs($user)->post(route('client.cards.attach.submit'), [
            'number' => '123', // Too short
            'expiry' => '12/25',
            'cvv' => '123',
            '_token' => csrf_token(),
        ]);

        $response->assertSessionHasErrors('number');

        // Test valid card data
        $response = $this->actingAs($user)->post(route('client.cards.attach.submit'), [
            'number' => '4111111111111111', // Valid format
            'expiry' => '12/25',
            'cvv' => '123',
            '_token' => csrf_token(),
        ]);

        $response->assertStatus(302); // Redirect to cards list
    }

    /** @test */
    public function payment_service_creates_transaction()
    {
        // Mock 2can API response
        Http::shouldReceive('timeout')->andReturnSelf();
        Http::shouldReceive('post')->andReturn(
            new \Illuminate\Http\Client\Response(
                new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                    'success' => true,
                    'payment_id' => 'test_payment_123',
                    'payment_url' => 'https://2can.ru/payment/test_payment_123',
                ]))
            )
        );

        $user = User::factory()->create(['balance_rub' => 0]);

        $service = app(\App\Services\TwoCanPaymentService::class);
        $result = $service->createPayment($user, 100.00);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'deposit',
            'deposit_type' => 'rub',
            'amount' => 100.00,
            'status' => 'pending',
            'payment_reference' => $result['payment_id'],
        ]);
    }

    /** @test */
    public function user_card_methods_work_correctly()
    {
        $user = User::factory()->create();
        $card = UserCard::factory()->create([
            'user_id' => $user->id,
            'expiry_month' => 12,
            'expiry_year' => 2025,
        ]);

        // Test masked number
        $this->assertEquals($card->card_mask, $card->masked_number);

        // Test formatted expiry
        $this->assertEquals('12/2025', $card->formatted_expiry);

        // Test not expired
        $this->assertFalse($card->isExpired());

        // Test expired
        $expiredCard = UserCard::factory()->create([
            'user_id' => $user->id,
            'expiry_month' => 1,
            'expiry_year' => 2020,
        ]);
        $this->assertTrue($expiredCard->isExpired());
    }
}
```

## Mock и stubs

### Mock HTTP запросов

```php
// Mock внешних API
Http::fake([
    'https://2can.ru/api/v1/payment/create' => Http::response([
        'success' => true,
        'payment_id' => 'test_payment_123',
        'payment_url' => 'https://2can.ru/payment/test_payment_123',
    ], 200),

    'https://2can.ru/api/v1/tokenize' => Http::response([
        'card_token' => 'card_token_123',
    ], 200),
]);

// Mock с последовательными ответами
Http::fakeSequence()
    ->push(['success' => true], 200)
    ->push(['error' => 'Card declined'], 400);
```

### Mock очередей

```php
Queue::fake();

// Выполнить код, который отправляет job
$user->sendWelcomeEmail();

// Проверить, что job был отправлен
Queue::assertPushed(SendWelcomeEmail::class, function ($job) use ($user) {
    return $job->user->id === $user->id;
});
```

### Mock уведомлений

```php
Notification::fake();

// Выполнить код, отправляющий уведомление
$user->notify(new WelcomeNotification());

// Проверить отправку
Notification::assertSentTo(
    $user,
    WelcomeNotification::class
);
```

## Фабрики для тестов

### Пользовательские фабрики

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'balance_rub' => $this->faker->randomFloat(2, 0, 10000),
            'role' => 'client',
            'phone' => $this->faker->phoneNumber(),
            'unique_id' => $this->faker->unique()->randomNumber(8),
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }

    public function broker(): static
    {
        return $this->state(['role' => 'broker']);
    }

    public function verified(): static
    {
        return $this->state(['email_verified_at' => now()]);
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
```

### Фабрика транзакций

```php
<?php

namespace Database\Factories;

use App\Models\Token;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'transaction_id' => 'txn_' . $this->faker->unique()->uuid(),
            'user_id' => User::factory(),
            'token_id' => Token::factory(),
            'type' => $this->faker->randomElement(['buy', 'sell', 'transfer', 'refund', 'deposit']),
            'deposit_type' => $this->faker->randomElement(['token', 'rub']),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed', 'cancelled']),
            'amount' => $this->faker->randomFloat(8, 0.0001, 1000),
            'price' => $this->faker->randomFloat(8, 0.01, 100),
            'total_amount' => $this->faker->randomFloat(8, 0.01, 10000),
            'fee' => $this->faker->randomFloat(8, 0, 10),
            'payment_method' => $this->faker->randomElement(['card', 'bank_transfer', 'crypto']),
            'payment_reference' => 'ref_' . $this->faker->unique()->uuid(),
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(['status' => 'failed']);
    }

    public function rubDeposit(): static
    {
        return $this->state([
            'type' => 'deposit',
            'deposit_type' => 'rub',
            'token_id' => null,
        ]);
    }
}
```

## Запуск тестов

### Основные команды

```bash
# Запуск всех тестов
php artisan test

# Запуск конкретного теста
php artisan test --filter=TwoCanPaymentTest

# Запуск тестов из конкретного файла
php artisan test tests/Feature/TwoCanPaymentTest.php

# Запуск с покрытием кода
php artisan test --coverage

# Запуск с подробным выводом
php artisan test --verbose

# Запуск только неудачных тестов
php artisan test --repeat
```

### Настройка CI/CD

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v3

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: pdo, pdo_mysql, bcmath

    - name: Install dependencies
      run: composer install --no-progress --prefer-dist --optimize-autoloader

    - name: Copy environment file
      run: cp .env.ci .env

    - name: Generate key
      run: php artisan key:generate

    - name: Run migrations
      run: php artisan migrate --force

    - name: Run tests
      run: php artisan test --coverage
```

## Производительность тестов

### Оптимизация

```php
// Использование RefreshDatabase только когда нужно
class FastTest extends TestCase
{
    // Без RefreshDatabase для быстрого выполнения
    // Создаем данные вручную

    protected function setUp(): void
    {
        parent::setUp();

        // Быстрое создание тестовых данных
        User::create([...]);
    }
}
```

### Параллельное выполнение

```xml
<!-- phpunit.xml -->
<phpunit>
    <php>
        <env name="PARALLEL_TESTING" value="1"/>
    </php>
</phpunit>
```

```bash
# Запуск в несколько потоков
php artisan test --parallel
```

## Интеграционные тесты

### Тестирование с реальной БД

```php
class DatabaseIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Использовать реальную БД для интеграционных тестов
        config(['database.default' => 'mysql']);
    }

    /** @test */
    public function full_payment_flow_works()
    {
        // Создание пользователя
        $user = User::factory()->create();

        // Создание платежа
        $response = $this->actingAs($user)->post(route('client.balance.topup.submit'), [
            'amount' => 100.00,
            '_token' => csrf_token(),
        ]);

        // Проверка редиректа
        $response->assertRedirect();

        // Проверка создания транзакции в БД
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => 100.00,
            'status' => 'pending',
        ]);
    }
}
```

## E2E тестирование

### Laravel Dusk

```php
<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PaymentTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_make_payment()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/client/balance/topup')
                    ->type('amount', '100.00')
                    ->press('Пополнить')
                    ->assertPathIs('/client/payment/success');
        });
    }
}
```

## Отчеты о покрытии

### Генерация отчетов

```bash
# HTML отчет
php artisan test --coverage-html=reports/coverage

# XML отчет для CI
php artisan test --coverage-xml=reports/coverage.xml

# Минимальное покрытие
php artisan test --coverage --min=80
```

### Интерпретация покрытия

- **Statement coverage**: процент выполненных строк кода
- **Branch coverage**: процент выполненных ветвей условий
- **Function coverage**: процент вызванных функций
- **Line coverage**: процент покрытых строк

## Лучшие практики

### Структурирование тестов

```php
/** @test */
public function user_can_create_and_complete_payment_flow()
{
    // Arrange
    $user = User::factory()->create(['balance_rub' => 0]);

    // Act
    $response = $this->actingAs($user)->post(route('client.balance.topup.submit'), [
        'amount' => 100.00,
        '_token' => csrf_token(),
    ]);

    // Assert
    $response->assertRedirect();
    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'amount' => 100.00,
        'status' => 'pending',
    ]);

    // Simulate webhook
    $transaction = Transaction::where('user_id', $user->id)->first();
    $webhookResponse = $this->post(route('payment.webhook'), [
        'payment_id' => $transaction->payment_reference,
        'status' => 'success',
    ]);

    $webhookResponse->assertStatus(200);
    $this->assertEquals(100.00, $user->fresh()->balance_rub);
}
```

### Избегайте распространенных ошибок

```php
// ❌ Плохо: зависимость от внешних сервисов
/** @test */
public function payment_works_with_real_2can()
{
    // Не делать так в unit тестах!
}

// ✅ Хорошо: использование mock
/** @test */
public function payment_works_with_mocked_2can()
{
    Http::fake([...]);
    // Тест с mock
}
```

### Организация тестов

```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   └── RegisterTest.php
│   ├── Payments/
│   │   ├── TwoCanPaymentTest.php
│   │   └── CardTest.php
│   └── Api/
│       ├── UserApiTest.php
│       └── TransactionApiTest.php
├── Unit/
│   ├── Models/
│   │   ├── UserTest.php
│   │   └── TransactionTest.php
│   └── Services/
│       ├── TwoCanPaymentServiceTest.php
│       └── TwoCanCardServiceTest.php
```

## Мониторинг качества кода

### PHPStan

```bash
# Статический анализ
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse
```

### PHPMD

```bash
# Анализ качества кода
composer require --dev phpmd/phpmd
./vendor/bin/phpmd app text codesize,unusedcode,naming
```

### Интеграция с CI

```yaml
# CI pipeline с качеством кода
test:
  script:
    - php artisan test --coverage
    - ./vendor/bin/phpstan analyse
    - ./vendor/bin/phpmd app text codesize
  coverage: '/Lines\s*:\s*(\d+\.\d+)%/'
  only:
    - merge_requests
    - main
```
