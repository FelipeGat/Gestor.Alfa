<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impressão - Relatório {{ strtoupper($filtros['tipo']) }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin: 18px 0 8px; }
        h3 { font-size: 13px; margin: 16px 0 6px; }
        .muted { color: #6b7280; font-size: 12px; }
        .box { border: 1px solid #d1d5db; border-radius: 8px; padding: 12px; }
        .grid { display: flex; flex-wrap: wrap; gap: 8px; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; min-width: 180px; flex: 1; }
        .k { font-size: 11px; color: #6b7280; }
        .v { font-size: 15px; font-weight: bold; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px; }
        th { background: #f9fafb; text-align: left; }
        .right { text-align: right; }
        .no-print { margin-bottom: 12px; }
        @media print { .no-print { display: none; } body { margin: 8mm; } }
    </style>
</head>
<body>
    @php
        $moeda = fn($valor) => 'R$ ' . number_format((float) $valor, 2, ',', '.');
        $numero = fn($valor, $casas = 2) => number_format((float) $valor, $casas, ',', '.');
    @endphp

    <div class="no-print">
        <button onclick="window.print()">Imprimir</button>
    </div>

    <h1>Relatório {{ strtoupper($filtros['tipo']) }}</h1>
    <div class="muted">
        Empresa ID: {{ $filtros['empresa_id'] }} |
        Período: {{ $filtros['data_inicio'] }} até {{ $filtros['data_fim'] }}
        @if(!empty($filtros['centro_custo_id']))
            | Centro de custo: {{ $filtros['centro_custo_id'] }}
        @endif
    </div>

    <h2>Dados</h2>
    <div class="box">
        @switch($filtros['tipo'])
            @case('financeiro')
                <div class="grid">
                    <div class="card"><div class="k">Receita</div><div class="v">{{ $moeda($dados['receita_total'] ?? 0) }}</div></div>
                    <div class="card"><div class="k">Despesa</div><div class="v">{{ $moeda($dados['despesa_total'] ?? 0) }}</div></div>
                    <div class="card"><div class="k">Lucro líquido</div><div class="v">{{ $moeda($dados['lucro_liquido'] ?? 0) }}</div></div>
                    <div class="card"><div class="k">Margem</div><div class="v">{{ $numero($dados['margem_percentual'] ?? 0) }}%</div></div>
                </div>
                @break

            @case('comercial')
                <div class="grid">
                    <div class="card"><div class="k">Orçamentos</div><div class="v">{{ $numero($dados['total_orcamentos_criados'] ?? 0, 0) }}</div></div>
                    <div class="card"><div class="k">Conversão</div><div class="v">{{ $numero($dados['taxa_conversao'] ?? 0) }}%</div></div>
                    <div class="card"><div class="k">Receita fechada</div><div class="v">{{ $moeda($dados['receita_fechada'] ?? 0) }}</div></div>
                    <div class="card"><div class="k">Ticket médio</div><div class="v">{{ $moeda($dados['ticket_medio'] ?? 0) }}</div></div>
                </div>
                @break

            @case('tecnico')
                <div class="grid">
                    <div class="card"><div class="k">Chamados</div><div class="v">{{ $numero($dados['total_chamados'] ?? 0, 0) }}</div></div>
                    <div class="card"><div class="k">Finalizados</div><div class="v">{{ $numero($dados['finalizados'] ?? 0, 0) }}</div></div>
                    <div class="card"><div class="k">Abertos</div><div class="v">{{ $numero($dados['abertos'] ?? 0, 0) }}</div></div>
                    <div class="card"><div class="k">Tempo médio</div><div class="v">{{ $numero($dados['tempo_medio_atendimento_minutos'] ?? 0) }} min</div></div>
                </div>
                @break

            @case('rh')
                <div class="grid">
                    <div class="card"><div class="k">Atrasos</div><div class="v">{{ $numero($dados['total_atrasos'] ?? 0, 0) }}</div></div>
                    <div class="card"><div class="k">Faltas</div><div class="v">{{ $numero($dados['total_faltas'] ?? 0, 0) }}</div></div>
                    <div class="card"><div class="k">Atestados</div><div class="v">{{ $numero($dados['total_atestados'] ?? 0, 0) }}</div></div>
                    <div class="card"><div class="k">Absenteísmo</div><div class="v">{{ $numero($dados['indice_absenteismo'] ?? 0) }}%</div></div>
                </div>
                @break

            @default
                <div class="grid">
                    <div class="card"><div class="k">Receita total</div><div class="v">{{ $moeda($dados['receita_total'] ?? 0) }}</div></div>
                    <div class="card"><div class="k">Despesa total</div><div class="v">{{ $moeda($dados['despesa_total'] ?? 0) }}</div></div>
                    <div class="card"><div class="k">Lucro</div><div class="v">{{ $moeda($dados['lucro'] ?? 0) }}</div></div>
                    <div class="card"><div class="k">Conversão comercial</div><div class="v">{{ $numero($dados['conversao_comercial'] ?? 0) }}%</div></div>
                </div>
        @endswitch

        <h3>Insights automáticos</h3>
        <ul>
            @forelse(($dados['insights_automaticos'] ?? []) as $insight)
                <li>{{ $insight }}</li>
            @empty
                <li>Sem insights para o período.</li>
            @endforelse
        </ul>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 200);
        });
    </script>
</body>
</html>
