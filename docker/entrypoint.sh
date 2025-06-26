#!/bin/sh

# Para a execução se qualquer comando falhar
set -e

# Roda as migrações do banco de dados.
# O --force é para não pedir confirmação em produção.
php artisan migrate --force

# Depois de rodar as migrations, executa o comando principal do contêiner
# que é iniciar o supervisor (que por sua vez inicia o Nginx e o PHP).
exec /usr/bin/supervisord -c /etc/supervisord.conf