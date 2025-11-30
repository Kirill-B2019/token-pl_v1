# 🔄 Миграции и обновления

Это руководство по управлению миграциями базы данных и обновлению системы.

## Основы миграций Laravel

### Создание миграции

```bash
# Создание миграции с таблицей
php artisan make:migration create_example_table

# Создание миграции для изменения таблицы
php artisan make:migration add_column_to_example_table --table=example

# Создание миграции с моделью
php artisan make:migration create_example_table --create=example
```

### Структура миграции

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('example', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Индексы
            $table->index('name');
            $table->unique(['name', 'price']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('example');
    }
};
```

## Миграции Токен Платформы

### Основные таблицы

#### 1. Users Table Migration
```php
// database/migrations/0001_01_01_000000_create_users_table.php
public function up(): void
{
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('phone')->nullable();
        $table->enum('role', ['client', 'broker', 'admin'])->default('client');
        $table->string('unique_id', 10)->unique();
        $table->boolean('two_factor_enabled')->default(false);
        $table->string('two_factor_secret')->nullable();
        $table->json('two_factor_recovery_codes')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamp('last_login_at')->nullable();
        $table->rememberToken();
        $table->timestamps();
    });
}
```

#### 2. Balance Rub Field Migration
```php
// database/migrations/2025_11_30_120043_add_balance_rub_to_users_table.php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->decimal('balance_rub', 15, 2)->default(0)->after('is_active');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('balance_rub');
    });
}
```

#### 3. Transactions Table Migration
```php
// database/migrations/2025_09_30_124000_create_transactions_table.php
public function up(): void
{
    Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        $table->string('transaction_id')->unique();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('token_id')->constrained()->onDelete('cascade');
        $table->enum('type', ['buy', 'sell', 'transfer', 'refund']);
        $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled']);
        $table->decimal('amount', 20, 8);
        $table->decimal('price', 20, 8);
        $table->decimal('total_amount', 20, 8);
        $table->decimal('fee', 20, 8)->default(0);
        $table->string('payment_method')->nullable();
        $table->string('payment_reference')->nullable();
        $table->json('metadata')->nullable();
        $table->timestamp('processed_at')->nullable();
        $table->timestamps();
    });
}
```

#### 4. Deposit Type Migration
```php
// database/migrations/2025_11_30_120628_add_deposit_type_field_to_transactions_table.php
public function up(): void
{
    Schema::table('transactions', function (Blueprint $table) {
        $table->enum('deposit_type', ['token', 'rub'])->nullable()->after('type');
    });
}

