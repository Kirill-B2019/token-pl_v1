# ❗ Устранение неисправностей

Это руководство по диагностике и устранению распространенных проблем в Токен Платформе.

## Диагностика проблем

### Проверка состояния системы

```bash
# Общий health check
php artisan tinker --execute="
echo 'PHP Version: ' . PHP_VERSION . PHP_EOL;
echo 'Laravel Version: ' . app()->version() . PHP_EOL;
echo 'Database: ' . (DB::connection()->getPdo() ? 'OK' : 'ERROR') . PHP_EOL;
echo 'Redis: ' . (Redis::ping() ? 'OK' : 'ERROR') . PHP_EOL;
echo 'Storage: ' . (is_writable(storage_path()) ? 'OK' : 'ERROR') . PHP_EOL;
"
```

### Проверка логов

```bash
# Просмотр последних ошибок
tail -f storage/logs/laravel.log

# Поиск ошибок по дате
grep "ERROR\|CRITICAL" storage/logs/laravel-$(date +%Y-%m-%d).log

# Поиск по конкретной ошибке
grep "MethodNotAllowedHttpException" storage/logs/laravel.log
```

### Проверка базы данных

```bash
# Проверка подключения
php artisan tinker --execute="DB::connection()->getPdo()"

# Проверка миграций
php artisan migrate:status

# Проверка размера таблиц
php artisan tinker --execute="
$tables = ['users', 'transactions', 'user_cards', 'user_balances'];
foreach ($tables as $table) {
    $count = DB::table(\$table)->count();
    echo \"\$table: \$count records\" . PHP_EOL;
}
"
```

## Распространенные проблемы

### 1. Ошибка 500 Internal Server Error

#### Симптомы
- Страница возвращает HTTP 500
- В логах: `Symfony\Component\Debug\Exception\FatalErrorException`

#### Диагностика

```bash
# Проверка прав доступа
ls -la storage/
ls -la bootstrap/cache/

# Проверка конфигурации
php artisan config:cache
php artisan config:clear

# Проверка PHP расширений
php -m | grep -E "(pdo|mbstring|openssl|tokenizer)"
```

#### Решение

```bash
# Исправление прав доступа
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data bootstrap/cache/
sudo chmod -R 755 storage/
sudo chmod -R 755 bootstrap/cache/

# Очистка кеша
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Перезапуск PHP-FPM
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

### 2. Ошибка подключения к базе данных

#### Симптомы
- `PDOException: SQLSTATE[HY000] [2002] Connection refused`
- Приложение не может загрузить данные

#### Диагностика

```bash
# Проверка настроек БД
php artisan tinker --execute="config('database.connections.mysql')"

# Тест подключения
mysql -h 127.0.0.1 -u username -p database_name -e "SELECT 1"

# Проверка статуса MySQL
sudo systemctl status mysql

# Проверка порта
netstat -tlnp | grep 3306
```

#### Решение

```bash
# Перезапуск MySQL
sudo systemctl restart mysql

# Проверка конфигурации
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
# bind-address = 127.0.0.1

# Создание пользователя БД
mysql -u root -p
CREATE USER 'platform_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON token_platform.* TO 'platform_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Ошибки платежей 2can

#### Симптомы
- Платежи не обрабатываются
- Webhook не работает
- Ошибки в логах платежей

#### Диагностика

```bash
# Проверка настроек 2can
php artisan tinker --execute="config('twocan')"

# Тест API подключения
curl -X GET "https://2can.ru/api/v1/test" \
  -H "Authorization: Basic $(echo -n 'SHOP_ID:SECRET_KEY' | base64)"

# Проверка webhook URL
curl -X POST https://cardfly.online/client/payment/webhook \
  -H "Content-Type: application/json" \
  -d '{"payment_id":"test","status":"success"}'
```

#### Решение

