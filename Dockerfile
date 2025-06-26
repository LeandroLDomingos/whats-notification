# --- Estágio 1: Construir os Assets do Frontend (Node.js) ---
# Usamos uma imagem oficial do Node.js para esta etapa.
FROM node:20 as node_assets

# Definimos o diretório de trabalho dentro do contêiner.
WORKDIR /app

# Copiamos os arquivos de definição de dependência primeiro para aproveitar o cache do Docker.
COPY package.json package-lock.json ./

# Instalamos as dependências do frontend.
RUN npm install

# Copiamos o restante do código do projeto.
COPY . .

# Compilamos os assets de produção (CSS, JS, etc.).
# O resultado será colocado na pasta /public/build por padrão.
RUN npm run build

# --- Estágio 2: Preparar a Aplicação Final (PHP + Nginx) ---
# Usamos uma imagem que já contém PHP-FPM, pronta para produção.
FROM php:8.3-fpm-alpine

# Definimos o diretório de trabalho.
WORKDIR /var/www/html

# Instala as extensões PHP necessárias para o Laravel, além do Nginx, Supervisor e Composer.
RUN apk add --no-cache \
      build-base \
      nginx \
      supervisor \
      libzip-dev \
      zip \
      oniguruma-dev \
      && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Copia o Composer para dentro da imagem.
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# Copia os arquivos de configuração do Nginx e do Supervisor do seu projeto para a imagem.
# Verifique se você tem uma pasta 'docker' com esses arquivos. Se não, podemos criá-los.
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Copia todo o código do seu projeto para dentro da imagem.
COPY . .

# Copia APENAS os assets já compilados do estágio 'node_assets' para a pasta public.
COPY --from=node_assets /app/public ./public

# Instala as dependências do Composer sem os pacotes de desenvolvimento.
RUN composer install --no-dev --optimize-autoloader

# Ajusta as permissões das pastas para que o servidor web possa escrever nelas.
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expõe a porta 80 para o tráfego web.
EXPOSE 80

# Define o comando que será executado quando o contêiner iniciar.
# O Supervisor irá gerenciar os processos do Nginx e do PHP-FPM.
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]