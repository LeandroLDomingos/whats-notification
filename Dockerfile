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
      # ===== CORREÇÃO AQUI =====
      # Adicionadas dependências para a extensão GD (libpng, libjpeg, freetype)
      libpng-dev \
      libjpeg-turbo-dev \
      freetype-dev \
      # =========================
      && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Copia o Composer.
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# Copia os arquivos de configuração.
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Copia todo o código do projeto.
COPY . .

# Copia os assets compilados do estágio 'node_assets'.
COPY --from=node_assets /app/public ./public

# Instala as dependências do Composer.
RUN composer install --no-dev --optimize-autoloader

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