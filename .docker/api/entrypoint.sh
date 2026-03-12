#!/bin/bash
set -e

# Wenn HOST_UID/HOST_GID gesetzt sind, www-data auf die UID/GID des Host-Users remappen.
# Dadurch passen die Rechte auf dem Bind-Mount (./api:/var/www/symfony) ohne chown.
if [ -n "$HOST_UID" ] && [ -n "$HOST_GID" ]; then
    groupmod -g "$HOST_GID" --non-unique www-data
    usermod -u "$HOST_UID" --non-unique www-data
fi

mkdir -p /var/www/symfony/var/cache /var/www/symfony/var/log
chown -R www-data:www-data /var/www/symfony/var
chmod -R 775 /var/www/symfony/var

exec "$@"
