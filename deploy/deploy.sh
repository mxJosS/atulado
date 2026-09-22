#!/usr/bin/env bash
# Despliegue de A Tu Lado en el servidor (Nginx + PHP-FPM 8.5 + MySQL).
#
# Uso:   APP_DIR=/var/www/atulado bash deploy/deploy.sh
#
# Hace un respaldo de la base ANTES de migrar. Si algo falla, se detiene
# (set -e) y la app queda en modo mantenimiento hasta que lo revises.
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/atulado}"
PHP="${PHP:-php8.5}"
RESPALDOS="${RESPALDOS:-$HOME/respaldos-atulado}"

cd "$APP_DIR"

echo "==> Respaldo de la base de datos"
mkdir -p "$RESPALDOS"
DB_DATABASE=$(grep -E '^DB_DATABASE=' .env | cut -d= -f2-)
DB_USERNAME=$(grep -E '^DB_USERNAME=' .env | cut -d= -f2-)
DB_PASSWORD=$(grep -E '^DB_PASSWORD=' .env | cut -d= -f2-)
ARCHIVO="$RESPALDOS/atulado-$(date +%Y%m%d-%H%M%S).sql.gz"
MYSQL_PWD="$DB_PASSWORD" mysqldump --single-transaction --routines -u "$DB_USERNAME" "$DB_DATABASE" | gzip > "$ARCHIVO"
echo "    $ARCHIVO"

echo "==> Modo mantenimiento"
$PHP artisan down --retry=30 || true

echo "==> Código y dependencias"
git pull --ff-only
composer install --no-dev --optimize-autoloader --no-interaction
# Sólo si cambiaron los assets de Vite:
# npm ci && npm run build

echo "==> Migraciones"
$PHP artisan migrate --force

echo "==> Cachés"
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache
$PHP artisan storage:link || true

echo "==> Reiniciar trabajadores de la cola (toman el código nuevo)"
$PHP artisan queue:restart

echo "==> Recargar PHP-FPM (limpia OPcache)"
sudo systemctl reload php8.5-fpm

$PHP artisan up
echo "==> Listo"