public function down(): void
{
    Schema::table('transactions', function (Blueprint $table) {
        $table->dropColumn('deposit_type');
    });
}
```

#### 5. Token ID Nullable Migration
```php
// database/migrations/2025_11_30_121236_make_token_id_nullable_in_transactions_table.php
public function up(): void
{
    Schema::table('transactions', function (Blueprint $table) {
        $table->foreignId('token_id')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('transactions', function (Blueprint $table) {
        $table->foreignId('token_id')->nullable(false)->change();
    });
}
```

#### 6. User Cards Table Migration
```php
// database/migrations/2025_11_30_122149_create_user_cards_table.php
public function up(): void
{
    Schema::create('user_cards', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('twocan_card_token')->unique();
        $table->string('card_mask');
        $table->string('card_brand')->nullable();
        $table->string('card_holder_name')->nullable();
        $table->tinyInteger('expiry_month');
        $table->smallInteger('expiry_year');
        $table->boolean('is_active')->default(true);
        $table->boolean('is_default')->default(false);
        $table->timestamps();

        $table->index(['user_id', 'is_active']);
        $table->index(['user_id', 'is_default']);
    });
}
```

## Управление миграциями

### Проверка статуса миграций

```bash
# Просмотр статуса всех миграций
php artisan migrate:status

# Вывод:
# +------+------------------------------------------------+-------+
# | Ran? | Migration                                      | Batch |
# +------+------------------------------------------------+-------+
# | Yes  | 0001_01_01_000000_create_users_table          | 1     |
# | Yes  | 2025_11_30_120043_add_balance_rub_to_users_table | 3     |
# | Yes  | 2025_11_30_122149_create_user_cards_table      | 3     |
# +------+------------------------------------------------+-------+
```

### Запуск миграций

```bash
# Запуск всех незавершенных миграций
php artisan migrate

# Запуск конкретной миграции
php artisan migrate --path=database/migrations/2025_11_30_122149_create_user_cards_table.php

# Имитация запуска (без изменений в БД)
php artisan migrate --pretend
```

### Откат миграций

```bash
# Откат последней миграции
php artisan migrate:rollback

# Откат нескольких миграций
php artisan migrate:rollback --step=3

# Откат до конкретной миграции
php artisan migrate:rollback --path=database/migrations/2025_11_30_122149_create_user_cards_table.php

# Полный сброс и повторный запуск
php artisan migrate:fresh
```

### Создание новых миграций

```bash
# Добавление поля в существующую таблицу
php artisan make:migration add_avatar_to_users_table --table=users

# Создание новой таблицы
php artisan make:migration create_user_profiles_table

# Изменение существующей таблицы
php artisan make:migration modify_users_table_add_indexes --table=users
```

## Расширенные миграции

### Миграции с данными (Data Migrations)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Заполнение начальными данными
        DB::table('tokens')->insert([
            [
                'symbol' => 'BTC',
                'name' => 'Bitcoin',
                'current_price' => 45000.00,
                'total_supply' => 21000000,
                'available_supply' => 19000000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'symbol' => 'ETH',
                'name' => 'Ethereum',
                'current_price' => 3000.00,
                'total_supply' => 120000000,
                'available_supply' => 120000000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Обновление существующих данных
        DB::table('users')->where('role', 'user')->update(['role' => 'client']);
    }

    public function down(): void
    {
        // Откат изменений данных
        DB::table('tokens')->whereIn('symbol', ['BTC', 'ETH'])->delete();
        DB::table('users')->where('role', 'client')->update(['role' => 'user']);
    }
};
```

### Миграции с внешними ключами

```php
<?php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Дополнительные индексы
            $table->index(['user_id', 'is_read']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
```

### Миграции с enum полями

```php
<?php

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Добавление значений в enum
            DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded')");
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Удаление значения из enum
            DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled')");
        });
    }
};
```

**⚠️ Внимание:** Изменение enum полей в MySQL может быть проблематичным. Рекомендуется использовать отдельные поля или lookup таблицы для сложных случаев.

## Сиды (Seeders)

### Создание сида

```bash
php artisan make:seeder UserSeeder
php artisan make:seeder TokenSeeder
php artisan make:seeder DatabaseSeeder
```

### Структура сида

```php
<?php

namespace Database\Seeders;

use App\Models\Token;
use Illuminate\Database\Seeder;

class TokenSeeder extends Seeder
{
    public function run(): void
    {
        $tokens = [
            [
                'symbol' => 'BTC',
                'name' => 'Bitcoin',
                'current_price' => 45000.00,
                'total_supply' => 21000000,
                'available_supply' => 19000000,
                'contract_address' => null,
                'decimals' => 8,
                'is_active' => true,
            ],
            [
                'symbol' => 'ETH',
                'name' => 'Ethereum',
                'current_price' => 3000.00,
                'total_supply' => 120000000,
                'available_supply' => 120000000,
                'contract_address' => '0x0000000000000000000000000000000000000000',
                'decimals' => 18,
                'is_active' => true,
            ],
        ];

        foreach ($tokens as $token) {
            Token::create($token);
        }
    }
}
```

### DatabaseSeeder

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TokenSeeder::class,
            UserSeeder::class,
            TransactionSeeder::class,
        ]);

        // Создание тестового администратора
        \App\Models\User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@token-platform.local',
            'role' => 'admin',
        ]);
    }
}
```

### Запуск сидов

```bash
# Запуск всех сидов
php artisan db:seed

# Запуск конкретного сида
php artisan db:seed --class=TokenSeeder

# Обновление данных (миграции + сиды)
php artisan migrate:fresh --seed
```

## Фабрики для тестирования

### Создание фабрики

```bash
php artisan make:factory UserFactory --model=User
php artisan make:factory TransactionFactory --model=Transaction
```

### Структура фабрики

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
            'role' => $this->faker->randomElement(['client', 'broker', 'admin']),
            'phone' => $this->faker->phoneNumber(),
            'unique_id' => $this->faker->unique()->randomNumber(8),
            'is_active' => true,
        ];
    }

    // Состояния фабрики
    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
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

### Использование фабрик

```php
// В тестах
$user = User::factory()->create();
$admin = User::factory()->admin()->create();

