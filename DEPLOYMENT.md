# Инструкция по развертыванию Токен-Платформы

## Предварительные требования

### Системные требования
- PHP 8.2 или выше
- Composer 2.0+
- Node.js 18+ и npm
- MySQL 8.0+ или PostgreSQL 13+
- Redis 6.0+
- Nginx или Apache
- SSL сертификат

### Рекомендуемая конфигурация сервера
- CPU: 4+ ядра
- RAM: 8+ GB
- Диск: 100+ GB SSD
- Сеть: 1 Gbps

## Пошаговая установка

### 1. Подготовка сервера

```bash
# Обновление системы (Ubuntu/Debian)
sudo apt update && sudo apt upgrade -y

# Установка необходимых пакетов
sudo apt install -y nginx mysql-server redis-server git curl unzip

# Установка PHP 8.2
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-bcmath php8.2-gd php8.2-redis

# Установка Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Установка Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

### 2. Настройка базы данных

```bash
# Вход в MySQL
sudo mysql -u root -p

# Создание базы данных и пользователя
CREATE DATABASE token_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'token_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON token_platform.* TO 'token_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Развертывание приложения

```bash
# Создание директории проекта
sudo mkdir -p /var/www/token-platform
sudo chown -R www-data:www-data /var/www/token-platform

# Клонирование репозитория
cd /var/www/token-platform
sudo -u www-data git clone <repository-url> .

# Установка зависимостей
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm install
sudo -u www-data npm run build
```

### 4. Настройка окружения

```bash
# Копирование файла конфигурации
sudo -u www-data cp .env.example .env

# Генерация ключа приложения
sudo -u www-data php artisan key:generate
```

Настройте файл `.env`:

```env
APP_NAME="Token Platform"
APP_ENV=production
APP_KEY=base64:your_generated_key
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=token_platform
DB_USERNAME=token_user
DB_PASSWORD=secure_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Настройки для двухфакторной аутентификации
FORTIFY_FEATURES="registration,login,password-reset,email-verification,two-factor-authentication"

# Настройки безопасности
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=your-domain.com
```

### 5. Выполнение миграций и сидеров

```bash
# Выполнение миграций
sudo -u www-data php artisan migrate --force

# Заполнение тестовыми данными
sudo -u www-data php artisan db:seed --force

# Очистка кэша
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### 6. Настройка Nginx

Создайте файл конфигурации `/etc/nginx/sites-available/token-platform`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/token-platform/public;
    index index.php;

    # SSL настройки
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Безопасность
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Обработка PHP
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Обработка статических файлов
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Основные правила
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Запрет доступа к системным файлам
    location ~ /\. {
        deny all;
    }

    location ~ /(storage|bootstrap/cache) {
        deny all;
    }
}
```

Активируйте конфигурацию:

```bash
sudo ln -s /etc/nginx/sites-available/token-platform /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 7. Настройка PHP-FPM

Отредактируйте `/etc/php/8.2/fpm/pool.d/www.conf`:

```ini
user = www-data
group = www-data
listen = /var/run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000
```

Перезапустите PHP-FPM:

```bash
sudo systemctl restart php8.2-fpm
```

### 8. Настройка Redis

Убедитесь, что Redis запущен:

```bash
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

### 9. Настройка cron задач

Добавьте в crontab:

```bash
sudo crontab -e
```

Добавьте строки:

```cron
* * * * * cd /var/www/token-platform && php artisan schedule:run >> /dev/null 2>&1
0 0 * * * cd /var/www/token-platform && php artisan backup:run >> /dev/null 2>&1
```

### 10. Настройка логирования

Создайте директории для логов:

```bash
sudo mkdir -p /var/log/token-platform
sudo chown -R www-data:www-data /var/log/token-platform
```

Настройте ротацию логов в `/etc/logrotate.d/token-platform`:

```
/var/log/token-platform/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload php8.2-fpm
    endscript
}
```

### 11. Настройка брандмауэра

```bash
# UFW (Ubuntu)
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# iptables (CentOS/RHEL)
sudo iptables -A INPUT -p tcp --dport 22 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 3306 -j DROP
sudo iptables -A INPUT -p tcp --dport 6379 -j DROP
```

### 12. Мониторинг и резервное копирование

#### Настройка мониторинга
```bash
# Установка htop для мониторинга
sudo apt install htop

# Настройка логирования ошибок
echo "log_errors = On" | sudo tee -a /etc/php/8.2/fpm/php.ini
echo "error_log = /var/log/token-platform/php_errors.log" | sudo tee -a /etc/php/8.2/fpm/php.ini
```

#### Резервное копирование
Создайте скрипт `/usr/local/bin/backup-token-platform.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/token-platform"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="token_platform"
DB_USER="token_user"
DB_PASS="secure_password"

mkdir -p $BACKUP_DIR

# Бэкап базы данных
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_$DATE.sql

# Бэкап файлов приложения
tar -czf $BACKUP_DIR/app_$DATE.tar.gz /var/www/token-platform

# Удаление старых бэкапов (старше 30 дней)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

Сделайте скрипт исполняемым:

```bash
sudo chmod +x /usr/local/bin/backup-token-platform.sh
```

## Проверка установки

1. Откройте браузер и перейдите на `https://your-domain.com`
2. Проверьте, что отображается главная страница
3. Попробуйте зарегистрироваться
4. Проверьте API endpoints

## Обслуживание

### Обновление приложения
```bash
cd /var/www/token-platform
sudo -u www-data git pull origin main
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm run build
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### Мониторинг логов
```bash
# Логи приложения
tail -f /var/log/token-platform/laravel.log

# Логи Nginx
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# Логи PHP-FPM
tail -f /var/log/php8.2-fpm.log
```

## Безопасность

1. Регулярно обновляйте систему и зависимости
2. Используйте сильные пароли
3. Настройте мониторинг безопасности
4. Регулярно создавайте резервные копии
5. Используйте SSL сертификаты
6. Настройте файрвол
7. Ограничьте доступ к административным функциям

## Поддержка

При возникновении проблем:
1. Проверьте логи системы
2. Убедитесь, что все сервисы запущены
3. Проверьте права доступа к файлам
4. Обратитесь к администратору системы
