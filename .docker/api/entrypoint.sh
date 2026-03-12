#!/bin/bash
set -e

# var/ wird durch den Volume-Mount (./api:/var/www/symfony) zur Laufzeit überschrieben.
# Die Unterverzeichnisse können dem Host-User gehören (z.B. UID 10000) – www-data hat dann
# nur "other"-Rechte und kann nicht schreiben.
# Lösung: var/cache und var/log beim Start für alle beschreibbar machen.
mkdir -p /var/www/symfony/var/cache /var/www/symfony/var/log
chmod -R 777 /var/www/symfony/var/cache /var/www/symfony/var/log

exec "$@"
