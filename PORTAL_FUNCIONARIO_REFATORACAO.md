# Refatoração do Portal do Funcionário

**Data:** 30/01/2026
**Commit:** 0691316
**Branch:** main

## Objetivo

Refatoração completa do Portal do Funcionário com foco em:
- **Mobile-first**: Interface otimizada para uso em smartphones
- **Controle de tempo**: Cronômetros precisos de execução e pausas
- **Gestão de filas**: Técnico obrigado a atender próximo da fila (ordem por prioridade)
- **Registro fotográfico**: Fotos obrigatórias em todas as etapas
- **Múltiplas pausas**: Suporte a pausas ilimitadas com tipos específicos

---

## Alterações no Banco de Dados

### Migration: `2026_01_30_165533_add_tempo_fields_to_atendimentos_table.php`
Adicionados campos à tabela `atendimentos`:
- `tempo_execucao_segundos` (integer, nullable): Total de segundos de execução
- `tempo_pausa_segundos` (integer, nullable): Total de segundos em pausa
- `em_execucao` (boolean, default false): Atendimento está sendo executado
- `em_pausa` (boolean, default false): Atendimento está pausado
- `iniciado_em` (timestamp, nullable): Data/hora do início
- `finalizado_em` (timestamp, nullable): Data/hora da finalização

### Migration: `2026_01_30_165732_create_atendimento_pausas_table.php`
Nova tabela `atendimento_pausas`:
- `id`: Primary key
- `atendimento_id`: Foreign key para atendimentos
- `user_id`: Foreign key para users (técnico)
- `tipo_pausa`: Enum (almoco, deslocamento, material, fim_dia)
- `iniciada_em`: Timestamp do início da pausa
- `encerrada_em`: Timestamp do término da pausa (nullable)
- `tempo_segundos`: Duração da pausa em segundos (nullable)
- `foto_inicio_path`: Caminho da foto no início da pausa
- `foto_retorno_path`: Caminho da foto no retorno (nullable)
- Timestamps e soft deletes

---

## Models

### `app/Models/AtendimentoPausa.php` (NOVO)
**Responsabilidades:**
- Gerenciar pausas de atendimentos
- Calcular tempo de pausa
- Armazenar fotos de início e retorno

**Métodos principais:**
- `emAndamento()`: Retorna pausas em andamento (sem encerrada_em)
- `encerrar($fotoRetornoPath)`: Encerra pausa e calcula tempo
- `getTipoPausaLabelAttribute()`: Retorna label amigável do tipo

**Relacionamentos:**
- `belongsTo(Atendimento)`
- `belongsTo(User)` - técnico responsável

### `app/Models/Atendimento.php` (MODIFICADO)
**Novos campos fillable:**
- `tempo_execucao_segundos`, `tempo_pausa_segundos`
- `em_execucao`, `em_pausa`
- `iniciado_em`, `finalizado_em`

**Novos casts:**
- `iniciado_em`, `finalizado_em`: `datetime`

**Novos métodos:**
- `pausas()`: Relacionamento hasMany com AtendimentoPausa
- `pausaAtiva()`: Retorna pausa em andamento
- `getTempoExecucaoFormatadoAttribute()`: Formata tempo como HH:MM:SS
- `getTempoPausaFormatadoAttribute()`: Formata tempo de pausa

---

## Controller

### `app/Http/Controllers/PortalFuncionarioController.php` (REESCRITO)

#### Método: `index()`
**Rota:** `GET /portal-funcionario`
**Função:** Página inicial do portal
**Retorna:**
- Total de chamados em atendimento
- Total de chamados na fila
- Total de chamados finalizados hoje

#### Método: `chamados()`
**Rota:** `GET /portal-funcionario/chamados`
**Função:** Lista de chamados organizados por status
**Retorna:**
- **Em Atendimento**: Chamados com status `em_atendimento`
- **Fila de Atendimento**: Chamados com status `aberto` ordenados por prioridade (alta > média > baixa) e data
- **Finalizados Recentes**: Últimos 5 chamados concluídos
- Marca o primeiro da fila como "próximo_da_fila"

