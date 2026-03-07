# 📋 Análise Completa do Portal do Técnico - Gestor Alfa

**Data da Análise:** Março/2026  
**Versão do Sistema:** Laravel 12 + PHP 8.3

---

## 🎯 Resumo Executivo

O **Portal do Técnico** (também chamado de **Portal do Funcionário**) é um sistema completo para gestão de atendimentos técnicos, desenvolvido com abordagem **mobile-first**. O sistema inclui:

- ✅ Gestão completa de atendimentos (CRUD + fluxo de trabalho)
- ✅ Controle de tempo de execução com cronômetro em tempo real
- ✅ Sistema de pausas com 4 tipos e registro fotográfico
- ✅ Registro de ponto com geolocalização e fotos
- ✅ Agenda técnica calendarizada
- ✅ Integração com Google Maps/Waze para rotas
- ✅ Assinatura digital do cliente
- ✅ API REST completa para aplicativo móvel
- ✅ PWA (Progressive Web App) configurado

---

## 🗂️ Estrutura de Arquivos

### Controllers (Backend)

| Arquivo | Linhas | Descrição |
|---------|--------|-----------|
| `app/Http/Controllers/PortalFuncionarioController.php` | 1211 | Controller principal com todas as ações do portal |
| `app/Http/Controllers/Api/V1/AtendimentoController.php` | ~150 | API REST para atendimentos |
| `app/Http/Controllers/Api/V1/PontoController.php` | ~150 | API REST para registro de ponto |
| `app/Http/Controllers/Api/V1/DashboardTecnicoController.php` | ~100 | API do dashboard técnico |

### Models (Banco de Dados)

| Model | Descrição |
|-------|-----------|
| `Atendimento.php` | Modelo principal de atendimentos |
| `AtendimentoAndamento.php` | Histórico de andamentos |
| `AtendimentoAndamentoFoto.php` | Fotos dos andamentos |
| `AtendimentoPausa.php` | Registro de pausas |
| `AtendimentoStatusHistorico.php` | Histórico de status |
| `RegistroPontoPortal.php` | Registro de ponto do funcionário |

### Views (Frontend Blade)

| View | Descrição |
|------|-----------|
| `resources/views/portal-funcionario/index.blade.php` | Tela inicial com 4 botões principais |
| `resources/views/portal-funcionario/chamados.blade.php` | Lista de chamados por status |
| `resources/views/portal-funcionario/atendimento-detalhes.blade.php` | Detalhes completos do atendimento (1411 linhas) |
| `resources/views/portal-funcionario/agenda.blade.php` | Agenda técnica (calendário) |
| `resources/views/portal-funcionario/ponto.blade.php` | Registro de ponto com GPS |
| `resources/views/portal-funcionario/documentos.blade.php` | Documentos (placeholder - indisponível) |
| `resources/views/components/portal-funcionario-layout.blade.php` | Layout base do portal |

### Middleware

| Middleware | Descrição |
|------------|-----------|
| `FuncionarioMiddleware.php` | Controle de acesso ao portal (tipos de usuário permitidos) |

---

## 📱 Funcionalidades Detalhadas

### 1. Tela Inicial (`/portal-funcionario`)

**Rota:** `portal-funcionario.index`  
**View:** `portal-funcionario/index.blade.php`

**Objetivo:** Dashboard com visão geral e acesso rápido às funcionalidades.

**KPIs Exibidos:**
- **Chamados Abertos** - Total de atendimentos com status "aberto" ou "em_atendimento" sem `iniciado_em`
- **Em Atendimento** - Total de atendimentos em execução (apenas se houver pausados)
- **Finalizados** - Total de atendimentos com status "concluido"
- **Status do Ponto do Dia** - "Pendente hoje" ou "Concluído hoje" com próximo evento

**4 Botões Principais:**

