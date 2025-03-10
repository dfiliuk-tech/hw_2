FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libsqlite3-dev \
    zip

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite

# Install PCOV for code coverage
RUN pecl install pcov && docker-php-ext-enable pcov

# Install Xdebug for test coverage reports
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create database directory
RUN mkdir -p /var/www/html/database

# Set directory permissions
RUN chown -R www-data:www-data /var/www/html

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose port 9000 (PHP-FPM)
EXPOSE 9000

# Set entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Start PHP-FPM
CMD ["php-fpm"]