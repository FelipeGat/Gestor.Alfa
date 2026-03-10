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
| | Firebase Admin SDK | Notificações Push |
| **Frontend** | TailwindCSS | 3.x |
| | AlpineJS | Framework leve |
| | Vite | Build tool |
| | Laravel Blade | Template engine |
| **Banco de Dados** | MySQL | 8.0 |
| **Cache** | Redis | 7 |
| **Infraestrutura** | Docker | Containerização |
| | Nginx | Servidor web |
| | PHP-FPM | Processamento PHP |
| | OSRM | Roteamento Offline |
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
│   ├── NotificacaoService.php # 🔔 Gestão de Push Notifications (FCM)
│   └── RotaService.php        # 🗺️ Cálculo de rotas (TomTom/OSRM)
├── Repositories/              # 💾 Acesso a dados
│   ├── Interfaces/           # Contratos (abstrações)
│   └── Eloquent/             # Implementações concretas
├── Models/                    # Modelos Eloquent
├── DTOs/                      # Data Transfer Objects
├── Enums/                     # Enumerações
├── Exceptions/                # Exceções customizadas
└── Providers/                 # Service Providers
```

### Services Implementados

| Service | Módulo | Status | Testes |
|---------|--------|--------|--------|
| `ContaPagarService` | Financeiro | ✅ | ✅ 16 testes |
| `ContaReceberService` | Financeiro | ✅ | ✅ 19 testes |
| `NotificacaoService` | Infra | ✅ | FCM Ativo |
| `RotaService` | Logística | ✅ | TomTom + OSRM |

---

## 📱 Funcionalidades Mobile (API V1)

O sistema possui uma API robusta para integração com aplicativos móveis:

- **Auth**: Autenticação via Sanctum (Bearer Token).
- **Notificações Push**: Registro de tokens FCM e envio automático para técnicos.
- **Ponto Digital**: Registro de entrada/saída com geolocalização.
- **Agenda**: Visualização de atendimentos agendados.
- **Rotas**: Cálculo de distância e tempo entre técnico e cliente.

### Gatilhos de Notificação (Push)
- 🔔 **Novo Chamado**: Disparado ao atribuir um técnico a um atendimento.
- 🔔 **Mudança de Status**: Notifica o técnico sobre atualizações no painel administrativo.
- 🔔 **Reagendamento**: Alerta sobre alterações de data/hora na agenda técnica.

---

## 🗺️ Geolocalização e Rotas

Implementado sistema híbrido para cálculo de rotas:
1. **TomTom API**: Utilizado para rotas com tráfego em tempo real (Prioritário).
2. **OSRM (Local)**: Fallback para roteamento offline rodando em container Docker.
3. **Multiplicadores de Horário**: Ajuste de tempo estimado baseado em horários de pico.

---

## 📊 Containers Docker

| Container | Imagem | Porta | Descrição |
|-----------|--------|-------|-----------|
| nginx | nginx:alpine | 80, 443 | Proxy reverso |
| php-fpm | PHP 8.3 | - | Processamento PHP |
| mysql | MySQL 8.0 | 3306 | Banco de dados |
| redis | Redis 7 | 6379 | Cache |
| osrm | osrm-backend | 5000 | Motor de rotas |
| queue-worker | PHP 8.3 | - | Jobs em fila |

---

## 🔧 Configuração do Firebase

Para o funcionamento das notificações:
1. Salvar `firebase-auth.json` em `storage/app/`.
2. Adicionar no `.env`: `FIREBASE_CREDENTIALS=/var/www/storage/app/firebase-auth.json`.
3. Limpar cache: `php artisan config:clear`.

---

## 🔐 Segurança e API

- **Sanctum**: Tokens de longa duração para o App.
- **Tokens FCM**: Armazenados na tabela `users` (`fcm_token`, `plataforma`).
- **Validação**: Todas as rotas de API V1 protegidas por middleware `auth:sanctum`.

---

## ⚠️ Licença

**Copyright (c) 2024-2026 Felipe Henrique Gat - Todos os Direitos Reservados**
