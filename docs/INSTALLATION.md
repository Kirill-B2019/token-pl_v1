# 🔧 Установка и настройка

Это подробное руководство по установке и настройке Токен Платформы.

## Системные требования

### Минимальные требования

- **ОС**: Linux, macOS, Windows 10+
- **PHP**: 8.2 или выше
- **База данных**: MySQL 8.0+, PostgreSQL 13+, SQLite 3.8.8+
- **Веб-сервер**: Nginx или Apache
- **RAM**: 512 MB минимум, 1 GB рекомендуется
- **Диск**: 1 GB свободного места

### Рекомендуемые требования

- **ОС**: Ubuntu 20.04+ LTS
- **PHP**: 8.3 с расширениями:
  - `pdo_mysql` или `pdo_pgsql`
  - `mbstring`
  - `openssl`
  - `tokenizer`
  - `xml`
  - `ctype`
  - `json`
  - `bcmath`
  - `curl`
  - `zip`
- **База данных**: MySQL 8.0+ или PostgreSQL 15+
- **Redis**: 6.0+ (для кеширования)
- **RAM**: 2 GB+
- **CPU**: 2 ядра+

## Установка

### 1. Скачивание проекта

```bash
# Клонирование репозитория
git clone https://github.com/your-org/token-platform.git
cd token-platform

# Или скачивание архива
wget https://github.com/your-org/token-platform/archive/main.zip
unzip main.zip
cd token-platform-main
```

### 2. Установка зависимостей

```bash
# Установка PHP зависимостей
composer install --no-dev --optimize-autoloader

# Установка Node.js зависимостей (для Vue.js)
npm install
npm run build
```

### 3. Настройка прав доступа

```bash
# Установка прав на папки
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

### 4. Настройка окружения

```bash
# Копирование файла конфигурации
cp .env.example .env

# Генерация ключа приложения
php artisan key:generate
```

### 5. Настройка базы данных

#### MySQL

```bash
# Создание базы данных
mysql -u root -p
CREATE DATABASE token_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL ON token_platform.* TO 'platform_user'@'localhost' IDENTIFIED BY 'secure_password';
FLUSH PRIVILEGES;
EXIT;
```

#### PostgreSQL

```bash
# Создание базы данных
sudo -u postgres psql
CREATE DATABASE token_platform;
CREATE USER platform_user WITH ENCRYPTED PASSWORD 'secure_password';
GRANT ALL PRIVILEGES ON DATABASE token_platform TO platform_user;
\q
```

#### Конфигурация в .env

```env
# MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=token_platform
DB_USERNAME=platform_user
DB_PASSWORD=secure_password

# PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=token_platform
DB_USERNAME=platform_user
DB_PASSWORD=secure_password

# SQLite (для разработки)
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

### 6. Запуск миграций

```bash
# Запуск миграций
php artisan migrate

# (Опционально) Заполнение тестовыми данными
php artisan db:seed
```

## Настройка платежной системы 2can

### Регистрация в 2can

1. Перейдите на [2can.ru](https://2can.ru)
2. Зарегистрируйте аккаунт продавца
3. Получите тестовые учетные данные

### Конфигурация

```env
# 2can настройки
TWOCAN_SHOP_ID=1337
TWOCAN_SECRET_KEY=0b36c46cd0796df20625c0c39fc6aaf9048edd659519ccd0f2115f2614e3412
TWOCAN_API_URL=https://2can.ru/api/v1/
TWOCAN_PAYMENT_URL=https://2can.ru/payment/
TWOCAN_CURRENCY=RUB
TWOCAN_MIN_AMOUNT=10
TWOCAN_MAX_AMOUNT=50000

# URLs для перенаправления
TWOCAN_SUCCESS_URL=/client/payment/success
TWOCAN_FAIL_URL=/client/payment/fail

# Webhook секрет (опционально)
TWOCAN_WEBHOOK_SECRET=your_webhook_secret
```

### Настройка webhook

В личном кабинете 2can укажите URL для webhook:
```
https://your-domain.com/client/payment/webhook
```

## Настройка веб-сервера

### Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/token-platform/public;
    index index.php index.html;

    # Логи
    access_log /var/log/nginx/token-platform.access.log;
    error_log /var/log/nginx/token-platform.error.log;

    # PHP обработка
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Статические файлы
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Скрытие файлов .env и .git
    location ~ /\. {
        deny all;
    }

    # SSL перенаправление (опционально)
    if ($scheme != "https") {
        return 301 https://$server_name$request_uri;
    }
}
```

### Apache

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/token-platform/public

    <Directory /var/www/token-platform/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/token-platform.error.log
    CustomLog ${APACHE_LOG_DIR}/token-platform.access.log combined
</VirtualHost>
```

## Настройка SSL

### Let's Encrypt (бесплатный SSL)

```bash
# Установка certbot
sudo apt install certbot python3-certbot-nginx

# Получение сертификата
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Автоматическое обновление
sudo crontab -e
# Добавить строку:
0 12 * * * /usr/bin/certbot renew --quiet
```

