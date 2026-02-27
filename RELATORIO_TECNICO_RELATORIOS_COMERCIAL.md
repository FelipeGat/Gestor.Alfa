# Relatório Técnico — Implementação dos Relatórios do Módulo Comercial

Data: 26/02/2026

## 1) Vistoria completa (ETAPA 1)

### Estrutura de dados mapeada (núcleo comercial/financeiro)

- `orcamentos` (PK: `id`, FKs: `empresa_id`, `cliente_id`, `pre_cliente_id`, `atendimento_id`, `created_by`)
- `orcamento_itens` (PK: `id`, FKs: `orcamento_id`, `item_comercial_id`)
- `itens_comerciais` (PK: `id`, FK: `categoria_id` -> `assuntos`)
- `cobrancas` (PK: `id`, FKs: `orcamento_id`, `cliente_id`, `conta_financeira_id`, `boleto_id`, `conta_fixa_id`)
- `clientes` (PK: `id`)
- `pre_clientes` (PK: `id`, FKs: `created_by`, `cliente_id`)
- `empresas` (PK: `id`)
- `users` (PK: `id`, FKs: `cliente_id`, `funcionario_id`)
- pivots multiempresa/perfil: `user_empresa`, `perfil_user`, `cliente_empresa`, `cliente_user`

### Fluxo comercial atual identificado

1. Orçamento criado em `OrcamentoController@store` com status inicial `em_elaboracao`.
2. Aprovação/andamento ocorre por atualização de status em `OrcamentoController@updateStatus`.
3. Entrada no financeiro ocorre via status `financeiro` e geração de cobranças (`GeradorCobrancaOrcamento`).
4. Receita real é registrada via `cobrancas` pagas (`status = pago`, `pago_em`).

### Lacunas encontradas

Campos estratégicos ausentes em `orcamentos`:

- `data_envio`
- `data_aprovacao`
- `vendedor_id`
- `origem_lead`
- `probabilidade_fechamento`

Campo equivalente existente:

- `status` (equivalente funcional para `status_orcamento`)

Campos já existentes:

- `valor_total`
- `empresa_id`

## 2) Validação e migrations seguras (ETAPA 2)

### Migration criada

- `database/migrations/2026_02_26_120000_add_comercial_fields_to_orcamentos_table.php`

### Alterações aplicadas

- Novas colunas em `orcamentos`:
    - `data_envio` (`datetime`, nullable)
    - `data_aprovacao` (`datetime`, nullable)
    - `vendedor_id` (FK para `users`, nullable, `nullOnDelete`)
    - `origem_lead` (`string`, nullable)
    - `probabilidade_fechamento` (`decimal(5,2)`, default `0`)
- Índices criados:
    - `orcamentos_empresa_status_created_idx`
    - `orcamentos_vendedor_status_created_idx`
    - `orcamentos_data_envio_idx`
    - `orcamentos_data_aprovacao_idx`
    - `orcamentos_origem_lead_idx`

### Segurança da migration

- Sem remoção de colunas existentes.
- Sem alteração de tipos críticos existentes.
- Valores default seguros (`probabilidade_fechamento = 0`).
- Colunas novas nullable para compatibilidade retroativa.

## 3) Relatórios implementados no submenu Comercial (ETAPA 3)

Rota e tela principal:

- `GET /relatorios/comercial` (`relatorios.comercial`)

Relatórios incluídos:

1. **Pipeline Comercial**
    - Orçamentos por status
    - Valor total por etapa
    - Taxa de conversão
    - Ticket médio
    - Tempo médio de fechamento

2. **Orçamentos Enviados x Fechados**
    - Quantidade enviada
    - Quantidade fechada
    - Conversão
    - Valor total enviado
    - Valor total fechado

3. **Follow-up Comercial**
    - Sem resposta > 3 dias
    - Sem resposta > 7 dias
    - Sem resposta > 15 dias
    - Lista paginada para ação comercial

4. **Receita Prevista x Receita Real**
    - Soma contratos/orçamentos aprovados
    - Soma ponderada de negociação por probabilidade
    - Receita efetiva de cobranças pagas
    - Diferença percentual

5. **Origem dos Leads**
    - Quantidade por origem
    - Valor gerado
    - Taxa de conversão

6. **Ticket Médio por Tipo de Serviço**
    - Ticket médio por tipo
    - Quantidade de vendas
    - Receita total por tipo

7. **Performance por Vendedor**
    - Valor vendido
    - Conversão
    - Ticket médio
    - Tempo médio de fechamento

8. **Clientes Ativos x Inativos**
    - Clientes com compra últimos 12 meses
    - Clientes sem compra no período
    - Receita anual por cliente

9. **Lucratividade por Serviço**
    - Valor vendido
    - Custo estimado
    - Margem bruta
    - Margem percentual

## 4) Padrão de implementação (ETAPA 4)

- Camada de cálculo isolada em Service:
    - `app/Services/Relatorio/RelatorioComercialService.php`
- Camada HTTP separada:
    - `app/Http/Controllers/RelatorioComercialController.php`
- Camada de exibição separada:
    - `resources/views/relatorios/comercial.blade.php`
- Filtros implementados:
    - Período (`data_inicio`, `data_fim`)
    - Empresa (`empresa_id`)
    - Vendedor (`vendedor_id`)
    - Tipo de serviço (`tipo_servico`)
- Consultas com agregações e índices para volume.

## 5) Segurança e preservação (ETAPA 5)

- Não foram removidas colunas/tabelas existentes.
- Não foram alteradas regras financeiras atuais.
- Fluxos atuais de `OrcamentoController` preservados e estendidos com campos opcionais.
- Atualização de status passou a registrar automaticamente:
    - `data_envio` ao entrar em `aguardando_aprovacao`
    - `data_aprovacao` ao entrar em fases fechadas/aprovadas

## 6) Testes executados (ETAPA 6)

### Automatizados

- Arquivo: `tests/Feature/RelatorioComercialTest.php`
- Casos:
    - exige autenticação
    - renderiza para usuário admin

Execução:

- `php artisan test tests/Feature/RelatorioComercialTest.php`
- Resultado: **2 passed**

## 7) Validação final e inventário de entrega (ETAPA 7)

### Arquivos criados

- `database/migrations/2026_02_26_120000_add_comercial_fields_to_orcamentos_table.php`
- `app/Services/Relatorio/RelatorioComercialService.php`
- `app/Http/Controllers/RelatorioComercialController.php`
- `resources/views/relatorios/comercial.blade.php`
- `tests/Feature/RelatorioComercialTest.php`
- `RELATORIO_TECNICO_RELATORIOS_COMERCIAL.md`

### Arquivos alterados

- `app/Models/Orcamento.php`
- `app/Http/Controllers/OrcamentoController.php`
- `routes/web.php`
- `resources/views/relatorios/index.blade.php`
- `resources/views/layouts/navigation.blade.php`

### Rotas adicionadas

- `relatorios.comercial` (`/relatorios/comercial`)

### Controllers adicionados

- `RelatorioComercialController`

### Services adicionados

- `RelatorioComercialService`

### Índices criados

- `orcamentos_empresa_status_created_idx`
- `orcamentos_vendedor_status_created_idx`
- `orcamentos_data_envio_idx`
- `orcamentos_data_aprovacao_idx`
- `orcamentos_origem_lead_idx`
