FROM php:8.3-apache

WORKDIR /app

# Install system dependencies and build tools
RUN apt-get update && apt-get install -y \
    build-essential \
    curl \
    wget \
    git \
    unzip \
    libssl-dev \
    libcurl4-openssl-dev \
    zlib1g-dev \
    libxml2-dev \
    libpq-dev \
    libreadline-dev \
    gettext \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libsodium-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    gd \
    pdo_mysql \
    curl \
    xml \
    mbstring \
    zip \
    bcmath \
    intl \
    pdo_pgsql

# Enable Apache modules
RUN a2enmod rewrite headers

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Set permissions
RUN chown -R www-data:www-data /app && \
    chmod -R 755 /app && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# Configure Apache for Laravel
RUN sed -i 's|/var/www/html|/app/public|g' /etc/apache2/sites-available/000-default.conf && \
    echo "<Directory /app/public>" >> /etc/apache2/sites-available/000-default.conf && \
    echo "  Options Indexes FollowSymLinks" >> /etc/apache2/sites-available/000-default.conf && \
    echo "  AllowOverride All" >> /etc/apache2/sites-available/000-default.conf && \
    echo "  Require all granted" >> /etc/apache2/sites-available/000-default.conf && \
    echo "</Directory>" >> /etc/apache2/sites-available/000-default.conf

# Copy .env.example if .env doesn't exist
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Generate app key
RUN php artisan key:generate --force || true

# Create startup script
RUN echo '#!/bin/bash\nphp artisan migrate --force\napache2-foreground' > /start.sh && chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