## Настройка Redis (опционально)

```bash
# Установка Redis
sudo apt install redis-server

# Настройка в .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Настройка очереди задач

```bash
# Запуск обработчика очередей
php artisan queue:work

# Или как systemd сервис
sudo nano /etc/systemd/system/laravel-queue.service
```

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/token-platform/artisan queue:work --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable laravel-queue
sudo systemctl start laravel-queue
```

## Настройка почты

### SMTP

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Sendmail

```env
MAIL_MAILER=sendmail
MAIL_HOST=127.0.0.1
MAIL_PORT=25
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

## Настройка бэкапов

### Автоматические бэкапы БД

```bash
# Создание скрипта бэкапа
sudo nano /usr/local/bin/backup-token-platform.sh
```

```bash
#!/bin/bash

# Настройки
DB_NAME="token_platform"
DB_USER="platform_user"
DB_PASS="secure_password"
BACKUP_DIR="/var/backups/token-platform"
DATE=$(date +%Y%m%d_%H%M%S)

# Создание директории
mkdir -p $BACKUP_DIR

# Бэкап БД
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_$DATE.sql

# Бэкап файлов
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/token-platform/storage

# Удаление старых бэкапов (старше 30 дней)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

```bash
# Сделать исполняемым
sudo chmod +x /usr/local/bin/backup-token-platform.sh

# Добавить в cron
sudo crontab -e
# Ежедневный бэкап в 2:00
0 2 * * * /usr/local/bin/backup-token-platform.sh
```

## Настройка мониторинга

### Логи Laravel

```bash
# Мониторинг логов в реальном времени
tail -f storage/logs/laravel.log

# Поиск ошибок
grep "ERROR" storage/logs/laravel.log
```

### Мониторинг производительности

```bash
# Установка New Relic или аналогичного инструмента
composer require newrelic/newrelic

# Или использование Laravel Telescope
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

## Безопасность

### Базовая настройка

```bash
# Отключение отладки в продакшене
APP_DEBUG=false
APP_ENV=production

# Скрытие .env файла
<Files ".env">
    Order allow,deny
    Deny from all
</Files>
```

### Firewall

```bash
# UFW (Ubuntu)
sudo ufw enable
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443

# Или firewalld (CentOS)
sudo firewall-cmd --permanent --add-port=80/tcp
sudo firewall-cmd --permanent --add-port=443/tcp
sudo firewall-cmd --reload
```

### SELinux (CentOS/RHEL)

```bash
# Проверка статуса
sestatus

# Если включен, настройка для Laravel
sudo setsebool -P httpd_can_network_connect 1
sudo setsebool -P httpd_execmem 1
```

## Оптимизация производительности

### PHP настройка

```ini
# php.ini
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 10M
post_max_size = 10M

# OPcache
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=7963
opcache.revalidate_freq=0
```

### Laravel оптимизация

```bash
# Оптимизация для продакшена
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Очистка кеша
php artisan cache:clear
php artisan config:clear
```

## Проверка установки

### 1. Проверка PHP

```bash
php artisan --version
php artisan list
```

### 2. Проверка БД

```bash
php artisan migrate:status
php artisan tinker
>>> User::count()
```

### 3. Проверка веб-сервера

```bash
curl -I http://your-domain.com
```

### 4. Проверка платежей

```bash
# Создание тестового пользователя
php artisan tinker
>>> User::create(['name'=>'Test','email'=>'test@test.com','password'=>Hash::make('password'),'role'=>'client'])
```

### 5. Запуск тестов

```bash
php artisan test
```

## Обновление системы

### Обновление кода

```bash
# Получение обновлений
git pull origin main

# Обновление зависимостей
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Миграции (если есть новые)
php artisan migrate

# Очистка кеша
php artisan optimize:clear

# Перезапуск очередей
sudo systemctl restart laravel-queue
```

### Резервное копирование перед обновлением

```bash
# Бэкап перед обновлением
php artisan backup:create

# Обновление
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate
php artisan optimize
```

## Troubleshooting

### Распространенные проблемы

#### Ошибка 500 Internal Server Error

```bash
# Проверка логов
tail -f storage/logs/laravel.log

# Проверка прав доступа
ls -la storage/
ls -la bootstrap/cache/

# Очистка кеша
php artisan optimize:clear
```

#### Ошибка подключения к БД

```bash
# Проверка настроек
php artisan tinker
>>> config('database.connections.mysql')

# Тест подключения
>>> DB::connection()->getPdo()
```

#### Проблемы с платежами

```bash
# Проверка логов платежей
tail -f storage/logs/laravel.log | grep -i payment

# Проверка конфигурации 2can
php artisan tinker
>>> config('twocan')
```

#### Медленная загрузка

```bash
# Проверка OPcache
php -r "var_dump(opcache_get_status());"

# Оптимизация
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Контакты поддержки

- **Документация**: [docs.token-platform.local](https://docs.token-platform.local)
- **Issues**: [GitHub Issues](https://github.com/your-org/token-platform/issues)
- **Email**: support@token-platform.local
