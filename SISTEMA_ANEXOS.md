# 📎 Sistema de Anexos - NF e Boleto

## ✅ Implementação Concluída

Foi implementado um sistema completo de anexos para as cobranças, permitindo anexar Notas Fiscais (NF) e Boletos em PDF.

---

## 🎯 Funcionalidades Implementadas

### 1️⃣ **Tela Contas a Receber** (`/financeiro/contas-a-receber`)

- ✅ **Ícone de Anexo** adicionado na coluna "Ações"
  - Cor roxa para fácil identificação
  - Exibe um contador (badge) com o número de anexos
  - Ao clicar, abre o modal de gerenciamento de anexos

### 2️⃣ **Modal de Gerenciamento de Anexos**

- ✅ **Upload de múltiplos arquivos PDF**
  - Selecionar tipo: Nota Fiscal ou Boleto
  - Aceita múltiplos arquivos de uma vez (até 10MB cada)
  - Visualização dos arquivos selecionados antes do envio

- ✅ **Listagem de anexos salvos**
  - Exibe todos os anexos da cobrança
  - Mostra tipo (NF/Boleto), tamanho e data de upload
  - Botão de download (verde)
  - Botão de exclusão (vermelho)

### 3️⃣ **Portal do Cliente** (`/portal/financeiro`)

- ✅ **Filtros de Período**
  - Data Início e Data Fim
  - Padrão: mês atual
  - Botões Filtrar e Limpar

- ✅ **KPIs/Resumo Financeiro**
  - Total em Aberto
  - Total Pago
  - Total Vencido
  - Total Geral

- ✅ **Listagem de Cobranças**
  - Exibe todas as cobranças do período filtrado
  - Coluna "Anexos" mostra botões para download de NF e Boleto
  - Diferenciação visual por cores:
    - 📄 NF = Azul
    - 💳 Boleto = Amarelo

- ✅ **Segurança**
  - Cliente só vê cobranças da empresa selecionada em `/portal/unidade`
  - Não pode acessar anexos de outras empresas

---

## 📁 Arquivos Criados/Modificados

### **Novos Arquivos:**
1. `database/migrations/2026_01_29_000001_create_cobranca_anexos_table.php` - Tabela de anexos
2. `app/Models/CobrancaAnexo.php` - Model de anexos
3. `resources/views/financeiro/partials/modal-anexos.blade.php` - Modal de gerenciamento

### **Arquivos Modificados:**
1. `app/Models/Cobranca.php` - Adicionado relacionamento `anexos()`
2. `app/Http/Controllers/FinanceiroController.php` - Redirecionamento para tela Cobrar
3. `app/Http/Controllers/ContasReceberController.php` - Métodos de upload/download/exclusão
4. `app/Http/Controllers/PortalController.php` - Adicionados filtros e anexos
5. `routes/web.php` - Rotas para gerenciamento de anexos
6. `resources/views/financeiro/contasareceber.blade.php` - Ícone de anexo
7. `resources/views/portal/financeiro/index.blade.php` - Filtros e exibição de anexos

---

## 🚀 Como Usar

### **Como Financeiro:**

1. Acesse **Contas a Receber**
2. Clique no ícone **roxo de anexo** (📎) na linha da cobrança
3. No modal que abrir:
   - Selecione o tipo (NF ou Boleto)
   - Escolha um ou mais arquivos PDF
   - Clique em "Enviar Anexo(s)"
4. Os anexos serão salvos e aparecerão no portal do cliente

### **Como Cliente:**

1. Acesse `/portal/unidade` e selecione sua empresa (se tiver mais de uma)
2. Acesse **Meu Financeiro** no menu
3. Use os filtros de data para buscar cobranças de um período específico
4. Na coluna "Anexos", clique nos botões para baixar NF ou Boleto
5. Os KPIs mostram totais do período filtrado

---

## 🔐 Segurança

- ✅ Apenas usuários do perfil **Financeiro** podem fazer upload/exclusão
- ✅ Clientes só veem anexos de cobranças da **empresa ativa** (selecionada em `/portal/unidade`)
- ✅ Validação de tipo de arquivo (apenas PDF)
- ✅ Validação de tamanho (máximo 10MB por arquivo)
- ✅ Arquivos são salvos em `storage/app/public/cobrancas/anexos`

---

## 📦 Armazenamento

Os arquivos são salvos em:
```
storage/app/public/cobrancas/anexos/
```

Para que os arquivos sejam acessíveis via web, certifique-se de que o link simbólico existe:
```bash
php artisan storage:link
```

---

## 🎨 Interface

### Contas a Receber:
- Ícone roxo de anexo com contador
- Modal moderno com upload drag-and-drop
- Listagem organizada dos anexos salvos

### Portal do Cliente:
- Filtros de data intuitivos
- KPIs coloridos e organizados
- Botões de download diferenciados por tipo (NF/Boleto)
- Design responsivo e moderno

---

## ⚡ Próximos Passos (Opcional)

Se precisar de melhorias futuras:
- [ ] Preview de PDF no modal
- [ ] Notificação por email quando anexos forem adicionados
- [ ] Histórico de downloads
- [ ] Upload via drag-and-drop
- [ ] Compressão automática de PDFs grandes

---

✨ **Sistema pronto para uso!**
