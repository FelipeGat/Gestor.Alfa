# Como Rodar o Projeto Localmente

## Pré-requisitos

- Docker >= 20.10
- Docker Compose >= 2.0
- Git
- Composer
- Node.js >= 18 (para build dos assets)

## Passos para Configuração

### 1. Clonar o Repositório

```bash
git clone https://github.com/rossini06/gestor_alfa.git
cd Gestor.Alfa
```

### 2. Configurar Variáveis de Ambiente

```bash
# Copiar o arquivo de exemplo
cp .env.example .env

# Gerar chave da aplicação
docker compose exec php php artisan key:generate
```

### 3. Iniciar os Containers Docker

```bash
# Iniciar containers em modo detached
docker compose up -d --build

# Verificar status dos containers
docker compose ps
```

### 4. Instalar Dependências PHP

```bash
docker compose exec php composer install
```

### 5. Configurar o Banco de Dados

```bash
# Executar migrações
docker compose exec php php artisan migrate --seed
```

### 6. Instalar e Buildar Assets Frontend

```bash
# Instalar dependências npm
npm install

# Buildar assets para produção
npm run build
```

### 7. Acessar a Aplicação

| Serviço | URL |
|---------|-----|
| Aplicação | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |

## Comandos Úteis

```bash
# Iniciar containers
docker compose up -d

# Parar containers
docker compose down

# Ver logs em tempo real
docker compose logs -f

# Acessar terminal do PHP
docker compose exec php bash

# Acessar MySQL
docker compose exec mysql mysql -u gestor_user -p gestor_alfa

# Limpar caches
docker compose exec php php artisan config:clear
docker compose exec php php artisan cache:clear
docker compose exec php php artisan route:clear
docker compose exec php php artisan view:clear

# Rebuild completo (após mudanças no Dockerfile)
docker compose up -d --build --force-recreate
```

## Estrutura dos Containers

| Container | Imagem | Porta |
|-----------|--------|-------|
| nginx | nginx:alpine | 80, 443 |
| php | PHP 8.3 + Laravel | - |
| mysql | MySQL 8.0 | 3306 |
| redis | Redis 7 | 6379 |
| queue | Laravel Queue | - |
| phpmyadmin | latest | 8081 |

## Solução de Problemas

### Erro 419 (CSRF) no Login

```bash
docker compose exec php php artisan config:clear
docker compose exec php php artisan cache:clear
```

### Porta já em Uso

```bash
# Verificar processos na porta
sudo lsof -i :8080

# Parar container usando a porta
docker compose down
```

### Permissões de Arquivos

```bash
docker compose exec php chown -R www-data:www-data /var/www/storage
docker compose exec php chmod -R 775 /var/www/storage
```

### Container não Inicia

```bash
# Ver logs detalhados
docker compose logs nginx
docker compose logs php

# Verificar configuração
docker compose config
```

## Variáveis de Ambiente Locais

Para desenvolvimento, use estas configurações no `.env`:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=gestor_alfa
DB_USERNAME=gestor_user
DB_PASSWORD=sua_senha_aqui

FORCE_HTTPS=false
SESSION_SECURE_COOKIE=false
```

## Desenvolvimento com Hot Reload (Opcional)

Para development com hot reload de assets:

```bash
# Em um terminal separado
npm run dev
```

## Deploy em Produção

Para deploy com Docker:

```bash
# Configurar variáveis de produção
cp .env.example .env
# Editar .env com valores de produção

# Buildar para produção
npm run build

# Iniciar containers
docker compose -f docker-compose.yml up -d --build
```

Consulte a documentação de deploy para mais detalhes sobre SSL, backup e manutenção.