```bash
# Обновление настроек 2can
php artisan config:cache

# Проверка webhook в 2can личном кабинете
# URL: https://cardfly.online/client/payment/webhook

# Тест webhook локально
php artisan tinker
$service = app(App\Services\TwoCanPaymentService::class);
$user = App\Models\User::first();
$result = $service->createPayment($user, 100.00);
dd($result);
```

### 4. Проблемы с картами

#### Симптомы
- Карты не привязываются
- Ошибки токенизации
- Платежи по картам не работают

#### Диагностика

```bash
# Проверка сервиса карт
php artisan tinker
$service = app(App\Services\TwoCanCardService::class);
$user = App\Models\User::first();
$result = $service->tokenizeCard($user, [
    'number' => '4111111111111111',
    'expiry' => '12/25',
    'cvv' => '123',
    'holder' => 'TEST USER'
]);
dd($result);

# Проверка таблицы карт
php artisan tinker --execute="App\Models\UserCard::count()"
```

#### Решение

```bash
# Очистка кеша
php artisan cache:clear

# Проверка таблицы user_cards
php artisan migrate:status

# Создание тестовой карты вручную
php artisan tinker
App\Models\UserCard::create([
    'user_id' => 1,
    'twocan_card_token' => 'test_token_' . time(),
    'card_mask' => '411111******1111',
    'card_brand' => 'Visa',
    'expiry_month' => 12,
    'expiry_year' => 2025,
    'is_active' => true,
    'is_default' => true,
]);
```

### 5. Ошибки очередей

#### Симптомы
- Задачи не выполняются
- Растет очередь failed jobs
- Email не отправляются

#### Диагностика

```bash
# Проверка статуса очередей
php artisan queue:status

# Просмотр неудачных задач
php artisan queue:failed

# Проверка воркеров
ps aux | grep "queue:work"

# Проверка логов очередей
tail -f storage/logs/laravel.log | grep -i queue
```

#### Решение

```bash
# Перезапуск воркеров очередей
php artisan queue:restart

# Очистка неудачных задач
php artisan queue:flush

# Запуск воркера вручную для тестирования
php artisan queue:work --once --verbose

# Проверка конфигурации очереди
php artisan config:show queue
```

### 6. Проблемы с аутентификацией

#### Симптомы
- Пользователи не могут войти
- Ошибки токенов
- Проблемы с 2FA

#### Диагностика

```bash
# Проверка пользователей
php artisan tinker --execute="App\Models\User::count()"

# Проверка токенов
php artisan tinker --execute="DB::table('personal_access_tokens')->count()"

# Тест входа
php artisan tinker
$user = App\Models\User::first();
auth()->login($user);
dd(auth()->check());
```

#### Решение

```bash
# Очистка сессий
php artisan session:clear

# Очистка токенов (осторожно!)
php artisan tinker --execute="DB::table('personal_access_tokens')->delete()"

# Проверка конфигурации сессий
php artisan config:show session

# Тест создания пользователя
php artisan tinker
App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => Hash::make('password'),
    'role' => 'client'
]);
```

### 7. Проблемы производительности

#### Симптомы
- Медленная загрузка страниц
- Высокое использование CPU/памяти
- Таймауты запросов

#### Диагностика

```bash
# Проверка производительности
php artisan tinker --execute="
// Memory usage
echo 'Memory: ' . memory_get_peak_usage(true) / 1024 / 1024 . ' MB' . PHP_EOL;

// Database queries
DB::listen(function (\$query) {
    if (\$query->time > 100) {
        echo 'Slow query: ' . \$query->sql . ' (' . \$query->time . 'ms)' . PHP_EOL;
    }
});

// Cache status
echo 'Cache: ' . (Cache::store()->getStore() ? 'OK' : 'ERROR') . PHP_EOL;
"
```

#### Решение

```bash
# Оптимизация
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Очистка кеша
php artisan cache:clear
php artisan config:clear

# Проверка медленных запросов
php artisan tinker
$queries = DB::select('
    SELECT sql_text, exec_count, avg_timer_wait/1000000000 avg_time
    FROM performance_schema.events_statements_summary_by_digest
    ORDER BY avg_timer_wait DESC LIMIT 10
');
dd($queries);

# Оптимизация БД
php artisan tinker --execute="
DB::statement('ANALYZE TABLE users, transactions, user_cards, user_balances');
"
```

