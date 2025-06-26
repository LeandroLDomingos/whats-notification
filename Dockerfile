# Estágio 1: Build dos assets com Node.js
FROM node:18-alpine as assets
WORKDIR /app
COPY package.json yarn.lock ./
RUN yarn install
COPY . .
RUN yarn build

# Estágio 2: Build da aplicação PHP
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Instalação de dependências do sistema e extensões do PHP
RUN apk add --no-cache $PHPIZE_DEPS \
    && apk add --no-cache \
        nginx \
        supervisor \
        curl \
        libzip-dev \
        zip \
        unzip \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip bcmath pcntl \
    && apk del $PHPIZE_DEPS

# Instalação do Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Cópia dos arquivos da aplicação
COPY --from=assets /app/vendor /var/www/html/vendor
COPY --from=assets /app /var/www/html

# Configuração do Nginx e Supervisor
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Cópia dos assets compilados
COPY --from=assets /app/public /var/www/html/public

# Permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]