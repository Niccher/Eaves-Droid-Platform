# Use the official PHP Apache image
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    nano \
    libicu-dev \
    default-mysql-client \
    libonig-dev \
    unzip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install mysqli pdo pdo_mysql intl

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN cat <<EOF > /etc/apache2/sites-available/000-default.conf
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

    <Directory "/var/www/html/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOF

RUN a2enmod rewrite
RUN service apache2 restart
# Copy application files to the container
COPY .. /var/www/html

RUN chmod -R 777 /var/www/html/writable/session

# Expose port 80 for web traffic
EXPOSE 80