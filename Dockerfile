FROM php:8.2.8-cli-alpine3.18

# Install composer
COPY --from=composer:2.4.2 /usr/bin/composer /usr/bin/composer

COPY . /app

WORKDIR /app

RUN composer install

CMD ["php", "bin/run"]
