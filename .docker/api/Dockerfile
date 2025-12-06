FROM php:8.3-apache

ENV TZ=Europe/Berlin

RUN apt-get update && apt-get install -yqq \
    git \
    unzip \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    ca-certificates \
    openssl libssl-dev \
    cron \
    && update-ca-certificates \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache bcmath

RUN a2enmod rewrite

RUN sed -ri -e 's!/var/www/html!/var/www/symfony/public!g' /etc/apache2/sites-available/000-default.conf

RUN echo "openssl.cafile=/etc/ssl/certs/ca-certificates.crt" > /usr/local/etc/php/conf.d/99-ca-certificates.ini

COPY conf.d/upload-limits.ini /usr/local/etc/php/conf.d/

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN echo "xdebug.mode=debug" > /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.log=/tmp/xdebug.log" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.log_level=10" >> /usr/local/etc/php/conf.d/xdebug.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY cron_wrapper.sh /usr/local/bin/cron_wrapper.sh

RUN chmod +x /usr/local/bin/cron_wrapper.sh

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN echo "date.timezone=$TZ" > /usr/local/etc/php/conf.d/timezone.ini

WORKDIR /var/www/symfony

RUN mkdir -p config/secrets/prod \
    && mkdir -p config/jwt \
    && mkdir -p var/cache \
    && mkdir -p var/log \
    && chmod -R 777 config/secrets config/jwt var

RUN mkdir -p var/log \
    && chown -R www-data:www-data var/log \
    && chmod -R 775 var/log

# RUN composer install --no-interaction --optimize-autoloader --no-dev

#RUN chown -R www-data:www-data /var/www/symfony/var /var/www/symfony/vendor
RUN chown -R www-data:www-data /var/www/symfony/var

EXPOSE 80

RUN echo "SHELL=/bin/bash" > /etc/cron.d/symfony-crons \
    && echo "PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin" >> /etc/cron.d/symfony-crons \
    && echo "# Collect weather data for events (daily at 3 AM)" >> /etc/cron.d/symfony-crons \
    && echo "0 3 * * * www-data /bin/bash -lc 'cd /var/www/symfony && . /var/www/symfony/.env 2>/dev/null || true && . /var/www/symfony/.env.local 2>/dev/null || true && . /var/www/symfony/.env.$APP_ENV 2>/dev/null || true && . /var/www/symfony/.env.$APP_ENV.local 2>/dev/null || true && /usr/local/bin/cron_wrapper.sh app:collect-weather-data-for-events' 2>&1" >> /etc/cron.d/symfony-crons \
    && echo "" >> /etc/cron.d/symfony-crons \
    && echo "# Calculate historical XP events (every 5 minutes)" >> /etc/cron.d/symfony-crons \
    && echo "*/5 * * * * www-data /bin/bash -lc 'cd /var/www/symfony && . /var/www/symfony/.env 2>/dev/null || true && . /var/www/symfony/.env.local 2>/dev/null || true && . /var/www/symfony/.env.$APP_ENV 2>/dev/null || true && . /var/www/symfony/.env.$APP_ENV.local 2>/dev/null || true && /usr/local/bin/cron_wrapper.sh app:xp:process-historical' 2>&1" >> /etc/cron.d/symfony-crons \
    && echo "" >> /etc/cron.d/symfony-crons \
    && echo "# Process pending XP events (every 5 minutes)" >> /etc/cron.d/symfony-crons \
    && echo "*/5 * * * * www-data /bin/bash -lc 'cd /var/www/symfony && . /var/www/symfony/.env 2>/dev/null || true && . /var/www/symfony/.env.local 2>/dev/null || true && . /var/www/symfony/.env.$APP_ENV 2>/dev/null || true && . /var/www/symfony/.env.$APP_ENV.local 2>/dev/null || true && /usr/local/bin/cron_wrapper.sh app:xp:process-pending' 2>&1" >> /etc/cron.d/symfony-crons \
    && echo "" >> /etc/cron.d/symfony-crons \
    && echo "# Calculate and award titles (daily at 2 AM)" >> /etc/cron.d/symfony-crons \
    && echo "0 2 * * * www-data /bin/bash -lc 'cd /var/www/symfony && . /var/www/symfony/.env 2>/dev/null || true && . /var/www/symfony/.env.local 2>/dev/null || true && . /var/www/symfony/.env.$APP_ENV 2>/dev/null || true && . /var/www/symfony/.env.$APP_ENV.local 2>/dev/null || true && /usr/local/bin/cron_wrapper.sh app:xp:award-titles' 2>&1" >> /etc/cron.d/symfony-crons \
    && chmod 0644 /etc/cron.d/symfony-crons \
    && echo "# Send unsent Notifications (every 5 minutes)" >> /etc/cron.d/symfony-crons \
    && echo "*/5 * * * * www-data /bin/bash -lc 'cd /var/www/symfony && . /var/www/symfony/.env 2>/dev/null || true && . /var/www/symfony/.env.local 2>/dev/null || true && . /var/www/symfony/.env.$APP_ENV 2>/dev/null || true && . /var/www/symfony/.env.$APP_ENV.local 2>/dev/null || true && /usr/local/bin/cron_wrapper.sh app:notifications:send-unsent' 2>&1" >> /etc/cron.d/symfony-crons \
    && chmod 0644 /etc/cron.d/symfony-crons


CMD cron && apache2-foreground
