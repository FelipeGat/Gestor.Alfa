# API de Relatórios

## Endpoints

Todos os endpoints abaixo são `GET`, autenticados e aceitam os parâmetros:

- `empresa_id` (obrigatório)
- `data_inicio` (obrigatório, formato `Y-m-d`)
- `data_fim` (obrigatório, formato `Y-m-d`)
- `centro_custo_id` (opcional)

### 1) Financeiro

`/api/relatorios/financeiro`

Retorna:
- receita e despesa do período
- lucro líquido e margem
- receita/despesa por centro de custo
- saldo bancário total
- contas em aberto
- insights automáticos

### 2) Técnico

`/api/relatorios/tecnico`

Retorna:
- total de chamados, finalizados, abertos e cancelados
- tempo médio de atendimento
- quantidade e receita por técnico
- chamados vencidos
- insights automáticos

### 3) Comercial

`/api/relatorios/comercial`

Retorna:
- total de orçamentos
- fechados, perdidos e em aberto
- taxa de conversão
- receita fechada
- ticket médio
- tempo médio até fechamento
- insights automáticos

### 4) RH

`/api/relatorios/rh`

Retorna:
- total de atrasos, faltas e atestados
- horas extras
- saldo consolidado de banco de horas
- índice de absenteísmo
- ranking por colaborador
- insights automáticos

### 5) Painel Executivo

`/api/relatorios/painel-executivo`

Retorna estrutura consolidada com:
- receita total
- despesa total
- lucro
- crescimento vs período anterior equivalente
- conversão comercial
- receita por técnico
- índice de absenteísmo
- bloco consolidado de todos os módulos
