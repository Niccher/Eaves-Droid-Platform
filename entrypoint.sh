#!/bin/bash
set -e

# Extract DB credentials from environment or defaults (Railway maps MYSQLHOST, MYSQLPORT, etc.)
DB_HOST="${MYSQLHOST:-${DB_HOST:-mysql}}"
DB_PORT="${MYSQLPORT:-${DB_PORT:-3306}}"
DB_USER="${MYSQLUSER:-${DB_USER:-root}}"
DB_PASS="${MYSQLPASSWORD:-${MYSQL_ROOT_PASSWORD:-${DB_PASS:-root_password}}}"

if [ -n "$MYSQLHOST" ] || [ -n "$DB_HOST_CUSTOM" ]; then
    echo "Waiting for MySQL to accept connections at ${DB_HOST}:${DB_PORT}..."
    max_retries=10
    count=0
    while ! php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT}', '${DB_USER}', '${DB_PASS}');" 2>/dev/null; do
        count=$((count + 1))
        if [ $count -ge $max_retries ]; then
            echo "WARNING: Could not connect to MySQL at ${DB_HOST}:${DB_PORT} within $max_retries retries."
            break
        fi
        echo "MySQL not ready yet... retrying ($count/$max_retries)"
        sleep 2
    done

    if php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT}', '${DB_USER}', '${DB_PASS}');" 2>/dev/null; then
        echo "MySQL is ready! Running database migrations & seeders..."
        cd /var/www/html
        php spark migrate --all 2>&1 || echo "WARNING: Migration encountered an issue. Check logs."
        php spark db:seed DatabaseSeeder 2>&1 || echo "WARNING: Seeding encountered an issue. Check logs."
    fi
else
    echo "NOTICE: No external database host provided (MYSQLHOST is empty). Skipping DB migrations."
fi

echo "Migrations complete. Starting Apache..."

# Ensure strictly one MPM module (mpm_prefork) is loaded at runtime
rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
      /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf
if [ ! -f /etc/apache2/mods-enabled/mpm_prefork.load ]; then
    ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
    ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf 2>/dev/null || true
fi

LISTEN_PORT="${PORT:-80}"
echo "Configuring Apache to listen on port ${LISTEN_PORT}..."

cat <<EOF > /etc/apache2/ports.conf
Listen ${LISTEN_PORT}

<IfModule ssl_module>
	Listen 443
</IfModule>

<IfModule gnutls_module>
	Listen 443
</IfModule>
EOF

cat <<EOF > /etc/apache2/sites-available/000-default.conf
<VirtualHost *:${LISTEN_PORT}>
	ServerAdmin webmaster@localhost
	DocumentRoot /var/www/html/public

	<Directory /var/www/html/public>
		Options Indexes FollowSymLinks
		AllowOverride All
		Require all granted
	</Directory>

	ErrorLog \${APACHE_LOG_DIR}/error.log
	CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

grep -q "ServerName localhost" /etc/apache2/apache2.conf || echo "ServerName localhost" >> /etc/apache2/apache2.conf

echo "Starting background cron runner daemon..."
(
    while true; do
        sleep 60
        php /var/www/html/spark cron:run >> /var/log/cron_runner.log 2>&1 || true
    done
) &

echo "Starting background Redis upload worker..."
(
    while true; do
        php /var/www/html/spark worker:upload >> /var/log/upload_worker.log 2>&1 || true
        sleep 5
    done
) &

exec apache2-foreground
