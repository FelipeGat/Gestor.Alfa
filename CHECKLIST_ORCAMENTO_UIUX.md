# Checklist — Módulo de Orçamentos (Uso + UI/UX)

Data: 26/02/2026

## 1) Cobertura funcional revisada

- [x] Listagem de orçamentos (`orcamentos.index`)
- [x] Filtros: busca, status, empresa e período
- [x] Ordenação por colunas na tabela
- [x] Atualização de status inline
- [x] Ações por linha: imprimir, duplicar, editar, excluir
- [x] Criação de orçamento (`orcamentos.create`)
- [x] Edição de orçamento (`orcamentos.edit`)
- [x] Busca de cliente/pré-cliente
- [x] Inclusão de itens (serviços/produtos)
- [x] Cálculo de descontos e taxas
- [x] Resumo financeiro em tempo real
- [x] Condição/forma de pagamento
- [x] Integração de status com fluxo comercial/financeiro
- [x] Impressão de orçamento (layouts por empresa)

## 2) Teste de uso técnico executado

### Cenários simulados

- [x] Navegação para listagem e uso de filtros
- [x] Mudança de status por select na tabela
- [x] Inclusão de taxa e itens no formulário
- [x] Validação de consistência dos totais na tela
- [x] Fluxos de testes automatizados relacionados a orçamento

### Testes automatizados executados

Comando:

`php artisan test tests/Unit/Enums/OrcamentoStatusTest.php tests/Unit/Actions/CriarOrcamentoDTOTest.php tests/Unit/Actions/AtualizarOrcamentoDTOTest.php tests/Feature/RelatorioComercialTest.php`

Resultado:

- [x] **23 passed**
- [x] **64 assertions**
- [x] sem falhas após correções

## 3) Problemas de usabilidade/UI/UX encontrados e corrigidos

### Problema 1 — KPIs da listagem inconsistentes

**Sintoma:** cards de resumo misturavam total geral com métricas calculadas só da página atual.

**Impacto UX:** leitura equivocada de aprovados, pendentes e valor total.

**Correção aplicada:** resumo agora vem de consulta agregada no backend sobre o conjunto filtrado completo.

Arquivos:

- `app/Http/Controllers/OrcamentoController.php`
- `resources/views/orcamentos/index.blade.php`

---

### Problema 2 — fallback de atualização de status com URL fixa

**Sintoma:** JS usava `'/orcamentos/{id}/status'` hardcoded no fallback.

**Impacto UX/robustez:** risco de quebra em base path/reverse proxy e ambientes não padrão.

**Correção aplicada:** fallback passa a usar URL da própria rota em `data-status-url`.

Arquivo:

- `resources/views/orcamentos/index.blade.php`

---

### Problema 3 — conflito de UX no bloco de taxas (criação)

**Sintoma:** havia script inline duplicando a lógica de taxas com nomes de campos diferentes dos processados no controller.

**Impacto UX:** interface duplicada/confusa e risco de taxa não persistir como esperado.

**Correção aplicada:** removido script inline conflitante; mantida lógica única via `resources/js/orcamento.js`.

Arquivo:

- `resources/views/orcamentos/create.blade.php`

---

### Problema 4 — erro funcional no enum de status

**Sintoma:** testes de transição falhavam com `Illegal offset type`.

**Impacto:** regra de transição de status inconsistente no domínio de orçamento.

**Correção aplicada:** mapa de transições passou a usar `value` (string) como chave e comparação estrita.

Arquivo:

- `app/Enums/OrcamentoStatus.php`

## 4) Resultado final

- [x] Fluxo de orçamento revisado ponta a ponta
- [x] Erros de usabilidade identificados e corrigidos
- [x] Correção funcional de regra de domínio relacionada a orçamento
- [x] Regressão validada por testes automatizados

## 5) Próximo passo recomendado (manual)

- [ ] Smoke manual com usuário comercial real em dados reais:
    - criar orçamento com serviço + produto + taxas + desconto
    - alterar status em sequência
    - imprimir
    - duplicar
    - validar visual mobile (larguras da tabela e filtros)
