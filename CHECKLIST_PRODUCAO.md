# ✅ CHECKLIST DE DEPLOY EM PRODUÇÃO
**Sistema de Contas a Pagar + Fornecedores**  
**Data:** 29/01/2026

---

## 📋 PRÉ-DEPLOY

### Backup
- [ ] Backup completo do banco de dados de produção
- [ ] Backup dos arquivos da aplicação
- [ ] Testar restauração do backup

### Ambiente de Homologação
- [ ] Executar script SQL em ambiente de teste
- [ ] Validar criação de todas as tabelas
- [ ] Validar inserção dos dados iniciais (seeders)
- [ ] Testar todas as funcionalidades

---

## 🗄️ BANCO DE DADOS

### 1. Executar Script SQL
```bash
mysql -u usuario -p nome_banco < database/PRODUCAO_UPDATE.sql
```

**Tabelas que serão criadas:**
- [ ] `centros_custo` (1 registro inicial)
- [ ] `categorias` (3 registros)
- [ ] `subcategorias` (10 registros)
- [ ] `contas` (27 registros)
- [ ] `fornecedores` (vazia)
- [ ] `fornecedor_contatos` (vazia)
- [ ] `contas_fixas_pagar` (vazia)
- [ ] `contas_pagar` (vazia)

### 2. Verificar Estrutura
```sql
-- Verificar se todas as tabelas existem
SHOW TABLES LIKE '%fornecedor%';
SHOW TABLES LIKE '%conta%';

-- Verificar foreign keys
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'nome_banco'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- Contar registros iniciais
SELECT 'Centros de Custo' as Tabela, COUNT(*) as Total FROM centros_custo
UNION ALL SELECT 'Categorias', COUNT(*) FROM categorias
UNION ALL SELECT 'Subcategorias', COUNT(*) FROM subcategorias
UNION ALL SELECT 'Contas', COUNT(*) FROM contas;
```

### 3. Verificar ENUMs
```sql
-- Fornecedores (deve ser PF, PJ)
SHOW COLUMNS FROM fornecedores LIKE 'tipo_pessoa';

-- Contas Fixas (deve ter periodicidade)
SHOW COLUMNS FROM contas_fixas_pagar LIKE 'periodicidade';

-- Contas a Pagar (deve ter forma_pagamento)
SHOW COLUMNS FROM contas_pagar LIKE 'forma_pagamento';
```

---

## 📁 ARQUIVOS DA APLICAÇÃO

### Novos Controllers
- [ ] `app/Http/Controllers/FornecedorController.php` (208 linhas)
- [ ] `app/Http/Controllers/ContasPagarController.php` (455 linhas)

### Controllers Modificados
- [ ] `app/Http/Controllers/FinanceiroController.php` (dashboard atualizado)

### Novos Models
- [ ] `app/Models/Fornecedor.php`
- [ ] `app/Models/FornecedorContato.php`
- [ ] `app/Models/ContaPagar.php`
- [ ] `app/Models/ContaFixaPagar.php`
- [ ] `app/Models/CentroCusto.php`
- [ ] `app/Models/Categoria.php`
- [ ] `app/Models/Subcategoria.php`
- [ ] `app/Models/Conta.php`

### Novas Views
- [ ] `resources/views/fornecedores/index.blade.php`
- [ ] `resources/views/fornecedores/create.blade.php`
- [ ] `resources/views/fornecedores/edit.blade.php`
- [ ] `resources/views/financeiro/partials/modal-conta-pagar.blade.php`
- [ ] `resources/views/financeiro/partials/modal-conta-fixa-pagar.blade.php`
- [ ] `resources/views/financeiro/partials/modal-confirmar-pagamento.blade.php`
- [ ] `resources/views/financeiro/partials/modal-excluir-conta-pagar.blade.php`

### Views Modificadas
- [ ] `resources/views/dashboard-financeiro/index.blade.php`

### Migrations
- [ ] `database/migrations/2026_01_29_150001_create_centros_custo_table.php`
- [ ] `database/migrations/2026_01_29_150002_create_categorias_table.php`
- [ ] `database/migrations/2026_01_29_150003_create_subcategorias_table.php`
- [ ] `database/migrations/2026_01_29_150004_create_contas_table.php`
- [ ] `database/migrations/2026_01_29_150005_create_contas_fixas_pagar_table.php`
- [ ] `database/migrations/2026_01_29_150006_create_contas_pagar_table.php`
- [ ] `database/migrations/2026_01_29_160000_create_fornecedores_table.php`

