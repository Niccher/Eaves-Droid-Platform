# syntax=docker/dockerfile:1
FROM php:8.3-fpm

# ── System dependencies ────────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        apache2 \
        zip \
        unzip \
    && rm -rf /var/lib/apt/lists/*

# ── Pre-compiled PHP Extensions (fast binary download vs 10min source compilation)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions intl mysqli pdo_mysql zip gd opcache xml dom

# Enable Apache modules for PHP-FPM and mpm_event
RUN a2enmod mpm_event proxy proxy_fcgi setenvif rewrite headers \
    && a2enconf php*-fpm || true \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && echo "<FilesMatch \\.php$>\n    SetHandler \"proxy:fcgi://127.0.0.1:9000\"\n</FilesMatch>" > /etc/apache2/conf-available/php-fpm.conf \
    && a2enconf php-fpm

# PHP upload and memory limits
RUN { \
        echo "upload_max_filesize = 120M"; \
        echo "post_max_size = 120M"; \
        echo "memory_limit = 512M"; \
    } > /usr/local/etc/php/conf.d/runtime-limits.ini

RUN { \
        echo "opcache.enable=1"; \
        echo "opcache.enable_cli=0"; \
        echo "opcache.memory_consumption=256"; \
        echo "opcache.interned_strings_buffer=16"; \
        echo "opcache.max_accelerated_files=10000"; \
        echo "opcache.validate_timestamps=0"; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Move DocumentRoot to public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# ── Composer dependency layer ─────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --ignore-platform-reqs \
    && rm /usr/bin/composer

# ── Application code ──────────────────────────────────────────────────────────
COPY . /var/www/html

RUN mkdir -p /var/www/html/writable/cache \
             /var/www/html/writable/debugbar \
             /var/www/html/writable/exports \
             /var/www/html/writable/logs \
             /var/www/html/writable/session \
             /var/www/html/writable/uploads/android_captured_audio \
             /var/www/html/writable/uploads/android_captured_files \
             /var/www/html/writable/uploads/android_captured_images \
             /var/www/html/writable/uploads/android_captured_screenshots \
             /var/www/html/writable/uploads/chat_attachments \
    && chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 775 /var/www/html/writable

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
EXPOSE 80
