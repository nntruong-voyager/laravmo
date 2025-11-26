FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libzookeeper-mt-dev \
    librdkafka-dev \
    && docker-php-ext-configure gd --with-jpeg=/usr/include/ \
    && docker-php-ext-install pdo_mysql bcmath gd intl \
    && pecl install zookeeper rdkafka \
    && docker-php-ext-enable zookeeper rdkafka \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install --no-interaction --no-dev --prefer-dist

COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache vendor

CMD ["php-fpm"]

