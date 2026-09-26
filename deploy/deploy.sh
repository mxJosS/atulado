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

echo "==> Revisión del .env de producción"
if ! grep -qE '^APP_ENV=production$' .env || ! grep -qE '^APP_DEBUG=false$' .env; then
  echo "ERROR: el .env del servidor debe tener APP_ENV=production y APP_DEBUG=false." >&2
  echo "       Con APP_DEBUG=true un error muestra código, rutas y variables a cualquiera." >&2
  exit 1
fi

echo "==> Respaldo cifrado de la base de datos"
APP_DIR="$APP_DIR" RESPALDOS="$RESPALDOS" bash "$APP_DIR/deploy/respaldo.sh"

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
