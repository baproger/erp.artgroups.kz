# Artgroups ERP — KPI Dashboard

Laravel 10 система управления KPI показателями для Artgroups.kz.

---

## Стек

- PHP 8.1+, Laravel 10, MySQL 8
- Blade + TailwindCSS CDN + Alpine.js v3 + Chart.js 4

---

## Локальная установка

```bash
git clone <repo-url>
cd erp.artgroups.kz

cp .env.example .env
# Заполните DB_DATABASE, DB_USERNAME, DB_PASSWORD в .env

composer install

php artisan key:generate
php artisan migrate
php artisan db:seed --class=BranchSeeder
php artisan storage:link

php artisan serve
```

---

## Деплой на сервер (субдомен)

```bash
git clone <repo-url> /var/www/erp.artgroups.kz
cd /var/www/erp.artgroups.kz

composer install --no-dev --optimize-autoloader

cp .env.example .env
# Заполняем: APP_URL, DB_*, APP_ENV=production, APP_DEBUG=false
php artisan key:generate

php artisan migrate --force
php artisan db:seed --class=BranchSeeder --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Nginx конфиг (субдомен erp.artgroups.kz)

```nginx
server {
    listen 80;
    server_name erp.artgroups.kz;
    root /var/www/erp.artgroups.kz/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

---

## Роли

| Роль | Доступ |
|------|--------|
| `ceo` | Все филиалы, все KPI, пользователи, настройки |
| `commercial_director` | Свои филиалы и все отделы в них |
| `finance` / `sales` / `marketing` / `production` / `surveyors` | Только свой отдел |

## Демо-аккаунты (после BranchSeeder)

| Email | Пароль | Роль |
|-------|--------|------|
| `ceo@artgroups.kz` | `password123` | CEO |
| `director.astana@artgroups.kz` | `password123` | Комм. директор (Астана) |
| `finance.almaty@artgroups.kz` | `password123` | Финансы (Алматы) |

---

## Обновление на сервере

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
