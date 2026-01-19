document.addEventListener('DOMContentLoaded', function () {

    const dataEl = document.getElementById('dashboard-data');
    if (!dataEl) return;

    const statusDataRaw = JSON.parse(dataEl.dataset.status || '{}');
    const empresas = JSON.parse(dataEl.dataset.empresas || '[]');
    const aprovados = Number(dataEl.dataset.aprovados || 0);
    const recusados = Number(dataEl.dataset.recusados || 0);

    // ================= TRADUÇÃO STATUS =================
    const statusLabels = {
        em_elaboracao: 'Em elaboração',
        aguardando_aprovacao: 'Aguardando aprovação',
        enviado: 'Enviado',
        aprovado: 'Aprovado',
        recusado: 'Recusado',
        concluido: 'Concluído',
        garantia: 'Garantia',
        cancelado: 'Cancelado',
    };

    const statusKeys = Object.keys(statusDataRaw);
    const statusLabelsFinal = statusKeys.map(s => statusLabels[s] || s);
    const statusValues = Object.values(statusDataRaw);

    const statusColors = [
        '#60A5FA', // azul
        '#FBBF24', // amarelo
        '#A78BFA', // roxo
        '#34D399', // verde
        '#F87171', // vermelho
        '#22D3EE', // ciano
        '#FDBA74', // laranja
        '#9CA3AF', // cinza
    ];

    // ================= GRÁFICO STATUS =================
    const chartStatus = document.getElementById('chartStatus');
    if (chartStatus) {
        new Chart(chartStatus, {
            type: 'bar',
            data: {
                labels: statusLabelsFinal,
                datasets: [{
                    data: statusValues,
                    backgroundColor: statusColors,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.raw} orçamentos`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    // ================= GRÁFICO EMPRESAS =================
    const chartEmpresa = document.getElementById('chartEmpresa');
    if (chartEmpresa) {
        new Chart(chartEmpresa, {
            type: 'bar',
            data: {
                labels: empresas.map(e => e.empresa?.nome_fantasia ?? '—'),
                datasets: [{
                    label: 'Valor Total (R$)',
                    data: empresas.map(e => e.total_valor),
                    backgroundColor: '#6366F1',
                    borderRadius: 6,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx =>
                                ` R$ ${Number(ctx.raw).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            callback: value =>
                                `R$ ${Number(value).toLocaleString('pt-BR')}`
                        }
                    }
                }
            }
        });
    }

    // ================= CONVERSÃO =================
    const chartConversao = document.getElementById('chartConversao');
    if (chartConversao) {
        new Chart(chartConversao, {
            type: 'doughnut',
            data: {
                labels: ['Aprovados', 'Recusados'],
                datasets: [{
                    data: [aprovados, recusados],
                    backgroundColor: ['#10B981', '#EF4444'],
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 20 }
                    }
                },
                cutout: '65%'
            }
        });
    }

});
