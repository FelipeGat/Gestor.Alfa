<x-portal-funcionario-layout>
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <a href="{{ route('portal-funcionario.index') }}" class="hover:text-[#3f9cae]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
            </a>
            <span>/</span>
            <span class="font-medium text-gray-900">Agenda</span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data>
        <!-- Toolbar -->
        <x-card class="mb-6">
            <div class="flex flex-col gap-3 items-center">
                <!-- Tabs de Visualização -->
                <div class="flex gap-2 flex-wrap justify-center">
                    <button type="button" class="px-4 py-2 rounded-full text-sm font-bold border border-gray-300 bg-white text-gray-700 hover:bg-[#3f9cae] hover:text-white transition-colors active" data-view="mes">Mês</button>
                    <button type="button" class="px-4 py-2 rounded-full text-sm font-bold border border-gray-300 bg-white text-gray-700 hover:bg-[#3f9cae] hover:text-white transition-colors" data-view="semana">Semana</button>
                    <button type="button" class="px-4 py-2 rounded-full text-sm font-bold border border-gray-300 bg-white text-gray-700 hover:bg-[#3f9cae] hover:text-white transition-colors" data-view="dia">Dia</button>
                </div>

                <!-- Navegação -->
                <div class="flex items-center justify-center gap-2">
                    <button type="button" class="px-3 py-1.5 rounded-md border border-gray-200 bg-gray-50 text-gray-700 font-bold hover:bg-gray-100" id="btnPrev">◀</button>
                    <div class="px-4 py-1.5 rounded-md border border-gray-300 bg-white text-gray-900 font-bold min-w-[150px] text-center whitespace-nowrap" id="btnMesFiltro">—</div>
                    <button type="button" class="px-3 py-1.5 rounded-md border border-gray-200 bg-gray-50 text-gray-700 font-bold hover:bg-gray-100" id="btnNext">▶</button>
                </div>
            </div>

            <!-- Título e KPIs -->
            <div class="mt-4">
                <h2 class="text-lg font-bold text-gray-900" id="agendaTitulo"></h2>
                
                <div class="grid grid-cols-3 gap-3 mt-4">
                    <div class="rounded-lg p-3 bg-white border border-gray-200">
                        <div class="text-xs font-bold text-gray-500 uppercase">Período</div>
                        <div class="text-2xl font-bold text-gray-900 mt-1" id="kpiPeriodo">0</div>
                    </div>
                    <div class="rounded-lg p-3 bg-white border border-gray-200">
                        <div class="text-xs font-bold text-gray-500 uppercase">Dia</div>
                        <div class="text-2xl font-bold text-gray-900 mt-1" id="kpiDia">0</div>
                    </div>
                    <div class="rounded-lg p-3 bg-white border border-gray-200">
                        <div class="text-xs font-bold text-gray-500 uppercase">Abertos/Execução</div>
                        <div class="text-2xl font-bold text-gray-900 mt-1" id="kpiAbertos">0</div>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Grid Agenda -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Calendário -->
            <x-card class="lg:col-span-2" id="calendarCard">
                <div class="flex justify-between items-center mb-3 pb-3 border-b border-gray-100">
                    <span class="text-sm font-bold text-gray-700">Calendário de Execução</span>
                    <span class="text-xs text-gray-500">Clique no dia para ver detalhes</span>
                </div>
                <div id="calendarGrid" class="grid grid-cols-7 gap-px bg-gray-300 rounded-lg overflow-hidden"></div>
            </x-card>

            <!-- Lista do Dia -->
            <x-card>
                <div class="flex justify-between items-center mb-3 pb-3 border-b border-gray-100">
                    <span class="text-sm font-bold text-gray-700" id="painelDiaTitulo">Atendimentos do dia</span>
                    <span class="text-xs text-gray-500" id="painelDiaSubtitulo"></span>
                </div>
                <div id="listaDia" class="space-y-3 max-h-[620px] overflow-auto"></div>
            </x-card>
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

            const tabs = document.querySelectorAll('[data-view]');
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
                    listaDiaEl.innerHTML = '<div class="border border-dashed border-gray-300 rounded-lg p-5 text-center text-gray-500 text-sm">Nenhum atendimento agendado para este dia.</div>';
                    return;
                }

                listaDiaEl.innerHTML = items.map(item => {
                    const horario = item.inicio ? `${item.inicio}${item.fim ? ` às ${item.fim}` : ''}` : 'Horário não definido';
                    const endereco = item.endereco && item.endereco.trim() !== '' ? item.endereco : 'Endereço não informado';
                    const complemento = item.complemento ? ` • ${item.complemento}` : '';
                    const cep = item.cep ? ` • CEP: ${item.cep}` : '';
                    const demanda = item.descricao && item.descricao.trim() !== '' ? item.descricao : 'Sem descrição detalhada da demanda';

                    const badgeColors = {
                        alta: 'bg-red-100 text-red-800',
                        media: 'bg-amber-100 text-amber-800',
                        baixa: 'bg-teal-100 text-teal-800'
                    };

                    return `
                        <div class="border border-gray-200 border-l-4 ${item.prioridade === 'alta' ? 'border-l-red-500' : (item.prioridade === 'media' ? 'border-l-amber-500' : 'border-l-teal-500')} rounded-lg p-3 cursor-pointer hover:shadow-md transition-shadow bg-white" onclick="window.location.href='${item.url}'">
                            <div class="flex justify-between mb-2">
                                <span class="text-xs font-bold text-gray-900">#${item.numero_atendimento}</span>
                                <span class="text-xs font-bold text-teal-700">${horario}</span>
                            </div>
                            <div class="font-bold text-gray-900 mb-2">${item.cliente_nome}</div>
                            <div class="flex gap-1 mb-2 flex-wrap">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-cyan-50 text-cyan-700">${(item.tipo_demanda || 'atendimento').replace('_',' ')}</span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold ${badgeColors[item.prioridade] || badgeColors.baixa}">${item.prioridade || 'baixa'}</span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800">${normalizeStatus(item.status)}</span>
                            </div>
                            <div class="text-xs text-gray-600"><strong class="text-gray-700">Empresa:</strong> ${item.empresa_nome || '—'}</div>
                            <div class="text-xs text-gray-600"><strong class="text-gray-700">Demanda:</strong> ${item.assunto_nome || 'Sem assunto'}</div>
                            <div class="text-xs text-gray-600"><strong class="text-gray-700">Descrição:</strong> ${demanda}</div>
                            <div class="text-xs text-gray-600"><strong class="text-gray-700">Endereço:</strong> ${endereco}${complemento}${cep}</div>
                            <div class="text-xs text-gray-600"><strong class="text-gray-700">Contato:</strong> ${item.telefone_solicitante || 'Não informado'}</div>
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

                let html = diasSemana.map(d => `<div class="bg-gray-800 text-white text-center py-2 text-xs font-bold">${d}</div>`).join('');

                for (let i = primeiroDia - 1; i >= 0; i--) {
                    const dia = ultimoDiaMesAnterior - i;
                    html += `<div class="bg-gray-50 opacity-55 p-2"><div class="text-xs font-bold">${dia}</div></div>`;
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
                        const chipColors = {
                            alta: 'bg-red-100 text-red-800',
                            media: 'bg-amber-100 text-amber-800',
                            baixa: 'bg-teal-100 text-teal-800'
                        };
                        return `<div class="text-xs rounded px-1 py-0.5 ${chipColors[item.prioridade] || chipColors.baixa} whitespace-nowrap overflow-hidden text-overflow-ellipsis">${hora}#${item.numero_atendimento} • ${item.cliente_nome}</div>`;
                    }).join('');

                    const extra = eventos.length > 2 ? `<div class="text-xs rounded px-1 py-0.5 bg-gray-200 text-gray-700">+ ${eventos.length - 2}</div>` : '';

                    html += `
                        <div class="bg-white p-2 min-h-[110px] cursor-pointer border-2 ${isHoje ? 'border-[#3f9cae]' : 'border-transparent'} ${isSelected ? 'bg-cyan-50 border-[#3f9cae]' : ''} hover:bg-gray-50" data-date="${dataStr}">
                            <div class="text-xs font-bold mb-1 ${isHoje ? 'bg-[#3f9cae] text-white w-6 h-6 rounded-full flex items-center justify-center' : ''}">${dia}</div>
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

                let html = diasSemana.map(d => `<div class="bg-gray-800 text-white text-center py-2 text-xs font-bold">${d}</div>`).join('');

                for (let i = 0; i < 7; i++) {
                    const diaData = new Date(inicioSemana);
                    diaData.setDate(inicioSemana.getDate() + i);
                    const dataStr = diaData.toISOString().split('T')[0];
                    const isHoje = new Date().toDateString() === diaData.toDateString();
                    const isSelected = diaSelecionado === dataStr;
                    const eventos = filtrarDia(dataStr).sort((a, b) => (a.inicio || '00:00').localeCompare(b.inicio || '00:00'));
                    
                    const chips = eventos.slice(0, 3).map(item => {
                        const hora = item.inicio ? `${item.inicio} ` : '';
                        const chipColors = {
                            alta: 'bg-red-100 text-red-800',
                            media: 'bg-amber-100 text-amber-800',
                            baixa: 'bg-teal-100 text-teal-800'
                        };
                        return `<div class="text-xs rounded px-1 py-0.5 ${chipColors[item.prioridade] || chipColors.baixa}">${hora}#${item.numero_atendimento}</div>`;
                    }).join('');

                    html += `
                        <div class="bg-white p-2 min-h-[110px] cursor-pointer border-2 ${isHoje ? 'border-[#3f9cae]' : 'border-transparent'} ${isSelected ? 'bg-cyan-50 border-[#3f9cae]' : ''} hover:bg-gray-50" data-date="${dataStr}">
                            <div class="text-xs font-bold mb-1 ${isHoje ? 'bg-[#3f9cae] text-white w-6 h-6 rounded-full flex items-center justify-center' : ''}">${diaData.getDate()}</div>
                            ${chips || '<div class="text-xs rounded px-1 py-0.5 bg-gray-100 text-gray-500">Sem agenda</div>'}
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
                if (viewAtual === 'mes') renderMes();
                else if (viewAtual === 'semana') renderSemana();
                else renderDia();
                renderListaDia();
                atualizarKPIs();
            }

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    tabs.forEach(t => t.classList.remove('active', 'bg-[#3f9cae]', 'text-white'));
                    this.classList.add('active', 'bg-[#3f9cae]', 'text-white');
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
                const day = event.target.closest('[data-date]');
                if (!day) return;
                diaSelecionado = day.dataset.date;
                dataAtual = new Date(`${diaSelecionado}T00:00:00`);
                render();
            });

            render();
        });
    </script>
    @endpush
</x-portal-funcionario-layout>
