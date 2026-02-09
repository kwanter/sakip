# SAKIP Application Dockerfile
# Multi-stage build for production-ready Laravel application

# Stage 1: Composer dependencies
FROM composer:2.8 AS composer

WORKDIR /app

# Copy composer files and install dependencies
COPY composer.json composer.lock* ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

# Stage 2: PHP/Apache with extensions
FROM php:8.4-apache

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    locales \
    && rm -rf /var/lib/apt/lists/*

# Set locale to Indonesia
RUN sed -i 's/# id_ID.UTF-8 UTF-8/id_ID.UTF-8 UTF-8/' /etc/locale.gen \
    && locale-gen id_ID.UTF-8 \
    && update-locale LANG=id_ID.UTF-8

ENV LC_ALL=id_ID.UTF-8
ENV LANG=id_ID.UTF-8

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    bcmath \
    exif \
    gd \
    mbstring \
    mysqli \
    pdo \
    pdo_mysql \
    zip \
    opcache

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Enable Apache modules
RUN a2enmod rewrite headers expires

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy Apache virtual host configuration
COPY docker/apache/sakip.conf /etc/apache2/sites-available/sakip.conf
RUN a2dissite 000-default.conf
RUN a2ensite sakip.conf

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/memory_limit = .*/memory_limit = 256M/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/max_execution_time = .*/max_execution_time = 300/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/upload_max_filesize = .*/upload_max_filesize = 50M/' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/post_max_size = .*/post_max_size = 50M/' "$PHP_INI_DIR/php.ini"

# Configure OPcache for production
RUN docker-php-ext-enable opcache \
    && echo 'opcache.memory_consumption=128' >> "$PHP_INI_DIR/conf.d/10-opcache.ini" \
    && echo 'opcache.interned_strings_buffer=8' >> "$PHP_INI_DIR/conf.d/10-opcache.ini" \
    && echo 'opcache.max_accelerated_files=10000' >> "$PHP_INI_DIR/conf.d/10-opcache.ini" \
    && echo 'opcache.revalidate_freq=2' >> "$PHP_INI_DIR/conf.d/10-opcache.ini" \
    && echo 'opcache.fast_shutdown=1' >> "$PHP_INI_DIR/conf.d/10-opcache.ini" \
    && echo 'opcache.enable_cli=1' >> "$PHP_INI_DIR/conf.d/10-opcache.ini"

# Create application directory
WORKDIR /var/www/html

# Copy Composer dependencies from composer stage
COPY --from=composer /app/vendor ./vendor

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && chmod -R 755 /var/www/html/app/Services

# Generate application key
RUN php artisan key:generate --force || true

# Clear and cache configurations
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && chmod -R 755 /var/www/html/storage/framework

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Copy startup script
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
