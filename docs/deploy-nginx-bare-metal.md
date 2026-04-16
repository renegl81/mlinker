# Despliegue de MenuLinker en servidor bare-metal con Nginx

Guia paso a paso para desplegar MenuLinker (Laravel 13 + Vue 3 + Inertia + Stancl Tenancy) en un servidor Ubuntu 24.04 sin Docker, usando Nginx como reverse proxy / web server.

---

## Indice

1. [Requisitos del servidor](#1-requisitos-del-servidor)
2. [Instalar dependencias del sistema](#2-instalar-dependencias-del-sistema)
3. [Configurar PostgreSQL](#3-configurar-postgresql)
4. [Configurar PHP-FPM](#4-configurar-php-fpm)
5. [Desplegar el codigo de la aplicacion](#5-desplegar-el-codigo-de-la-aplicacion)
6. [Configurar el .env de produccion](#6-configurar-el-env-de-produccion)
7. [Instalar dependencias y compilar assets](#7-instalar-dependencias-y-compilar-assets)
8. [Configurar Nginx](#8-configurar-nginx)
9. [SSL con Certbot (Let's Encrypt)](#9-ssl-con-certbot-lets-encrypt)
10. [Configurar Supervisor (queue worker + SSR)](#10-configurar-supervisor-queue-worker--ssr)
11. [Configurar el cron del scheduler](#11-configurar-el-cron-del-scheduler)
12. [Permisos y seguridad](#12-permisos-y-seguridad)
13. [Despliegues posteriores (deploy script)](#13-despliegues-posteriores-deploy-script)
14. [Troubleshooting](#14-troubleshooting)

---

## 1. Requisitos del servidor

| Componente       | Version minima | Notas                                       |
|------------------|----------------|---------------------------------------------|
| Ubuntu           | 24.04 LTS      | O Debian 12+                                |
| PHP              | 8.5            | Con extensiones listadas abajo              |
| PostgreSQL       | 16+            | Multi-tenant: un schema/db por tenant       |
| Node.js          | 20 LTS+        | Para compilar assets y SSR                  |
| Nginx            | 1.24+          | Reverse proxy a PHP-FPM                     |
| Composer         | 2.x            |                                             |
| Supervisor       | 4.x            | Para queue workers y SSR server             |
| Git              | 2.x            | Para deploy desde repositorio               |

**RAM recomendada:** 2 GB minimo (4 GB si activas SSR con cluster mode).

---

## 2. Instalar dependencias del sistema

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Agregar repositorio PHP (Ondrej PPA)
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Instalar PHP 8.5 y extensiones necesarias
sudo apt install -y \
    php8.5-fpm \
    php8.5-cli \
    php8.5-pgsql \
    php8.5-gd \
    php8.5-curl \
    php8.5-mbstring \
    php8.5-xml \
    php8.5-zip \
    php8.5-bcmath \
    php8.5-intl \
    php8.5-readline \
    php8.5-redis \
    php8.5-imagick

# Instalar Nginx, PostgreSQL, Supervisor y utilidades
sudo apt install -y nginx postgresql postgresql-client \
    supervisor git unzip curl acl

# Instalar Composer
curl -sS https://getcomposer.org/installer | sudo php -- \
    --install-dir=/usr/bin --filename=composer

# Instalar Node.js (via NodeSource)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

> **Nota sobre extensiones PHP:** El proyecto usa QR codes (`endroid/qr-code` necesita `gd` o `imagick`), Excel export (`maatwebsite/excel` necesita `zip`, `gd`, `xml`), y Stripe (`curl`). Si usas Redis para cache/sesiones en produccion, necesitas `php8.5-redis`.

---

## 3. Configurar PostgreSQL

```bash
# Crear usuario y base de datos
sudo -u postgres psql <<SQL
CREATE USER qmenuo_user WITH PASSWORD '**qwe--poi**';
CREATE DATABASE qmenuo_db OWNER qmenuo_user;

-- El usuario necesita permiso para crear bases de datos (para tenants)
ALTER USER qmenuo_user CREATEDB;
SQL
```

> **Importante para multi-tenancy:** Stancl Tenancy crea una base de datos por cada tenant (`tenant{uuid}`). El usuario de PostgreSQL **debe tener permiso `CREATEDB`**.

Editar `/etc/postgresql/*/main/pg_hba.conf` si necesitas ajustar autenticacion:

```
# Permitir conexion local con password
local   all   qmenuo_user   md5
```

```bash
sudo systemctl restart postgresql
```

---

## 4. Configurar PHP-FPM

Editar `/etc/php/8.5/fpm/pool.d/www.conf`:

```ini
[www]
user = www-data
group = www-data
listen = /run/php/php8.5-fpm.sock
listen.owner = www-data
listen.group = www-data

; Ajustar segun RAM del servidor (ejemplo para 2-4 GB)
pm = dynamic
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 3
pm.max_spare_servers = 10
pm.max_requests = 500
```

Ajustar `/etc/php/8.5/fpm/php.ini` para produccion:

```ini
upload_max_filesize = 20M
post_max_size = 25M
memory_limit = 256M
max_execution_time = 60
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0   ; desactivar en produccion, limpiar con deploy
```

```bash
sudo systemctl restart php8.5-fpm
sudo systemctl enable php8.5-fpm
```

---

## 5. Desplegar el codigo de la aplicacion

```bash
# Crear directorio de la aplicacion
sudo mkdir -p /var/www/menulinker
sudo chown www-data:www-data /var/www/menulinker

# Clonar repositorio (como www-data o tu usuario deploy)
sudo -u www-data git clone git@github.com:TU_ORG/menulinker-core.git /var/www/menulinker

# O si usas un usuario deploy separado:
# git clone ... /var/www/menulinker
# sudo chown -R www-data:www-data /var/www/menulinker
```

---

## 6. Configurar el .env de produccion

```bash
cd /var/www/menulinker
sudo -u www-data cp .env.example .env
sudo -u www-data php artisan key:generate
```

Editar `.env` con los valores de produccion:

```dotenv
APP_NAME=MenuLinker
APP_ENV=production
APP_DEBUG=false
APP_URL=https://menulinker.com
ASSET_URL=https://menulinker.com

# Multi-tenancy: dominio central
APP_DOMAIN=menulinker.com
SESSION_DOMAIN=.menulinker.com
SANCTUM_STATEFUL_DOMAINS=*.menulinker.com

APP_LOCALE=es
APP_FALLBACK_LOCALE=es

# Base de datos
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=menulinker_db
DB_USERNAME=menulinker_user
DB_PASSWORD=TU_PASSWORD_SEGURA

# Sesiones y cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Redis (opcional pero recomendado en produccion)
# CACHE_STORE=redis
# SESSION_DRIVER=redis
# QUEUE_CONNECTION=redis
# REDIS_HOST=127.0.0.1

# Mail (configurar con tu proveedor SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.tuproveedor.com
MAIL_PORT=587
MAIL_USERNAME=tu_usuario
MAIL_PASSWORD=tu_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hello@menulinker.com
MAIL_FROM_NAME="${APP_NAME}"

# Stripe
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Filesystem
FILESYSTEM_DISK=local

LOG_CHANNEL=stack
LOG_LEVEL=warning
BCRYPT_ROUNDS=12
```

> **Dominio central:** Actualiza tambien `config/tenancy.php` -> `central_domains` si los valores estan hardcodeados ahi. Normalmente lee de `APP_DOMAIN`.

---

## 7. Instalar dependencias y compilar assets

```bash
cd /var/www/menulinker

# Instalar dependencias PHP (sin dev)
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction

# Instalar dependencias Node y compilar
sudo -u www-data npm ci
sudo -u www-data npm run build    # genera public/build/ y bootstrap/ssr/

# Migraciones
sudo -u www-data php artisan migrate --force

# Optimizaciones de Laravel para produccion
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache
sudo -u www-data php artisan icons:cache   # si usas Filament icons
sudo -u www-data php artisan filament:cache-components

# Storage link
sudo -u www-data php artisan storage:link
```

---

## 8. Configurar Nginx

### 8.1 Dominio central (menulinker.com)

Crear `/etc/nginx/sites-available/menulinker-central`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name menulinker.com www.menulinker.com;

    root /var/www/menulinker/public;
    index index.php;

    charset utf-8;
    client_max_body_size 25M;

    # Assets compilados por Vite (cache agresivo, tienen hash en nombre)
    location /build/ {
        expires 1y;
        access_log off;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # Archivos estaticos generales
    location ~* \.(ico|css|js|gif|jpe?g|png|svg|woff2?|ttf|eot)$ {
        expires 7d;
        access_log off;
        add_header Cache-Control "public";
        try_files $uri =404;
    }

    # Bloquear acceso a archivos ocultos
    location ~ /\.(?!well-known) {
        deny all;
    }

    # Todas las requests van a Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;

        # Timeouts generosos para operaciones pesadas
        fastcgi_read_timeout 60s;
        fastcgi_send_timeout 60s;

        # Buffers
        fastcgi_buffer_size 16k;
        fastcgi_buffers 4 16k;
    }

    # Bloquear acceso directo a .env y otros archivos sensibles
    location ~ /\.env {
        deny all;
    }
}
```

### 8.2 Dominios tenant (catch-all)

Crear `/etc/nginx/sites-available/menulinker-tenant`:

```nginx
# Catch-all para dominios de tenants
# Stancl Tenancy identifica el tenant por el dominio HTTP Host
server {
    listen 80 default_server;
    listen [::]:80 default_server;

    # Acepta cualquier dominio que no sea el central
    server_name _;

    root /var/www/menulinker/public;
    index index.php;

    charset utf-8;
    client_max_body_size 25M;

    # Assets compilados por Vite
    location /build/ {
        expires 1y;
        access_log off;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # Archivos estaticos
    location ~* \.(ico|css|js|gif|jpe?g|png|svg|woff2?|ttf|eot)$ {
        expires 7d;
        access_log off;
        add_header Cache-Control "public";
        try_files $uri =404;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 60s;
        fastcgi_send_timeout 60s;
        fastcgi_buffer_size 16k;
        fastcgi_buffers 4 16k;
    }

    location ~ /\.env {
        deny all;
    }
}
```

### 8.3 Activar sitios

```bash
sudo ln -s /etc/nginx/sites-available/menulinker-central /etc/nginx/sites-enabled/
sudo ln -s /etc/nginx/sites-available/menulinker-tenant /etc/nginx/sites-enabled/

# Eliminar el default si existe
sudo rm -f /etc/nginx/sites-enabled/default

# Validar configuracion
sudo nginx -t

# Aplicar
sudo systemctl reload nginx
sudo systemctl enable nginx
```

### 8.5 Nota sobre dominios tenant personalizados

Cuando un tenant registra un dominio personalizado (ej: `carta.mirestaurante.com`), ese dominio debe apuntar (DNS) a la IP de tu servidor. El bloque `server_name _;` de Nginx capturara la request, y Stancl Tenancy resolvera el tenant correcto por la cabecera `Host`.

**No necesitas crear un nuevo bloque Nginx por cada tenant.** Solo asegurate de que:
1. El DNS del dominio apunte a tu servidor.
2. Generes un certificado SSL para ese dominio (ver seccion SSL).

---

## 9. SSL con Certbot (Let's Encrypt)

```bash
# Instalar Certbot
sudo apt install -y certbot python3-certbot-nginx

# Certificado para el dominio central
sudo certbot --nginx -d menulinker.com -d www.menulinker.com

# Para dominios de tenants, tienes dos opciones:

# Opcion A: Certificado wildcard (si usas subdominios *.menulinker.com)
# Requiere DNS challenge (necesitas API de tu proveedor DNS)
sudo certbot certonly --dns-<proveedor> \
    -d "*.menulinker.com" -d menulinker.com

# Opcion B: Certificado individual por dominio personalizado
# Ejecutar cuando se registra un nuevo tenant con dominio propio
sudo certbot --nginx -d carta.mirestaurante.com
```

Certbot modifica automaticamente los bloques Nginx para redirigir HTTP -> HTTPS.

**Renovacion automatica** (ya viene configurado con el paquete de Certbot):

```bash
# Verificar que el timer existe
sudo systemctl list-timers | grep certbot

# Si no, agregar cron:
echo "0 3 * * * certbot renew --quiet --post-hook 'systemctl reload nginx'" | sudo crontab -
```

---

## 10. Configurar Supervisor (queue worker + SSR)

Crear `/etc/supervisor/conf.d/menulinker.conf`:

```ini
;------------------------------------------------------
; Queue Worker
; Procesa jobs de la cola (emails, QR generation, etc.)
;------------------------------------------------------
[program:menulinker-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/menulinker/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --max-jobs=500
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/menulinker-worker.log
stdout_logfile_maxbytes=10MB
stopwaitsecs=3600

;------------------------------------------------------
; SSR Server (Inertia Vue)
; Renderiza la primera carga en el servidor para SEO/performance
; Escucha en 127.0.0.1:13714 (solo accesible por PHP, no por Nginx)
;------------------------------------------------------
[program:menulinker-ssr]
command=node /var/www/menulinker/bootstrap/ssr/ssr.js
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/menulinker-ssr.log
stdout_logfile_maxbytes=10MB
stopwaitsecs=10
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start menulinker-worker:*
sudo supervisorctl start menulinker-ssr

# Verificar estado
sudo supervisorctl status
```

> **SSR es opcional.** Si no lo necesitas (no te importa SEO de las paginas del panel), puedes omitir el bloque `menulinker-ssr`. Inertia funciona perfectamente sin SSR como SPA.

---

## 11. Configurar el cron del scheduler

```bash
# Editar crontab de www-data
sudo crontab -u www-data -e
```

Agregar:

```cron
* * * * * cd /var/www/menulinker && php artisan schedule:run >> /dev/null 2>&1
```

---

## 12. Permisos y seguridad

```bash
cd /var/www/menulinker

# Propietario: www-data
sudo chown -R www-data:www-data .

# Permisos base
sudo find . -type f -exec chmod 644 {} \;
sudo find . -type d -exec chmod 755 {} \;

# Directorios que Laravel necesita escribir
sudo chmod -R 775 storage bootstrap/cache

# Asegurar que .env no sea legible por otros
sudo chmod 640 .env

# Si tu usuario deploy necesita escribir (agregar al grupo www-data)
sudo usermod -aG www-data tu_usuario_deploy
```

### Firewall (UFW)

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### Headers de seguridad (agregar en bloques Nginx)

```nginx
# Dentro de cada server { }
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

---

## 13. Despliegues posteriores (deploy script)

Crear `/var/www/menulinker/deploy.sh`:

```bash
#!/bin/bash
set -e

cd /var/www/menulinker

echo ">> Pulling latest code..."
git pull origin main

echo ">> Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo ">> Installing Node dependencies..."
npm ci

echo ">> Building assets..."
npm run build

echo ">> Running migrations..."
php artisan migrate --force

echo ">> Clearing and rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:cache-components

echo ">> Restarting queue workers..."
php artisan queue:restart

echo ">> Restarting SSR server..."
sudo supervisorctl restart menulinker-ssr

echo ">> Reloading PHP-FPM (clear opcache)..."
sudo systemctl reload php8.5-fpm

echo "Deploy complete!"
```

```bash
chmod +x /var/www/menulinker/deploy.sh
```

Ejecutar con: `sudo -u www-data /var/www/menulinker/deploy.sh`

> Para zero-downtime deployments mas avanzados, considera [Envoyer](https://envoyer.io/) o un setup con symlinks tipo Deployer.

---

## 14. Troubleshooting

### Logs importantes

```bash
# Laravel
tail -f /var/www/menulinker/storage/logs/laravel.log

# Nginx
tail -f /var/log/nginx/error.log

# PHP-FPM
tail -f /var/log/php8.5-fpm.log

# Supervisor / Queue workers
tail -f /var/log/supervisor/menulinker-worker.log

# SSR
tail -f /var/log/supervisor/menulinker-ssr.log
```

### Problemas comunes

| Problema | Solucion |
|----------|----------|
| 502 Bad Gateway | Verificar que PHP-FPM esta corriendo: `systemctl status php8.5-fpm` |
| Permiso denegado en storage/ | `sudo chown -R www-data:www-data storage && chmod -R 775 storage` |
| Tenant no encontrado | Verificar que el dominio existe en la tabla `domains` de la DB central |
| Assets no cargan (404) | Verificar que `npm run build` se ejecuto y `public/build/` existe |
| SSR no funciona | Verificar `bootstrap/ssr/ssr.js` existe y el supervisor esta corriendo |
| Colas no procesan | `supervisorctl status menulinker-worker:*` y revisar logs |
| OPcache no refresca | `systemctl reload php8.5-fpm` despues de cada deploy |
| SSL para nuevo tenant | `certbot --nginx -d nuevo-dominio.com` |
| Conexion DB de tenant falla | Verificar que el user de PostgreSQL tiene `CREATEDB` |

### Verificar que todo funciona

```bash
# PHP-FPM
sudo systemctl status php8.5-fpm

# Nginx
sudo systemctl status nginx
sudo nginx -t

# PostgreSQL
sudo systemctl status postgresql
psql -U menulinker_user -d menulinker_db -c "SELECT 1;"

# Queue workers
sudo supervisorctl status

# SSR server
curl -s http://127.0.0.1:13714 && echo "SSR OK" || echo "SSR no responde"

# Aplicacion
curl -I https://menulinker.com
```

---

## Arquitectura del despliegue (resumen visual)

```
                    Internet
                       |
                   [ Nginx ]
                   /       \
          central domain    tenant domains (catch-all)
          menulinker.com    *.menulinker.com / custom domains
                   \       /
                [ PHP 8.5 FPM ]
                       |
               [ Laravel 13 App ]
              /        |         \
    [ PostgreSQL ]  [ Supervisor ]  [ Node SSR ]
     central DB     queue workers    :13714
     tenant DBs
```