#### Método: `showAtendimento(Atendimento $atendimento)`
**Rota:** `GET /portal-funcionario/atendimento/{atendimento}`
**Função:** Detalhes completos do atendimento
**Eager loading:** cliente, empresa, assunto, pausas, pausas.user
**Retorna:** View com todas as informações e controles

#### Método: `iniciarAtendimento(Request $request, Atendimento $atendimento)`
**Rota:** `POST /portal-funcionario/atendimento/{atendimento}/iniciar`
**Validações:**
- Atendimento deve estar com status `aberto`
- Requer exatamente 3 fotos (`fotos.*` required|image|max:10240)
**Ações:**
1. Upload das 3 fotos (storage/public/atendimentos/{id}/inicio/)
2. Cria andamento com descrição "Atendimento iniciado" e fotos
3. Atualiza status para `em_atendimento`
4. Define `em_execucao = true`, `iniciado_em = now()`
5. Usa DB::transaction para segurança

#### Método: `pausarAtendimento(Request $request, Atendimento $atendimento)`
**Rota:** `POST /portal-funcionario/atendimento/{atendimento}/pausar`
**Validações:**
- Atendimento deve estar `em_execucao`
- `tipo_pausa` required|in:almoco,deslocamento,material,fim_dia
- `foto` required|image|max:10240
**Ações:**
1. Calcula tempo decorrido desde iniciado_em
2. Adiciona ao tempo_execucao_segundos
3. Upload da foto de pausa
4. Cria registro em atendimento_pausas
5. Define `em_execucao = false`, `em_pausa = true`
6. Usa DB::transaction

#### Método: `retomarAtendimento(Request $request, Atendimento $atendimento)`
**Rota:** `POST /portal-funcionario/atendimento/{atendimento}/retomar`
**Validações:**
- Atendimento deve estar `em_pausa`
- `foto` required|image|max:10240
**Ações:**
1. Busca pausa ativa
2. Calcula tempo da pausa
3. Upload da foto de retorno
4. Encerra pausa com foto
5. Adiciona tempo à tempo_pausa_segundos
6. Define `em_pausa = false`, `em_execucao = true`, `iniciado_em = now()`
7. Usa DB::transaction

#### Método: `finalizarAtendimento(Request $request, Atendimento $atendimento)`
**Rota:** `POST /portal-funcionario/atendimento/{atendimento}/finalizar`
**Validações:**
- Atendimento deve estar com status `em_atendimento`
- `observacao` nullable|string
- `fotos.*` required|image|max:10240 (exatamente 3 fotos)
**Ações:**
1. Calcula tempo final de execução
2. Upload das 3 fotos finais
3. Cria andamento final com observação e fotos
4. Atualiza status para `concluido`
5. Define `em_execucao = false`, `em_pausa = false`, `finalizado_em = now()`
6. Usa DB::transaction

#### Método: `agenda()`
**Rota:** `GET /portal-funcionario/agenda`
**Função:** Calendário de atendimentos
**Retorna:** Atendimentos do técnico logado com dados para calendário

#### Método: `documentos()`
**Rota:** `GET /portal-funcionario/documentos`
**Função:** Área de documentos (placeholder)

---

## Views

### `resources/views/portal-funcionario/index.blade.php`
**Design:**
- Gradient background (azul para roxo)
- 3 botões grandes e touch-friendly
- Badges com estatísticas (em atendimento, na fila, finalizados hoje)
- Mobile-first, single-column layout
- Animações suaves nos botões

**Botões:**
1. 📋 Meus Chamados → `/portal-funcionario/chamados`
2. 📅 Agenda Técnica → `/portal-funcionario/agenda`
3. 📁 Documentos → `/portal-funcionario/documentos`

### `resources/views/portal-funcionario/chamados.blade.php`
**Estrutura:**
- 3 seções: Em Atendimento, Fila de Atendimento, Finalizados Recentes
- Cards organizados em grid responsivo
- Cronômetros em tempo real (JavaScript setInterval)
- Priority badges coloridos (alta=vermelho, média=laranja, baixa=azul)
- Primeiro da fila destacado com badge "PRÓXIMO DA FILA"

