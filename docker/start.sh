#!/bin/sh
set -e

mkdir -p /run/php

# Force HTTPS
php /app/artisan config:cache
php /app/artisan route:cache
php /app/artisan view:cache

# Start PHP-FPM
php-fpm -D

sleep 2

# Start Nginx
nginx -g 'daemon off;'
