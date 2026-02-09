FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    icu-dev \
    curl-dev \
    zlib-dev \
    build-base \
    autoconf \
    unzip \
    wget \
    git \
    nodejs \
    npm

# Install PHP extensions - many are already built-in for PHP 8.3
RUN docker-php-ext-install -j$(nproc) \
    gd \
    pdo_mysql \
    mysqli \
    zip \
    intl \
    bcmath \
    opcache \
    curl

# Install Redis extension via pecl
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy PHP ini configuration
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Install Node.js dependencies globally
RUN npm install -g cross-env

# Set permissions
RUN chown -R www-data:www-data /var/www

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]