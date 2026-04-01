# Step 1: Build & Dependencies
FROM php:8.4-fpm-alpine AS builder

WORKDIR /var/www/html
USER root

RUN apk add --no-cache \
    git \
    $PHPIZE_DEPS \
    libpq-dev \
    libzip-dev \
    zip \
    libpng-dev \
    libxml2-dev \
    icu-dev \
    oniguruma-dev \
    curl-dev

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install -j$(nproc) \
    exif \
    pcntl \
    bcmath \
    ctype \
    zip \
    pdo \
    pdo_pgsql \
    pgsql \
    intl \
    opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------

# Step 2: Runtime (Final Image)
FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

# git добавлен сюда, чтобы он был доступен пользователю www изначально
RUN apk add --no-cache \
    libpq \
    libzip \
    libpng \
    libxml2 \
    icu-libs \
    oniguruma \
    nodejs \
    npm \
    bash \
    git

COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d
COPY --from=builder /usr/bin/composer /usr/bin/composer

RUN addgroup -g 1000 www && adduser -u 1000 -G www -D www

COPY --chown=www:www ./src /var/www/html

USER www

EXPOSE 9000

CMD ["php-fpm"]