// В сидах
User::factory(10)->create();
User::factory(5)->admin()->create();
```

## Стратегия обновлений

### Версионирование

Система использует семантическое версионирование:
- **MAJOR.MINOR.PATCH** (например: 1.2.3)
- **MAJOR**: критические изменения API
- **MINOR**: новые функции (обратно совместимые)
- **PATCH**: исправления ошибок

### Процесс обновления

#### 1. Подготовка релиза

```bash
# Создание git тега
git tag -a v1.2.3 -m "Release version 1.2.3"

# Отправка тега
git push origin v1.2.3
```

#### 2. Миграции для обновления

```php
// database/migrations/2025_12_01_000000_add_new_feature_to_users.php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('avatar')->nullable();
        $table->json('preferences')->nullable();
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['avatar', 'preferences']);
    });
}
```

#### 3. Data migrations

```php
// database/migrations/2025_12_01_000001_migrate_user_preferences.php
public function up(): void
{
    // Миграция существующих данных
    User::chunk(100, function ($users) {
        foreach ($users as $user) {
            $user->update([
                'preferences' => [
                    'theme' => 'light',
                    'notifications' => true,
                    'language' => 'ru',
                ]
            ]);
        }
    });
}
```

#### 4. Post-deployment скрипты

```php
// app/Console/Commands/PostUpdateCommand.php
class PostUpdateCommand extends Command
{
    protected $signature = 'app:post-update {version}';
    protected $description = 'Run post-update tasks';

    public function handle(): void
    {
        $version = $this->argument('version');

        $this->info("Running post-update for version {$version}");

        // Очистка кешей
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        // Оптимизация
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        // Специфические действия для версии
        if (version_compare($version, '1.2.0', '>=')) {
            $this->updateUserPreferences();
        }

        if (version_compare($version, '1.3.0', '>=')) {
            $this->migrateCardData();
        }

        $this->info('Post-update completed successfully');
    }

    private function updateUserPreferences(): void
    {
        User::whereNull('preferences')->update([
            'preferences' => [
                'theme' => 'light',
                'notifications' => true,
                'language' => 'ru',
            ]
        ]);
    }

    private function migrateCardData(): void
    {
        // Миграция данных карт при обновлении
        UserCard::whereNull('card_brand')->each(function ($card) {
            $brand = $this->detectCardBrand($card->card_mask);
            $card->update(['card_brand' => $brand]);
        });
    }
}
```

### Резервное копирование перед обновлением

```bash
#!/bin/bash
# pre-update-backup.sh

VERSION=$1
BACKUP_DIR="/backups/pre-update-${VERSION}-$(date +%Y%m%d_%H%M%S)"

echo "Creating pre-update backup for version ${VERSION}"

# Создание директории
mkdir -p $BACKUP_DIR

# Бэкап базы данных
mysqldump -u username -p password token_platform > $BACKUP_DIR/database.sql

# Бэкап файлов
tar -czf $BACKUP_DIR/files.tar.gz \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    /var/www/token-platform

# Создание манифеста
cat > $BACKUP_DIR/manifest.txt << EOF
Version: ${VERSION}
Date: $(date)
Database: token_platform
Files: /var/www/token-platform
Backup location: $BACKUP_DIR
EOF

echo "Backup completed: $BACKUP_DIR"
```

### План отката

```bash
#!/bin/bash
# rollback.sh

VERSION=$1
ROLLBACK_TO=$2

echo "Rolling back from ${VERSION} to ${ROLLBACK_TO}"

# Остановка приложения
php artisan down --message="System rollback in progress"

# Восстановление бэкапа
if [ -d "/backups/pre-update-${ROLLBACK_TO}" ]; then
    echo "Restoring backup for version ${ROLLBACK_TO}"

    # Восстановление БД
    mysql -u username -p password token_platform < /backups/pre-update-${ROLLBACK_TO}/database.sql

    # Восстановление файлов
    cd /
    tar -xzf /backups/pre-update-${ROLLBACK_TO}/files.tar.gz

    echo "Rollback completed"
else
    echo "Backup not found for version ${ROLLBACK_TO}"
    exit 1
fi

# Очистка кешей
php artisan optimize:clear

