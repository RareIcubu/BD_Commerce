FROM php:8.2-fpm

# Instalacja zależności systemowych
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install pdo_mysql zip mbstring

# Instalacja Composera
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-interaction --prefer-dist --optimize-autoloader
COPY . .
RUN composer run-script post-autoload-dump

# Kopiowanie reszty projektu
COPY . .

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

