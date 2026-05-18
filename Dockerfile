FROM php:8.3-fpm as php-builder

WORKDIR /app

# Install system dependencies BEFORE PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    autoconf \
    automake \
    curl \
    git \
    libcurl4-openssl-dev \
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    libpng-dev \
    libpq-dev \
    libsodium-dev \
    libssl-dev \
    libxml2-dev \
    libzip-dev \
    pkg-config \
    unzip \
    zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd \
        --with-freetype=/usr/include/ \
        --with-jpeg=/usr/include/ && \
    docker-php-ext-configure intl && \
    docker-php-ext-install \
    bcmath \
    curl \
    gd \
    intl \
    mbstring \
    pdo_mysql \
    pdo_pgsql \
    xml \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Generate APP_KEY
RUN cp .env.example .env && php artisan key:generate --force || true

# --- Nginx stage ---
FROM nginx:alpine

WORKDIR /app

# Install curl for health checks
RUN apk add --no-cache curl

# Copy PHP files from builder
COPY --from=php-builder /app /app

# Copy PHP-FPM from builder
COPY --from=php-builder /usr/local/bin/php /usr/local/bin/php
COPY --from=php-builder /usr/local/lib/php /usr/local/lib/php
COPY --from=php-builder /usr/local/etc/php /usr/local/etc/php

# Set permissions
RUN chown -R 82:82 /app && chmod -R 755 /app && chmod -R 775 /app/storage /app/bootstrap/cache

# Configure Nginx - use printf for better compatibility with Alpine
RUN printf 'server {\n    listen 80 default_server;\n    listen [::]:80 default_server;\n    server_name _;\n    root /app/public;\n    index index.php index.html index.htm;\n    client_max_body_size 100M;\n\n    location / {\n        try_files $uri $uri/ /index.php?$query_string;\n    }\n\n    location ~ \.php$ {\n        fastcgi_pass 127.0.0.1:9000;\n        fastcgi_index index.php;\n        fastcgi_param SCRIPT_FILENAME /app/public$fastcgi_script_name;\n        include fastcgi_params;\n        fastcgi_buffer_size 128k;\n        fastcgi_buffers 4 256k;\n    }\n\n    location ~ /\.ht {\n        deny all;\n    }\n}\n' > /etc/nginx/conf.d/default.conf

# Health check script
RUN mkdir -p /usr/local/bin && \
    echo '#!/bin/sh' > /usr/local/bin/health-check.sh && \
    echo 'curl -f http://localhost/ || exit 1' >> /usr/local/bin/health-check.sh && \
    chmod +x /usr/local/bin/health-check.sh

# Health check
HEALTHCHECK --interval=10s --timeout=5s --start-period=20s --retries=3 \
  CMD /usr/local/bin/health-check.sh

EXPOSE 80

# Start both PHP-FPM and Nginx
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]