### Seeders
- [ ] `database/seeders/CentroCustoSeeder.php`
- [ ] `database/seeders/CategoriasSeeder.php`

### Routes
- [ ] Verificar `routes/web.php` (10 novas rotas)

---

## 🚀 DEPLOY

### 1. Transferir Arquivos
```bash
# Via FTP/SFTP ou Git
git pull origin main
```

### 2. Limpar Caches do Laravel
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear
```

### 3. Recompilar Otimizações
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 4. Permissões (se necessário)
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 🧪 TESTES PÓS-DEPLOY

### Fornecedores

#### 1. Criar Fornecedor Pessoa Física
- [ ] Acessar `/fornecedores/create`
- [ ] Selecionar "Pessoa Física"
- [ ] Preencher CPF: 123.456.789-00
- [ ] Preencher Nome Completo
- [ ] Adicionar contato
- [ ] Salvar
- [ ] Verificar se aparece na listagem

#### 2. Criar Fornecedor Pessoa Jurídica
- [ ] Acessar `/fornecedores/create`
- [ ] Selecionar "Pessoa Jurídica"
- [ ] Preencher CNPJ: 12.345.678/0001-00
- [ ] Preencher Razão Social
- [ ] Preencher Nome Fantasia
- [ ] Buscar CEP automático
- [ ] Adicionar múltiplos contatos
- [ ] Salvar
- [ ] Verificar se aparece na listagem

#### 3. Editar Fornecedor
- [ ] Clicar em "Editar" em um fornecedor
- [ ] Alterar dados
- [ ] Adicionar/remover contatos
- [ ] Salvar
- [ ] Verificar alterações

#### 4. Buscar Fornecedor
- [ ] Usar campo de busca por nome/CPF/CNPJ
- [ ] Filtrar por status (ativo/inativo)
- [ ] Verificar resultados

#### 5. Deletar Fornecedor
- [ ] Tentar deletar fornecedor sem vínculos
- [ ] Tentar deletar fornecedor COM contas vinculadas (deve falhar ou avisar)

---

### Contas a Pagar

#### 1. Criar Conta Avulsa
- [ ] Acessar `/financeiro/contas-a-pagar`
- [ ] Clicar em "Nova Conta"
- [ ] Selecionar Centro de Custo
- [ ] Selecionar Categoria
- [ ] Verificar se subcategorias carregam dinamicamente
- [ ] Selecionar Subcategoria
- [ ] Verificar se contas carregam dinamicamente
- [ ] Selecionar Conta
- [ ] Selecionar Fornecedor (opcional)
- [ ] Preencher Descrição
- [ ] Preencher Valor
- [ ] Selecionar Data de Vencimento
- [ ] Selecionar Forma de Pagamento
- [ ] Selecionar Conta Financeira
- [ ] Salvar
- [ ] Verificar se aparece na listagem com status "Em Aberto"

#### 2. Criar Conta Fixa (Recorrente)
- [ ] Clicar em "Nova Conta Fixa"
- [ ] Preencher todos os campos
- [ ] Selecionar Periodicidade: MENSAL
- [ ] Selecionar Data Inicial: 01/02/2026
- [ ] Selecionar Data Final: 01/01/2027
- [ ] Salvar
- [ ] **IMPORTANTE:** Verificar se foram criadas 12 parcelas
- [ ] Verificar descrição: "Nome - 02/2026", "Nome - 03/2026", etc.
- [ ] Verificar se todas têm status "Em Aberto"

#### 3. Testar Outras Periodicidades
- [ ] Criar conta fixa SEMANAL (deve gerar ~52 parcelas/ano)
- [ ] Criar conta fixa QUINZENAL (deve gerar ~24 parcelas/ano)
- [ ] Criar conta fixa TRIMESTRAL (deve gerar 4 parcelas/ano)
- [ ] Criar conta fixa SEMESTRAL (deve gerar 2 parcelas/ano)
- [ ] Criar conta fixa ANUAL (deve gerar 1 parcela/ano)

#### 4. Confirmar Pagamento
- [ ] Clicar em "Pagar" em uma conta
- [ ] Selecionar Forma de Pagamento
- [ ] Selecionar Conta Financeira
- [ ] Confirmar
- [ ] Verificar se status mudou para "Pago"
- [ ] Verificar se data de pagamento foi registrada
- [ ] **CRÍTICO:** Verificar se saldo da conta financeira foi atualizado

#### 5. Deletar Conta Avulsa
- [ ] Clicar em "Excluir" em uma conta avulsa
- [ ] Confirmar exclusão
- [ ] Verificar se foi removida da listagem

#### 6. Deletar Conta Fixa - Opção 1
- [ ] Clicar em "Excluir" em UMA parcela de conta fixa
- [ ] Modal deve aparecer com 2 opções
- [ ] Selecionar "Apenas esta parcela"
- [ ] Confirmar
- [ ] Verificar se apenas aquela parcela foi deletada
- [ ] Verificar se as outras parcelas continuam

#### 7. Deletar Conta Fixa - Opção 2
- [ ] Clicar em "Excluir" em UMA parcela de conta fixa
- [ ] Selecionar "Esta e todas as próximas"
- [ ] Confirmar
- [ ] Verificar se a parcela atual E todas as futuras foram deletadas
- [ ] Verificar se as parcelas anteriores continuam
- [ ] Verificar se parcelas JÁ PAGAS não foram deletadas

#### 8. Filtros e Busca
- [ ] Filtrar por status: Em Aberto
- [ ] Filtrar por status: Pago
- [ ] Filtrar por status: Vencido
- [ ] Buscar por descrição
- [ ] Filtrar por período (data início/fim)
- [ ] Filtrar por fornecedor
- [ ] Combinar múltiplos filtros

---

### Dashboard Financeiro

#### 1. Visualização Geral
- [ ] Acessar `/financeiro/dashboard`
- [ ] Verificar se KPIs aparecem:
  - Total a Receber
  - Total Recebido
  - Total a Pagar (deve mostrar valor)
  - Saldo Atual

#### 2. Gráfico de Fluxo de Caixa
- [ ] Verificar se gráfico possui 4 linhas:
  - Receita Prevista (azul)
  - Receita Recebida (verde)
  - **Despesa Prevista (laranja)** ← NOVO
  - **Despesa Paga (vermelho)** ← NOVO
- [ ] Verificar se valores das despesas aparecem corretamente
- [ ] Passar mouse sobre pontos para ver tooltips

#### 3. Validar Cálculos
```sql
-- Comparar valores do dashboard com query direta
SELECT 
    DATE_FORMAT(data_vencimento, '%Y-%m') as mes,
    SUM(CASE WHEN status = 'pago' THEN valor ELSE 0 END) as pago,
    SUM(CASE WHEN status = 'em_aberto' THEN valor ELSE 0 END) as previsto
