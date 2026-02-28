# 🏢 Gestor Alfa - Contexto do Projeto

## Visão Geral

**Gestor Alfa** é um sistema de gestão empresarial completo (ERP) desenvolvido em **Laravel 12** e **PHP 8.3**, projetado para atender pequenas e médias empresas com controle financeiro, gestão de clientes, fornecedores, orçamentos e atendimentos.

O sistema oferece dashboards especializados por departamento (Administrativo, Comercial, Técnico e Financeiro), permitindo uma visão 360° da operação do negócio.

---

## 🛠 Stack Tecnológico

| Categoria | Tecnologia | Versão |
|-----------|------------|--------|
| **Backend** | Laravel | 12.x |
| | PHP | 8.3 |
| | Laravel Breeze | Autenticação |
| | DOMPDF | Geração de PDFs |
| **Frontend** | TailwindCSS | 3.x |
| | AlpineJS | Framework leve |
| | Vite | Build tool |
| | Laravel Blade | Template engine |
| **Banco de Dados** | MySQL | 8.0 |
| **Cache** | Redis | 7 |
| **Infraestrutura** | Docker | Containerização |
| | Nginx | Servidor web |
| | PHP-FPM | Processamento PHP |
| **Testes** | Pest PHP | Framework de testes |
| | Laravel Pint | Linter |

---

## 🏗 Arquitetura

### Clean Architecture

O projeto implementa **Clean Architecture** com separação clara de responsabilidades:

```
app/
├── Http/Controllers/          # 🎯 Apenas entrada HTTP (thin controllers)
├── Services/                  # 🧠 Lógica de negócio (regras do domínio)
│   ├── Financeiro/
│   │   ├── ContaPagarService.php
│   │   └── ContaReceberService.php
│   └── Comercial/
│       └── OrcamentoService.php
├── Repositories/              # 💾 Acesso a dados
│   ├── Interfaces/           # Contratos (abstrações)
│   └── Eloquent/             # Implementações concretas
├── Models/                    # Modelos Eloquent
├── DTOs/                      # Data Transfer Objects
├── Enums/                     # Enumerações
├── Exceptions/                # Exceções customizadas
└── Providers/                 # Service Providers
```

### Fluxo de Requisição

```
Request → Controller → Service → Repository → Model → Database
                ↓
Response ← View/JSON ← Service ← Repository ← Model
```

### Services Implementados

| Service | Módulo | Status | Testes |
|---------|--------|--------|--------|
| `ContaPagarService` | Financeiro | ✅ | ✅ 16 testes |
| `ContaReceberService` | Financeiro | ✅ | ✅ 19 testes |
| `OrcamentoService` | Comercial | ✅ | ⏳ Em breve |
| `MovimentacaoFinanceiraService` | Financeiro | ✅ | - |

---

## 📦 Estrutura do Projeto

```
gestor_alfa/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Controladores
│   │   │   ├── Auth/          # Autenticação
│   │   │   ├── Admin/         # Área administrativa
│   │   │   ├── Portal/        # Portais (Funcionário, Cliente)
│   │   │   └── Relatorios/    # Relatórios
│   │   └── Middleware/        # Middlewares
│   ├── Services/              # Services (Clean Architecture)
│   ├── Repositories/          # Repositórios
│   ├── Models/                # Modelos Eloquent
│   ├── DTOs/                  # Data Transfer Objects
│   ├── Enums/                 # Enums
│   └── Providers/             # Providers
├── bootstrap/                 # Bootstrap Laravel
├── config/                    # Configurações
├── database/
│   ├── factories/             # Factories para testes
│   ├── migrations/            # Migrações
│   └── seeders/               # Seeders
├── docker-compose.yml         # Orquestração Docker
├── nginx/                     # Configuração Nginx
├── php/                       # Dockerfile PHP
├── public/                    # Arquivos públicos
├── resources/
│   ├── css/                   # Estilos Tailwind
│   ├── js/                    # Scripts AlpineJS
│   └── views/                 # Templates Blade
├── routes/
│   ├── web.php                # Rotas web
│   ├── api.php                # Rotas API
│   ├── auth.php               # Rotas de autenticação
│   └── console.php            # Comandos console
├── storage/                   # Logs, cache, uploads
├── tests/
│   ├── Unit/                  # Testes unitários
│   │   ├── Services/          # Testes de Services
│   │   ├── Actions/
│   │   ├── Domain/
│   │   └── Enums/
│   └── Feature/               # Testes de integração
├── .env                       # Variáveis de ambiente
├── .env.example               # Exemplo de variáveis
├── composer.json              # Dependências PHP
├── package.json               # Dependências Node.js
├── vite.config.js             # Configuração Vite
├── tailwind.config.js         # Configuração Tailwind
└── phpunit.xml                # Configuração PHPUnit
```

