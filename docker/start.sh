#!/bin/sh
set -e

# Run migrations (optional, hapus kalau tidak perlu)
php /app/artisan migrate --force || true

# Start PHP-FPM
mkdir -p /run/php
php-fpm -D

# Start Nginx
nginx -g 'daemon off;'
