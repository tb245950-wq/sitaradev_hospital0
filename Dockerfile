# Dockerfile untuk deployment Laravel di Render.com
FROM php:8.3-cli

# Install dependencies sistem
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    && docker-php-ext-install \
    pdo_mysql \
    intl \
    zip \
    opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy semua file proyek ke container
COPY . .

# Install dependencies PHP (tanpa dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Buat file SQLite database
RUN touch /var/www/database/database.sqlite

# Set permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Cache konfigurasi Laravel
RUN php artisan config:cache && \
    php artisan route:cache

# Expose port (Render akan override dengan env PORT)
EXPOSE 8000

# Command untuk menjalankan server
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
