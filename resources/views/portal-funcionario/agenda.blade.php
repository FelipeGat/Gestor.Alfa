<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Agenda Técnica
            </h2>
            <a href="{{ route('portal-funcionario.index') }}" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
        </div>
    </x-slot>

    <style>
        .agenda-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }

        .agenda-toolbar {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .agenda-tabs {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .agenda-tab {
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            border-radius: 999px;
            padding: 0.5rem 0.9rem;
            font-size: 0.875rem;
            font-weight: 700;
            cursor: pointer;
        }

        .agenda-tab.active {
            background: linear-gradient(135deg, #3f9cae 0%, #327d8c 100%);
            border-color: #327d8c;
            color: #fff;
        }

        .agenda-nav-btn {
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            color: #374151;
            border-radius: 0.5rem;
            padding: 0.45rem 0.75rem;
            font-weight: 700;
            cursor: pointer;
        }

        .agenda-nav-label {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #1f2937;
            border-radius: 0.5rem;
            padding: 0.45rem 0.9rem;
            font-weight: 800;
            min-width: 150px;
            text-align: center;
            white-space: nowrap;
        }

        .agenda-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: #1f2937;
        }

        .agenda-kpi {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
            margin-top: 0.9rem;
        }

        .agenda-kpi .kpi-card {
            border-radius: 0.75rem;
            padding: 0.8rem;
            background: #fff;
            border: 1px solid #e5e7eb;
        }

        .agenda-kpi .kpi-label {
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 700;
        }

        .agenda-kpi .kpi-value {
            margin-top: 0.25rem;
            font-size: 1.2rem;
            font-weight: 800;
            color: #1f2937;
        }

        .agenda-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 1rem;
        }

        .agenda-card {
            background: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .agenda-card-header {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .agenda-calendar {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 1px;
            background: #e5e7eb;
        }

        .agenda-day-h {
            background: #1f2937;
            color: #fff;
            text-align: center;
            padding: 0.65rem;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .agenda-day {
            background: #fff;
            min-height: 110px;
            padding: 0.45rem;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .agenda-day:hover {
            background: #f9fafb;
        }

        .agenda-day.selected {
            border-color: #3f9cae;
            background: #f0fafb;
        }

        .agenda-day.today .day-number {
            display: inline-flex;
            width: 26px;
            height: 26px;
            border-radius: 999px;
            align-items: center;
            justify-content: center;
            background: #3f9cae;
            color: #fff;
            font-weight: 800;
        }

        .day-number {
            font-size: 0.8rem;
            font-weight: 800;
            color: #1f2937;
        }

        .event-chip {
            margin-top: 0.3rem;
            border-radius: 0.35rem;
            padding: 0.2rem 0.35rem;
            font-size: 0.7rem;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .event-chip.alta {
            background: #fee2e2;
            color: #991b1b;
        }

        .event-chip.media {
            background: #fed7aa;
            color: #92400e;
        }

        .event-chip.baixa {
            background: #d7edf1;
            color: #2f7c8a;
        }

        .agenda-list {
            padding: 0.8rem;
            max-height: 620px;
            overflow: auto;
        }

        .agenda-item {
            border: 1px solid #e5e7eb;
            border-left: 4px solid #3f9cae;
            border-radius: 0.65rem;
            padding: 0.75rem;
            margin-bottom: 0.7rem;
            cursor: pointer;
            background: #fff;
        }

        .agenda-item.alta {
            border-left-color: #ef4444;
        }

        .agenda-item.media {
            border-left-color: #f59e0b;
        }

        .agenda-item.baixa {
            border-left-color: #3f9cae;
        }

        .agenda-item-top {
            display: flex;
            justify-content: space-between;
            gap: 0.6rem;
            margin-bottom: 0.4rem;
        }

        .agenda-item-numero {
            font-size: 0.8rem;
            font-weight: 800;
            color: #1f2937;
        }

        .agenda-item-horario {
            font-size: 0.8rem;
            font-weight: 800;
            color: #0f766e;
        }

        .agenda-item-cliente {
            font-size: 0.92rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: 0.3rem;
        }

        .agenda-row {
            display: flex;
            gap: 0.35rem;
            font-size: 0.78rem;
            color: #4b5563;
            margin-bottom: 0.2rem;
            line-height: 1.35;
        }

        .agenda-row strong {
            color: #374151;
            min-width: 78px;
            font-weight: 700;
        }

        .badge {
            border-radius: 999px;
            padding: 0.18rem 0.55rem;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .badge-status {
            background: #e0f2fe;
            color: #075985;
        }

        .badge-tipo {
            background: #ecfeff;
            color: #155e75;
        }

        .badge-tipo.orcamento {
            background: #ede9fe;
            color: #5b21b6;
        }

        .badge-prioridade.alta {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-prioridade.media {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-prioridade.baixa {
            background: #d7edf1;
            color: #2f7c8a;
        }

        .empty-box {
            border: 1px dashed #d1d5db;
            border-radius: 0.65rem;
            padding: 1.25rem;
            text-align: center;
            color: #6b7280;
            font-size: 0.86rem;
        }

        @media (max-width: 1024px) {
            .agenda-grid {
                grid-template-columns: 1fr;
            }

            .agenda-kpi {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="agenda-wrap" x-data>
        <div class="agenda-toolbar">
            <div class="flex flex-col gap-3 items-center">
                <div class="agenda-tabs">
                    <button type="button" class="agenda-tab active" data-view="mes">Mês</button>
                    <button type="button" class="agenda-tab" data-view="semana">Semana</button>
                    <button type="button" class="agenda-tab" data-view="dia">Dia</button>
                </div>

                <div class="flex items-center justify-center gap-2">
                    <button type="button" class="agenda-nav-btn" id="btnPrev">◀</button>
                    <div class="agenda-nav-label" id="btnMesFiltro">—</div>
                    <button type="button" class="agenda-nav-btn" id="btnNext">▶</button>
                </div>
            </div>

            <div class="agenda-title mt-3" id="agendaTitulo"></div>

            <div class="agenda-kpi">
                <div class="kpi-card">
                    <div class="kpi-label">Atendimentos no período</div>
                    <div class="kpi-value" id="kpiPeriodo">0</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Atendimentos do dia</div>
                    <div class="kpi-value" id="kpiDia">0</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Em aberto / execução</div>
                    <div class="kpi-value" id="kpiAbertos">0</div>
                </div>
            </div>
        </div>

        <div class="agenda-grid">
            <div class="agenda-card" id="calendarCard">
                <div class="agenda-card-header">
                    <div class="text-sm font-bold text-gray-700">Calendário de Execução</div>
                    <div class="text-xs text-gray-500">Clique no dia para ver detalhes</div>
                </div>
                <div id="calendarGrid" class="agenda-calendar"></div>
            </div>

            <div class="agenda-card">
                <div class="agenda-card-header">
                    <div class="text-sm font-bold text-gray-700" id="painelDiaTitulo">Atendimentos do dia</div>
                    <div class="text-xs text-gray-500" id="painelDiaSubtitulo"></div>
                </div>
                <div id="listaDia" class="agenda-list"></div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const atendimentos = @json($atendimentos);
            const diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
            const meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

            let viewAtual = 'mes';
            let dataAtual = new Date();
            let diaSelecionado = dataAtual.toISOString().split('T')[0];

            const tabs = document.querySelectorAll('.agenda-tab');
            const titleEl = document.getElementById('agendaTitulo');
            const gridEl = document.getElementById('calendarGrid');
            const listaDiaEl = document.getElementById('listaDia');
            const painelDiaTituloEl = document.getElementById('painelDiaTitulo');
            const painelDiaSubtituloEl = document.getElementById('painelDiaSubtitulo');
            const calendarCard = document.getElementById('calendarCard');
            const mesFiltroEl = document.getElementById('btnMesFiltro');

            function normalizeStatus(status) {
                if (!status) return '—';
                return String(status).replaceAll('_', ' ');
            }

            function formatDateBR(date) {
                return date.toLocaleDateString('pt-BR');
            }

            function filtrarDia(dataStr) {
                return atendimentos.filter(item => item.data_atendimento === dataStr);
            }

            function periodoAtual() {
                const base = new Date(dataAtual);

                if (viewAtual === 'dia') {
                    const ini = new Date(base.getFullYear(), base.getMonth(), base.getDate(), 0, 0, 0);
                    const fim = new Date(base.getFullYear(), base.getMonth(), base.getDate(), 23, 59, 59);
                    return { ini, fim };
                }

                if (viewAtual === 'semana') {
                    const ini = new Date(base);
                    ini.setDate(base.getDate() - base.getDay());
                    ini.setHours(0, 0, 0, 0);
                    const fim = new Date(ini);
                    fim.setDate(ini.getDate() + 6);
                    fim.setHours(23, 59, 59, 999);
                    return { ini, fim };
                }

                const ini = new Date(base.getFullYear(), base.getMonth(), 1, 0, 0, 0);
                const fim = new Date(base.getFullYear(), base.getMonth() + 1, 0, 23, 59, 59);
                return { ini, fim };
            }

            function atualizarKPIs() {
                const { ini, fim } = periodoAtual();

                const noPeriodo = atendimentos.filter(item => {
                    const d = new Date(`${item.data_atendimento}T00:00:00`);
                    return d >= ini && d <= fim;
                });

                const doDia = filtrarDia(diaSelecionado);
                const abertos = noPeriodo.filter(item => ['aberto', 'em_atendimento'].includes(item.status)).length;

                document.getElementById('kpiPeriodo').textContent = noPeriodo.length;
                document.getElementById('kpiDia').textContent = doDia.length;
                document.getElementById('kpiAbertos').textContent = abertos;
            }

            function renderListaDia() {
                const data = new Date(`${diaSelecionado}T00:00:00`);
                const items = filtrarDia(diaSelecionado).sort((a, b) => (a.inicio || '00:00').localeCompare(b.inicio || '00:00'));

                painelDiaTituloEl.textContent = `Atendimentos de ${formatDateBR(data)}`;
                painelDiaSubtituloEl.textContent = `${items.length} item(ns)`;

                if (!items.length) {
                    listaDiaEl.innerHTML = '<div class="empty-box">Nenhum atendimento agendado para este dia.</div>';
                    return;
                }

                listaDiaEl.innerHTML = items.map(item => {
                    const horario = item.inicio ? `${item.inicio}${item.fim ? ` às ${item.fim}` : ''}` : 'Horário não definido';
                    const endereco = item.endereco && item.endereco.trim() !== '' ? item.endereco : 'Endereço não informado';
                    const complemento = item.complemento ? ` • ${item.complemento}` : '';
                    const cep = item.cep ? ` • CEP: ${item.cep}` : '';
                    const demanda = item.descricao && item.descricao.trim() !== ''
                        ? item.descricao
                        : 'Sem descrição detalhada da demanda';

                    return `
                        <div class="agenda-item ${item.prioridade}" onclick="window.location.href='${item.url}'">
                            <div class="agenda-item-top">
                                <div class="agenda-item-numero">#${item.numero_atendimento}</div>
                                <div class="agenda-item-horario">${horario}</div>
                            </div>

                            <div class="agenda-item-cliente">${item.cliente_nome}</div>

                            <div class="flex items-center gap-1 mb-2">
                                <span class="badge badge-tipo ${item.tipo_demanda || 'atendimento'}">${(item.tipo_demanda || 'atendimento').replace('_',' ')}</span>
                                <span class="badge badge-prioridade ${item.prioridade}">${item.prioridade || 'baixa'}</span>
                                <span class="badge badge-status">${normalizeStatus(item.status)}</span>
                            </div>

                            <div class="agenda-row"><strong>Empresa:</strong> <span>${item.empresa_nome || '—'}</span></div>
                            <div class="agenda-row"><strong>Demanda:</strong> <span>${item.assunto_nome || 'Sem assunto'}</span></div>
                            <div class="agenda-row"><strong>Descrição:</strong> <span>${demanda}</span></div>
                            <div class="agenda-row"><strong>Endereço:</strong> <span>${endereco}${complemento}${cep}</span></div>
                            <div class="agenda-row"><strong>Contato:</strong> <span>${item.telefone_solicitante || 'Não informado'}</span></div>
                        </div>
                    `;
                }).join('');
            }

            function renderMes() {
                calendarCard.style.display = '';
                titleEl.textContent = `${meses[dataAtual.getMonth()]} ${dataAtual.getFullYear()}`;

                const ano = dataAtual.getFullYear();
                const mes = dataAtual.getMonth();
                const primeiroDia = new Date(ano, mes, 1).getDay();
                const ultimoDia = new Date(ano, mes + 1, 0).getDate();
                const ultimoDiaMesAnterior = new Date(ano, mes, 0).getDate();

                let html = diasSemana.map(d => `<div class="agenda-day-h">${d}</div>`).join('');

                for (let i = primeiroDia - 1; i >= 0; i--) {
                    const dia = ultimoDiaMesAnterior - i;
                    html += `<div class="agenda-day" style="background:#f9fafb; opacity:.55;"><div class="day-number">${dia}</div></div>`;
                }

                const hoje = new Date();
                for (let dia = 1; dia <= ultimoDia; dia++) {
                    const dataCompleta = new Date(ano, mes, dia);
                    const dataStr = dataCompleta.toISOString().split('T')[0];
                    const isHoje = hoje.toDateString() === dataCompleta.toDateString();
                    const isSelected = diaSelecionado === dataStr;
                    const eventos = filtrarDia(dataStr).sort((a, b) => (a.inicio || '00:00').localeCompare(b.inicio || '00:00'));

                    const chips = eventos.slice(0, 2).map(item => {
                        const hora = item.inicio ? `${item.inicio} ` : '';
                        return `<div class="event-chip ${item.prioridade}">${hora}#${item.numero_atendimento} • ${item.cliente_nome}</div>`;
                    }).join('');

                    const extra = eventos.length > 2
                        ? `<div class="event-chip" style="background:#e5e7eb;color:#374151;">+ ${eventos.length - 2} atend.</div>`
                        : '';

                    html += `
                        <div class="agenda-day ${isHoje ? 'today' : ''} ${isSelected ? 'selected' : ''}" data-date="${dataStr}">
                            <div class="day-number">${dia}</div>
                            ${chips}
                            ${extra}
                        </div>
                    `;
                }

                gridEl.innerHTML = html;
            }

            function renderSemana() {
                calendarCard.style.display = '';

                const base = new Date(dataAtual);
                const inicioSemana = new Date(base);
                inicioSemana.setDate(base.getDate() - base.getDay());
                inicioSemana.setHours(0, 0, 0, 0);
                const fimSemana = new Date(inicioSemana);
                fimSemana.setDate(inicioSemana.getDate() + 6);

                titleEl.textContent = `Semana de ${formatDateBR(inicioSemana)} até ${formatDateBR(fimSemana)}`;

                let html = diasSemana.map(d => `<div class="agenda-day-h">${d}</div>`).join('');

                for (let i = 0; i < 7; i++) {
                    const diaData = new Date(inicioSemana);
                    diaData.setDate(inicioSemana.getDate() + i);
                    const dataStr = diaData.toISOString().split('T')[0];
                    const isHoje = new Date().toDateString() === diaData.toDateString();
                    const isSelected = diaSelecionado === dataStr;

                    const eventos = filtrarDia(dataStr).sort((a, b) => (a.inicio || '00:00').localeCompare(b.inicio || '00:00'));
                    const chips = eventos.slice(0, 3).map(item => {
                        const hora = item.inicio ? `${item.inicio} ` : '';
                        return `<div class="event-chip ${item.prioridade}">${hora}#${item.numero_atendimento}</div>`;
                    }).join('');

                    html += `
                        <div class="agenda-day ${isHoje ? 'today' : ''} ${isSelected ? 'selected' : ''}" data-date="${dataStr}">
                            <div class="day-number">${diaData.getDate()}</div>
                            ${chips || '<div class="event-chip" style="background:#f3f4f6;color:#6b7280;">Sem agenda</div>'}
                        </div>
                    `;
                }

                gridEl.innerHTML = html;
            }

            function renderDia() {
                calendarCard.style.display = 'none';
                const d = new Date(`${diaSelecionado}T00:00:00`);
                titleEl.textContent = `Dia ${formatDateBR(d)}`;
            }

            function render() {
                mesFiltroEl.textContent = `${meses[dataAtual.getMonth()]} ${dataAtual.getFullYear()}`;

                if (viewAtual === 'mes') {
                    renderMes();
                } else if (viewAtual === 'semana') {
                    renderSemana();
                } else {
                    renderDia();
                }

                renderListaDia();
                atualizarKPIs();
            }

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    viewAtual = this.dataset.view;
                    render();
                });
            });

            document.getElementById('btnPrev').addEventListener('click', function() {
                if (viewAtual === 'dia') {
                    const d = new Date(`${diaSelecionado}T00:00:00`);
                    d.setDate(d.getDate() - 1);
                    diaSelecionado = d.toISOString().split('T')[0];
                    dataAtual = new Date(d);
                } else if (viewAtual === 'semana') {
                    dataAtual.setDate(dataAtual.getDate() - 7);
                } else {
                    dataAtual.setMonth(dataAtual.getMonth() - 1);
                }

                render();
            });

            document.getElementById('btnNext').addEventListener('click', function() {
                if (viewAtual === 'dia') {
                    const d = new Date(`${diaSelecionado}T00:00:00`);
                    d.setDate(d.getDate() + 1);
                    diaSelecionado = d.toISOString().split('T')[0];
                    dataAtual = new Date(d);
                } else if (viewAtual === 'semana') {
                    dataAtual.setDate(dataAtual.getDate() + 7);
                } else {
                    dataAtual.setMonth(dataAtual.getMonth() + 1);
                }

                render();
            });

            document.addEventListener('click', function(event) {
                const day = event.target.closest('.agenda-day[data-date]');
                if (!day) return;

                diaSelecionado = day.dataset.date;
                dataAtual = new Date(`${diaSelecionado}T00:00:00`);
                render();
            });

            const hoje = new Date();
            const hojeMes = hoje.getMonth();
            const hojeAno = hoje.getFullYear();
            const eventosNoMesAtual = atendimentos.filter(item => {
                const d = new Date(`${item.data_atendimento}T00:00:00`);
                return d.getMonth() === hojeMes && d.getFullYear() === hojeAno;
            }).length;

            if (eventosNoMesAtual === 0) {
                const proximos = atendimentos
                    .filter(item => item.data_atendimento && new Date(`${item.data_atendimento}T00:00:00`) >= new Date(hojeAno, hojeMes, hoje.getDate()))
                    .sort((a, b) => new Date(`${a.data_atendimento}T00:00:00`) - new Date(`${b.data_atendimento}T00:00:00`));

                if (proximos.length > 0) {
                    dataAtual = new Date(`${proximos[0].data_atendimento}T00:00:00`);
                    diaSelecionado = proximos[0].data_atendimento;
                }
            }

            render();
        });
    </script>
    @endpush
</x-app-layout>
