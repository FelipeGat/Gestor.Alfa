# Auditoria Técnica RH

## Escopo analisado

- Cadastro de funcionários
- Registro de ponto/jornada
- Dependências com `funcionario_id`
- Permissões, rotas e controllers relacionados

## Onde está o cadastro de funcionários

- Controller: `app/Http/Controllers/FuncionarioController.php`
- Model principal: `app/Models/Funcionario.php`
- Views: `resources/views/funcionarios/index.blade.php`, `create.blade.php`, `edit.blade.php`
- Rotas: `Route::resource('funcionarios', FuncionarioController::class)` em `routes/web.php`

## Onde está o registro de ponto hoje

- Não existe tabela dedicada de ponto clássico (entrada/saída) no core atual.
- Controle operacional de tempo está no fluxo de atendimentos:
    - Campos em `atendimentos`: `iniciado_em`, `finalizado_em`, `tempo_execucao_segundos`, `tempo_pausa_segundos`, `em_execucao`, `em_pausa`
    - Pausas em `AtendimentoPausa` (`app/Models/AtendimentoPausa.php`)
    - Processamento em `app/Http/Controllers/PortalFuncionarioController.php`

## Relacionamentos da tabela funcionarios

- `empresa_funcionario` (pivot com `empresas`)
- `users.funcionario_id`
- `atendimentos.funcionario_id`
- No novo módulo RH, também:
    - `funcionario_jornadas`
    - `funcionario_epis`
    - `funcionario_documentos`
    - `funcionario_beneficios`
    - `ferias`
    - `afastamentos`
    - `advertencias`
    - `rh_ajustes_ponto`

## Permissões relacionadas a funcionários

- Controle via `canPermissao('funcionarios', <acao>)` no `FuncionarioController`.
- Perfis administrativos detectados por `User::isAdminPanel()`.
- Novo módulo RH foi protegido por middleware administrativo (`dashboard.admin`).

## Rotas/controllers relacionados a funcionários e ponto

- Funcionários:
    - `routes/web.php` → resource `funcionarios`
    - `FuncionarioController`
- Ponto/Jornada operacional existente:
    - `routes/web.php` → prefixo `portal-funcionario`
    - `PortalFuncionarioController`
    - `AtendimentoAndamentoFotoController`

## Dependências mapeadas (`funcionario_id`)

- `users.funcionario_id`
- `atendimentos.funcionario_id`
- Serviços e dashboards:
    - Agenda técnica (`AgendaTecnicaController`, `AgendaTecnicaService`)
    - Dashboard técnico/admin/comercial com métricas por técnico
    - Portal do funcionário com execução/pausa/finalização

## Itens críticos para não quebrar

- Não remover nem renomear rotas legadas de `funcionarios`.
- Não alterar sem validação a lógica de execução/pausa/finalização de atendimento.
- Preservar FKs e integridade referencial em todas as novas tabelas RH.

## Resultado da fase de análise

- Dependências foram validadas antes da criação do módulo RH.
- Migração foi desenhada em paralelo ao legado, mantendo compatibilidade.
