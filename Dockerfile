FROM php:8.3-apache

# System deps needed to build the mongodb extension, plus fileinfo (used for
# upload MIME validation) which isn't enabled by default on this base image.
RUN apt-get update && apt-get install -y --no-install-recommends \
        libssl-dev \
        pkg-config \
        unzip \
        git \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && docker-php-ext-install fileinfo \
    && apt-get purge -y --auto-remove pkg-config \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install dependencies first so this layer is cached unless composer.json/lock change.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --optimize-autoloader

COPY . .

EXPOSE 80
