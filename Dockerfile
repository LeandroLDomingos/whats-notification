# Estágio 1: Instalar dependências PHP
FROM composer:2.7 as vendor
WORKDIR /app
COPY database/ database/
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist

# Estágio 2: Construir assets do front-end
FROM node:20-alpine as frontend
WORKDIR /app
COPY . .
COPY --from=vendor /app/vendor/ /app/vendor/
RUN npm install
RUN npm run build

# Estágio 3: Imagem final de produção
# Usamos uma imagem base que já vem com Nginx e PHP-FPM, é otimizada e segura.
FROM webdevops/php-nginx:8.3-alpine

# Copia somente os artefatos necessários dos estágios anteriores
COPY --from=vendor /app/vendor/ /app/vendor/
COPY --from=frontend /app/public/ /app/public/
COPY . /app

# Usa as configurações que você já tem, isso é ótimo!
COPY docker/nginx.conf /opt/docker/etc/nginx/vhost.conf
COPY docker/supervisord.conf /opt/docker/etc/supervisor.d/laravel.conf

# Ajusta permissões
RUN chown -R application:application /app/storage /app/bootstrap/cache

WORKDIR /app

# Expõe a porta 80 de dentro do contêiner, que o Nginx usará
EXPOSE 80

# Comando para iniciar Nginx e PHP-FPM gerenciados pelo Supervisor
CMD ["/usr/local/bin/entrypoint.sh"]