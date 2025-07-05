#!/bin/bash

# Aguardar mais tempo para o banco estar pronto
echo "Aguardando banco de dados..."
sleep 90

# Tentar conectar ao banco várias vezes
echo "Verificando conexão com banco de dados..."
for i in {1..30}; do
    echo "Tentativa $i de 30..."
    if php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; then
        echo "Conexão com banco estabelecida!"
        break
    fi
    echo "Aguardando 5 segundos..."
    sleep 5
done

# Executar migrações
echo "Executando migrações..."
php artisan migrate --force

# Executar seeders (opcional - descomente se quiser dados de teste)
# echo "Executando seeders..."
# php artisan db:seed --force

# Executar o comando passado como argumento
exec "$@" 