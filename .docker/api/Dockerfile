# ./app/Dockerfile

FROM php:8.3-apache

ENV TZ=Europe/Berlin

# Install system dependencies und PHP-Erweiterungen
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    ca-certificates \
    openssl libssl-dev \
    && update-ca-certificates \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

RUN sed -ri -e 's!/var/www/html!/var/www/symfony/public!g' /etc/apache2/sites-available/000-default.conf

RUN echo "openssl.cafile=/etc/ssl/certs/ca-certificates.crt" > /usr/local/etc/php/conf.d/99-ca-certificates.ini

# Copy PHP configuration
COPY conf.d/upload-limits.ini /usr/local/etc/php/conf.d/

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/symfony

# Nur die notwendigen Verzeichnisse erstellen
RUN mkdir -p config/secrets/prod \
    && mkdir -p config/jwt \
    && mkdir -p var/cache \
    && mkdir -p var/log \
    && chmod -R 777 config/secrets config/jwt var

# Install PHP dependencies via Composer
#RUN composer install --no-interaction --optimize-autoloader --no-dev

# Set file permissions (optional, je nach Benutzer)
#RUN chown -R www-data:www-data /var/www/symfony/var /var/www/symfony/vendor

EXPOSE 80
CMD ["apache2-foreground"]
