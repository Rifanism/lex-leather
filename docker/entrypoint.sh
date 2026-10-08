#!/bin/sh
set -eu

# Render injects $PORT (default 10000).
PORT="${PORT:-10000}"
sed -i "s/listen 10000;/listen ${PORT};/" /etc/nginx/nginx.conf

# Persistent disk (Render mount /var/data) — SQLite + uploaded photos.
mkdir -p /var/data/uploads
touch /var/data/database.sqlite
chown -R www-data:www-data /var/data   # php-fpm workers run as www-data
rm -rf storage/app/public
ln -s /var/data/uploads storage/app/public

php artisan storage:link --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force
php artisan db:seed --force   # idempotent (firstOrCreate everywhere)

exec supervisord -c /etc/supervisord.conf
