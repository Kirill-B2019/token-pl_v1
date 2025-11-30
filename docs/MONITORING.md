# 📊 Мониторинг и логирование

Это руководство по мониторингу и логированию Токен Платформы.

## Обзор системы мониторинга

### Цели мониторинга

- **Производительность**: отслеживание скорости работы системы
- **Доступность**: обеспечение бесперебойной работы сервисов
- **Безопасность**: обнаружение подозрительной активности
- **Пользовательский опыт**: качество обслуживания пользователей

### Компоненты мониторинга

- **Метрики приложения**: Laravel Telescope, Prometheus
- **Инфраструктура**: серверные метрики, базы данных
- **Логи**: централизованное хранение и анализ
- **Алерты**: автоматические уведомления о проблемах

## Laravel Telescope

### Установка и настройка

```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

### Конфигурация

```php
// config/telescope.php
'watchers' => [
    Watchers\QueryWatcher::class => env('TELESCOPE_QUERY_WATCHER', true),
    Watchers\RequestWatcher::class => env('TELESCOPE_REQUEST_WATCHER', true),
    Watchers\CommandWatcher::class => env('TELESCOPE_COMMAND_WATCHER', true),
    Watchers\JobWatcher::class => env('TELESCOPE_JOB_WATCHER', true),
    Watchers\ExceptionWatcher::class => env('TELESCOPE_EXCEPTION_WATCHER', true),
    Watchers\LogWatcher::class => env('TELESCOPE_LOG_WATCHER', true),
    Watchers\DumpWatcher::class => env('TELESCOPE_DUMP_WATCHER', true),
],
```

### Доступ к Telescope

```
/telescope - веб-интерфейс для просмотра метрик
```

## Метрики производительности

### Response Time

```php
// Middleware для измерения времени ответа
class ResponseTimeMiddleware
{
    public function handle($request, Closure $next)
    {
        $start = microtime(true);

        $response = $next($request);

        $end = microtime(true);
        $duration = ($end - $start) * 1000; // в миллисекундах

        Log::info('Request completed', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'duration_ms' => round($duration, 2),
            'status' => $response->status(),
            'user_id' => auth()->id(),
        ]);

        return $response;
    }
}
```

### Database Queries

```php
// Мониторинг медленных запросов
DB::listen(function ($query) {
    if ($query->time > 1000) { // > 1 секунды
        Log::warning('Slow query detected', [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time_ms' => $query->time,
        ]);
    }
});
```

### Memory Usage

```php
// Мониторинг использования памяти
class MemoryMonitorMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $memoryUsage = memory_get_peak_usage(true) / 1024 / 1024; // MB

        if ($memoryUsage > 128) { // > 128 MB
            Log::warning('High memory usage detected', [
                'url' => $request->fullUrl(),
                'memory_mb' => round($memoryUsage, 2),
                'user_id' => auth()->id(),
            ]);
        }

        return $response;
    }
}
```

## Логирование

### Конфигурация логов

```php
// config/logging.php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack', 'papertrail'],
    ],

    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],

    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'critical',
    ],

    'papertrail' => [
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => \Monolog\Handler\SyslogUdpHandler::class,
        'handler_with' => [
            'host' => env('PAPERTRAIL_HOST'),
            'port' => env('PAPERTRAIL_PORT'),
        ],
    ],
],
```

### Структурированное логирование

```php
// Логирование платежных операций
Log::info('Payment processed', [
    'user_id' => $user->id,
    'transaction_id' => $transaction->id,
    'amount' => $transaction->amount,
    'payment_system' => '2can',
    'status' => $transaction->status,
    'processing_time_ms' => $processingTime,
]);

// Логирование ошибок аутентификации
Log::warning('Failed login attempt', [
    'email' => $request->input('email'),
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'attempts_count' => $failedAttempts,
]);

// Логирование системных событий
Log::error('Database connection failed', [
    'error' => $exception->getMessage(),
    'connection' => config('database.default'),
    'trace' => $exception->getTraceAsString(),
]);
```

### Аудит логов

```php
// Модель для аудита действий
class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}

