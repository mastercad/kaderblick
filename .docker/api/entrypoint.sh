#!/bin/bash
set -e

# Wenn HOST_UID/HOST_GID gesetzt sind, www-data auf die UID/GID des Host-Users remappen.
# Dadurch passen die Rechte auf dem Bind-Mount (./api:/var/www/symfony) ohne chown.
if [ -n "$HOST_UID" ] && [ -n "$HOST_GID" ]; then
    groupmod -g "$HOST_GID" www-data 2>/dev/null || true
    usermod -u "$HOST_UID" www-data 2>/dev/null || true
fi

mkdir -p /var/www/symfony/var/cache /var/www/symfony/var/log
chown -R www-data:www-data /var/www/symfony/var
chmod -R 775 /var/www/symfony/var

exec "$@"
