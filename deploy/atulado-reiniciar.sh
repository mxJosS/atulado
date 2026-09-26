#!/usr/bin/env bash
# Reinicio desde el monitor del servidor de A Tu Lado.
#
# La app (usuario www-data) no puede reiniciar nada por sí misma: llama a este
# script con «sudo -n» y la regla de deploy/atulado-reiniciar.sudoers sólo le
# permite ejecutar ESTE archivo, con uno de dos argumentos:
#
#   servicios  reinicia MySQL, PHP-FPM, Nginx y la cola (la máquina sigue encendida)
#   maquina    reinicia el servidor completo
#
# El reinicio se agenda a 5 segundos con systemd-run para que la página reciba
# la respuesta antes de que se caiga PHP-FPM.
#
# Instalación (una vez, como root):
#   sudo install -o root -g root -m 0755 deploy/atulado-reiniciar.sh /usr/local/sbin/atulado-reiniciar
#   sudo install -o root -g root -m 0440 deploy/atulado-reiniciar.sudoers /etc/sudoers.d/atulado-reiniciar
#   sudo visudo -cf /etc/sudoers.d/atulado-reiniciar
#
# Comprueba (no reinicia nada):
#   sudo -u www-data sudo -n -l /usr/local/sbin/atulado-reiniciar
#
# Si los servicios se llaman distinto (p. ej. mariadb o php8.4-fpm), ajusta SERVICIOS.
set -euo pipefail

SERVICIOS="mysql php8.5-fpm nginx atulado-cola"

case "${1:-}" in
  servicios)
    systemd-run --quiet --collect --on-active=5 --unit="atulado-reinicio-servicios-$(date +%s)" \
      /bin/systemctl restart $SERVICIOS
    logger -t atulado "Reinicio de servicios solicitado desde el monitor: $SERVICIOS"
    ;;
  maquina)
    systemd-run --quiet --collect --on-active=5 --unit="atulado-reinicio-maquina-$(date +%s)" \
      /bin/systemctl reboot
    logger -t atulado "Reinicio del servidor solicitado desde el monitor"
    ;;
  *)
    echo "Uso: $0 servicios|maquina" >&2
    exit 64
    ;;
esac

echo "agendado"
