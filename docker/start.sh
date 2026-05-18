#!/bin/sh
set -e

mkdir -p /run/php

# Start PHP-FPM
php-fpm -D

sleep 2

# Start Nginx
nginx -g 'daemon off;'
