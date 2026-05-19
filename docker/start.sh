#!/bin/sh
set -e

mkdir -p /run/php

# Run migrations
php /app/artisan migrate --force

# Start PHP-FPM
php-fpm -D

sleep 2

# Start Nginx
nginx -g 'daemon off;'