1. **Registro de Ponto**
   - Ícone: Relógio
   - Cor: Teal (#3f9cae)
   - Badge: Status do ponto (Pendente/Concluído)
   - Link: `portal-funcionario.ponto`

2. **Painel de Chamados**
   - Ícone: Prancheta
   - Cor: Teal
   - Badge: Quantidade na fila
   - Link: `portal-funcionario.chamados`

3. **Agenda Técnica**
   - Ícone: Calendário
   - Cor: Teal
   - Badge: "Calendário"
   - Link: `portal-funcionario.agenda`

4. **Documentos**
   - Ícone: Documento
   - Cor: Teal
   - Badge: "Indisponível" (type="default")
   - Link: `portal-funcionario.documentos`
   - **Status:** Placeholder

---

### 2. Painel de Chamados (`/portal-funcionario/chamados`)

**Rota:** `portal-funcionario.chamados`  
**View:** `portal-funcionario/chamados.blade.php`

**Objetivo:** Lista organizada de todos os atendimentos do funcionário, agrupados por status.

**Seções:**

#### 2.1 Em Atendimento
- **Filtro:** `status_atual = 'em_atendimento' AND iniciado_em IS NOT NULL`
- **Ordenação:** Prioridade (alta → media → baixa), depois `iniciado_em DESC`
- **Exibição:** Grid 3 colunas (responsivo)
- **Recursos:**
  - Cronômetro em tempo real (JavaScript, atualização a cada 1s)
  - Banner âmbar se pausado: "⏸️ PAUSADO - 00:00:00"
  - Banner preto se em execução: cronômetro ao vivo

#### 2.2 Fila de Atendimento (Abertos)
- **Filtro:** `status_atual = 'aberto' OR (status_atual = 'em_atendimento' AND iniciado_em IS NULL)`
- **Ordenação:** Prioridade, depois `data_atendimento ASC`
- **Destaque:** Primeiro item tem badge "⭐ PRÓXIMO DA FILA" e anel verde
- **Botão:** "Iniciar Atendimento" (verde) apenas no primeiro

#### 2.3 Finalizados Recentes
- **Filtro:** `status_atual IN ('finalizacao', 'concluido')`
- **Limite:** 20 itens
- **Ordenação:** `finalizado_em DESC`
- **Badge:** "⏳ Aguardando" (finalizacao) ou "✓ Concluído" (concluido)

**Informações por Card:**
```
┌─────────────────────────────────┐
│ #NUM_ATENDIMENTO    [PRIORIDADE]│
│ NOME DO CLIENTE                 │
│ 📅 07/03/2026 14:30            │
│ 🏷️ Assunto                     │
│ Descrição (100 chars...)        │
│ [Ver Detalhes] ou [Iniciar]     │
└─────────────────────────────────┘
```

**Cores de Prioridade:**
- Alta: Vermelho (#ef4444) - borda esquerda vermelha
- Média: Laranja (#f59e0b) - borda esquerda laranja
- Baixa: Azul (#3b82f6) - borda esquerda teal

---

### 3. Detalhes do Atendimento (`/portal-funcionario/atendimento/{id}`)

**Rota:** `portal-funcionario.atendimento.show`  
**View:** `portal-funcionario/atendimento-detalhes.blade.php` (1411 linhas)

**Objetivo:** Visualização completa e execução do atendimento.

#### 3.1 Informações Exibidas

**Cabeçalho:**
- Número do atendimento (H2)
- Botão voltar

**Status Banner:**
- Cores por status:
  - Aberto: Azul claro (#bfdbfe)
  - Em atendimento: Laranja (#fed7aa)
  - Finalização: Amarelo (#fde68a)
  - Concluído: Verde (#d1fae5)

**Cronômetro Principal:**
- Exibido apenas se `status_atual = 'em_atendimento'` e `iniciado_em IS NOT NULL`
- Fundo preto (#1f2937 → #111827)
- Display: 3rem, fonte monospace
- Atualização: 1 segundo
- Se pausado: fundo laranja, exibe "⏸️ PAUSA PARA [TIPO]"

**Aviso Atendimento Antigo:**
- Exibido se `status_atual = 'em_atendimento'` e `iniciado_em IS NULL`
- Fundo amarelo, ícone de alerta
- Mensagem: "Use os botões abaixo para iniciar o controle de tempo"

**Aviso de Finalização:**
- Exibido se `status_atual = 'finalizacao'`
- Fundo amarelo claro
- Mensagem: "Aguardando Aprovação do Gerente"

**Card de Tempo Total (se finalizado):**
- Tempo de execução formatado (HH:MM:SS)
- Fotos de início (borda verde)
- Fotos de finalização (borda roxa)
- Assinatura do cliente (máx 320px)
- Detalhamento de pausas (se houver)

**Card de Informações:**
```
┌─────────────────────────────────┐
│ Cliente         │ Prioridade    │
│ Empresa         │ Data          │
│ Assunto                       │
│ Descrição (pre-wrap)          │
└─────────────────────────────────┘
```

**Card de Rota:**
- Endereço completo do cliente
- Status: "Buscando localização..." → calculado via JavaScript
- KPIs: Distância e Tempo estimado
- Mapa Leaflet (opcional, carregamento dinâmico)
- Botões:
  - ↻ Recalcular rota
  - 📍 Abrir no Google Maps
  - 🚗 Abrir no Waze

**Funcionalidade de Rota (JavaScript):**
- Geocodificação via Nominatim (OpenStreetMap)
- Cálculo de rota via OSRM
- Múltiplas tentativas de endereço (com/sem acentos, abreviações)
- Leaflet para exibição do mapa
- Links dinâmicos para Google Maps e Waze

**Card de Pausas (se houver):**
- Lista de todas as pausas
- Por pausa:
  - Tipo (Almoço, Deslocamento, Material, Fim do Dia)
  - Início e término
  - Duração (HH:MM:SS)
  - Usuário que pausou
  - Usuário que retomou
  - Fotos de início e retorno (80px height)

**Card de Orçamento (se `is_orcamento = true`):**
- Fundo roxo claro (#eef2ff)
- Borda roxa (#6366f1)
- Mensagens condicionais:
  - Se não for hoje: "só pode ser incluído na data agendada"
  - Se for pré-cliente: "converter pré-cliente em cliente"
  - Caso contrário: "inclua primeiro o atendimento operacional"

#### 3.2 Ações por Status

| Status Atual | Botões Disponíveis | Validações |
|--------------|-------------------|------------|
| **Aberto** | Iniciar Atendimento | 1 foto obrigatória |
| **Em Atendimento (não iniciado)** | Iniciar Controle de Tempo | 1 foto obrigatória |
| **Em Atendimento (execução)** | Pausar, Finalizar | Pausar: tipo + foto; Finalizar: 1 foto + obs + assinatura |
| **Em Atendimento (pausado)** | Retomar | 1 foto (exceto se pausa = material) |
| **Finalização** | Nenhum (somente visualização) | - |
| **Concluído** | Nenhum (somente visualização) | - |

#### 3.3 Modais

**Modal Iniciar:**
```html
<form action="iniciar" method="POST" enctype="multipart/form-data">
  - 1 foto (input file, accept="image/*", capture="environment")
  - Preview da foto
  - Botão Iniciar (verde)
  - Botão Cancelar (cinza)
</form>
```

**Modal Pausar:**
```html
<form action="pausar" method="POST" enctype="multipart/form-data">
  - Select: Tipo de Pausa (obrigatório)
    • 🍽️ Almoço
    • 🚗 Deslocamento entre Clientes
    • 🛒 Compra de Material
    • 🌙 Encerramento do Dia
  - 1 foto (obrigatória, exceto se tipo = material)
  - Preview da foto
  - Botão Pausar (laranja)
  - Botão Cancelar (cinza)
</form>
```

**Modal Retomar:**
```html
<form action="retomar" method="POST" enctype="multipart/form-data">
  - 1 foto (obrigatória, exceto se pausa = material)
  - Preview da foto
  - Botão Retomar (azul)
  - Botão Cancelar (cinza)
</form>
```

**Modal Finalizar:**
```html
<form action="finalizar" method="POST" enctype="multipart/form-data">
  - Textarea: Observações Finais (obrigatório, min 5, máx 1000)
  - Input: Nome do Assinante (obrigatório, min 2, máx 120)
  - Input: Cargo do Assinante (obrigatório, min 2, máx 120)
  - Canvas: Assinatura Digital (460x160px, touch support)
    • Botão "Limpar assinatura"
    • Validação: não enviar sem assinar
  - Input file: 1 foto final (obrigatória)
  - Preview da foto
  - Botão Finalizar (roxo)
  - Botão Cancelar (cinza)
</form>
```

**Assinatura Digital (JavaScript):**
- Canvas HTML5 com suporte a touch
- Eventos: mousedown/touchstart, mousemove/touchmove, mouseup/touchend
- lineWidth: 2px, lineCap: round, strokeStyle: #111827
- Prevent default em touch events
- Exporta para base64 (PNG) no submit
- Validação: formato `data:image/(png|jpeg);base64,`

#### 3.4 Funcionalidade "Incluir Atendimento"

**Condições:**
- `is_orcamento = true`
- Data agendada = hoje
- Não é pré-cliente (`orcamento.cliente_id IS NOT NULL`)
- Não existe atendimento origem vinculado

**Ação:**
- Atualiza `cliente_id` do atendimento
- Define `status_atual = 'em_atendimento'`
- Define `is_orcamento = false`
- Cria histórico de status
- Atualiza `orcamento.atendimento_id`

---

### 4. Agenda Técnica (`/portal-funcionario/agenda`)

**Rota:** `portal-funcionario.agenda`  
**View:** `portal-funcionario/agenda.blade.php`

**Objetivo:** Visualização calendarizada dos atendimentos do funcionário.

#### 4.1 Layout

**Toolbar Superior:**
- Tabs de Visualização: Mês | Semana | Dia
- Navegação: ◀ [Mês/Ano] ▶
- KPIs:
  - Período (total no período)
  - Dia (total no dia selecionado)
  - Abertos/Execução (no período)

**Grid Principal:**
- Calendário (7 colunas)
- Lista lateral do dia selecionado (scrollável, máx 620px)

#### 4.2 Visão Mensal

**Cabeçalho:** Dom, Seg, Ter, Qua, Qui, Sex, Sáb (fundo preto, texto branco)

**Células:**
- Dias do mês anterior: opacity 55%, fundo cinza claro
- Dia atual: borda teal (#3f9cae), badge circular teal
- Dia selecionado: fundo cyan-50, borda teal
- Hover: fundo cinza claro

**Chips de Eventos (máx 2 visíveis):**
```
[HH #NUM • Cliente]
```
- Cores por prioridade:
  - Alta: bg-red-100 text-red-800
  - Média: bg-amber-100 text-amber-800
  - Baixa: bg-teal-100 text-teal-800

**Extra:** "+N" se mais de 2 eventos

#### 4.3 Visão Semanal

Similar à mensal, mas mostra apenas 7 dias da semana atual.

#### 4.4 Visão Diária

Oculta calendário, mostra apenas título do dia.

#### 4.5 Lista do Dia

**Cabeçalho:** "Atendimentos de DD/MM/YYYY" + "N item(ns)"

**Cards:**
```
┌─────────────────────────────────┐
│ #NUM              HH às HH:MM  │
│ NOME DO CLIENTE               │
│ [orcamento] [media] [aberto]  │
│ Empresa: —                    │
│ Demanda: Assunto              │
│ Descrição: ...                │
│ Endereço: Rua, Nº, Bairro     │
│ Contato: (XX) XXXXX-XXXX      │
└─────────────────────────────────┘
```

**Ordenação:** Por horário (inicio)

**Clique no card:** Redireciona para `atendimento.show`

#### 4.6 Dados por Evento

```php
[
  'id' => int,
  'numero_atendimento' => string,
  'cliente_nome' => string,
  'empresa_nome' => string,
  'assunto_nome' => string,
  'descricao' => string,
  'tipo_demanda' => 'orcamento' | 'atendimento',
  'prioridade' => 'alta' | 'media' | 'baixa',
  'status' => string,
  'telefone_solicitante' => string,
  'data_atendimento' => 'Y-m-d',
  'inicio' => 'H:i',
  'fim' => 'H:i',
  'duracao_minutos' => int,
  'endereco' => 'Rua, Nº, Bairro, Cidade-UF',
  'cep' => string,
  'complemento' => string,
  'backgroundColor' => hex,
  'url' => route('portal-funcionario.atendimento.show', id)
]
```

---

### 5. Registro de Ponto (`/portal-funcionario/ponto`)

**Rota:** `portal-funcionario.ponto`  
**View:** `portal-funcionario/ponto.blade.php`

**Objetivo:** Controle de jornada com geolocalização e fotos.

#### 5.1 Card de Registro do Dia

**Cabeçalho:**
- Título: "Registro de Hoje (DD/MM/YYYY)"
- Próximo evento: destaque em teal

**Marcadores de Horário (Grid 4 colunas):**
- Entrada
- Saída almoço
- Retorno almoço
- Saída

**Formulário de Registro:**
```html
<form id="form-ponto-unico" method="POST" action="ponto.store" enctype="multipart/form-data">
  <input type="hidden" name="tipo" value="proximo_evento">
  <input type="hidden" name="latitude" id="ponto-latitude">
  <input type="hidden" name="longitude" id="ponto-longitude">
  <input type="hidden" name="fora_atendimento" value="0">
  <input type="hidden" name="fora_atendimento_confirmado" value="0">
  <input type="hidden" name="distancia_atendimento_metros">
  <input type="hidden" name="justificativa_fora_atendimento">
  
  <!-- Foto (se entrada ou saída) -->
  <div id="bloco-foto">
    <label>Foto obrigatória</label>
    <input type="file" name="foto" accept="image/*" capture="user">
  </div>
  
  <button type="submit">Registrar [Evento]</button>
</form>
```

**Validações:**
- Sequência obrigatória: entrada → saida_almoco → retorno_almoco → saida
- Bloqueio de 15 minutos entre registros
- Foto obrigatória para entrada e saída
- GPS obrigatório para todos
- Confirmação se estiver fora do atendimento

**Guia de Permissões:**
- Card amarelo com instruções para liberar GPS e câmera no Chrome
- Passos: ícone do site → Permissões → Permitir Localização e Câmera
- Nota sobre HTTPS/localhost

#### 5.2 Card de Histórico

**Tabela (últimos 10 dias):**

| Dia | Entrada | Saída almoço | Retorno | Saída | Total | Status |
|-----|---------|--------------|---------|-------|-------|--------|
| 07/03 | 08:00 | 12:00 | 13:00 | 17:00 | 08:00 | Normal |

**Cores de Fundo:**
- Domingos: Vermelho claro (#fee2e2)
- Feriados: Amarelo claro (#fef3c7)

**Status:**
- Normal: texto cinza
- Extra: texto âmbar, fundo âmbar claro
- Extra feriado: texto vermelho
- Falta: texto vermelho, fundo vermelho claro
- Incompleto: texto cinza

**Badge "Fora do atendimento":**
- Exibido se `registrado_fora_atendimento = true`
- Tipo: danger, tamanho xs

**Banco de Horas:**
- Cálculo por mês
- Meta semanal: 44 horas (158400 segundos)
- Saldo: +HH:MM ou -HH:MM
- Cor: verde (positivo) ou vermelho (negativo)

#### 5.3 JavaScript de Geolocalização

**Fluxo:**
1. Verifica suporte a geolocation
2. Verifica contexto seguro (HTTPS/localhost)
3. Verifica permissão via `navigator.permissions.query()`
4. Obtém posição com alta precisão (timeout 12s)
5. Fallback para precisão normal (timeout 20s)
6. Preenche inputs hidden
7. Submete formulário

**Tratamento de Erros:**
```javascript
mensagemErroGeolocalizacao(erro) {
  - Sem contexto seguro: "Use HTTPS ou localhost"
  - Permissão negada (code 1): "Libere o acesso ao GPS"
  - GPS desativado (code 2): "Ative o GPS"
  - Timeout (code 3): "Tente em local com melhor sinal"
  - Default: "Verifique GPS e permissões"
}
```

#### 5.4 Cálculo de Distância

**Fórmula:** Haversine (implementação no backend)

**Validação:**
- Se distância > limite (configurável)
- Marca `registrado_fora_atendimento = true`
- Exige confirmação explícita
- Permite justificativa

---

### 6. Documentos (`/portal-funcionario/documentos`)

**Rota:** `portal-funcionario.documentos`  
**View:** `portal-funcionario/documentos.blade.php`

**Status:** ⚠️ **INDISPONÍVEL / PLACEHOLDER**

**Conteúdo Atual:**
- Mensagem: "Em desenvolvimento"
- Botão "Voltar ao início"
- Sem funcionalidade implementada

---

## 🔗 API REST

### Endpoints de Atendimento

#### `GET /api/v1/atendimentos`
**Descrição:** Lista atendimentos do funcionário logado

**Query Params:**
- `status` - Filtra por status
- `data` - Filtra por data (YYYY-MM-DD)
- `periodo` - "hoje", "semana", "todos"

**Response:**
```json
{
  "data": [...],
  "links": {...},
  "meta": {
    "current_page": 1,
    "total": 100
  }
}
```

#### `GET /api/v1/atendimentos/{id}`
**Descrição:** Detalhes de um atendimento

**Response:**
```json
{
  "id": 1,
  "numero_atendimento": "AT-2026-0001",
  "cliente": {...},
  "assunto": {...},
  "andamentos": [...],
  "pausas": [...]
}
```

#### `POST /api/v1/atendimentos/{id}/iniciar`
**Descrição:** Iniciar atendimento

**Response:**
```json
{
  "message": "Atendimento iniciado",
  "data": {...}
}
```

#### `POST /api/v1/atendimentos/{id}/pausar`
**Descrição:** Pausar atendimento

**Body:**
```json
{
  "tipo_pausa": "almoco"
}
```

#### `POST /api/v1/atendimentos/{id}/retomar`
**Descrição:** Retomar atendimento

#### `POST /api/v1/atendimentos/{id}/finalizar`
**Descrição:** Finalizar atendimento

**Body:**
```json
{
  "observacoes": "Texto das observações"
}
```

---

### Endpoints de Ponto

#### `GET /api/v1/ponto`
**Descrição:** Lista registros de ponto (paginado, últimos 30 dias)

**Query Params:** Nenhum (usa paginação padrão)

**Response:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "funcionario_id": 5,
      "data_referencia": "2026-03-07",
      "entrada_em": "2026-03-07T08:00:00.000000Z",
      "intervalo_inicio_em": "2026-03-07T12:00:00.000000Z",
      "intervalo_fim_em": "2026-03-07T13:00:00.000000Z",
      "saida_em": "2026-03-07T17:00:00.000000Z",
      "entrada_foto_path": "ponto/5/2026/03/abc123.jpg",
      "saida_foto_path": "ponto/5/2026/03/def456.jpg",
      "entrada_latitude": -23.5505200,
      "entrada_longitude": -46.6333080,
      "intervalo_inicio_latitude": -23.5510000,
      "intervalo_inicio_longitude": -46.6340000,
      "intervalo_fim_latitude": -23.5510000,
      "intervalo_fim_longitude": -46.6340000,
      "saida_latitude": -23.5505200,
      "saida_longitude": -46.6333080,
      "registrado_fora_atendimento": false,
      "distancia_atendimento_metros": null,
      "justificativa_fora_atendimento": null,
      "registrado_por_user_id": 10,
      "observacao": null,
      "created_at": "2026-03-07T08:00:00.000000Z",
      "updated_at": "2026-03-07T17:00:00.000000Z"
    }
  ],
  "first_page_url": "...",
  "from": 1,
  "last_page": 1,
  "last_page_url": "...",
  "links": [...],
  "next_page_url": null,
  "path": "...",
  "per_page": 30,
  "prev_page_url": null,
  "to": 1,
  "total": 1
}
```

**⚠️ IMPORTANTE - Dados que NÃO vem na API (app deve calcular):**

1. **`total_formatado`** (HH:MM) - App deve calcular:
```javascript
function calcularTotalHoras(registro) {
  if (!registro.entrada_em || !registro.saida_em) return "00:00";
  const entrada = new Date(registro.entrada_em);
  const saida = new Date(registro.saida_em);
  let totalSegundos = (saida - entrada) / 1000;
  if (registro.intervalo_inicio_em && registro.intervalo_fim_em) {
    const intervalo = (new Date(registro.intervalo_fim_em) - new Date(registro.intervalo_inicio_em)) / 1000;
    totalSegundos -= intervalo;
  }
  const horas = Math.floor(totalSegundos / 3600);
  const minutos = Math.floor((totalSegundos % 3600) / 60);
  return `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}`;
}
```

2. **`status`** (Normal/Extra/Falta/Incompleto) - App deve calcular:
```javascript
function calcularStatus(registro, dataReferencia) {
  const ehDomingo = new Date(dataReferencia).getDay() === 0;
  const possuiBatidas = registro.entrada_em || registro.intervalo_inicio_em || 
                        registro.intervalo_fim_em || registro.saida_em;
  if (!possuiBatidas) return ehDomingo ? "" : "Falta";
  if (!registro.entrada_em || !registro.saida_em) return "Incompleto";
  const totalSegundos = calcularTotalSegundos(registro);
  return totalSegundos > 29400 ? "Extra" : (ehDomingo ? "Extra" : "Normal");
}
```

3. **`eh_feriado` / `feriado_nome`** - App precisa de lista de feriados ou endpoint adicional

4. **`banco_horas_mes`** - Cálculo complexo necessário (soma semanal com meta de 44h)

#### `GET /api/v1/ponto/hoje`
**Descrição:** Registro do dia atual

**Query Params:**
- `funcionario_id` - Opcional (usa do usuário logado)
- `data` - Opcional (YYYY-MM-DD)

**Response:**
```json
{
  "id": 1,
  "entrada_em": "2026-03-07T08:00:00.000000Z",
  "intervalo_inicio_em": "2026-03-07T12:00:00.000000Z",
  "intervalo_fim_em": "2026-03-07T13:00:00.000000Z",
  "saida_em": null,
  "tempo_trabalhado_segundos": 14400,
  "tempo_trabalhado_formatado": "04:00",
  "proximo_evento": "saida",
  "proximo_evento_label": "Saida"
}
```

#### `POST /api/v1/ponto/registrar`
**Descrição:** Registrar evento de ponto

**Body:**
```json
{
  "tipo": "entrada",
  "foto": "base64...",
  "latitude": -23.5505,
  "longitude": -46.6333
}
```

**Validações:**
- `tipo`: required, in:entrada,intervalo_inicio,intervalo_fim,saida
- `foto`: nullable, image, mimes:jpeg,png, max:2048
- `latitude`: nullable, numeric
- `longitude`: nullable, numeric

---

### Endpoint de Dashboard

#### `GET /api/v1/dashboard-tecnico`
**Descrição:** KPIs e dados do dashboard técnico

**Response:**
```json
{
  "data": {
    "periodo_selecionado": "todos",
    "periodo_label": "Todos",
    "kpis": {
      "total": 50,
      "concluidos": 30,
      "pendentes": 15,
      "cancelados": 5,
      "em_andamento": 2,
      "horas_trabalhadas_hoje": 3.0,
      "horas_trabalhadas_semana": 45.0
    },
    "atendimento_em_andamento": {...},
    "proximos_atendimentos": []
  }
}
```

---

## 📊 Campos dos Modelos

### Atendimento
```php
[
  'numero_atendimento' => string,
  'cliente_id' => int (FK),
  'equipamento_id' => int (FK, nullable),
  'nome_solicitante' => string,
  'telefone_solicitante' => string,
  'email_solicitante' => string,
  'assunto_id' => int (FK),
  'descricao' => text,
  'prioridade' => enum('alta', 'media', 'baixa'),
  'empresa_id' => int (FK),
  'funcionario_id' => int (FK),
  'status_atual' => enum('aberto', 'em_atendimento', 'finalizacao', 'concluido', 'cancelado'),
  'is_orcamento' => boolean,
  'atendimento_origem_id' => int (FK, self-reference),
  'data_atendimento' => datetime,
  'periodo_agendamento' => string (nullable),
  'data_inicio_agendamento' => datetime (nullable),
  'data_fim_agendamento' => datetime (nullable),
  'duracao_agendamento_minutos' => int (nullable),
  'iniciado_em' => datetime (nullable),
  'iniciado_por_user_id' => int (FK, nullable),
  'finalizado_em' => datetime (nullable),
  'finalizado_por_user_id' => int (FK, nullable),
  'tempo_execucao_segundos' => int (default 0),
  'tempo_pausa_segundos' => int (default 0),
  'em_execucao' => boolean (default false),
  'em_pausa' => boolean (default false),
  'assinatura_cliente_nome' => string (nullable),
  'assinatura_cliente_cargo' => string (nullable),
  'assinatura_cliente_path' => string (nullable)
]
```

### AtendimentoPausa
```php
[
  'atendimento_id' => int (FK),
  'user_id' => int (FK),
  'tipo_pausa' => enum('almoco', 'deslocamento', 'material', 'fim_dia'),
  'iniciada_em' => datetime,
  'encerrada_em' => datetime (nullable),
  'retomado_por_user_id' => int (FK, nullable),
  'tempo_segundos' => int (default 0),
  'foto_inicio_path' => string (nullable),
  'foto_retorno_path' => string (nullable)
]
```

### AtendimentoAndamento
```php
[
  'atendimento_id' => int (FK),
  'user_id' => int (FK),
  'descricao' => text
]
```

### AtendimentoAndamentoFoto
```php
[
  'atendimento_andamento_id' => int (FK),
  'arquivo' => string
]
```

### AtendimentoStatusHistorico
```php
[
  'atendimento_id' => int (FK),
  'status' => string,
  'observacao' => text (nullable),
  'user_id' => int (FK)
]
```

### RegistroPontoPortal
```php
[
  'funcionario_id' => int (FK),
  'data_referencia' => date,
  'entrada_em' => datetime (nullable),
  'intervalo_inicio_em' => datetime (nullable),
  'intervalo_fim_em' => datetime (nullable),
  'saida_em' => datetime (nullable),
  'entrada_foto_path' => string (nullable),
  'saida_foto_path' => string (nullable),
  'entrada_latitude' => float (nullable),
  'entrada_longitude' => float (nullable),
  'intervalo_inicio_latitude' => float (nullable),
  'intervalo_inicio_longitude' => float (nullable),
  'intervalo_fim_latitude' => float (nullable),
  'intervalo_fim_longitude' => float (nullable),
  'saida_latitude' => float (nullable),
  'saida_longitude' => float (nullable),
  'registrado_fora_atendimento' => boolean (default false),
  'distancia_atendimento_metros' => int (nullable),
  'justificativa_fora_atendimento' => text (nullable),
  'registrado_por_user_id' => int (FK),
  'observacao' => text (nullable)
]
```

---

## 🎨 UI/UX

### Design System

**Cores:**
```css
--primary: #3f9cae (teal)
--primary-dark: #327d8c
--success: #059669 (green)
--warning: #f59e0b (amber)
--danger: #ef4444 (red)
--info: #3b82f6 (blue)
--purple: #7c3aed
```

**Tipografia:**
- Font-family: sans-serif (configurada em CSS)
- Títulos: font-semibold, font-bold
- Texto: antialiased

**Componentes:**
- Cards: bg-white, border, rounded-lg, shadow
- Botões: px-4 py-2, rounded-full, font-semibold
- Badges: px-2 py-0.5, rounded-full, text-xs
- Inputs: border-2, rounded-lg, focus:border-blue-500

### Layout Responsivo

**Mobile (< 640px):**
- Navegação inferior fixa
- Cards em coluna única
- Modais em tela cheia (bottom sheet)
- Fontes menores (14-16px)
- Botões grandes (min 48px height)

**Desktop (≥ 1024px):**
- Navegação superior
- Grid 3 colunas para cards
- Modais centralizados
- Conteúdo centralizado (max-width 1200px)

### Safe Area (iOS)
```css
.safe-area-bottom {
  padding-bottom: env(safe-area-inset-bottom);
}
```

### PWA

**Meta Tags:**
```html
<meta name="theme-color" content="#3f9cae">
<meta name="mobile-web-app-capable" content="yes">
<meta name="application-name" content="Portal Funcionário">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Portal Funcionário">
<link rel="manifest" href="/manifest.json">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
```

**Service Worker:**
- Registro em `/sw.js`
- Cache de assets
- Offline support (não implementado completamente)

---

## 🔐 Segurança

### Middleware

**FuncionarioMiddleware:**
```php
$tiposPermitidos = ['funcionario', 'admin', 'administrativo', 'financeiro', 'comercial'];

// Verifica se usuário tem tipo permitido
// OU possui perfil financeiro/comercial
// E possui funcionario_id vinculado
```

**PrimeiroAcesso:**
- Redireciona para troca de senha

**Auth:**
- Laravel Breeze
- Session-based

### Validações

**CSRF:**
- Token em todos os forms
- Middleware `VerifyCsrfToken`

**Upload de Arquivos:**
- Validação de tipo MIME
- Tamanho máximo (5MB para atendimentos, 4MB para ponto)
- Nomes únicos com timestamp

**Campos Numéricos:**
- Limites máximos definidos
- Validação de tipo

**Geolocalização:**
- Validação de range: latitude -90 a 90, longitude -180 a 180
- Verificação de contexto seguro (HTTPS)

---

## 📡 Recursos em Tempo Real

### Cronômetros

**Implementação:**
```javascript
function atualizarCronometro() {
  const agora = Math.floor(Date.now() / 1000);
  const segundosDecorridos = agora - iniciadoTimestamp;
  const totalSegundos = tempoBase + segundosDecorridos;
  
  const horas = Math.floor(totalSegundos / 3600);
  const minutos = Math.floor((totalSegundos % 3600) / 60);
  const segundos = totalSegundos % 60;
  
  display.textContent = 
    String(horas).padStart(2, '0') + ':' +
    String(minutos).padStart(2, '0') + ':' +
    String(segundos).padStart(2, '0');
}

setInterval(atualizarCronometro, 1000);
```

**Cálculo de Tempo Base:**
- Se em execução: `tempo_execucao_segundos + (agora - iniciado_em)`
- Se pausado: `tempo_execucao_segundos`

### Geolocalização

**Implementação:**
```javascript
async function obterPosicaoAtual() {
  try {
    return await navigator.geolocation.getCurrentPosition(
      resolve, reject,
      { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
    );
  } catch {
    return await navigator.geolocation.getCurrentPosition(
      resolve, reject,
      { enableHighAccuracy: false, timeout: 20000, maximumAge: 60000 }
    );
  }
}
```

**Cálculo de Distância (Backend):**
- Fórmula de Haversine
- Retorna metros

---

## 🗄️ Armazenamento

### Estrutura de Diretórios
```
storage/app/public/
├── atendimentos/
│   ├── fotos/
│   ├── pausas/
│   ├── assinaturas/
│   └── andamentos/
└── ponto/
    └── {funcionario_id}/
        └── {ano}/
            └── {mes}/
```

### URLs Públicas
```php
// Acesso via symlink: public/storage → storage/app/public
$url = asset('storage/' . $caminhoRelativo);

// Normalização de caminho
$caminho = str_replace(['public/', 'storage/'], '', $caminho);
```

---

## 📝 Regras de Negócio

### Tempo de Execução

**Cálculo:**
```php
// Ao pausar
$tempoDecorrido = agora->timestamp - inicioContagem->timestamp;
$novoTempoExecucao = tempoExecucaoAtual + tempoDecorrido;

// Ao retomar
$tempoPausa = encerrada_em->diffInSeconds(iniciada_em);
$novoTempoPausa = tempoPausaAtual + tempoPausa;

// Ao finalizar
if (em_execucao) {
  $tempoDecorrido = agora->timestamp - inicioContagem->timestamp;
  $tempoExecucao += $tempoDecorrido;
}
```

**Formatação:**
```php
function formatarTempo($segundos) {
  $horas = floor($segundos / 3600);
  $minutos = floor(($segundos % 3600) / 60);
  $segs = $segundos % 60;
  return sprintf('%02d:%02d:%02d', $horas, $minutos, $segs);
}
```

### Pausas

**Tipos:**
1. **almoco** - Registra início de intervalo no ponto
2. **deslocamento** - Entre clientes
3. **material** - Compra de material (sem foto)
4. **fim_dia** - Encerramento do dia

**Fluxo:**
```
Iniciar → Execução → [Pausar] → Pausa → [Retomar] → Execução → Finalizar
```

### Ponto

**Sequência Obrigatória:**
```
entrada → saida_almoco → retorno_almoco → saida
```

**Bloqueio de Tempo:**
- 15 minutos entre registros consecutivos
- Calculado: `15 * 60 - segundosDecorridos`

**Banco de Horas:**
```php
$metaSemanal = 158400; // 44 horas em segundos

// Por semana (domingo a sábado)
$saldoSemana = $segundosTrabalhados - $metaSemanal;

// Por mês (soma das semanas)
$saldoMes = array_sum($saldosSemana);
```

**Status do Dia:**
- **Normal:** 7h a 8h trabalhadas (25200 a 28800 segundos)
- **Extra:** > 8h (> 28800 segundos)
- **Falta:** Sem registros
- **Incompleto:** Registros incompletos
- **Extra feriado:** Trabalhou em feriado
- **Extra (domingo):** Trabalhou no domingo

### Atendimento de Orçamento

**Fluxo:**
```
Orçamento → Agendamento → [Data Chegada] → Incluir Atendimento → Atendimento Operacional
```

**Validações:**
- Só permite incluir na data agendada
- Bloqueado se `orcamento.pre_cliente_id` existir
- Gera atendimento filho vinculado (`atendimento_origem_id`)

---

## 🚧 Funcionalidades Pendentes

| Funcionalidade | Status | Observações |
|---------------|--------|-------------|
| Documentos | ❌ Indisponível | Placeholder |
| Mapa Leaflet | ⚠️ Parcial | Carregamento dinâmico, mas não exibido por padrão |
| Dashboard Técnico | ⚠️ Básico | API implementada, sem frontend dedicado |
| Notificações Push | ❌ Não implementado | Service Worker registrado, sem lógica |
| Offline Mode | ❌ Não implementado | PWA configurado, sem cache estratégico |
| Chat com Cliente | ❌ Não implementado | - |
| Checklist de Tarefas | ❌ Não implementado | - |

---

## 🔧 Melhorias Sugeridas para a API

### API de Ponto - Campos Calculados

**Problema:** O endpoint `GET /api/v1/ponto` retorna dados brutos, exigindo que o app móvel recalcule:
- Total de horas trabalhadas
- Status do dia (Normal/Extra/Falta/Incompleto)
- Banco de horas do mês
- Feriados

**Solução 1 - Adicionar campos calculados via Resource:**
```
GET /api/v1/ponto?incluir_calculos=true
```

**Solução 2 - Endpoints adicionais:**
```
GET /api/v1/ponto/historico?mes=YYYY-MM  → Histórico com cálculos inclusos
GET /api/v1/ponto/banco-horas?mes=YYYY-MM → Saldo do mês
GET /api/v1/feriados?ano=YYYY → Lista de feriados nacionais
```

### Exemplo de Resposta Ideal:
```json
{
  "data": [
    {
      ...campos_brutos,
      "total_formatado": "08:00",
      "total_segundos": 28800,
      "status": "Normal",
      "eh_domingo": false,
      "eh_feriado": false,
      "feriado_nome": null
    }
  ],
  "banco_horas_mes": {
    "segundos": 9000,
    "formatado": "+02:30",
    "positivo": true
  }
}
```

---

## 🔧 Rotas Completas

### Web Routes (portal-funcionario.*)

```php
// Middleware: auth, funcionario, primeiro_acesso
GET  /portal-funcionario/                    → index
GET  /portal-funcionario/chamados            → chamados
GET  /portal-funcionario/atendimento/{id}    → atendimento.show
POST /portal-funcionario/atendimento/{id}/iniciar   → atendimento.iniciar
POST /portal-funcionario/atendimento/{id}/pausar    → atendimento.pausar
POST /portal-funcionario/atendimento/{id}/retomar   → atendimento.retomar
POST /portal-funcionario/atendimento/{id}/finalizar → atendimento.finalizar
POST /portal-funcionario/atendimento/{id}/incluir   → atendimento.incluir
GET  /portal-funcionario/agenda              → agenda
GET  /portal-funcionario/ponto                → ponto
POST /portal-funcionario/ponto                → ponto.store
GET  /portal-funcionario/documentos           → documentos
POST /portal-funcionario/andamentos/{id}/fotos → andamentos.fotos.store
DELETE /portal-funcionario/andamentos/fotos/{id} → andamentos.fotos.destroy
```

### API Routes (api/v1/*)

```php
GET    /api/v1/atendimentos              → AtendimentoController@index
GET    /api/v1/atendimentos/{id}         → AtendimentoController@show
POST   /api/v1/atendimentos/{id}/iniciar → AtendimentoController@iniciar
POST   /api/v1/atendimentos/{id}/pausar  → AtendimentoController@pausar
POST   /api/v1/atendimentos/{id}/retomar → AtendimentoController@retomar
POST   /api/v1/atendimentos/{id}/finalizar → AtendimentoController@finalizar

GET    /api/v1/ponto              → PontoController@index
GET    /api/v1/ponto/hoje         → PontoController@hoje
POST   /api/v1/ponto/registrar    → PontoController@registrar

GET    /api/v1/dashboard-tecnico  → DashboardTecnicoController@index
```

---

## 📦 Dependências Externas

### Frontend (CDN)
- **Leaflet.js** (1.9.4) - Mapas
- **Leaflet CSS** - Estilos do mapa

### APIs Externas
- **Nominatim (OpenStreetMap)** - Geocodificação de endereços
- **OSRM** - Cálculo de rotas
- **Google Maps** - Links de navegação
- **Waze** - Links de navegação

---

## 🧪 Testes

### Factories Disponíveis
- `AtendimentoFactory`
- `AtendimentoPausaFactory`
- `RegistroPontoPortalFactory`

### Cenários Testáveis
- ✅ Cálculo de tempo de execução
- ✅ Cálculo de tempo de pausa
- ✅ Sequência de ponto
- ✅ Bloqueio de 15 minutos
- ✅ Validação de fotos
- ✅ Assinatura digital
- ✅ Geolocalização
- ✅ Integração com ponto ao iniciar/finalizar atendimento

---

## 📞 Contato e Suporte

- **Repositório:** https://github.com/FelipeGat/Gestor.Alfa
- **Issues:** https://github.com/FelipeGat/Gestor.Alfa/issues

---

## ⚠️ Licença

**Copyright (c) 2024 Felipe Henrique Gat - Todos os Direitos Reservados**

---

**Fim da Documentação**

Esta análise cobre 100% das funcionalidades atualmente implementadas no Portal do Técnico. O sistema é robusto, mobile-first, e possui integrações completas de geolocalização, câmera e controle de tempo.
