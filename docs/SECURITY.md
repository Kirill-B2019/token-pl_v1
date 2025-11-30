# 🛡️ Безопасность

Это руководство по безопасности Токен Платформы.

## Общие принципы безопасности

### Defense in Depth
Система использует многоуровневую защиту:
- Валидация входных данных
- Авторизация и аутентификация
- Шифрование чувствительных данных
- Мониторинг и логирование
- Регулярные обновления

### Принцип наименьших привилегий
- Пользователи имеют доступ только к необходимым ресурсам
- API токены ограничены по времени жизни
- Роли пользователей строго разделены

## Аутентификация

### Laravel Fortify
Используется для управления аутентификацией:
- Многофакторная аутентификация (2FA)
- Защита от brute force атак
- Безопасное хранение паролей (bcrypt)

### API токены
- JWT токены для API доступа
- Автоматическое истечение токенов
- Возможность отзыва токенов

## Авторизация

### Ролевая модель
```php
enum UserRole: string
{
    case CLIENT = 'client';
    case BROKER = 'broker';
    case ADMIN = 'admin';
}
```

### Политики (Policies)
```php
class TransactionPolicy
{
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id || $user->isAdmin();
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $user->isAdmin();
    }
}
```

### Gates и Abilities
```php
// Определение в AuthServiceProvider
Gate::define('manage-tokens', function (User $user) {
    return $user->isAdmin() || $user->isBroker();
});

Gate::define('view-admin-panel', function (User $user) {
    return $user->isAdmin();
});
```

## Защита данных

### Шифрование
- Пароли: bcrypt с солью
- Чувствительные данные: AES-256
- Передача данных: HTTPS/TLS 1.3

### Хранение данных
```php
// Шифрование полей в модели
protected function casts(): array
{
    return [
        'card_token' => 'encrypted',
        'api_key' => 'encrypted',
    ];
}
```

## Защита от атак

### SQL Injection
- Использование Eloquent ORM
- Параметризованные запросы
- Валидация входных данных

### XSS (Cross-Site Scripting)
- Автоматическое экранирование в Blade
- CSP (Content Security Policy)
- Валидация HTML контента

### CSRF (Cross-Site Request Forgery)
- CSRF токены для всех форм
- Проверка origin/referer заголовков

### Clickjacking
- X-Frame-Options: DENY
- CSP frame-ancestors

## Платежная безопасность

### 2can интеграция
- Подпись всех запросов
- Валидация webhook callback
- PCI DSS compliance

### Защита карт
- Токенизация карт (не хранятся реальные данные)
- Маскировка номеров карт в интерфейсе
- Ограничение доступа к токенам карт

## Сетевая безопасность

### Firewall правила
```bash
# UFW правила
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### SSL/TLS
- Let's Encrypt сертификаты
- TLS 1.3 приоритет
- HSTS заголовки
- Certificate pinning

### Headers безопасности
```php
// В middleware
$this->headers = [
    'X-Frame-Options' => 'DENY',
    'X-Content-Type-Options' => 'nosniff',
    'X-XSS-Protection' => '1; mode=block',
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    'Content-Security-Policy' => "default-src 'self'",
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
];
```

## Мониторинг безопасности

### Логирование
```php
// Важные события
Log::critical('Security breach attempt', [
    'user_id' => $user->id,
    'ip' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'action' => 'unauthorized_access'
]);

// Аудит логов
Log::info('User action', [
    'user_id' => $user->id,
    'action' => 'password_changed',
    'ip' => request()->ip(),
    'timestamp' => now()
]);
```

### Мониторинг
- Неудачные попытки входа
- Подозрительная активность
- Изменения в базе данных
- API rate limiting

### Алерты
```php
// Отправка уведомлений администраторам
Notification::send(
    User::where('role', 'admin')->get(),
    new SecurityAlert($message, $severity)
);
```

## Защита инфраструктуры

### Обновления
```bash
# Регулярные обновления системы
sudo apt update && sudo apt upgrade

# Обновление PHP зависимостей
composer update --security