FROM contas_pagar
WHERE data_vencimento BETWEEN '2026-01-01' AND '2026-12-31'
GROUP BY mes
ORDER BY mes;
```

---

## 🔍 VERIFICAÇÕES DE INTEGRIDADE

### ENUMs Corretos
- [ ] tipo_pessoa aceita: `PF`, `PJ` (NÃO FISICA/JURIDICA)
- [ ] periodicidade aceita: `SEMANAL`, `QUINZENAL`, `MENSAL`, etc.
- [ ] forma_pagamento aceita: `PIX`, `BOLETO`, `TRANSFERENCIA`, etc.
- [ ] status aceita: `em_aberto`, `pago`, `vencido`
- [ ] tipo aceita: `avulsa`, `fixa`

### Foreign Keys Funcionando
```sql
-- Tentar inserir conta com centro_custo inexistente (deve falhar)
INSERT INTO contas_pagar (centro_custo_id, conta_id, descricao, valor, data_vencimento)
VALUES (999999, 1, 'Teste', 100, '2026-02-01');
-- Esperado: ERROR 1452 (23000): Cannot add or update a child row

-- Deletar categoria que tem subcategorias (CASCADE deve funcionar)
-- Criar categoria de teste, subcategoria, e deletar categoria
-- Subcategoria deve ser deletada automaticamente
```

### Soft Deletes
```sql
-- Verificar se deleted_at é setado ao invés de delete físico
SELECT * FROM contas_pagar WHERE deleted_at IS NOT NULL;
SELECT * FROM fornecedores WHERE deleted_at IS NOT NULL;
```

### Atualização de Saldo
```sql
-- Antes de pagar uma conta
SELECT saldo_atual FROM contas_financeiras WHERE id = X;