---

## 🚀 Módulos Principais

### Módulo Financeiro
- **Contas a Pagar**: Gestão de despesas, fornecedores, centros de custo
- **Contas a Receber**: Cobranças, recebimentos, estornos
- **Contas Fixas**: Recorrência automática
- **Movimentação Financeira**: Fluxo de caixa
- **Dashboard Financeiro**: KPIs e indicadores
- **Anexos**: NF e boletos em PDF

### Módulo Comercial
- **Clientes**: Cadastro completo (PF/PJ)
- **Pré-Clientes**: Gestão de leads
- **Orçamentos**: Emissão e acompanhamento
- **Itens Comerciais**: Catálogo de produtos/serviços
- **Dashboard Comercial**: Vendas, metas e conversões

### Módulo Técnico
- **Atendimentos**: Chamados e suporte
- **Portal do Funcionário**: Controle de tempo, pausas, fotos
- **Agenda Técnica**: Calendário de atendimentos
- **Dashboard Técnico**: Métricas de produtividade

### Dashboards
- **Administrativo**: Visão geral da empresa
- **Comercial**: Vendas e orçamentos
- **Financeiro**: Fluxo de caixa e KPIs
- **Técnico**: Atendimentos e equipe

---

## 💻 Comandos Úteis

### Docker

```bash
# Iniciar containers
docker compose up -d

# Parar containers
docker compose down

# Ver logs
docker compose logs -f

# Rebuild completo
docker compose up -d --build --force-recreate

# Acessar terminal PHP
docker compose exec php-fpm bash

# Acessar MySQL
docker compose exec mysql mysql -u gestor_user -p gestor_alfa
```

### Artisan (Laravel)

```bash
# Limpar caches
docker compose exec php-fpm php artisan config:clear
docker compose exec php-fpm php artisan cache:clear
docker compose exec php-fpm php artisan route:clear
docker compose exec php-fpm php artisan view:clear

# Migrações
docker compose exec php-fpm php artisan migrate
docker compose exec php-fpm php artisan migrate --seed

# Criar controller
docker compose exec php-fpm php artisan make:controller NomeController

# Criar model com migration
docker compose exec php-fpm php artisan make:model NomeModel -m
```

### Frontend

```bash
# Modo desenvolvimento (hot reload)
npm run dev

# Build para produção
npm run build
```

### Testes

```bash
# Executar todos os testes
docker compose exec php-fpm php artisan test

# Teste específico
docker compose exec php-fpm php artisan test tests/Unit/Services/Financeiro/ContaPagarServiceTest.php

# Com cobertura
docker compose exec php-fpm php artisan test --coverage
```

### Composer Scripts

```bash
# Setup completo do projeto
composer run-script setup

# Modo desenvolvimento (server + queue + logs + vite)
composer run-script dev

# Executar testes
composer run-script test
```

---

## 🔧 Configuração do Ambiente

### Pré-requisitos
- Docker >= 20.10
- Docker Compose >= 2.0
- Git
- Node.js >= 18

### Passo a Passo

1. **Clone o repositório**
```bash
git clone <repository-url>
cd gestor_alfa
```

2. **Configure variáveis de ambiente**
```bash
cp .env.example .env
# Edite .env com suas credenciais
```

3. **Inicie os containers**
```bash
docker compose up -d --build
```

4. **Instale dependências**
```bash
docker compose exec php-fpm composer install
npm install
npm run build
```

5. **Configure banco de dados**
```bash
docker compose exec php-fpm php artisan key:generate
docker compose exec php-fpm php artisan migrate --seed
```

6. **Acesse a aplicação**
- Aplicação: http://localhost:80
- phpMyAdmin: http://localhost:8080

---

## 📊 Containers Docker

| Container | Imagem | Porta | Descrição |
|-----------|--------|-------|-----------|
| nginx | nginx:alpine | 80, 443 | Proxy reverso |
| php-fpm | PHP 8.3 | - | Processamento PHP |
| mysql | MySQL 8.0 | 3306 | Banco de dados |
| redis | Redis 7 | 6379 | Cache |
| queue-worker | PHP 8.3 | - | Jobs em fila |
| phpmyadmin | latest | 8080 | Admin BD |
| backup | docker:cli | - | Backups |

---

## 🧪 Testes

### Estrutura de Testes