# Запуск приложения
php artisan up
```

## Мониторинг миграций

### Отслеживание миграций в продакшене

```php
// app/Console/Commands/MonitorMigrations.php
class MonitorMigrations extends Command
{
    protected $signature = 'migrations:monitor';
    protected $description = 'Monitor migration status and report issues';

    public function handle(): void
    {
        $pendingMigrations = $this->getPendingMigrations();

        if (empty($pendingMigrations)) {
            $this->info('✅ All migrations are applied');
            return;
        }

        $this->error('❌ Pending migrations found:');
        foreach ($pendingMigrations as $migration) {
            $this->error("  - {$migration['migration']}");
        }

        // Отправка уведомления администраторам
        Notification::send(
            User::where('role', 'admin')->get(),
            new MigrationAlert($pendingMigrations)
        );

        return 1;
    }

    private function getPendingMigrations(): array
    {
        $migrationsPath = database_path('migrations');
        $migrated = collect(DB::table('migrations')->pluck('migration'))->toArray();

        $pending = [];
        foreach (glob("{$migrationsPath}/*.php") as $file) {
            $migrationName = basename($file, '.php');
            if (!in_array($migrationName, $migrated)) {
                $pending[] = [
                    'migration' => $migrationName,
                    'file' => $file,
                ];
            }
        }

        return $pending;
    }
}
```

### Автоматическое применение миграций

```bash
# В CI/CD pipeline
#!/bin/bash

echo "Running migrations..."

# Проверка на pending миграции
php artisan migrate:status --pending

if [ $? -eq 0 ]; then
    echo "No pending migrations"
else
    echo "Applying migrations..."
    php artisan migrate --force

    if [ $? -eq 0 ]; then
        echo "Migrations applied successfully"
    else
        echo "Migration failed!"
        exit 1
    fi
fi

# Запуск сидов (только при первой установке)
if [ "$FIRST_DEPLOY" = "true" ]; then
    php artisan db:seed --force
fi
```

## Лучшие практики

### Правила именования миграций

```bash
# Хорошо
php artisan make:migration add_avatar_column_to_users_table --table=users
php artisan make:migration create_user_notifications_table
php artisan make:migration add_foreign_key_to_posts_table --table=posts

# Плохо
php artisan make:migration update_users
php artisan make:migration fix_bug
php artisan make:migration new_stuff
```

### Тестирование миграций

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function migrations_can_run_successfully()
    {
        // Этот тест проходит если RefreshDatabase работает
        $this->assertTrue(true);
    }

    /** @test */
    public function required_tables_exist()
    {
        $tables = [
            'users',
            'transactions',
            'user_cards',
            'tokens',
            'user_balances',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} does not exist");
        }
    }

    /** @test */
    public function required_columns_exist()
    {
        $this->assertTrue(Schema::hasColumn('users', 'balance_rub'));
        $this->assertTrue(Schema::hasColumn('transactions', 'deposit_type'));
        $this->assertTrue(Schema::hasColumn('user_cards', 'twocan_card_token'));
    }
}
```

### Документирование изменений

```markdown
# Release Notes - v1.2.3

## Новые функции
- Добавлена поддержка привязки банковских карт
- Новый API для управления картами

## Изменения в БД
- Добавлена таблица `user_cards`
- Добавлено поле `balance_rub` в таблицу `users`
- Добавлено поле `deposit_type` в таблицу `transactions`

## Миграции
- `2025_11_30_122149_create_user_cards_table.php`
- `2025_11_30_120043_add_balance_rub_to_users_table.php`
- `2025_11_30_120628_add_deposit_type_field_to_transactions_table.php`

## Обновление
```bash
php artisan migrate
php artisan optimize
```

## Откат (при необходимости)
```bash
php artisan migrate:rollback --step=3
```
```

## Заключение

Правильное управление миграциями критически важно для:

- **Целостности данных**: безопасные изменения схемы БД
- **Версионирования**: отслеживание изменений в коде и данных
- **Командной работы**: синхронизация изменений между разработчиками
- **Продакшена**: безопасные обновления без простоев

Всегда:
1. Тестируйте миграции перед запуском в продакшен
2. Создавайте резервные копии перед применением миграций
3. Документируйте изменения
4. Используйте осмысленные имена миграций
5. Пишите обратные миграции (down методы)
