# --- Estágio 1: Construir os Assets do Frontend (Node.js) ---
FROM node:20 as node_assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install
COPY . .
RUN npm run build

# --- Estágio 2: Preparar a Aplicação Final (PHP + Nginx) ---
FROM php:8.3-fpm-alpine
WORKDIR /var/www/html

# Instala as extensões PHP e dependências do sistema
RUN apk add --no-cache \
      build-base \
      nginx \
      supervisor \
      libzip-dev \
      zip \
      oniguruma-dev \
      libpng-dev \
      libjpeg-turbo-dev \
      freetype-dev \
      && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Copia o Composer.
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# Copia os arquivos de configuração.
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Copia todo o código do projeto.
COPY . .

# ===== CORREÇÃO FINAL AQUI =====
# Copia APENAS a pasta 'build' de dentro da 'public' do estágio anterior.
# Isso preserva o nosso index.php e outros assets.
COPY --from=node_assets /app/public/build ./public/build
# ===============================

# Instala as dependências do Composer.
RUN composer install --no-dev --optimize-autoloader

# Limpa os caches do Laravel para garantir que ele use as novas configurações em produção.
RUN php artisan config:clear && php artisan route:clear && php artisan view:clear

# Ajusta as permissões das pastas.
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copia e dá permissão ao script de entrypoint.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expõe a porta 80.
EXPOSE 80

# Define o script como ponto de entrada.
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]