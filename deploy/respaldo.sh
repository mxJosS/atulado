#!/usr/bin/env bash
# Respaldo cifrado de la base de datos de A Tu Lado.
#
# Uso:   APP_DIR=/var/www/atulado bash deploy/respaldo.sh
#
# Genera respaldos/atulado-AAAAMMDD-HHMMSS.sql.gz.enc cifrado con AES-256 y
# borra los de más de RESPALDOS_DIAS días. Si defines RESPALDO_COPIAR, lo
# ejecuta con la ruta del archivo para mandarlo fuera del servidor, p. ej.:
#   RESPALDO_COPIAR='rclone copy {} remoto:atulado-respaldos'
#
# Instalación (una vez):
#   sudo mkdir -p /etc/atulado
#   sudo sh -c 'openssl rand -base64 48 > /etc/atulado/respaldo.key'
#   sudo chown root:www-data /etc/atulado/respaldo.key && sudo chmod 0640 /etc/atulado/respaldo.key
#   Guarda una copia de esa llave Y del APP_KEY del .env FUERA del servidor
#   (gestor de contraseñas). Sin ellas, los respaldos y los datos cifrados no se pueden leer.
#
# Programarlo todos los días a las 03:30 (crontab del usuario que despliega):
#   30 3 * * * APP_DIR=/var/www/atulado bash /var/www/atulado/deploy/respaldo.sh >> $HOME/respaldo.log 2>&1
#
# Restaurar (probarlo de vez en cuando en otra base):
#   openssl enc -d -aes-256-cbc -pbkdf2 -iter 200000 -pass file:/etc/atulado/respaldo.key \
#     -in atulado-AAAAMMDD-HHMMSS.sql.gz.enc | gunzip | mysql -u USUARIO -p BASE_DE_PRUEBA
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/atulado}"
RESPALDOS="${RESPALDOS:-$HOME/respaldos-atulado}"
RESPALDOS_DIAS="${RESPALDOS_DIAS:-30}"
LLAVE="${RESPALDO_LLAVE:-/etc/atulado/respaldo.key}"

if [ ! -r "$LLAVE" ]; then
  echo "ERROR: no existe la llave de respaldo $LLAVE. Ver las instrucciones al inicio de deploy/respaldo.sh." >&2
  exit 1
fi

cd "$APP_DIR"
DB_DATABASE=$(grep -E '^DB_DATABASE=' .env | cut -d= -f2-)
DB_USERNAME=$(grep -E '^DB_USERNAME=' .env | cut -d= -f2-)
DB_PASSWORD=$(grep -E '^DB_PASSWORD=' .env | cut -d= -f2-)

umask 077
mkdir -p "$RESPALDOS"
ARCHIVO="$RESPALDOS/atulado-$(date +%Y%m%d-%H%M%S).sql.gz.enc"

MYSQL_PWD="$DB_PASSWORD" mysqldump --single-transaction --routines -u "$DB_USERNAME" "$DB_DATABASE" \
  | gzip \
  | openssl enc -aes-256-cbc -pbkdf2 -iter 200000 -salt -pass file:"$LLAVE" -out "$ARCHIVO"

echo "Respaldo cifrado: $ARCHIVO"

find "$RESPALDOS" -name 'atulado-*.sql.gz.enc' -mtime +"$RESPALDOS_DIAS" -delete
# Los respaldos viejos sin cifrar (de versiones anteriores de deploy.sh) también se retiran.
find "$RESPALDOS" -name 'atulado-*.sql.gz' -mtime +"$RESPALDOS_DIAS" -delete

if [ -n "${RESPALDO_COPIAR:-}" ]; then
  eval "${RESPALDO_COPIAR//\{\}/\"$ARCHIVO\"}"
  echo "Copia externa hecha."
fi
