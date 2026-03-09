# Relatório de Arquitetura do Sistema

## 1. Estrutura de Pastas

- **app/**: Código principal da aplicação (Actions, Console, Domain, DTOs, Enums, Exceptions, Helpers, Http, Listeners, Mail, Models, Notifications, Observers, Providers, Repositories, Resources, Services, Traits, View)
- **bootstrap/**: Inicialização do framework
- **config/**: Arquivos de configuração (auth, database, mail, etc)
- **database/**: Migrations, seeders, scripts SQL
- **lang/**: Traduções (en, pt_BR)
- **nginx/**: Configurações de servidor
- **php/**: Dockerfile, php.ini
- **public/**: Arquivos públicos (index.php, assets, manifest.json)
- **resources/**: CSS, JS, views Blade
- **routes/**: Definição de rotas (api, auth, web, console)
- **scripts/**: Scripts utilitários
- **storage/**: Arquivos gerados, logs, cache
- **tests/**: Testes (Feature, Unit)
- **vendor/**: Dependências Composer

## 2. Tecnologias Usadas

- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Vite, TailwindCSS, Alpine.js
- **Autenticação:** Laravel Sanctum
- **Banco de Dados:** MySQL/MariaDB
- **PDF:** barryvdh/laravel-dompdf
- **Logs de Atividade:** spatie/laravel-activitylog
- **Testes:** PestPHP
- **Outros:** Axios, PostCSS, Docker (php, nginx)

## 3. Fluxo de Autenticação

- Utiliza Laravel Sanctum para autenticação de APIs e sessão para web.
- Rotas de autenticação em `routes/auth.php`:
    - Registro, login, logout, recuperação de senha, verificação de e-mail.
    - Controllers: `AuthenticatedSessionController`, `RegisteredUserController`, `PasswordResetLinkController`, etc.
- Configuração de guard padrão em `config/auth.php` (driver session para web, sanctum para API).

## 4. Estrutura das Entidades Principais

- **User:** Usuários do sistema (autenticação, permissões)
- **Empresa:** Dados da empresa
- **Funcionario:** Funcionários vinculados à empresa
- **Cliente:** Clientes (pessoa física/jurídica, dados cadastrais)
- **Atendimento:** Chamados/atendimentos vinculados a clientes
- **Orcamento:** Orçamentos comerciais vinculados a clientes e atendimentos
- **ContaFinanceira:** Contas bancárias, cartões, caixa
- **Cobranca:** Cobranças financeiras (boletos, notas fiscais, contas fixas)

## 5. Módulo Financeiro Existente

- **ContaFinanceira:** Gerencia contas correntes, poupança, cartões, caixa, limites e saldos.
- **Cobranca:** Relaciona cobranças a orçamentos, clientes, contas financeiras, boletos e contas fixas.
- **Boleto:** Geração e controle de boletos para clientes.
- **ContaFixa:** Controle de despesas recorrentes.
- **NotaFiscal:** Controle de notas fiscais emitidas/recebidas.

## 6. Tratamento de Clientes, Contratos, Orçamentos e Financeiro

- **Clientes:** Cadastro completo, tipos (normal, VIP, prospect), pessoa física/jurídica, endereço, observações.
- **Orçamentos:** Associados a clientes, atendimentos e empresas. Possuem itens (produtos/serviços), taxas, pagamentos, status, valores, descontos e observações.
- **Financeiro:** Controle de contas financeiras, cobranças, boletos, contas fixas, notas fiscais. Relacionamento entre cobranças e orçamentos/clientes.
- **Contratos:** (Arquivo Contrato.php não encontrado, pode estar embutido em orçamentos ou atendimentos)

## 7. Tabelas do Banco e O Que Elas Guardam

- **users:** Usuários do sistema (login, senha, e-mail)
- **empresas:** Dados das empresas
- **funcionarios:** Funcionários das empresas
- **clientes:** Dados cadastrais dos clientes
- **atendimentos:** Chamados/atendimentos de clientes
- **orcamentos:** Orçamentos comerciais
- **orcamento_itens:** Itens de cada orçamento (produto/serviço)
- **orcamento_taxas:** Taxas aplicadas ao orçamento
- **orcamento_pagamentos:** Formas de pagamento do orçamento
- **contas_financeiras:** Contas bancárias, cartões, caixa
- **cobrancas:** Cobranças financeiras (relacionadas a orçamentos/clientes)
- **boletos:** Boletos gerados para clientes
- **contas_fixas:** Despesas recorrentes
- **notas_fiscais:** Notas fiscais emitidas/recebidas
- **pivot tables:** Relacionamentos entre empresas, clientes e funcionários

---

Relatório gerado automaticamente em 08/03/2026.
