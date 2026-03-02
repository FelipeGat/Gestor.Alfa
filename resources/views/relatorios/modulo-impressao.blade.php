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
        .muted { color: #6b7280; font-size: 12px; }
        .box { border: 1px solid #d1d5db; border-radius: 8px; padding: 12px; }
        pre { white-space: pre-wrap; word-wrap: break-word; font-size: 12px; margin: 0; }
        .no-print { margin-bottom: 12px; }
        @media print { .no-print { display: none; } body { margin: 8mm; } }
    </style>
</head>
<body>
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
        <pre>{{ json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 200);
        });
    </script>
</body>
</html>
