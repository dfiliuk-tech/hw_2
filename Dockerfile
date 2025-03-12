# Base PHP image with common dependencies
FROM php:8.3-fpm AS base

# Install system dependencies and PHP extensions in a single layer
# This reduces image size and improves build time
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_sqlite \
        mysqli \
        zip \
        mbstring \
        xml \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Development image with debugging tools
FROM base AS development

# Install development dependencies
# Note: Xdebug must use PECL as it's not bundled with PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer with specific version for stability
COPY --from=composer:2.6.5 /usr/bin/composer /usr/bin/composer

# Create database directory and set permissions
RUN mkdir -p /var/www/html/database \
    && chown -R www-data:www-data /var/www/html

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose port 9000 (PHP-FPM)
EXPOSE 9000

# Set entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Start PHP-FPM
CMD ["php-fpm"]

# Dependencies builder stage for production
FROM base AS builder

# Install build dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer with specific version
COPY --from=composer:2.6.5 /usr/bin/composer /usr/bin/composer

# Copy only composer files first for better layer caching
COPY composer.json composer.lock* ./

# Install production dependencies without dev dependencies
RUN composer install --no-dev --no-scripts --no-autoloader

# Production image (smaller footprint)
FROM base AS production

# Copy application files
COPY . .

# Copy vendor directory from builder stage
COPY --from=builder /var/www/html/vendor /var/www/html/vendor

# Generate optimized autoloader
COPY --from=composer:2.6.5 /usr/bin/composer /usr/bin/composer
RUN composer dump-autoload --no-dev --optimize

# Create database directory and set permissions
RUN mkdir -p /var/www/html/database \
    && chown -R www-data:www-data /var/www/html

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose port 9000 (PHP-FPM)
EXPOSE 9000

# Set entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Start PHP-FPM
CMD ["php-fpm"]