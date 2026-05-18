FROM php:8.3-apache

WORKDIR /app

# Install system dependencies and build tools
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    curl \
    wget \
    git \
    unzip \
    libssl-dev \
    libcurl4-openssl-dev \
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

# Enable Apache modules (disable conflicting MPMs first)
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true && \
    a2enmod mpm_prefork rewrite headers

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Generate APP_KEY if needed
RUN cp .env.example .env && php artisan key:generate --force || true

# Set permissions
RUN chown -R www-data:www-data /app && \
    chmod -R 755 /app && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# Configure Apache to serve Laravel from public directory
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /app/public|g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's|/var/www/html|/app/public|g' /etc/apache2/apache2.conf

# Add health check endpoint script
RUN cat > /usr/local/bin/health-check.sh << 'EOF'
#!/bin/bash
response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/health || echo "000")
if [ "$response" = "200" ] || [ "$response" = "404" ]; then
  exit 0
else
  exit 1
fi
EOF
RUN chmod +x /usr/local/bin/health-check.sh

# Add health check to Docker
HEALTHCHECK --interval=10s --timeout=5s --start-period=30s --retries=3 \
  CMD /usr/local/bin/health-check.sh

EXPOSE 80

CMD ["apache2-foreground"]

