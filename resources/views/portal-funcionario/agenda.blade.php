<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📅 Agenda Técnica
        </h2>
    </x-slot>

    <style>
        .agenda-container {
            padding: 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .view-switcher {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            overflow-x: auto;
        }

        .view-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            border: 2px solid #e5e7eb;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .view-btn.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border-color: #2563eb;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .calendar-nav {
            display: flex;
            gap: 0.5rem;
        }

        .nav-btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background: #f3f4f6;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .nav-btn:hover {
            background: #e5e7eb;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #e5e7eb;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .calendar-day-header {
            background: #1f2937;
            color: white;
            padding: 1rem;
            text-align: center;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .calendar-day {
            background: white;
            padding: 0.5rem;
            min-height: 100px;
            position: relative;
        }

        .calendar-day.other-month {
            background: #f9fafb;
            opacity: 0.5;
        }

        .day-number {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .calendar-day.today .day-number {
            background: #3b82f6;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .event-item {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            margin-bottom: 0.25rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .event-item:hover {
            transform: scale(1.05);
        }

        .event-item.alta {
            background: #fee2e2;
            color: #991b1b;
        }

        .event-item.media {
            background: #fed7aa;
            color: #92400e;
        }

        .event-item.baixa {
            background: #dbeafe;
            color: #1e40af;
        }

        .lista-view {
            display: none;
        }

        .lista-view.active {
            display: block;
        }

        .atendimento-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid;
        }

        .atendimento-card.alta {
            border-color: #ef4444;
        }

        .atendimento-card.media {
            border-color: #f59e0b;
        }

        .atendimento-card.baixa {
            border-color: #3b82f6;
        }

        @media (max-width: 768px) {
            .calendar-grid {
                grid-template-columns: 1fr;
            }

            .calendar-day-header {
                display: none;
            }

            .calendar-day {
                border-bottom: 1px solid #e5e7eb;
                display: flex;
                flex-direction: column;
            }

            .day-number::before {
                content: attr(data-weekday) ' ';
                font-weight: 400;
                color: #6b7280;
            }
        }
    </style>

    <div class="agenda-container">
        <!-- View Switcher -->
        <div class="view-switcher">
            <button class="view-btn active" onclick="switchView('mes')">📅 Mês</button>
            <button class="view-btn" onclick="switchView('semana')">📆 Semana</button>
            <button class="view-btn" onclick="switchView('tres-dias')">3️⃣ 3 Dias</button>
            <button class="view-btn" onclick="switchView('dia')">📋 Dia</button>
        </div>

        <!-- Calendar Header -->
        <div class="calendar-header">
            <div class="calendar-nav">
                <button class="nav-btn" onclick="navegarMes(-1)">◀</button>
                <button class="nav-btn" onclick="hoje()">Hoje</button>
                <button class="nav-btn" onclick="navegarMes(1)">▶</button>
            </div>
            <div id="mes-ano" style="font-weight: 700; font-size: 1.125rem;"></div>
        </div>

        <!-- Calendar Grid -->
        <div id="calendario" class="calendar-grid"></div>

        <!-- Lista View -->
        <div id="lista-view" class="lista-view">
            <div id="lista-atendimentos"></div>
        </div>
    </div>

    @push('scripts')
    <script>
        let dataAtual = new Date();
        let viewAtual = 'mes';
        const atendimentos = @json($atendimentos);

        const diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        const meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                      'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

        function switchView(view) {
            viewAtual = view;
            document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            if (view === 'dia') {
                mostrarListaDia();
            } else {
                document.getElementById('calendario').style.display = 'grid';
                document.getElementById('lista-view').classList.remove('active');
                renderizarCalendario();
            }
        }

        function navegarMes(delta) {
            dataAtual.setMonth(dataAtual.getMonth() + delta);
            renderizarCalendario();
        }

        function hoje() {
            dataAtual = new Date();
            renderizarCalendario();
        }

        function mostrarListaDia() {
            document.getElementById('calendario').style.display = 'none';
            document.getElementById('lista-view').classList.add('active');

            const dataStr = dataAtual.toISOString().split('T')[0];
            const atendimentosDia = atendimentos.filter(a => a.data_atendimento === dataStr);

            const lista = document.getElementById('lista-atendimentos');
            lista.innerHTML = '';

            if (atendimentosDia.length === 0) {
                lista.innerHTML = '<p style="text-align: center; color: #6b7280; padding: 2rem;">Nenhum atendimento para esta data.</p>';
                return;
            }

            atendimentosDia.forEach(atendimento => {
                const card = document.createElement('div');
                card.className = `atendimento-card ${atendimento.prioridade}`;
                card.innerHTML = `
                    <div style="font-weight: 700; margin-bottom: 0.5rem;">
                        #${atendimento.numero_atendimento} - ${atendimento.cliente_nome}
                    </div>
                    <div style="font-size: 0.875rem; color: #6b7280;">
                        ${atendimento.assunto_nome}
                    </div>
                    <div style="margin-top: 0.5rem; font-size: 0.75rem; font-weight: 600; color: ${
                        atendimento.prioridade === 'alta' ? '#ef4444' : 
                        atendimento.prioridade === 'media' ? '#f59e0b' : '#3b82f6'
                    };">
                        ${atendimento.prioridade.toUpperCase()}
                    </div>
                `;
                card.onclick = () => window.location.href = `/portal-funcionario/atendimento/${atendimento.id}`;
                lista.appendChild(card);
            });
        }

        function renderizarCalendario() {
            const ano = dataAtual.getFullYear();
            const mes = dataAtual.getMonth();

            document.getElementById('mes-ano').textContent = `${meses[mes]} ${ano}`;

            const primeiroDia = new Date(ano, mes, 1).getDay();
            const ultimoDia = new Date(ano, mes + 1, 0).getDate();
            const ultimoDiaMesAnterior = new Date(ano, mes, 0).getDate();

            let html = '';

            // Headers
            diasSemana.forEach(dia => {
                html += `<div class="calendar-day-header">${dia}</div>`;
            });

            // Dias do mês anterior
            for (let i = primeiroDia - 1; i >= 0; i--) {
                const dia = ultimoDiaMesAnterior - i;
                html += `<div class="calendar-day other-month"><div class="day-number">${dia}</div></div>`;
            }

            // Dias do mês atual
            const hoje = new Date();
            for (let dia = 1; dia <= ultimoDia; dia++) {
                const dataCompleta = new Date(ano, mes, dia);
                const dataStr = dataCompleta.toISOString().split('T')[0];
                const isHoje = hoje.getDate() === dia && hoje.getMonth() === mes && hoje.getFullYear() === ano;

                const atendimentosDia = atendimentos.filter(a => a.data_atendimento === dataStr);

                let eventos = '';
                atendimentosDia.forEach(atendimento => {
                    eventos += `
                        <div class="event-item ${atendimento.prioridade}" 
                             onclick="window.location.href='/portal-funcionario/atendimento/${atendimento.id}'">
                            ${atendimento.numero_atendimento}
                        </div>
                    `;
                });

                html += `
                    <div class="calendar-day ${isHoje ? 'today' : ''}" onclick="selecionarDia(${dia})">
                        <div class="day-number" data-weekday="${diasSemana[dataCompleta.getDay()]}">${dia}</div>
                        ${eventos}
                    </div>
                `;
            }

            document.getElementById('calendario').innerHTML = html;
        }

        function selecionarDia(dia) {
            dataAtual.setDate(dia);
            switchView('dia');
            document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.view-btn')[3].classList.add('active');
        }

        // Inicializar
        renderizarCalendario();
    </script>
    @endpush
</x-app-layout>