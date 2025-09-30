# PHP 8.2 FPM
FROM php:8.2-fpm

WORKDIR /var/www/html

# Sistеma bog'lamalari va PHP kengaytmalari
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    nodejs npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# BUTUN loyihani ko'chiramiz (artisan ham kiradi)
COPY . .

# PHP dep'lar
RUN composer install --no-dev --optimize-autoloader --no-interaction

# (ixtiyoriy) Frontend build
# Agar sizda Node build kerak bo'lsa qoldiring, aks holda bu ikki qatorni o'chiring
RUN npm ci || npm install
RUN npm run build || true

# Ruxsatlar
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
