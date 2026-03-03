# 📅 Reprogramação de Agendamento de Orçamentos

## 📋 Visão Geral

Implementada funcionalidade para reprogramar agendamentos de orçamentos aprovados/agendados que já possuem atendimento vinculado.

---

## 🔍 Problema Identificado

Quando um orçamento era aprovado e um técnico era agendado, um atendimento era criado automaticamente com data de agendamento. Porém, **não existia nenhuma forma de editar ou alterar essa data posteriormente**.

### Fluxo Anterior:
1. Orçamento aprovado → Técnico agendado
2. Atendimento criado automaticamente com `data_inicio_agendamento`
3. ❌ **Sem opção de edição** - único caminho era excluir e criar outro

---

## ✅ Solução Implementada

### 1. Backend

#### `AgendaTecnicaController.php`
Adicionado método `reprogramarAgendamento()`:
- Valida se orçamento possui atendimento vinculado
- Valida se atendimento possui técnico atribuído
- Reprograma atendimento usando `AgendaTecnicaService`
- Atualiza `data_agendamento` no orçamento
- Retorna mensagem informativa com mudança de técnico (se aplicável)

```php
public function reprogramarAgendamento(
    Request $request,
    Orcamento $orcamento,
    AgendaTecnicaService $agendaService
): RedirectResponse
```

**Validações:**
- `funcionario_id`: required, exists
- `data_agendamento`: required, date_format: Y-m-d
- `periodo_agendamento`: required, in: [manha, tarde, noite, dia_todo]
- `hora_inicio`: required, date_format: H:i
- `duracao_horas`: required, integer, min:1, max:9

#### `AgendaTecnicaService.php`
Adicionado método `reprogramarAtendimento()`:
- Reutiliza lógica existente do `agendarAtendimento()`
- Passa ID do atendimento para ignorar conflito consigo mesmo
- Valida disponibilidade do técnico na nova data/horário

```php
public function reprogramarAtendimento(
    Atendimento $atendimento,
    int $funcionarioId,
    string $data,
    string $periodo,
    string $horaInicio,
    int $duracaoHoras
): Atendimento
```

#### `OrcamentoController.php`
Atualizado relacionamento na listagem:
```php
Orcamento::with([
    'atendimento:id,orcamento_id,funcionario_id,data_inicio_agendamento,data_fim_agendamento,periodo_agendamento'
])
```

#### `routes/web.php`
Adicionada nova rota:
```php
Route::post('/orcamentos/{orcamento}/reprogramar-agendamento', [AgendaTecnicaController::class, 'reprogramarAgendamento'])
    ->name('orcamentos.reprogramar-agendamento');
```

---

### 2. Frontend

#### `resources/views/orcamentos/index.blade.php`

**Botões Diferenciados:**
- 🔵 **Azul** (Reprogramar): Exibido quando já existe agendamento
  - Tooltip: "Reprogramar Agendamento (DD/MM/YYYY)"
  - Data attributes com dados atuais do agendamento
- 🟢 **Verde** (Agendar): Exibido quando não existe agendamento

**Modal Dinâmico:**
- Título muda: "Agendar Técnico" vs "Reprogramar Agendamento"
- Pré-preenche dados atuais quando reprogramação
- Usa endpoint diferente no submit

**JavaScript Atualizado:**
```javascript
// Novo event listener para botões de reprogramar
document.querySelectorAll('.btn-reprogramar-agendamento').forEach(button => {
    button.addEventListener('click', function() {
        abrirModal({
            isReprogramacao: true,
            dadosAgendamento: {
                funcionarioId: this.dataset.funcionarioId,
                data: this.dataset.dataAgendamento,
                periodo: this.dataset.periodo,
                horaInicio: this.dataset.horaInicio,
                duracao: this.dataset.duracao
            }
        });
    });
});
```

---

## 🔄 Fluxo de Uso

### Para Novo Agendamento:
1. Usuário clica no botão **verde** "Agendar Técnico"
2. Preenche todos os campos do modal
3. Clica em "Salvar agendamento"
4. Endpoint: `POST /orcamentos/{id}/agendar-tecnico`

### Para Reprogramação:
1. Usuário clica no botão **azul** "Reprogramar Agendamento"
2. Modal abre com dados atuais pré-preenchidos
3. Usuário altera data/horário/técnico conforme necessário
4. Clica em "Salvar agendamento"
5. Endpoint: `POST /orcamentos/{id}/reprogramar-agendamento`

