# Sessio API

**Autor:** Gustavo Santos Schneider

## Sobre o Projeto

Esta é uma API desenvolvida para o [Sessio](https://github.com/gugsguga844/sessio). Trata-se de um **SaaS (Software as a Service) projetado para psicólogos**. O objetivo é simplificar a gestão de rotina dos mesmos, permitindo organizar todas as sessões em um único lugar. O sistema inclui funcionalidades robustas para **agendamento de consultas, registro detalhado de notas de sessão** e, futuramente, recursos de pagamentos.
O Sessio visa ser a ferramenta central para psicólogos e terapeutas que buscam otimizar seu tempo e focar no que realmente importa: o bem-estar de seus pacientes.

## Configuração Local

### 1. Clone o Repositório

```bash
git clone https://github.com/gugsguga844/sessio-api.git
cd sessio-api
```

### 2. Instale as Dependências

```bash
composer install
```

### 3. Configure o Ambiente

```bash
# Copie o arquivo de exemplo
cp .env.example .env

# Gere a chave da aplicação
php artisan key:generate
```

### 4. Configure o Banco de Dados

Edite o arquivo `.env` com suas configurações de banco:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sessio_api
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### 5. Execute as Migrações

```bash
# Crie o banco de dados (se não existir)
mysql -u root -p -e "CREATE DATABASE sessio_api;"

# Execute as migrações
php artisan migrate

# (Opcional) Execute os seeders para dados de teste
php artisan db:seed
```

### 6. Configure o Storage

```bash
# Crie o link simbólico para storage
php artisan storage:link
```

### 7. Gere a Documentação Swagger

```bash
# Gere a documentação da API
php artisan l5-swagger:generate
```

## Executando a Aplicação

### Servidor de Desenvolvimento

```bash
# Inicie o servidor Laravel
php artisan serve
```

A aplicação estará disponível em: `http://localhost:8000`

### Testes

```bash
# Execute todos os testes
php vendor/bin/phpunit

# Ou usando o comando do Laravel
php artisan test
```

## Documentação Swagger

Após executar `php artisan l5-swagger:generate`, a documentação interativa, juntamente com todas as endpoints do projeto, estará disponível em:

`http://localhost:8000/api/documentation`


