# =============================================================
# Dockerfile — Backend Laravel (Render.com)
# Menggunakan php:8.3-apache agar bisa serve HTTP langsung
# =============================================================
FROM php:8.3-apache

# Install ekstensi PHP yang dibutuhkan
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        intl \
        mbstring \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Aktifkan mod_rewrite untuk Laravel routing
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set document root ke public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copy composer files dulu untuk cache layer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy semua file project
COPY . .

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Jalankan post-install scripts
RUN composer run-script post-autoload-dump

EXPOSE 80

# Jalankan migrate lalu start Apache
CMD php artisan migrate --force --no-interaction \
    && php artisan config:cache \
    && php artisan route:cache \
    && apache2-foreground
