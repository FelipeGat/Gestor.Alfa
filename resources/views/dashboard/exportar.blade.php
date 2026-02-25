<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Técnico - {{ $statusTexto }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 22px;
            color: #4f46e5;
            margin-bottom: 8px;
        }

        .header .subtitle {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
        }

        .info-box {
            background-color: #f3f4f6;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #4f46e5;
        }

        .info-box strong {
            color: #1f2937;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        thead {
            background-color: #4f46e5;
            color: white;
        }

        thead th {
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        tbody td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tbody tr:hover {
            background-color: #f3f4f6;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }

        .status-aberto {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-em_atendimento {
            background-color: #fed7aa;
            color: #9a3412;
        }

        .status-aguardando_cliente {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-concluido {
            background-color: #d1fae5;
            color: #047857;
        }

        .status-cancelado {
            background-color: #f3f4f6;
            color: #4b5563;
        }

        .totais {
            margin-top: 20px;
            text-align: right;
            font-size: 12px;
        }

        .totais .total-label {
            font-weight: bold;
            color: #1f2937;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }
    </style>
</head>

<body>
    {{-- HEADER --}}
    <div class="header">
        <h1>Dashboard Técnico</h1>
        <div class="subtitle">Relatório de Atendimentos</div>
        <div class="subtitle">{{ $statusTexto }}</div>
    </div>

    {{-- INFO BOX --}}
    <div class="info-box">
        <div class="info-row">
            <span><strong>Período:</strong> {{ $periodoTexto }}</span>
            <span><strong>Empresa:</strong> {{ $empresaNome }}</span>
        </div>
        <div class="info-row">
            <span><strong>Status:</strong> {{ $statusTexto }}</span>
            <span><strong>Total de Atendimentos:</strong> {{ $total }}</span>
        </div>
    </div>

    {{-- TABELA --}}
    @if($atendimentos->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">ID</th>
                <th style="width: 20%;">Cliente</th>
                <th style="width: 18%;">Empresa</th>
                <th style="width: 15%;">Técnico</th>
                <th style="width: 22%;">Descrição</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 7%;">Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($atendimentos as $atendimento)
            <tr>
                <td><strong>#{{ $atendimento->id }}</strong></td>
                <td>
                    @if($atendimento->cliente)
                        {{ $atendimento->cliente->nome_fantasia ?? $atendimento->cliente->razao_social ?? $atendimento->cliente->nome ?? 'N/A' }}
                    @elseif($atendimento->nome_solicitante)
                        {{ $atendimento->nome_solicitante }}
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $atendimento->empresa->nome_fantasia ?? 'N/A' }}</td>
                <td>{{ $atendimento->funcionario->nome ?? 'Não atribuído' }}</td>
                <td>{{ Str::limit($atendimento->descricao, 40) }}</td>
                <td>
                    <span class="status-badge status-{{ $atendimento->status_atual }}">
                        {{ ucfirst(str_replace('_', ' ', $atendimento->status_atual)) }}
                    </span>
                </td>
                <td>{{ \Carbon\Carbon::parse($atendimento->data_atendimento)->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTAIS --}}
    <div class="totais">
        <div class="total-label">
            Total de Atendimentos: {{ $total }}
        </div>
    </div>
    @else
    <div style="text-align: center; padding: 40px; color: #9ca3af;">
        <p style="font-size: 14px;">Nenhum atendimento encontrado para os filtros selecionados.</p>
    </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        <p>Relatório gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Dashboard Técnico - Sistema de Gestão</p>
    </div>
</body>

</html>