---

## 📊 Dados Sincronizados

Ao reprogramar, os seguintes campos são atualizados:

| Tabela | Campo | Descrição |
|--------|-------|-----------|
| `atendimentos` | `funcionario_id` | Técnico responsável |
| `atendimentos` | `data_inicio_agendamento` | Data/hora início |
| `atendimentos` | `data_fim_agendamento` | Data/hora fim |
| `atendimentos` | `periodo_agendamento` | Período (manhã/tarde/noite/dia) |
| `atendimentos` | `duracao_agendamento_minutos` | Duração em minutos |
| `orcamentos` | `data_agendamento` | Data de agendamento (apenas data) |

---

## 🛡 Validações e Regras de Negócio

### Validações Aplicadas
- ✅ Técnico deve estar ativo
- ✅ Data no formato correto
- ✅ Horário dentro do período selecionado
- ✅ Duração compatível com período (máx 4h para manhã/tarde, 9h para dia)
- ✅ Sem conflito com outros agendamentos do mesmo técnico

### Tratamento de Erros
- Conflito de agenda: "Já existe atendimento agendado para este técnico no mesmo horário"
- Sem atendimento: "Não foi possível reprogramar: orçamento não possui atendimento vinculado"
- Sem técnico: "Atendimento não possui técnico atribuído"

---

## 🎨 Interface do Usuário

### Indicadores Visuais

| Situação | Cor | Ícone | Tooltip |
|----------|-----|-------|---------|
| Sem agendamento | 🟢 Verde | Calendário | "Agendar Técnico" |
| Com agendamento | 🔵 Azul | Calendário | "Reprogramar Agendamento (DD/MM/AAAA)" |

### Mensagens de Feedback
- **Sucesso**: "Agendamento reprogramado com sucesso."
- **Sucesso com troca de técnico**: "Agendamento reprogramado com sucesso. Técnico alterado de João Silva para Maria Santos."

---

## 📝 Considerações Técnicas

### Clean Architecture
- **Controller**: Apenas entrada HTTP e validação
- **Service**: Lógica de negócio de reprogramação
- **Model**: Relacionamentos e casts

### Transacionalidade
- Operação ocorre dentro de transação de banco de dados
- Rollback automático em caso de erro

### Reutilização de Código
- `reprogramarAtendimento()` delega para `agendarAtendimento()`
- Mesma validação de conflito de agenda
- Mesma lógica de cálculo de janela temporal

---

## 🧪 Testes Sugeridos

### Testes Unitários
- [ ] `reprogramarAtendimento()` calcula corretamente data fim
- [ ] `reprogramarAtendimento()` ignora conflito com próprio atendimento
- [ ] Validação de período funciona corretamente

### Testes de Integração
- [ ] Reprogramação para data sem conflitos
- [ ] Reprogramação para data com conflitos (deve falhar)
- [ ] Reprogramação com troca de técnico
- [ ] Reprogramação mantém histórico de status

### Testes Manuais
1. Aprovar orçamento e agendar técnico
2. Verificar botão azul na listagem
3. Clicar em reprogramar e alterar data
4. Verificar sincronização entre `orcamentos` e `atendimentos`

---

## 📌 Arquivos Alterados

| Arquivo | Tipo | Descrição |
|---------|------|-----------|
| `app/Http/Controllers/AgendaTecnicaController.php` | Controller | Método `reprogramarAgendamento()` |
| `app/Services/AgendaTecnicaService.php` | Service | Método `reprogramarAtendimento()` |
| `app/Http/Controllers/OrcamentoController.php` | Controller | Carregar relacionamento atendimento |
| `routes/web.php` | Routes | Rota de reprogramação |
| `resources/views/orcamentos/index.blade.php` | View | UI diferenciada + JavaScript |

---

## 🚀 Próximos Passos (Opcional)

- [ ] Adicionar histórico de reprogramações (tabela `agendamento_historicos`)
- [ ] Notificar técnico e cliente sobre reprogramação (email/SMS)
- [ ] Permitir reprogramação em lote (múltiplos agendamentos)
- [ ] Adicionar justificativa obrigatória para reprogramação
- [ ] Dashboard de reprogramações (quantas vezes cada técnico foi reprogramado)

---

## 📞 Suporte

Em caso de dúvidas ou problemas, abrir issue no repositório do projeto.

---

**Implementado em:** Março de 2026  
**Autor:** Qwen Code Assistant  
**Versão:** 1.0