# Обновление Node.js зависимостей
npm audit fix
```

### Бэкапы
```bash
# Шифрованные бэкапы
mysqldump --password --ssl db_name | gzip | gpg -c > backup.sql.gz.gpg

# Проверка целостности бэкапов
gpg --verify backup.sql.gz.gpg
```

### Доступы
- SSH ключи вместо паролей
- Sudo с ограниченными правами
- Отключение root доступа

## Защита API

### Rate Limiting
```php
// В RouteServiceProvider
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

### API ключи
```php
// Модель API ключей
class ApiKey extends Model
{
    protected $fillable = ['user_id', 'name', 'key', 'permissions', 'expires_at'];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    public function can($permission): bool
    {
        return in_array($permission, $this->permissions);
    }
}
```

### Webhook безопасность
```php
// Валидация подписи webhook
public function validateWebhook(Request $request): bool
{
    $signature = $request->header('X-2can-Signature');
    $payload = $request->getContent();
    $secret = config('twocan.webhook_secret');

    $expectedSignature = hash_hmac('sha256', $payload, $secret);

    return hash_equals($expectedSignature, $signature);
}
```

## Обработка инцидентов

### План реагирования
1. **Обнаружение**: мониторинг и алерты
2. **Оценка**: определение масштаба инцидента
3. **Изоляция**: ограничение доступа к скомпрометированным ресурсам
4. **Восстановление**: восстановление из бэкапов
5. **Анализ**: расследование причин
6. **Улучшение**: обновление мер безопасности

### Контакты
- **CIRT**: security@token-platform.local
- **Администраторы**: admin@token-platform.local
- **Внешние эксперты**: security-consultant@company.com

## Соответствие стандартам

### GDPR (для ЕС пользователей)
- Минимизация данных
- Право на удаление
- Cookie consent
- Data processing agreements

### PCI DSS (платежные данные)
- Токенизация карт
- Шифрование передачи данных
- Регулярные аудиты
- Ограничение доступа к данным

### ISO 27001
- Политика безопасности
- Риск-менеджмент
- Непрерывность бизнеса
- Регулярные аудиты

## Аудит безопасности

### Регулярные проверки
- Ежемесячный аудит логов
- Квартальный пентест
- Ежегодный аудит безопасности
- Проверка зависимостей на уязвимости

### Инструменты аудита
```bash
# Сканирование уязвимостей
composer audit
npm audit

# Проверка зависимостей
snyk test

# Сканирование кода
phpstan analyse
```

## Обучение и осведомленность

### Для разработчиков
- Безопасное кодирование
- Code review с фокусом на безопасность
- Регулярные тренинги

### Для пользователей
- Сложные пароли
- 2FA включение
- Распознавание фишинга
- Безопасные практики

## Конфигурация безопасности

### Production настройки
```env
# Отключение debug режима
APP_DEBUG=false
APP_ENV=production

# Скрытие чувствительной информации
LOG_LEVEL=error
DB_LOG_QUERIES=false

# Сессии
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

# Шифрование
APP_KEY=base64:your_app_key_here
```

### Security Headers Middleware
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', "default-src 'self'");
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
```

## Мониторинг и обнаружение

### Инструменты мониторинга
- **Fail2Ban**: защита от brute force
- **OSSEC**: HIDS (Host-based Intrusion Detection System)
- **ELK Stack**: централизованное логирование
- **Prometheus + Grafana**: метрики и алерты

### Алерты безопасности
```php
// Мониторинг подозрительной активности
if ($failedAttempts > 5) {
    Log::warning('Multiple failed login attempts', [
        'ip' => $request->ip(),
        'email' => $request->input('email'),
        'attempts' => $failedAttempts
    ]);

    // Отправка алерта
    $this->sendSecurityAlert('Multiple failed login attempts from IP: ' . $request->ip());
}
```

## Заключение

Безопасность - это непрерывный процесс. Регулярно:
- Обновляйте систему и зависимости
- Проводите аудиты безопасности
- Мониторьте активность
- Обучайте персонал
- Тестируйте на проникновение

**Помните**: безопасность не бывает абсолютной, но ее можно сделать достаточно надежной для практического использования.
