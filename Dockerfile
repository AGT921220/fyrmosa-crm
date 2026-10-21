FROM php:8.4-fpm

ARG INSTALL_XDEBUG=false
ARG REDIS_VERSION=6.2.0
ARG XDEBUG_VERSION=3.4.5

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    nano \
    default-mysql-client \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libpq-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libsqlite3-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        pdo_sqlite \
        mysqli \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        soap \
        zip \
    && curl -fsSL "https://github.com/phpredis/phpredis/archive/refs/tags/${REDIS_VERSION}.tar.gz" -o /tmp/redis.tgz \
    && mkdir -p /tmp/redis \
    && tar -xzf /tmp/redis.tgz -C /tmp/redis --strip-components=1 \
    && cd /tmp/redis \
    && phpize \
    && ./configure \
    && make -j"$(nproc)" \
    && make install \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/redis /tmp/redis.tgz \
    && if [ "$INSTALL_XDEBUG" = "true" ]; then \
        curl -fsSL "https://github.com/xdebug/xdebug/archive/refs/tags/${XDEBUG_VERSION}.tar.gz" -o /tmp/xdebug.tgz \
        && mkdir -p /tmp/xdebug \
        && tar -xzf /tmp/xdebug.tgz -C /tmp/xdebug --strip-components=1 \
        && cd /tmp/xdebug \
        && phpize \
        && ./configure \
        && make -j"$(nproc)" \
        && make install \
        && docker-php-ext-enable xdebug \
        && rm -rf /tmp/xdebug /tmp/xdebug.tgz ; \
    fi \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && if [ "$INSTALL_XDEBUG" != "true" ]; then rm -f /usr/local/etc/php/conf.d/xdebug.ini; fi \
    && chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