// Observer для автоматического логирования
class UserObserver
{
    public function updated(User $user)
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'model_type' => User::class,
            'model_id' => $user->id,
            'old_values' => $user->getOriginal(),
            'new_values' => $user->getAttributes(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
```

## Prometheus + Grafana

### Установка Prometheus

```yaml
# docker-compose.yml
version: '3.8'
services:
  prometheus:
    image: prom/prometheus:latest
    ports:
      - "9090:9090"
    volumes:
      - ./monitoring/prometheus.yml:/etc/prometheus/prometheus.yml
    command:
      - '--config.file=/etc/prometheus/prometheus.yml'

  grafana:
    image: grafana/grafana:latest
    ports:
      - "3000:3000"
    environment:
      - GF_SECURITY_ADMIN_PASSWORD=admin
    volumes:
      - grafana_data:/var/lib/grafana

volumes:
  grafana_data:
```

### Конфигурация Prometheus

```yaml
# monitoring/prometheus.yml
global:
  scrape_interval: 15s

scrape_configs:
  - job_name: 'laravel-app'
    static_configs:
      - targets: ['app:8000']

  - job_name: 'nginx'
    static_configs:
      - targets: ['nginx:80']

  - job_name: 'mysql'
    static_configs:
      - targets: ['mysql:3306']
```

### Laravel Exporter для Prometheus

```php
// routes/web.php
Route::get('/metrics', function () {
    $registry = new \Prometheus\Registry(new \Prometheus\Storage\InMemory());

    // HTTP requests counter
    $counter = $registry->getOrRegisterCounter(
        'laravel',
        'http_requests_total',
        'Total HTTP requests',
        ['method', 'endpoint', 'status']
    );

    // Database connections gauge
    $gauge = $registry->getOrRegisterGauge(
        'laravel',
        'db_connections_active',
        'Active database connections'
    );

    // Business metrics
    $usersGauge = $registry->getOrRegisterGauge(
        'laravel',
        'users_total',
        'Total registered users'
    );
    $usersGauge->set(\App\Models\User::count());

    $transactionsCounter = $registry->getOrRegisterCounter(
        'laravel',
        'transactions_total',
        'Total transactions by status',
        ['status']
    );

    foreach (\App\Models\Transaction::selectRaw('status, count(*) as count')->groupBy('status')->get() as $stat) {
        $transactionsCounter->set($stat->count, [$stat->status]);
    }

    $renderer = new \Prometheus\RenderTextFormat();
    return response($renderer->render($registry->getMetricFamilySamples()))
           ->header('Content-Type', 'text/plain; charset=utf-8');
});
```

### Дашборды Grafana

#### Основные метрики

```json
{
  "dashboard": {
    "title": "Laravel Application Metrics",
    "tags": ["laravel", "php"],
    "panels": [
      {
        "title": "HTTP Requests Rate",
        "type": "graph",
        "targets": [
          {
            "expr": "rate(laravel_http_requests_total[5m])",
            "legendFormat": "{{method}} {{endpoint}}"
          }
        ]
      },
      {
        "title": "Response Time",
        "type": "graph",
        "targets": [
          {
            "expr": "histogram_quantile(0.95, rate(laravel_http_request_duration_seconds_bucket[5m]))",
            "legendFormat": "95th percentile"
          }
        ]
      },
      {
        "title": "Active Database Connections",
        "type": "graph",
        "targets": [
          {
            "expr": "laravel_db_connections_active",
            "legendFormat": "Active connections"
          }
        ]
      }
    ]
  }
}
```

## ELK Stack (Elasticsearch, Logstash, Kibana)

### Установка ELK

```yaml
# docker-compose.elk.yml
version: '3.8'
services:
  elasticsearch:
    image: elasticsearch:8.6.0
    environment:
      - discovery.type=single-node
      - xpack.security.enabled=false
    ports:
      - "9200:9200"
    volumes:
      - elasticsearch_data:/usr/share/elasticsearch/data

  logstash:
    image: logstash:8.6.0
    volumes:
      - ./monitoring/logstash.conf:/usr/share/logstash/pipeline/logstash.conf
    ports:
      - "5044:5044"
    depends_on:
      - elasticsearch

  kibana:
    image: kibana:8.6.0
    ports:
      - "5601:5601"
    depends_on:
      - elasticsearch

volumes:
  elasticsearch_data:
```

### Конфигурация Logstash

```conf
# monitoring/logstash.conf
input {
  file {
    path => "/var/log/laravel/*.log"
    start_position => "beginning"
  }

  tcp {
    port => 5044
    codec => json
  }
}

filter {
  grok {
    match => { "message" => "%{TIMESTAMP_ISO8601:timestamp} %{WORD:level} %{GREEDYDATA:message}" }
  }

  json {
    source => "message"
    target => "parsed_json"
  }

  mutate {
    add_field => {
      "service" => "laravel-app"
      "environment" => "production"
    }
  }
}

output {
  elasticsearch {
    hosts => ["elasticsearch:9200"]
    index => "laravel-%{+YYYY.MM.dd}"
  }
}
```

### Настройка Laravel для отправки логов в Logstash

```php
// config/logging.php
'logstash' => [
    'driver' => 'monolog',
    'handler' => \Monolog\Handler\SocketHandler::class,
    'handler_with' => [
        'connectionString' => env('LOGSTASH_HOST', 'tcp://logstash:5044'),
        'persistent' => true,
    ],
    'formatter' => \Monolog\Formatter\JsonFormatter::class,
],
```

## Алерты и уведомления

### Laravel Notifications

```php
// Уведомление о системных ошибках
class SystemErrorNotification extends Notification
{
    public function __construct(public string $error, public string $trace) {}

    public function via($notifiable): array
    {
        return ['mail', 'slack', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('System Error Alert')
            ->line('A critical error occurred:')
            ->line($this->error)
            ->action('View Details', url('/admin/errors'));
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->error()
            ->content('🚨 System Error')
            ->attachment(function ($attachment) {
                $attachment->title('Error Details')
                          ->content($this->error);
            });
    }
}
```

### Автоматические алерты

```php
// Проверка здоровья системы
class HealthCheckService
{
    public function checkSystemHealth(): array
    {
        $issues = [];

        // Проверка БД
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $issues[] = 'Database connection failed: ' . $e->getMessage();
        }

        // Проверка Redis
        try {
            Redis::ping();
        } catch (\Exception $e) {
            $issues[] = 'Redis connection failed: ' . $e->getMessage();
        }

        // Проверка внешних сервисов
        if (!$this->checkExternalService('https://2can.ru/api/v1/health')) {
            $issues[] = '2can API is not responding';
        }

        // Проверка места на диске
        $freeSpace = disk_free_space('/') / 1024 / 1024 / 1024; // GB
        if ($freeSpace < 1) {
            $issues[] = 'Low disk space: ' . round($freeSpace, 2) . ' GB remaining';
        }

        return $issues;
    }

    protected function checkExternalService(string $url): bool
    {
        try {
            $response = Http::timeout(5)->get($url);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
```

### Мониторинг очередей

```php
// Проверка очередей
class QueueMonitorService
{
    public function checkQueueHealth(): array
    {
        $issues = [];

        // Проверка размера очередей
        $pendingJobs = DB::table('jobs')->count();
        if ($pendingJobs > 1000) {
            $issues[] = "High queue backlog: {$pendingJobs} pending jobs";
        }

        // Проверка неудачных job'ов
        $failedJobs = DB::table('failed_jobs')->count();
        if ($failedJobs > 50) {
            $issues[] = "High failed jobs count: {$failedJobs}";
        }

        // Проверка воркеров
        $activeWorkers = $this->getActiveQueueWorkers();
        if ($activeWorkers === 0) {
            $issues[] = 'No active queue workers';
        }

        return $issues;
    }

    protected function getActiveQueueWorkers(): int
    {
        // Проверка процессов queue:work
        $output = shell_exec('pgrep -f "queue:work" | wc -l');
        return (int) trim($output);
    }
}
```

## Мониторинг безопасности

### Обнаружение вторжений

```php
class SecurityMonitorService
{
    public function detectSuspiciousActivity(): array
    {
        $alerts = [];

        // Проверка неудачных входов
        $failedLogins = DB::table('audit_logs')
            ->where('action', 'failed_login')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($failedLogins > 10) {
            $alerts[] = "High number of failed logins: {$failedLogins} in last hour";
        }

        // Проверка подозрительных IP
        $suspiciousIPs = DB::table('audit_logs')
            ->select('ip_address', DB::raw('count(*) as attempts'))
            ->where('action', 'failed_login')
            ->where('created_at', '>=', now()->subDay())
            ->groupBy('ip_address')
            ->having('attempts', '>', 5)
            ->get();

        foreach ($suspiciousIPs as $ip) {
            $alerts[] = "Suspicious IP: {$ip->ip_address} ({$ip->attempts} failed attempts)";
        }

        // Проверка необычной активности
        $unusualActivity = $this->detectUnusualActivity();

        return array_merge($alerts, $unusualActivity);
    }

    protected function detectUnusualActivity(): array
    {
        $alerts = [];

        // Проверка большого количества транзакций с одного IP
        $highVolumeIPs = DB::table('transactions')
            ->join('audit_logs', 'transactions.user_id', '=', 'audit_logs.user_id')
            ->select('audit_logs.ip_address', DB::raw('count(*) as transactions'))
            ->where('transactions.created_at', '>=', now()->subHour())
            ->groupBy('audit_logs.ip_address')
            ->having('transactions', '>', 50)
            ->get();

        foreach ($highVolumeIPs as $ip) {
            $alerts[] = "High transaction volume from IP: {$ip->ip_address} ({$ip->transactions} transactions/hour)";
        }

        return $alerts;
    }
}
```

### Регулярные аудиты

```php
class AuditService
{
    public function performSecurityAudit(): array
    {
        $findings = [];

        // Проверка устаревших паролей
        $oldPasswords = User::where('password_updated_at', '<', now()->subDays(90))->count();
        if ($oldPasswords > 0) {
            $findings[] = "{$oldPasswords} users have passwords older than 90 days";
        }

        // Проверка неактивных пользователей
        $inactiveUsers = User::where('last_login_at', '<', now()->subDays(365))->count();
        if ($inactiveUsers > 0) {
            $findings[] = "{$inactiveUsers} users inactive for more than a year";
        }

        // Проверка слабых паролей (демонстрация)
        $weakPasswords = User::whereRaw('LENGTH(password) < 60')->count(); // bcrypt min length
        if ($weakPasswords > 0) {
            $findings[] = "{$weakPasswords} users may have weak passwords";
        }

        // Проверка истекших карт
        $expiredCards = UserCard::where(function ($query) {
            $query->where('expiry_year', '<', date('Y'))
                  ->orWhere(function ($q) {
                      $q->where('expiry_year', '=', date('Y'))
                        ->where('expiry_month', '<', date('m'));
                  });
        })->count();

        if ($expiredCards > 0) {
            $findings[] = "{$expiredCards} expired cards found";
        }

        return $findings;
    }
}
```

## Производительность

### Кеширование метрик

```php
class MetricsCacheService
{
    public function getCachedMetrics(): array
    {
        return Cache::remember('system_metrics', 300, function () { // 5 minutes
            return [
                'total_users' => User::count(),
                'active_users_today' => User::where('last_login_at', '>=', today())->count(),
                'total_transactions' => Transaction::count(),
                'pending_transactions' => Transaction::where('status', 'pending')->count(),
                'system_health' => $this->getSystemHealth(),
                'db_connections' => $this->getDatabaseConnections(),
            ];
        });
    }

    protected function getSystemHealth(): array
    {
        return [
            'cpu_usage' => sys_getloadavg()[0],
            'memory_usage' => memory_get_peak_usage(true) / 1024 / 1024, // MB
            'disk_free' => disk_free_space('/') / 1024 / 1024 / 1024, // GB
        ];
    }

    protected function getDatabaseConnections(): array
    {
        $connections = DB::select('SHOW PROCESSLIST');

        return [
            'total' => count($connections),
            'active' => count(array_filter($connections, fn($conn) => $conn->Command !== 'Sleep')),
        ];
    }
}
```

## Отчеты и дашборды

### Ежедневные отчеты

```php
class DailyReportService
{
    public function generateDailyReport(): array
    {
        $yesterday = now()->subDay();

        return [
            'date' => $yesterday->format('Y-m-d'),
            'new_users' => User::whereDate('created_at', $yesterday)->count(),
            'total_users' => User::count(),
            'transactions' => [
                'total' => Transaction::whereDate('created_at', $yesterday)->count(),
                'by_status' => Transaction::selectRaw('status, count(*) as count')
                    ->whereDate('created_at', $yesterday)
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray(),
                'total_amount' => Transaction::whereDate('created_at', $yesterday)
                    ->where('status', 'completed')
                    ->sum('amount'),
            ],
            'system_metrics' => [
                'avg_response_time' => $this->getAverageResponseTime($yesterday),
                'error_rate' => $this->getErrorRate($yesterday),
                'uptime_percentage' => $this->getUptimePercentage($yesterday),
            ],
            'security_events' => [
                'failed_logins' => AuditLog::where('action', 'failed_login')
                    ->whereDate('created_at', $yesterday)
                    ->count(),
                'suspicious_ips' => $this->getSuspiciousIPs($yesterday),
            ],
        ];
    }

    protected function getAverageResponseTime($date): float
    {
        // Из логов или метрик
        return 150.5; // ms
    }

    protected function getErrorRate($date): float
    {
        $totalRequests = 10000; // из метрик
        $errorRequests = 150;   // из метрик

        return ($errorRequests / $totalRequests) * 100;
    }

    protected function getUptimePercentage($date): float
    {
        return 99.8; // из мониторинга
    }

    protected function getSuspiciousIPs($date): array
    {
        return AuditLog::select('ip_address', DB::raw('count(*) as attempts'))
            ->where('action', 'failed_login')
            ->whereDate('created_at', $date)
            ->groupBy('ip_address')
            ->having('attempts', '>', 3)
            ->orderBy('attempts', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }
}
```

## CI/CD мониторинг

### Автоматизированное тестирование

```yaml
# .github/workflows/monitoring.yml
name: System Monitoring

on:
  schedule:
    - cron: '0 */6 * * *'  # Каждые 6 часов
  workflow_dispatch:

jobs:
  health-check:
    runs-on: ubuntu-latest

    steps:
    - name: Health Check
      run: |
        # Проверка доступности API
        curl -f https://your-domain.com/api/health || exit 1

        # Проверка БД
        php artisan tinker --execute="DB::connection()->getPdo() && echo 'DB OK'"

        # Проверка очередей
        php artisan queue:failed | grep -q "No failed jobs" || exit 1

  performance-test:
    runs-on: ubuntu-latest

    steps:
    - name: Load Testing
      uses: artilleryio/action-cli@v1
      with:
        command: run --output report.json artillery-config.yml

    - name: Upload Report
      uses: actions/upload-artifact@v3
      with:
        name: performance-report
        path: report.json
```

## Лучшие практики

### Регулярные проверки

1. **Ежедневно**:
   - Проверка логов на ошибки
   - Мониторинг производительности
   - Проверка резервных копий

2. **Еженедельно**:
   - Анализ метрик производительности
   - Проверка безопасности
   - Обновление зависимостей

3. **Ежемесячно**:
   - Полный аудит системы
   - Анализ трендов
   - Планирование улучшений

### Уведомления

- **Email**: ежедневные отчеты, критические алерты
- **Slack**: мгновенные уведомления о проблемах
- **SMS**: критические системные проблемы
- **Dashboard**: визуализация метрик в реальном времени

### Хранение данных

- **Логи**: 30 дней онлайн, 1 год в архиве
- **Метрики**: 90 дней детально, 1 год агрегировано
- **Аудит**: бессрочно для compliance
