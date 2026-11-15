#!/bin/sh
set -e

for variable in APP_ENV APP_DEBUG APP_URL DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD; do
    valor=$(printenv "$variable" || true)
    [ -n "$valor" ] && sed -i "s|^#\?\s*$variable=.*|$variable=$valor|" .env
done

php artisan config:clear
php artisan migrate --force
php artisan db:seed --force --class=PersonajeSeeder --no-interaction

exec php artisan serve --host=0.0.0.0 --port=8000
