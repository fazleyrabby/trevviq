#!/bin/sh
set -e

cd /var/www/html

# ---------------------------------------------------------------------------
# Writable runtime directories
# ---------------------------------------------------------------------------
mkdir -p \
    storage/framework/sessions \
    storage/framework/cache/data \
    storage/framework/views \
    storage/logs \
    storage/tmp \
    storage/app/backups \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Public storage symlink (idempotent)
php artisan storage:link --force >/dev/null 2>&1 || true

# Generate an app key if none was provided and a .env file is writable.
if [ -z "${APP_KEY}" ] && [ -f .env ]; then
    echo "[entrypoint] APP_KEY missing — generating one."
    php artisan key:generate --force >/dev/null 2>&1 || true
fi

# ---------------------------------------------------------------------------
# Wait for the database
# ---------------------------------------------------------------------------
if [ -n "${DB_HOST}" ]; then
    echo "[entrypoint] Waiting for database ${DB_HOST}:${DB_PORT:-3306}..."
    tries=0
    until php -r 'exit(@fsockopen(getenv("DB_HOST"), (int)(getenv("DB_PORT") ?: 3306)) ? 0 : 1);' >/dev/null 2>&1; do
        tries=$((tries + 1))
        if [ "$tries" -ge 30 ]; then
            echo "[entrypoint] Database still unreachable after 60s — starting anyway."
            break
        fi
        sleep 2
    done
fi

# ---------------------------------------------------------------------------
# Migrations + caches
# ---------------------------------------------------------------------------
php artisan migrate --force || echo "[entrypoint] migrate failed — starting anyway."

# Always ensure the bootstrap admin account exists (idempotent).
php artisan db:seed --class='Database\Seeders\AdminUserSeeder' --force || echo "[entrypoint] admin seed skipped."

# Optionally seed the demo dataset (idempotent). Enable with ROAM_SEED_DEMO=true.
if [ "${ROAM_SEED_DEMO}" = "true" ]; then
    echo "[entrypoint] Seeding demo data (ROAM_SEED_DEMO=true)..."
    php artisan db:seed --class='Database\Seeders\DemoSeeder' --force || echo "[entrypoint] demo seed skipped."
fi

php artisan config:clear >/dev/null 2>&1 || true
php artisan config:cache || true
php artisan route:cache || true

exec "$@"