### 8. Проблемы с фронтендом

#### Симптомы
- Ошибки JavaScript
- Стили не загружаются
- Vue.js компоненты не работают

#### Диагностика

```bash
# Проверка сборки
npm run dev

# Проверка файлов
ls -la public/build/
ls -la public/css/
ls -la public/js/

# Проверка консоли браузера
# Network tab, Console tab
```

#### Решение

```bash
# Пересборка ассетов
npm install
npm run build

# Очистка кеша браузера
# Ctrl+F5 или Cmd+Shift+R

# Проверка конфигурации Vite
cat vite.config.js

# Проверка Laravel Mix (если используется)
npm run production
```

## Инструменты диагностики

### Laravel Debugbar

```bash
# Установка
composer require barryvdh/laravel-debugbar --dev

# Включение в .env
DEBUGBAR_ENABLED=true
```

### Laravel Telescope

```bash
# Установка
composer require laravel/telescope
php artisan telescope:install
php artisan migrate

# Доступ: /telescope
```

### Database Query Analyzer

```php
// В AppServiceProvider
if (app()->environment('local')) {
    DB::listen(function ($query) {
        if ($query->time > 500) {
            logger()->warning('Slow query', [
                'sql' => $query->sql,
                'time' => $query->time . 'ms',
                'bindings' => $query->bindings,
            ]);
        }
    });
}
```

### Memory Profiler

```php
// Проверка утечек памяти
class MemoryProfiler
{
    public static function profile(callable $callback): array
    {
        $startMemory = memory_get_usage();
        $startTime = microtime(true);

        $result = $callback();

        $endMemory = memory_get_usage();
        $endTime = microtime(true);

        return [
            'memory_used' => ($endMemory - $startMemory) / 1024 / 1024, // MB
            'time_taken' => ($endTime - $startTime) * 1000, // ms
            'result' => $result,
        ];
    }
}

// Использование
$profile = MemoryProfiler::profile(function () {
    // Ваш код
    return User::all();
});

dd($profile);
```

## Автоматизированная диагностика

### Health Check Endpoint

```php
// routes/web.php
Route::get('/health', function () {
    $checks = [];

    // Database
    try {
        DB::connection()->getPdo();
        $checks['database'] = 'OK';
    } catch (\Exception $e) {
        $checks['database'] = 'ERROR: ' . $e->getMessage();
    }

    // Redis
    try {
        Redis::ping();
        $checks['redis'] = 'OK';
    } catch (\Exception $e) {
        $checks['redis'] = 'ERROR: ' . $e->getMessage();
    }

    // Storage
    $checks['storage'] = is_writable(storage_path()) ? 'OK' : 'ERROR: Not writable';

    // External services
    try {
        $response = Http::timeout(5)->get('https://2can.ru/api/v1/test');
        $checks['2can_api'] = $response->successful() ? 'OK' : 'ERROR: HTTP ' . $response->status();
    } catch (\Exception $e) {
        $checks['2can_api'] = 'ERROR: ' . $e->getMessage();
    }

    $status = collect($checks)->contains(function ($check) {
        return str_starts_with($check, 'ERROR');
    }) ? 500 : 200;

    return response()->json([
        'status' => $status === 200 ? 'healthy' : 'unhealthy',
        'checks' => $checks,
        'timestamp' => now(),
    ], $status);
});
```