-- Pagar conta de R$ 100,00

-- Depois de pagar
SELECT saldo_atual FROM contas_financeiras WHERE id = X;
-- Saldo deve ter diminuído em R$ 100,00
```

---

## 🚨 PROBLEMAS CONHECIDOS E SOLUÇÕES

### Problema 1: Enum não aceita valores
**Sintoma:** Erro ao salvar fornecedor com tipo_pessoa  
**Causa:** Banco em produção pode estar com ENUM antigo (FISICA/JURIDICA)  
**Solução:**
```sql
ALTER TABLE fornecedores 
MODIFY COLUMN tipo_pessoa ENUM('PF', 'PJ') NOT NULL;
```

### Problema 2: Conta fixa gera apenas 1 parcela
**Sintoma:** Ao criar conta fixa MENSAL, gera só 1 parcela  
**Causa:** Lógica do match expression não está executando  
**Solução:** Verificar `ContasPagarController@storeContaFixa`, linha do loop

### Problema 3: Saldo não atualiza ao pagar
**Sintoma:** Conta marcada como paga mas saldo da conta financeira não muda  
**Causa:** Transaction não está commitando  
**Solução:** Verificar `ContasPagarController@marcarComoPago`

### Problema 4: Selects não carregam dinamicamente
**Sintoma:** Ao selecionar categoria, subcategorias não aparecem  
**Causa:** AlpineJS não está carregado ou rotas API não existem  
**Solução:** 
- Verificar se AlpineJS está no layout principal
- Testar rotas API manualmente: `/financeiro/api/subcategorias/1`

### Problema 5: Dashboard não mostra despesas
**Sintoma:** Gráfico mostra apenas receitas  
**Causa:** Controlador errado ou variáveis não passadas  
**Solução:** Garantir que rota aponta para `FinanceiroController@dashboard`

---

## 📊 MÉTRICAS DE SUCESSO

- [ ] Todas as 8 tabelas criadas
- [ ] 41 registros iniciais inseridos (1 centro + 3 categorias + 10 subcategorias + 27 contas)
- [ ] Fornecedores PF e PJ criados com sucesso
- [ ] Conta fixa MENSAL gera 12 parcelas
- [ ] Delete seletivo funciona (apenas esta / esta e próximas)
- [ ] Pagamento atualiza saldo da conta financeira
- [ ] Dashboard mostra 4 linhas no gráfico
- [ ] Filtros e buscas retornam resultados corretos
- [ ] Sem erros 500 nos logs
- [ ] Sem erros JavaScript no console

---

## 📝 LOGS IMPORTANTES

### Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

### Erros SQL
```sql
SHOW ENGINE INNODB STATUS;
```

### Performance
```sql
SHOW PROCESSLIST;
```

---

## 🔄 ROLLBACK (Se necessário)

### Reverter Banco de Dados
```sql
-- Deletar tabelas na ordem reversa (foreign keys)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS contas_pagar;
DROP TABLE IF EXISTS contas_fixas_pagar;
DROP TABLE IF EXISTS fornecedor_contatos;
DROP TABLE IF EXISTS fornecedores;
DROP TABLE IF EXISTS contas;
DROP TABLE IF EXISTS subcategorias;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS centros_custo;
SET FOREIGN_KEY_CHECKS = 1;
```

### Reverter Arquivos
```bash
git checkout HEAD~1  # Voltar 1 commit
# ou
git revert <commit_hash>  # Reverter commit específico
```

---

## ✅ CONCLUSÃO

**Deployment Completo:**
- [ ] Backup realizado
- [ ] Script SQL executado com sucesso
- [ ] Todos os arquivos transferidos
- [ ] Caches limpos
- [ ] Todos os testes passaram
- [ ] Sem erros nos logs
- [ ] Usuários notificados sobre novas funcionalidades

**Documentação:**
- [ ] README atualizado
- [ ] Documentação de API criada (se aplicável)
- [ ] Manual do usuário atualizado
- [ ] Changelog publicado

---

**Data do Deploy:** _______________  
**Responsável:** _______________  
**Homologado por:** _______________
