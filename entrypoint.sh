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
        echo "MySQL is ready! Running database migrations..."
        cd /var/www/html
        php spark migrate --all 2>&1 || echo "WARNING: Migration encountered an issue. Check logs."
    fi
else
    echo "NOTICE: No external database host provided (MYSQLHOST is empty). Skipping DB migrations."
fi

echo "Migrations complete. Starting Apache..."

LISTEN_PORT="${PORT:-80}"
echo "Configuring Apache to listen on port ${LISTEN_PORT}..."
sed -i "s/Listen 80/Listen ${LISTEN_PORT}/g" /etc/apache2/ports.conf 2>/dev/null || true
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${LISTEN_PORT}>/g" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true

exec apache2-foreground