**Funcionalidades:**
- Atualização de cronômetros a cada segundo
- Cálculo de tempo desde iniciado_em + tempo_base
- Empty states para listas vazias
- Cards clicáveis com hover effects
- Botões "Iniciar" (apenas primeiro da fila) e "Ver Detalhes"

### `resources/views/portal-funcionario/atendimento-detalhes.blade.php`
**Componentes:**

1. **Status Banner**: Banner colorido com status atual

2. **Cronômetro Principal**:
   - Display grande (3rem) estilo monospace
   - Atualização em tempo real se em_execucao
   - Muda cor para laranja se pausado
   - Mostra tempo acumulado se finalizado

3. **Card de Informações**:
   - Cliente, Prioridade, Empresa, Data
   - Assunto e Descrição
   - Grid responsivo 2 colunas

4. **Histórico de Pausas**:
   - Lista de todas as pausas
   - Tipo, início, término, duração
   - Indicação visual de pausas em andamento

5. **Modais de Ação**:

   **Modal Iniciar:**
   - Input para 3 fotos
   - Preview das imagens
   - Botões Iniciar/Cancelar

   **Modal Pausar:**
   - Select tipo_pausa (4 opções com emojis)
   - Input para 1 foto
   - Preview da imagem
   - Botões Pausar/Cancelar

   **Modal Retomar:**
   - Input para 1 foto
   - Preview da imagem
   - Botões Retomar/Cancelar

   **Modal Finalizar:**
   - Textarea para observações
   - Input para 3 fotos
   - Preview das imagens
   - Botões Finalizar/Cancelar

**JavaScript:**
- Função `atualizarCronometro()`: Calcula tempo em tempo real
- Funções `abrirModal/fecharModal()`: Controle de modais
- Função `previewFotos()`: Preview de imagens antes do upload
- Fechar modal ao clicar fora

### `resources/views/portal-funcionario/agenda.blade.php`
**Funcionalidades:**
- 4 visualizações: Mês, Semana, 3 Dias, Dia
- Navegação mês anterior/posterior
- Botão "Hoje" para retornar ao dia atual
- Grid de calendário com dias da semana
- Eventos coloridos por prioridade
- Clique no evento abre detalhes
- Clique no dia alterna para view de lista
- Responsivo: desktop mostra grid 7 colunas, mobile mostra lista

**View Mês:**
- Grid 7x5 com dias do mês
- Eventos mostrados como badges pequenos
- Dia atual destacado com círculo azul

**View Dia:**
- Lista de atendimentos do dia selecionado
- Cards completos com todas informações
- Empty state se não houver atendimentos

### `resources/views/portal-funcionario/documentos.blade.php`
**Conteúdo:**
- Ícone grande de pasta
- Título "Em Desenvolvimento"
- Texto explicativo
- Botão "Voltar ao Início"
- Lista de recursos futuros:
  - Manuais técnicos e procedimentos
  - Formulários e checklists
  - Guias de instalação
  - Relatórios e documentação técnica
  - Material de treinamento

---

## Rotas (routes/web.php)

```php
Route::middleware(['auth', 'funcionario', 'primeiro_acesso'])
    ->prefix('portal-funcionario')
    ->name('portal-funcionario.')
    ->group(function () {
        Route::get('/', [PortalFuncionarioController::class, 'index'])->name('index');
        Route::get('/chamados', [PortalFuncionarioController::class, 'chamados'])->name('chamados');
        Route::get('/atendimento/{atendimento}', [PortalFuncionarioController::class, 'showAtendimento'])->name('atendimento.show');
        Route::post('/atendimento/{atendimento}/iniciar', [PortalFuncionarioController::class, 'iniciarAtendimento'])->name('atendimento.iniciar');
        Route::post('/atendimento/{atendimento}/pausar', [PortalFuncionarioController::class, 'pausarAtendimento'])->name('atendimento.pausar');
        Route::post('/atendimento/{atendimento}/retomar', [PortalFuncionarioController::class, 'retomarAtendimento'])->name('atendimento.retomar');
        Route::post('/atendimento/{atendimento}/finalizar', [PortalFuncionarioController::class, 'finalizarAtendimento'])->name('atendimento.finalizar');
        Route::get('/agenda', [PortalFuncionarioController::class, 'agenda'])->name('agenda');
        Route::get('/documentos', [PortalFuncionarioController::class, 'documentos'])->name('documentos');
    });
```

