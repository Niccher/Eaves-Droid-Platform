#!/bin/bash
set -e

# Extract DB credentials from environment or defaults (Railway maps MYSQLHOST, MYSQLPORT, etc.)
DB_HOST="${MYSQLHOST:-${DB_HOST:-mysql}}"
DB_PORT="${MYSQLPORT:-${DB_PORT:-3306}}"
DB_USER="${MYSQLUSER:-${DB_USER:-root}}"
DB_PASS="${MYSQLPASSWORD:-${MYSQL_ROOT_PASSWORD:-${DB_PASS:-root_password}}}"

echo "Waiting for MySQL to accept connections at ${DB_HOST}:${DB_PORT}..."
max_retries=30
count=0
while ! php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT}', '${DB_USER}', '${DB_PASS}');" 2>/dev/null; do
    count=$((count + 1))
    if [ $count -ge $max_retries ]; then
        echo "ERROR: MySQL did not become ready in time. Starting Apache without migrations."
        exec apache2-foreground
    fi
    echo "MySQL not ready yet... retrying ($count/$max_retries)"
    sleep 2
done

echo "MySQL is ready!"

# Run migrations (safe to run multiple times — only applies pending migrations)
echo "Running database migrations..."
cd /var/www/html
php spark migrate --all 2>&1 || echo "WARNING: Migration encountered an issue. Check logs."

echo "Migrations complete. Starting Apache..."

if [ -n "$PORT" ]; then
    echo "Configuring Apache to listen on port ${PORT}..."
    sed -i "s/80/${PORT}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
fi

exec apache2-foreground
