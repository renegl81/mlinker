# MenuFlow Core (Backend)

**MenuFlow** es una plataforma SaaS multi-tenant diseñada para la gestión de menús digitales en restaurantes. Este repositorio contiene el **Backend (Laravel 12)** y el **Panel de Administración (Inertia.js + Vue 3)**.

La aplicación pública (Carta Digital) se encuentra en el repositorio del cliente (Nuxt 3).

## 🚀 Stack Tecnológico

* **Framework:** Laravel 12
* **Base de Datos:** PostgreSQL 14+
* **Admin Frontend:** Vue 3 + Inertia.js + Tailwind CSS (Shadcn UI)
* **Arquitectura:** Monolito Modular con Multi-tenancy (Single Database)
* **API:** REST para consumo del cliente Nuxt (PWA)

## 📋 Requisitos Previos

Asegúrate de tener instalado en tu entorno local:
* Linux Mint / Ubuntu (Entorno recomendado)
* PHP 8.2 o superior
* Composer
* Node.js & NPM

## 🛠️ Guía de Instalación Completa

Sigue estos pasos para levantar el proyecto desde cero en un entorno Linux Mint recién instalado.

### 1. Instalar PostgreSQL en el Sistema

Si aún no tienes el motor de base de datos, ejecuta los siguientes comandos en tu terminal para instalar Postgres y los drivers necesarios para PHP:

```bash
# Actualizar lista de paquetes
sudo apt update

# Instalar PostgreSQL y la extensión para PHP
sudo apt install postgresql postgresql-contrib php-pgsql
-- 1. Crear el usuario para el proyecto
CREATE USER menuflow_user WITH PASSWORD 'secret';

-- 2. Darle permisos para crear bases de datos (útil para tests automáticos)
ALTER USER menuflow_user CREATEDB;

-- 3. Crear la base de datos vacía
CREATE DATABASE menuflow_db OWNER menuflow_user;

-- 4. Conectarnos a esa base de datos para activar extensiones
\c menuflow_db

-- 5. Activar extensión para UUIDs (Recomendado para Tenancy)
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 6. Salir
\q

## 🎨 Formateo de Código con Laravel Pint

**Laravel Pint** es un formateador de código PHP que viene preinstalado en Laravel 12. Utiliza PHP-CS-Fixer bajo el capó para mantener un estilo de código consistente.

### Uso Básico

```bash
# Formatear todo el código del proyecto
./vendor/bin/pint

# Ver qué archivos cambiarían sin modificarlos (modo dry-run)
./vendor/bin/pint --test

# Formatear archivos/carpetas específicas
./vendor/bin/pint app/Models
./vendor/bin/pint app/Http/Controllers/Admin