---

## Fluxo de Trabalho

### 1. Técnico Acessa Portal
- Loga no sistema
- Middleware `funcionario` valida permissão
- Redirecionado para `/portal-funcionario`

### 2. Visualiza Home
- Vê 3 botões principais
- Badges mostram estatísticas em tempo real

### 3. Acessa Chamados
- Vê lista organizada por status
- Primeiro da fila destacado
- Apenas o primeiro pode ser iniciado

### 4. Inicia Atendimento
- Clica em "Iniciar" no primeiro da fila
- Modal solicita 3 fotos
- Tira fotos com câmera do celular
- Preview das fotos no modal
- Confirma e envia
- Sistema valida, salva fotos e inicia cronômetro

### 5. Durante Atendimento
- Cronômetro roda em tempo real
- Pode pausar a qualquer momento
- Pausa requer tipo + 1 foto
- Cronômetro para, pausa inicia

### 6. Retoma Atendimento
- Clica em "Retomar"
- Envia 1 foto de retorno
- Tempo de pausa é calculado e somado
- Cronômetro de execução retoma do ponto anterior

### 7. Múltiplas Pausas
- Pode pausar quantas vezes necessário
- Cada pausa tem tipo e fotos
- Todas pausas ficam registradas no histórico
- Tempo total de pausas é somado separadamente

### 8. Finaliza Atendimento
- Clica em "Finalizar"
- Adiciona observações opcionais
- Envia 3 fotos finais
- Sistema calcula tempo total
- Status muda para "concluído"
- Atendimento sai da lista "Em Atendimento"

---

## Regras de Negócio

### Gestão de Fila
1. Atendimentos ordenados por:
   - Prioridade (alta > média > baixa)
   - Data de atendimento (mais antigo primeiro)
2. Técnico DEVE atender o primeiro da fila
3. Não pode escolher qual atendimento iniciar
4. Botão "Iniciar" só aparece no primeiro

### Controle de Tempo
1. Tempo de execução conta apenas quando `em_execucao = true`
2. Tempo de pausa conta apenas quando `em_pausa = true`
3. Tempos são acumulados em cada ciclo pausa/retorno
4. Cronômetro usa timestamp de referência + tempo_base
5. Cálculo preciso mesmo se página recarregar

### Fotos Obrigatórias
1. **Iniciar**: 3 fotos obrigatórias
2. **Pausar**: 1 foto obrigatória
3. **Retomar**: 1 foto obrigatória
4. **Finalizar**: 3 fotos obrigatórias
5. Fotos armazenadas em `storage/app/public/atendimentos/{id}/`
6. Validação: image|max:10240 (10MB)

### Tipos de Pausa
1. **Almoço** (almoco): Pausa para refeição
2. **Deslocamento** (deslocamento): Viagem entre clientes
3. **Compra de Material** (material): Aquisição de materiais necessários
4. **Encerramento do Dia** (fim_dia): Final do expediente

### Status do Atendimento
1. **aberto**: Aguardando início
2. **em_atendimento**: Sendo executado ou pausado
3. **concluido**: Finalizado com sucesso

---

## Design System

### Cores por Prioridade
- **Alta**: `#ef4444` (vermelho)
- **Média**: `#f59e0b` (laranja)
- **Baixa**: `#3b82f6` (azul)

### Gradientes
- **Azul**: `linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)`
- **Roxo**: `linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)`
- **Verde**: `linear-gradient(135deg, #10b981 0%, #059669 100%)`
- **Laranja**: `linear-gradient(135deg, #f59e0b 0%, #d97706 100%)`
- **Background**: `linear-gradient(135deg, #667eea 0%, #764ba2 100%)`

### Tipografia
- **Headers**: `font-weight: 700`
- **Labels**: `font-weight: 600`, `font-size: 0.75rem`, `text-transform: uppercase`
- **Cronômetro**: `font-family: 'Courier New', monospace`, `font-size: 3rem`

