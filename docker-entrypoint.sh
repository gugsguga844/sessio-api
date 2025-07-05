#!/bin/bash

# Aguardar um pouco para o banco estar pronto
echo "Aguardando banco de dados..."
sleep 10

# Executar migrações
echo "Executando migrações..."
php artisan migrate --force

# Executar seeders (opcional - descomente se quiser dados de teste)
# echo "Executando seeders..."
# php artisan db:seed --force

# Executar o comando passado como argumento
exec "$@" 