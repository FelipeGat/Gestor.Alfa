<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Comercial - {{ $tituloStatus }}</title>
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

        .status-aguardando_aprovacao {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-financeiro {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-aprovado {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-aguardando_pagamento {
            background-color: #dbeafe;
            color: #1e3a8a;
        }

        .status-concluido {
            background-color: #d1fae5;
            color: #047857;
        }

        .status-reprovado {
            background-color: #fee2e2;
            color: #991b1b;
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

        .totais .total-value {
            font-size: 16px;
            font-weight: bold;
            color: #4f46e5;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>📊 Dashboard Comercial</h1>
        <div class="subtitle">{{ $tituloStatus }}</div>
        @if($empresa)
        <div class="subtitle">{{ $empresa->nome_fantasia }}</div>
        @endif
    </div>

    <div class="info-box">
        <div class="info-row">
            <span><strong>Período:</strong> {{ $periodoTexto }}</span>
            <span><strong>Total de Orçamentos:</strong> {{ $orcamentos->count() }}</span>
        </div>
        <div class="info-row">
            <span><strong>Data de Emissão:</strong> {{ now()->format('d/m/Y H:i') }}</span>
            <span><strong>Valor Total:</strong> R$ {{ number_format($valorTotal, 2, ',', '.') }}</span>
        </div>
    </div>

    @if($orcamentos->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 20%;">Cliente</th>
                <th style="width: 15%;">Empresa</th>
                <th style="width: 15%;">Vendedor</th>
                <th style="width: 12%;" class="text-right">Valor</th>
                <th style="width: 18%;">Status</th>
                <th style="width: 15%;">Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orcamentos as $orc)
            <tr>
                <td class="font-bold">{{ $orc->id }}</td>
                <td>{{ $orc->cliente ? ($orc->cliente->nome ?? $orc->cliente->razao_social ?? 'N/A') : 'N/A' }}</td>
                <td>{{ $orc->empresa ? ($orc->empresa->nome_fantasia ?? 'N/A') : 'N/A' }}</td>
                <td>{{ $orc->criadoPor ? ($orc->criadoPor->name ?? 'N/A') : 'N/A' }}</td>
                <td class="text-right font-bold">R$ {{ number_format($orc->valor_total ?? 0, 2, ',', '.') }}</td>
                <td>
                    <span class="status-badge status-{{ $orc->status }}">
                        @switch($orc->status)
                        @case('aguardando_aprovacao') Aguardando Aprovação @break
                        @case('financeiro') Financeiro @break
                        @case('aprovado') Aprovado @break
                        @case('aguardando_pagamento') Aguardando Pagamento @break
                        @case('concluido') Concluído @break
                        @case('reprovado') Reprovado @break
                        @case('cancelado') Cancelado @break
                        @default {{ ucfirst($orc->status) }}
                        @endswitch
                    </span>
                </td>
                <td>{{ $orc->created_at ? $orc->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totais">
        <div style="margin-bottom: 8px;">
            <span class="total-label">Total de Orçamentos:</span>
            <span class="total-value">{{ $orcamentos->count() }}</span>
        </div>
        <div>
            <span class="total-label">Valor Total:</span>
            <span class="total-value">R$ {{ number_format($valorTotal, 2, ',', '.') }}</span>
        </div>
    </div>
    @else
    <div style="text-align: center; padding: 40px; color: #9ca3af;">
        <p style="font-size: 14px;">Nenhum orçamento encontrado para os filtros aplicados.</p>
    </div>
    @endif

    <div class="footer">
        Relatório gerado automaticamente pelo Sistema de Gestão em {{ now()->format('d/m/Y') }} às {{ now()->format('H:i') }}
    </div>
</body>

</html>