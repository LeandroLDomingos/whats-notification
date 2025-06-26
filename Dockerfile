# Estágio 1: Build dos assets com Node.js
FROM node:18-alpine as assets
WORKDIR /app
COPY package.json yarn.lock ./
RUN yarn install
COPY . .
RUN yarn build

# Estágio 2: Build da aplicação PHP para Produção
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Instalação de dependências do sistema e um conjunto completo de extensões PHP
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
        libxml2-dev \
        oniguruma-dev \
        icu-dev \
        linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip bcmath pcntl sockets exif mbstring soap \
    && apk del $PHPIZE_DEPS

# Instalação do Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia os arquivos do composer e instala as dependências SEM EXECUTAR SCRIPTS
COPY composer.json composer.lock ./
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

# Copia o resto dos arquivos da aplicação
COPY . .

# Gera o autoload, que é seguro de se fazer no build
RUN composer dump-autoload --optimize --no-dev

# Configuração do Nginx e Supervisor
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Cópia dos assets compilados do estágio anterior
COPY --from=assets /app/public /var/www/html/public

# Permissões finais
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

# O comando final agora é apenas iniciar o supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]