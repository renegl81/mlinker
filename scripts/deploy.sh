#!/usr/bin/env bash
#
# MenuLinker - Script de despliegue a producción (sin Docker)
#
# Uso:
#   ./scripts/deploy.sh [rama]
#
# Por defecto actualiza la rama "main". Pasa otro nombre como primer argumento
# si necesitas desplegar desde otra rama (p.ej. ./scripts/deploy.sh master).
#

set -euo pipefail

# ----------------------------------------------------------------------------
# Configuración
# ----------------------------------------------------------------------------
BRANCH="${1:-main}"
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
NPM_BIN="${NPM_BIN:-npm}"

cd "$PROJECT_DIR"

# ----------------------------------------------------------------------------
# Helpers
# ----------------------------------------------------------------------------
log()  { printf "\n\033[1;36m==> %s\033[0m\n" "$*"; }
warn() { printf "\n\033[1;33m[!] %s\033[0m\n" "$*"; }
fail() { printf "\n\033[1;31m[x] %s\033[0m\n" "$*" >&2; exit 1; }

command -v "$PHP_BIN"      >/dev/null 2>&1 || fail "No se encuentra PHP ($PHP_BIN)"
command -v "$COMPOSER_BIN" >/dev/null 2>&1 || fail "No se encuentra Composer ($COMPOSER_BIN)"
command -v git             >/dev/null 2>&1 || fail "No se encuentra git"

[[ -f .env ]] || fail "No existe el archivo .env en $PROJECT_DIR"

# ----------------------------------------------------------------------------
# 1. Modo mantenimiento
# ----------------------------------------------------------------------------
log "Activando modo mantenimiento"
"$PHP_BIN" artisan down --render="errors::503" --retry=60 || warn "No se pudo activar 'down' (¿primera ejecución?)"

# Si algo falla a partir de aquí, levantamos la app antes de salir.
trap '"$PHP_BIN" artisan up || true' EXIT

# ----------------------------------------------------------------------------
# 2. Actualizar código (pull --rebase)
# ----------------------------------------------------------------------------
log "Descargando cambios de origin/$BRANCH (pull --rebase)"
git fetch --prune origin
git checkout "$BRANCH"
git pull --rebase --autostash origin "$BRANCH"

CURRENT_COMMIT="$(git rev-parse --short HEAD)"
log "Commit desplegado: $CURRENT_COMMIT"

# ----------------------------------------------------------------------------
# 3. Dependencias PHP
# ----------------------------------------------------------------------------
log "Instalando dependencias PHP (composer install)"
"$COMPOSER_BIN" install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

# ----------------------------------------------------------------------------
# 4. Assets frontend (opcional, si hay Node disponible)
# ----------------------------------------------------------------------------
if command -v "$NPM_BIN" >/dev/null 2>&1; then
    log "Instalando dependencias JS y compilando assets (npm ci && npm run build)"
    "$NPM_BIN" ci
    "$NPM_BIN" run build
else
    warn "npm no está disponible: se omite la compilación de assets"
fi

# ----------------------------------------------------------------------------
# 5. Limpieza de cachés previos
# ----------------------------------------------------------------------------
log "Limpiando cachés de la aplicación"
"$PHP_BIN" artisan config:clear
"$PHP_BIN" artisan route:clear
"$PHP_BIN" artisan view:clear
"$PHP_BIN" artisan event:clear || true

# ----------------------------------------------------------------------------
# 6. Migraciones
# ----------------------------------------------------------------------------
log "Ejecutando migraciones (--force)"
"$PHP_BIN" artisan migrate --force

# Migraciones de tenants (Stancl Tenancy).
if "$PHP_BIN" artisan list | grep -q "tenants:migrate"; then
    log "Ejecutando migraciones de tenants"
    "$PHP_BIN" artisan tenants:migrate --force
fi

# ----------------------------------------------------------------------------
# 7. Cachés optimizadas para producción
# ----------------------------------------------------------------------------
log "Regenerando cachés de producción"
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan event:cache || true

# ----------------------------------------------------------------------------
# 8. Enlaces y permisos
# ----------------------------------------------------------------------------
log "Asegurando symlink storage → public"
"$PHP_BIN" artisan storage:link || true

# ----------------------------------------------------------------------------
# 9. Reinicio de workers (colas / scheduler)
# ----------------------------------------------------------------------------
log "Reiniciando workers de colas"
"$PHP_BIN" artisan queue:restart || true

# ----------------------------------------------------------------------------
# 10. Salir del modo mantenimiento
# ----------------------------------------------------------------------------
trap - EXIT
log "Desactivando modo mantenimiento"
"$PHP_BIN" artisan up

log "Despliegue completado correctamente ($CURRENT_COMMIT)"
