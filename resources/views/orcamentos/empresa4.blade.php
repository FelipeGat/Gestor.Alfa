<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans; font-size: 12px; }
        .header { display: flex; justify-content: space-between; }
        table { width:100%; border-collapse: collapse; margin-top:10px; }
        th, td { border:1px solid #ccc; padding:6px; }
        th { background:#f5f5f5; }
    </style>
</head>
<body>

<div class="header">
    <img src="{{ public_path('images/logo/'.$empresa->logo) }}" height="70">
    <div>
        <strong>{{ $empresa->nome }}</strong><br>
        Orçamento Nº {{ $orcamento->numero }}<br>
        Data: {{ $orcamento->created_at->format('d/m/Y') }}
    </div>
</div>

<hr>

<p><strong>Cliente:</strong> {{ $orcamento->cliente->nome }}</p>

<h4>Serviços</h4>
<table>
    <tr>
        <th>Descrição</th>
        <th>Valor</th>
    </tr>
    @foreach($orcamento->servicos as $servico)
        <tr>
            <td>{{ $servico->descricao }}</td>
            <td>R$ {{ number_format($servico->valor, 2, ',', '.') }}</td>
        </tr>
    @endforeach
</table>

<h3 style="text-align:right">
    Total: R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}
</h3>

</body>
</html>