```
tests/
├── Unit/
│   ├── Services/           # Testes de Services
│   │   └── Financeiro/
│   │       ├── ContaPagarServiceTest.php    # 16 testes
│   │       └── ContaReceberServiceTest.php  # 19 testes
│   ├── Actions/
│   ├── Domain/
│   └── Enums/
└── Feature/
    └── Auth/               # Testes de autenticação
```

### Factories Disponíveis

- `ContaPagarFactory`
- `ContaReceberFactory`
- `FornecedorFactory`
- `ClienteFactory`
- `CentroCustoFactory`
- `ContaFinanceiraFactory`

### O que é Testado

- ✅ Cálculos matemáticos (KPIs, totais)
- ✅ Funcionalidade do cache
- ✅ Operações CRUD
- ✅ Transações financeiras
- ✅ Tratamento de erros

---

## 📝 Convenções de Desenvolvimento

### Código

- **Padrão**: PSR-12
- **Linter**: Laravel Pint
- **Indentação**: 4 espaços (EditorConfig)
- **Charset**: UTF-8
- **Fim de linha**: LF

### Executar Linter

```bash
docker compose exec php-fpm ./vendor/bin/pint
```

### Arquitetura

- **Controllers**: Apenas entrada HTTP, sem lógica de negócio
- **Services**: Toda lógica de negócio
- **Repositories**: Acesso a dados
- **Models**: Apenas relacionamentos e scopes

### Validações

- Usar validações completas com limites máximos
- Validar unicidade quando aplicável
- Usar `required_if` para campos condicionais
- Mensagens de erro claras

### Banco de Dados

- Migrations nomeadas com timestamp e descrição
- Soft deletes em tabelas que requerem histórico
- Foreign keys com constraints apropriadas
- Índices em colunas de busca frequente

---

## 🔐 Segurança

### Validações Implementadas

- CSRF protection em todos os forms
- Validação de tipo e tamanho de arquivos (PDF, imagens)
- Limites máximos em campos numéricos
- Unicidade de CPF/CNPJ, nomes, etc.
- Verificação cruzada entre tabelas

### Middlewares

- `auth`: Autenticação obrigatória
- `financeiro`: Acesso ao módulo financeiro
- `cliente`: Acesso ao portal do cliente
- `funcionario`: Acesso ao portal do funcionário
- `dashboard.*`: Acesso a dashboards específicos
- `primeiro_acesso`: Redireciona para troca de senha

### Upload de Arquivos

- Armazenamento: `storage/app/public/`
- Validação: tipo MIME e tamanho máximo
- Nomes únicos com timestamp
- Link simbólico: `php artisan storage:link`

---

## 📚 Documentação Existente

| Arquivo | Descrição |
|---------|-----------|
| `README.md` | Documentação principal do projeto |
| `LOCAL-DEVELOPMENT.md` | Guia de desenvolvimento local |
| `AUDITORIA_COMERCIAL.md` | Auditoria do módulo comercial |
| `PORTAL_FUNCIONARIO_REFATORACAO.md` | Refatoração do portal |
| `SISTEMA_ANEXOS.md` | Sistema de anexos (NF/Boleto) |
| `CHECKLIST_ORCAMENTO_UIUX.md` | Checklist UI/UX de orçamentos |
| `RELATORIO_TECNICO_RELATORIOS_COMERCIAL.md` | Relatório técnico |
| `RESUMO_ALTERACOES.md` | Resumo de alterações |

---

## 🐛 Solução de Problemas Comuns

### Erro 419 (CSRF) no Login
```bash
docker compose exec php-fpm php artisan config:clear
docker compose exec php-fpm php artisan cache:clear
```

### Porta já em uso
```bash
sudo lsof -i :80
docker compose down
```

### Permissões de arquivos
```bash
docker compose exec php-fpm chown -R www-data:www-data /var/www/storage
docker compose exec php-fpm chmod -R 775 /var/www/storage
```

### Storage link não funciona
```bash
docker compose exec php-fpm php artisan storage:link
```

---

## 📞 Contato e Suporte

- **Repositório**: https://github.com/FelipeGat/Gestor.Alfa
- **Issues**: https://github.com/FelipeGat/Gestor.Alfa/issues

---

## ⚠️ Licença

**Copyright (c) 2024 Felipe Henrique Gat - Todos os Direitos Reservados**

Este software é propriedade intelectual exclusiva de Felipe Henrique Gat. Uso comercial, distribuição e modificação são proibidos sem autorização expressa.

Consulte o arquivo [LICENSE](LICENSE) para os termos completos.
