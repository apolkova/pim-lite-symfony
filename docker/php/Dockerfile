FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git unzip autoconf pkg-config \
    libzip-dev libicu-dev libpng-dev libonig-dev \
    librabbitmq-dev libssl-dev \
 && docker-php-ext-install pdo pdo_mysql intl zip \
 # --------- PECL: amqp ----------
 && pecl install amqp \
 && docker-php-ext-enable amqp \
 # -------------------------------
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