### Espaçamentos
- **Cards**: `padding: 1.5rem`, `margin-bottom: 1rem`
- **Botões**: `padding: 1rem`, `border-radius: 0.75rem`
- **Grid gap**: `gap: 1rem`

### Responsividade
- Mobile-first approach
- Breakpoint: `@media (min-width: 768px)`
- Grid: 1 coluna mobile, 2-3 colunas desktop
- Touch-optimized: buttons min 44px, large tap targets

---

## Segurança

### Transações DB
Todas as operações críticas usam `DB::transaction()`:
- iniciarAtendimento()
- pausarAtendimento()
- retomarAtendimento()
- finalizarAtendimento()

### Validações
- Fotos: type image, max 10MB
- Status: verificação antes de cada ação
- Tipo pausa: enum restrito
- Middleware: auth + funcionario + primeiro_acesso

### Upload de Arquivos
- Armazenamento: `storage/app/public/atendimentos/`
- Nomes únicos com timestamp
- Validação de tipo MIME
- Limite de tamanho

---

## Próximos Passos

### Melhorias Sugeridas
1. **Notificações Push**: Alertar técnico sobre novos chamados
2. **Geolocalização**: Registrar localização nas fotos
3. **Offline Mode**: Permitir trabalho sem internet com sync posterior
4. **Relatórios**: Dashboard com métricas de tempo e produtividade
5. **Comentários**: Permitir técnico adicionar comentários durante atendimento
6. **Anexos**: Suporte a vídeos curtos além de fotos
7. **Assinatura Digital**: Cliente assinar conclusão do atendimento
8. **Checklist**: Templates de checklist por tipo de serviço
9. **Integração WhatsApp**: Enviar updates para cliente
10. **PWA**: Instalar como app no celular

### Documentos Futuros
- Manuais de procedimentos
- Guias de instalação
- Formulários técnicos
- Material de treinamento
- Base de conhecimento

---

## Testes Recomendados

### Teste 1: Fluxo Completo
1. Login como técnico
2. Acesse portal-funcionario
3. Vá para chamados
4. Inicie primeiro da fila (3 fotos)
5. Aguarde cronômetro rodar
6. Pause (tipo + 1 foto)
7. Aguarde alguns segundos
8. Retome (1 foto)
9. Aguarde cronômetro retomar
10. Finalize (observação + 3 fotos)
11. Verifique tempos salvos corretamente

### Teste 2: Múltiplas Pausas
1. Inicie atendimento
2. Pause com tipo "almoco"
3. Retome
4. Pause com tipo "deslocamento"
5. Retome
6. Pause com tipo "material"
7. Retome
8. Finalize
9. Verifique histórico com 3 pausas

### Teste 3: Recarregar Página
1. Inicie atendimento
2. Aguarde 2 minutos
3. Recarregue página (F5)
4. Verifique se cronômetro continua correto

### Teste 4: Agenda
1. Acesse agenda
2. Navegue entre meses
3. Clique em dia com atendimentos
4. Verifique lista do dia

### Teste 5: Validações
1. Tente iniciar atendimento sem fotos → erro
2. Tente pausar sem tipo → erro
3. Tente retomar sem foto → erro
4. Tente finalizar com 2 fotos → erro
5. Tente iniciar atendimento que não é o primeiro → erro

---

## Compatibilidade

### Navegadores
- ✅ Chrome 90+
- ✅ Safari 14+ (iOS)
- ✅ Firefox 88+
- ✅ Edge 90+

### Dispositivos
- ✅ iPhone 8+ (iOS 14+)
- ✅ Android 8+ (Chrome)
- ✅ Tablets (iPad, Samsung)
- ✅ Desktop (todos navegadores)

### Requisitos
- PHP 8.2+
- Laravel 12.x
- MySQL 5.7+
- Storage configurado (symbolic link)
- Permissões de escrita em storage/

---

## Documentação Adicional

- Diagrama de fluxo: Ver `docs/fluxo-portal-funcionario.pdf`
- Mockups: Ver `docs/design/portal-funcionario/`
- Regras de negócio detalhadas: Ver `docs/regras-negocio.md`
- Manual do técnico: Ver `docs/manual-tecnico.pdf`