### Diagnostic Command

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class DiagnosticCommand extends Command
{
    protected $signature = 'diagnose {--fix : Attempt to fix issues}';
    protected $description = 'Run system diagnostics';

    public function handle()
    {
        $this->info('🔍 Running system diagnostics...');

        $issues = [];
        $fixes = [];

        // Check database
        try {
            DB::connection()->getPdo();
            $this->info('✅ Database: OK');
        } catch (\Exception $e) {
            $issues[] = 'Database connection failed: ' . $e->getMessage();
            $fixes[] = 'Check database configuration and ensure MySQL is running';
        }

        // Check Redis
        try {
            Redis::ping();
            $this->info('✅ Redis: OK');
        } catch (\Exception $e) {
            $issues[] = 'Redis connection failed: ' . $e->getMessage();
            $fixes[] = 'Check Redis configuration and ensure Redis is running';
        }

        // Check storage permissions
        if (!is_writable(storage_path())) {
            $issues[] = 'Storage directory is not writable';
            if ($this->option('fix')) {
                chmod(storage_path(), 0755);
                $this->info('🔧 Fixed storage permissions');
            } else {
                $fixes[] = 'Run: chmod -R 755 storage/';
            }
        } else {
            $this->info('✅ Storage: OK');
        }

        // Check migrations
        $pendingMigrations = $this->callSilent('migrate:status');
        if ($pendingMigrations !== 0) {
            $issues[] = 'Pending migrations found';
            $fixes[] = 'Run: php artisan migrate';
        } else {
            $this->info('✅ Migrations: OK');
        }

        // Report issues
        if (empty($issues)) {
            $this->info('🎉 All systems operational!');
        } else {
            $this->error('❌ Issues found:');
            foreach ($issues as $issue) {
                $this->error('  - ' . $issue);
            }

            $this->info('💡 Suggested fixes:');
            foreach ($fixes as $fix) {
                $this->info('  - ' . $fix);
            }
        }

        return empty($issues) ? 0 : 1;
    }
}
```

## Профилактика

### Регулярные проверки

```bash
# Ежедневные проверки (cron)
0 2 * * * /usr/local/bin/daily-checks.sh

# Содержимое daily-checks.sh
#!/bin/bash
cd /var/www/token-platform

# Health check
curl -f http://localhost/health > /dev/null
if [ $? -ne 0 ]; then
    echo "Health check failed" | mail -s "System Alert" admin@domain.com
fi

# Disk space check
DISK_USAGE=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 90 ]; then
    echo "Disk usage is ${DISK_USAGE}%" | mail -s "Disk Alert" admin@domain.com
fi

# Log rotation
find storage/logs -name "*.log" -mtime +30 -delete
```

### Мониторинг ресурсов

```bash
# Проверка использования ресурсов
#!/bin/bash
echo "=== System Resources ==="
echo "CPU Usage: $(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1"%"}')"
echo "Memory Usage: $(free | grep Mem | awk '{printf "%.2f%%", $3/$2 * 100.0}')"
echo "Disk Usage: $(df / | tail -1 | awk '{print $5}')"

echo ""
echo "=== Laravel Processes ==="
ps aux | grep -E "(php|artisan|queue)" | grep -v grep

echo ""
echo "=== Database Connections ==="
mysql -u root -p -e "SHOW PROCESSLIST;" 2>/dev/null | wc -l
```

## Экстренные ситуации

### Полная остановка системы

```bash
# Остановка всех сервисов
sudo systemctl stop nginx
sudo systemctl stop php8.2-fpm
sudo systemctl stop mysql
sudo systemctl stop redis

# Остановка Laravel очередей
php artisan queue:restart
```

### Восстановление из бэкапа

```bash
# Восстановление БД
mysql -u username -p database_name < backup.sql

# Восстановление файлов
tar -xzf backup.tar.gz -C /var/www/

# Очистка кеша
php artisan optimize:clear
```

### Режим обслуживания

```bash
# Включение режима обслуживания
php artisan down --message="Technical maintenance in progress"

# Отключение режима обслуживания
php artisan up
```

## Контакты поддержки

- **Документация**: [docs.token-platform.local](https://docs.token-platform.local)
- **GitHub Issues**: [Создать issue](https://github.com/your-org/token-platform/issues)
- **Email**: support@token-platform.local
- **Slack**: #support (для команды разработчиков)
