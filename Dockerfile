FROM php:8.3-fpm as php-builder

WORKDIR /app

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    curl \
    git \
    libicu-dev \
    libjpeg-dev \
    libonig-dev \
    libpng-dev \
    libfreetype6-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
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

# Configure Nginx
RUN cat > /etc/nginx/conf.d/default.conf << 'EOF'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root /app/public;
    index index.php index.html index.htm;
    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME /app/public$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

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

