<div align="center">

# 🏢 Gestor Alfa

### Sistema de Gestão Empresarial Completo

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://docker.com)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)

</div>

---

## 📋 Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Funcionalidades](#funcionalidades)
- [Tecnologias](#tecnologias)
- [Arquitetura](#arquitetura)
- [Instalação](#instalação)
- [Comandos Úteis](#comandos-úteis)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Contribuição](#contribuição)
- [Licença](#licença)

---

## 🎯 Sobre o Projeto

O **Gestor Alfa** é um sistema de gestão empresarial completo desenvolvido em Laravel 12, projetado para atender pequenas e médias empresas com controle financeiro, gestão de clientes, fornecedores, orçamentos e atendimentos.

O sistema oferece dashboards especializados por departamento (Administrativo, Comercial, Técnico e Financeiro), permitindo uma visão 360° da operação do negócio.

### ✨ Principais Características

- **Multi-departamentos**: Dashboards específicos para cada área
- **Controle Financeiro**: Contas a pagar e receber com classificação detalhada
- **Gestão Comercial**: Clientes, orçamentos e cobranças integrados
- **Portal do Funcionário**: Acesso centralizado para colaboradores
- **Relatórios PDF**: Geração de documentos com DOMPDF
- **Infraestrutura Docker**: Deploy simplificado com containers

---

## 🚀 Funcionalidades

### 💰 Módulo Financeiro

- **Contas a Pagar**
  - Cadastro de despesas fixas e variáveis
  - Classificação por centros de custo, categorias e subcategorias
  - Controle de fornecedores e contatos
  - Agendamento de pagamentos
  - Dashboard financeiro com indicadores

- **Contas a Receber**
  - Gestão de receitas e cobranças
  - Integração com sistema de boletos
  - Controle de inadimplência

### 👥 Gestão de Pessoas

- **Clientes**
  - Cadastro completo (PF/PJ)
  - Histórico de atendimentos
  - Controle de orçamentos

- **Fornecedores**
  - Cadastro com dados fiscais
  - Múltiplos contatos
  - Classificação por categoria

- **Funcionários**
  - Portal do funcionário
  - Controle de acesso por perfil

### 📊 Dashboards

- **Dashboard Administrativo**: Visão geral da empresa
- **Dashboard Comercial**: Vendas, orçamentos e metas
- **Dashboard Técnico**: Ordens de serviço e atendimentos
- **Dashboard Financeiro**: Fluxo de caixa e indicadores

### 📋 Gestão Operacional

- **Atendimentos**: Controle de chamados e suporte
- **Orçamentos**: Emissão e acompanhamento
- **Itens Comerciais**: Catálogo de produtos/serviços

---

## 🛠 Tecnologias

### Backend
- **Laravel 12** - Framework PHP moderno e elegante
- **PHP 8.3** - Última versão estável
- **Laravel Breeze** - Sistema de autenticação
- **DOMPDF** - Geração de PDFs

### Frontend
- **TailwindCSS 3** - Framework CSS utilitário
- **AlpineJS** - Framework JavaScript leve
- **Vite** - Build tool moderno
- **Laravel Blade** - Template engine

### Banco de Dados & Cache
- **MySQL 8.0** - Banco de dados relacional
- **Redis 7** - Cache e sessões

### Infraestrutura
- **Docker** - Containerização
- **Docker Compose** - Orquestração
- **Nginx** - Servidor web
- **PHP-FPM** - Processamento PHP

### Testes & Qualidade
- **Pest PHP** - Framework de testes
- **Laravel Pint** - Linter e formatador de código

---

## 🏗 Arquitetura

```
┌─────────────────────────────────────────────────────────────┐
│                        Nginx (80/443)                       │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│                      PHP-FPM (Laravel)                      │
└──────────────────────┬──────────────────────────────────────┘
                       │
       ┌───────────────┼───────────────┐
       ▼               ▼               ▼
┌─────────────┐ ┌─────────────┐ ┌─────────────┐
│ MySQL 8.0   │ │ Redis 7     │ │ Queue Worker│
│ (Port 3306) │ │ (Port 6379) │ │             │
└─────────────┘ └─────────────┘ └─────────────┘
```

### Containers Docker

| Container | Imagem | Porta | Descrição |
|-----------|--------|-------|-----------|
| nginx | nginx:alpine | 80, 443 | Proxy reverso e servidor web |
| php-fpm | PHP 8.3 + Laravel | - | Processamento PHP |
| mysql | MySQL 8.0 | 3306 | Banco de dados |
| redis | Redis 7 | 6379 | Cache e filas |
| queue-worker | PHP 8.3 | - | Processamento de jobs |
| phpmyadmin | latest | 8080 | Administração do BD |

---

## 🏗️ Clean Architecture

O projeto implementa **Clean Architecture** para manter o código organizado, testável e de fácil manutenção:

### Estrutura de Camadas

```
app/
├── Http/
│   └── Controllers/          # 🎯 Apenas entrada HTTP (thin controllers)
│       └── Responsabilidade: Receber request, chamar Service, retornar response
├── Services/                 # 🧠 Lógica de negócio (regras do domínio)
│   ├── Financeiro/
│   │   ├── ContaPagarService.php      # Regras de contas a pagar
│   │   └── ContaReceberService.php    # Regras de contas a receber
│   └── Comercial/
│       └── OrcamentoService.php       # Regras de orçamentos
├── Repositories/             # 💾 Acesso a dados
│   ├── Interfaces/          # Contratos (abstrações)
│   └── Eloquent/            # Implementações concretas
└── Providers/
    └── RepositoryServiceProvider.php  # Injeção de dependência
```

### Benefícios

| Benefício | Descrição |
|-----------|-----------|
| **Separação de responsabilidades** | Controllers não têm lógica de negócio |
| **Testabilidade** | Services podem ser testados isoladamente |
| **Reutilização** | Mesmo Service usado em diferentes Controllers |
| **Manutenibilidade** | Mudanças em uma camada não afetam as outras |

### Fluxo de uma Requisição

```
Request → Controller → Service → Repository → Model → Database
                ↓
Response ← View/JSON ← Service ← Repository ← Model
```

### Exemplo de Uso

**Controller (magro):**
```php
class ContasPagarController extends Controller
{
    protected $service;

    public function __construct(ContaPagarService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $contas = $this->service->listar($request->all());
        $kpis = $this->service->calcularKPIs();
        return view('financeiro.contasapagar', compact('contas', 'kpis'));
    }
}
```

**Service (lógica de negócio):**
```php
class ContaPagarService
{
    public function calcularKPIs(): array
    {
        return Cache::remember('kpis', 300, function () {
            return [
                'a_pagar' => ContaPagar::where('status', 'em_aberto')->sum('valor'),
                'pago' => ContaPagar::where('status', 'pago')->sum('valor'),
            ];
        });
    }
}
```

### Services Disponíveis

| Service | Módulo | Status | Testes |
|---------|--------|--------|--------|
| `ContaPagarService` | Financeiro | ✅ Implementado | ✅ 16 testes |
| `ContaReceberService` | Financeiro | ✅ Implementado | ✅ 19 testes |
| `OrcamentoService` | Comercial | ✅ Implementado | ⏭️ Em breve |

---

## 📦 Instalação

### Pré-requisitos

- Docker >= 20.10
- Docker Compose >= 2.0
- Git
- Node.js >= 18 (para build dos assets)

### Passo a Passo

#### 1. Clone o Repositório

```bash
git clone https://github.com/FelipeGat/Gestor.Alfa.git
cd Gestor.Alfa
```

#### 2. Configure as Variáveis de Ambiente

```bash
# Copie o arquivo de exemplo
cp .env.example .env

# Configure o arquivo .env com suas credenciais
# Edite DB_PASSWORD e MYSQL_ROOT_PASSWORD
```

#### 3. Inicie os Containers

```bash
docker compose up -d --build
```

#### 4. Instale as Dependências

```bash
# Dependências PHP
docker compose exec php-fpm composer install

# Dependências Node.js
npm install
npm run build
```

#### 5. Configure o Banco de Dados

```bash
# Gere a chave da aplicação
docker compose exec php-fpm php artisan key:generate

# Execute as migrações e seeders
docker compose exec php-fpm php artisan migrate --seed
```

#### 6. Acesse a Aplicação

| Serviço | URL |
|---------|-----|
| Aplicação | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |

---

## 💻 Comandos Úteis

### Docker

```bash
# Iniciar containers
docker compose up -d

# Parar containers
docker compose down

# Ver logs em tempo real
docker compose logs -f

# Rebuild completo
docker compose up -d --build --force-recreate

# Acessar terminal do PHP
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

# Executar migrações
docker compose exec php-fpm php artisan migrate

# Executar seeders
docker compose exec php-fpm php artisan db:seed

# Criar controller
docker compose exec php-fpm php artisan make:controller NomeController

# Criar model com migration
docker compose exec php-fpm php artisan make:model NomeModel -m
```

### Desenvolvimento Frontend

```bash
# Modo desenvolvimento com hot reload
npm run dev

# Build para produção
npm run build
```

### Testes

```bash
# Executar todos os testes
docker compose exec php-fpm php artisan test

# Executar testes de um arquivo específico
docker compose exec php-fpm php artisan test tests/Unit/Services/Financeiro/ContaPagarServiceTest.php
docker compose exec php-fpm php artisan test tests/Unit/Services/Financeiro/ContaReceberServiceTest.php

# Executar testes com cobertura
docker compose exec php-fpm php artisan test --coverage

# Ou usando Pest (se instalado)
docker compose exec php-fpm ./vendor/bin/pest
```

#### Estrutura de Testes

O projeto utiliza **PHPUnit** para testes unitários e de integração:

```
tests/
├── Unit/                     # Testes unitários
│   └── Services/            # Testes dos Services (Clean Architecture)
│       └── Financeiro/
│           ├── ContaPagarServiceTest.php
│           └── ContaReceberServiceTest.php
├── Feature/                 # Testes de integração
│   └── Auth/               # Testes de autenticação
└── TestCase.php            # Classe base dos testes
```

#### Testes dos Services (Clean Architecture)

Os testes dos Services garantem que a lógica de negócio funciona corretamente:

- **ContaPagarServiceTest**: 16 testes cobrindo cálculos de KPIs, cache, CRUD e pagamentos
- **ContaReceberServiceTest**: 19 testes cobrindo cálculos de KPIs, cache, CRUD, recebimentos e estornos

**O que é testado:**
- ✅ Cálculos matemáticos (KPIs, totais)
- ✅ Funcionalidade do cache (salvar e invalidar)
- ✅ Operações CRUD completas
- ✅ Transações financeiras (pagamentos, recebimentos, estornos)
- ✅ Tratamento de erros (IDs inexistentes)

**Factories disponíveis:**
- `ContaPagarFactory` - Gera contas a pagar para testes
- `ContaReceberFactory` - Gera cobranças para testes
- `FornecedorFactory` - Gera fornecedores
- `ClienteFactory` - Gera clientes
- `CentroCustoFactory` - Gera centros de custo
- `ContaFinanceiraFactory` - Gera contas financeiras

---

## 📁 Estrutura do Projeto

```
Gestor.Alfa/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Controladores da aplicação
│   │   │   ├── Auth/         # Autenticação (Breeze)
│   │   │   ├── ContasPagarController.php
│   │   │   ├── ContasReceberController.php
│   │   │   ├── DashboardFinanceiroController.php
│   │   │   └── ...
│   │   └── Middleware/       # Middlewares
│   ├── Models/               # Modelos Eloquent
│   └── Providers/            # Service Providers
├── bootstrap/                # Bootstrap da aplicação
├── config/                   # Arquivos de configuração
├── database/
│   ├── factories/            # Factories para testes
│   ├── migrations/           # Migrações do banco
│   └── seeders/              # Seeders para dados iniciais
├── nginx/                    # Configuração Nginx
├── php/                      # Dockerfile PHP
├── public/                   # Arquivos públicos
├── resources/
│   ├── css/                  # Estilos Tailwind
│   ├── js/                   # Scripts AlpineJS
│   └── views/                # Templates Blade
├── routes/
│   ├── web.php               # Rotas web
│   └── api.php               # Rotas API
├── storage/                  # Logs, cache, uploads
├── tests/                    # Testes Pest/PHPUnit
├── docker-compose.yml        # Configuração Docker
└── composer.json             # Dependências PHP
```

### Principais Controllers

| Controller | Descrição |
|------------|-----------|
| `DashboardAdmController` | Dashboard administrativo |
| `DashboardComercialController` | Dashboard comercial |
| `DashboardFinanceiroController` | Dashboard financeiro |
| `ContasPagarController` | Gestão de contas a pagar |
| `ContasReceberController` | Gestão de contas a receber |
| `FornecedorController` | Cadastro de fornecedores |
| `ClienteController` | Gestão de clientes |
| `OrcamentoController` | Emissão de orçamentos |
| `PortalFuncionarioController` | Portal do funcionário |

---

## 🤝 Contribuição

Contribuições são bem-vindas! Siga estas diretrizes:

1. **Fork** o projeto
2. Crie uma **branch** para sua feature (`git checkout -b feature/nova-funcionalidade`)
3. **Commit** suas mudanças (`git commit -m 'Adiciona nova funcionalidade'`)
4. **Push** para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um **Pull Request**

### Padrões de Código

- Siga o padrão PSR-12
- Execute o linter antes de commitar:
  ```bash
  docker compose exec php-fpm ./vendor/bin/pint
  ```
- Escreva testes para novas funcionalidades
- Mantenha a cobertura de testes acima de 80%

---

## 🐛 Solução de Problemas

### Erro 419 (CSRF) no Login

```bash
docker compose exec php-fpm php artisan config:clear
docker compose exec php-fpm php artisan cache:clear
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
docker compose exec php-fpm chown -R www-data:www-data /var/www/storage
docker compose exec php-fpm chmod -R 775 /var/www/storage
```

---

## 📝 Licença

Este projeto é licenciado sob a [Licença MIT](LICENSE).

---

## 📧 Contato

Para dúvidas, sugestões ou suporte, entre em contato:

- **Email**: [seu-email@exemplo.com]
- **GitHub Issues**: [https://github.com/FelipeGat/Gestor.Alfa/issues](https://github.com/FelipeGat/Gestor.Alfa/issues)

---

<div align="center">

Desenvolvido com ❤️ usando Laravel

</div>
