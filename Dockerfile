# ==============================================================================
# Stage 1: Build frontend assets (Vite / Tailwind)
# ==============================================================================
FROM node:22-alpine AS frontend-builder
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

# ==============================================================================
# Stage 2: Install Composer dependencies
# ==============================================================================
FROM composer:2 AS vendor-builder
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-reqs

# ==============================================================================
# Stage 3: Final production image (PHP 8.3 + Nginx + PHP-FPM)
# ==============================================================================
FROM php:8.4-fpm-alpine

# Install runtime packages
RUN apk add --no-cache \
    bash \
    curl \
    freetype \
    icu-libs \
    libjpeg-turbo \
    libpng \
    libpq \
    libwebp \
    libzip \
    nginx \
    sqlite-libs

# Install build dependencies, build PHP extensions, and remove build dependencies
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    freetype-dev \
    icu-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxml2-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    sqlite-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        zip \
    && apk del .build-deps

WORKDIR /var/www/html

# Copy application source code
COPY . .

# Copy production PHP dependencies from vendor-builder
COPY --from=vendor-builder /app/vendor /var/www/html/vendor

# Copy compiled frontend assets from frontend-builder
COPY --from=frontend-builder /app/public/build /var/www/html/public/build

# Copy configuration files and entrypoint script
RUN rm -f /etc/nginx/http.d/default.conf
COPY docker/nginx.conf /etc/nginx/http.d/default.conf.template
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Configure permissions and discover packages
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && php artisan package:discover --ansi \
    && php artisan filament:upgrade --ansi

EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
