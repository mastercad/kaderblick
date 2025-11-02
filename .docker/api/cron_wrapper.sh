#!/bin/bash
set -e

# Symfony Projektpfad
APP_DIR="/var/www/symfony"
APP_ENV="prod"

cd "$APP_DIR"

# .env-Dateien in der richtigen Symfony-Reihenfolge laden
# Siehe: https://symfony.com/doc/current/configuration.html#configuration-environments
load_env_file() {
  local file="$1"
  if [ -f "$file" ]; then
    export $(grep -v '^#' "$file" | xargs)
  fi
}

# Baseline
load_env_file ".env"
# Environment-spezifische Overrides
load_env_file ".env.local"
load_env_file ".env.$APP_ENV"
load_env_file ".env.$APP_ENV.local"

# PHP-Konsole mit allen übergebenen Parametern aufrufen
exec /usr/local/bin/php bin/console "$@"
