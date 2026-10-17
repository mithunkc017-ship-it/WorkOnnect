
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    $PHPIZE_DEPS \
    libssl-dev \
    unzip \
    && pecl install mongodb-2.1.7 \
    && docker-php-ext-enable mongodb \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

COPY . .

RUN a2enmod rewrite \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
