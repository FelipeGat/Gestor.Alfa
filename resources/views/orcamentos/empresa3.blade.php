<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 1cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header-table td {
            vertical-align: top;
        }

        .logo-container {
            width: 150px;
        }

        .logo-container img {
            width: 120px;
        }

        .company-info {
            font-size: 10px;
            line-height: 1.2;
        }

        .company-name {
            font-weight: bold;
            font-size: 13px;
            display: block;
            margin-bottom: 2px;
        }

        .budget-info {
            text-align: right;
        }

        .budget-title {
            font-size: 18px;
            margin: 0;
        }

        .budget-date {
            font-size: 14px;
            margin: 5px 0;
        }

        .vendedor-info {
            font-size: 11px;
            margin-top: 10px;
        }

        .section-header {
            font-size: 14px;
            margin: 15px 0 8px 0;
            color: #333;
            font-weight: bold;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
            text-transform: uppercase;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .items-table th {
            background-color: #f2f2f2;
            padding: 6px 5px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
            font-size: 10px;
        }

        .items-table td {
            padding: 5px;
            border: 1px solid #eee;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-container {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .total-row td {
            padding: 4px 8px;
        }

        .total-label {
            text-align: right;
            font-weight: bold;
            width: 85%;
        }

        .total-value {
            text-align: right;
            font-weight: bold;
            width: 15%;
            white-space: nowrap;
        }

        .color-desconto {
            color: #0056b3;
        }

        /* Azul */
        .color-taxa {
            color: #e67e22;
        }

        /* Laranja */
        .color-total {
            font-size: 14px;
            border-top: 2px solid #333;
            padding-top: 8px !important;
        }

        .payment-info {
            margin-top: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
            line-height: 1.5;
        }

        .observations {
            margin-top: 15px;
            line-height: 1.5;
        }

        .obs-item {
            margin-bottom: 5px;
        }

        .fixed-terms {
            margin-top: 15px;
            font-size: 10px;
            color: #444;
        }

        .fixed-terms ul {
            padding-left: 15px;
            margin: 5px 0;
        }

        .page-footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            font-size: 10px;
        }
    </style>
</head>

<body>

    {{-- CABEÇALHO --}}
    <table class="header-table">
        <tr>
            <td class="logo-container">
                @if($empresa->logo)
                <img src="{{ public_path('images/logo/'.$empresa->logo) }}">
                @endif
            </td>
            <td class="company-info">
                <span class="company-name">{{ $empresa->razao_social ?? $empresa->nome }}</span>
                {{ $empresa->nome_fantasia ?? '' }}<br>
                CNPJ {{ $empresa->cnpj ?? '' }} - I.E. {{ $empresa->ie ?? '' }}<br>
                {{ $empresa->endereco ?? '' }} , 79<br>
                {{ $empresa->bairro ?? 'CENTRO' }}, {{ $empresa->cidade ?? 'VILA VELHA' }} - {{ $empresa->estado ?? 'ES' }} - CEP {{ $empresa->cep ?? '29.100.190' }}<br>
                ({{ $empresa->ddd ?? '27' }}) {{ $empresa->telefone ?? '4042-4157' }} . WHATSAPP: ({{ $empresa->ddd ?? '27' }}) {{ $empresa->whatsapp ?? '3109-3265' }}
            </td>
            <td class="budget-info">
                <h1 class="budget-title">Orçamento nº {{ $orcamento->numero_orcamento }}</h1>
                <div class="budget-date">{{ $orcamento->created_at->format('d/m/Y') }}</div>
                <div class="vendedor-info">
                    Vendedor:<br>
                    <strong>{{ strtoupper($orcamento->vendedor->name ?? 'INVEST') }}</strong>
                </div>
            </td>
        </tr>
    </table>

    {{-- CLIENTE --}}
    @php $pessoa = $orcamento->cliente ?? $orcamento->preCliente; @endphp
    <div style="margin-bottom: 15px;">
        <strong>CLIENTE:</strong> {{ strtoupper($pessoa->nome_fantasia ?? $pessoa->nome ?? $pessoa->razao_social ?? 'Não informado') }}<br>
        <strong>CPF/CNPJ:</strong> {{ $pessoa->cpf_cnpj ?? '-' }} | <strong>FONE:</strong>
        @if($orcamento->cliente)
        {{ optional($orcamento->cliente->telefones->first())->valor ?? '-' }}
        @else
        {{ $pessoa->telefone ?? '-' }}
        @endif
        <br>
        <strong>ENDEREÇO:</strong> {{ $pessoa->logradouro ?? $pessoa->endereco ?? '-' }}, {{ $pessoa->numero ?? '-' }} - {{ $pessoa->bairro ?? '-' }} - {{ $pessoa->cidade ?? '-' }}/{{ $pessoa->estado ?? '-' }}
    </div>

    <div class="obs-item">
        <strong>DESCRIÇÃO:</strong>
        <br>{{ $orcamento->descricao }}
    </div>

    @php
    $servicos = $orcamento->itens->where('tipo', 'servico');
    $produtos = $orcamento->itens->where('tipo', 'produto');
    $totalServicos = $servicos->sum('subtotal');
    $totalProdutos = $produtos->sum('subtotal');
    @endphp

    {{-- BLOCO SERVIÇOS --}}
    @if($servicos->count() > 0)
    <div class="section-header">Serviços</div>
    <table class="items-table">
        <thead>
            <tr>
                <th width="60%">Serviço</th>
                <th width="10%" class="text-center">Qtd.</th>
                <th width="15%" class="text-right">Valor Un.</th>
                <th width="15%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($servicos as $item)
            <tr>
                <td>{{ strtoupper($item->nome) }}</td>
                <td class="text-center">{{ number_format($item->quantidade, 0, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- BLOCO PRODUTOS --}}
    @if($produtos->count() > 0)
    <div class="section-header">Produtos / Materiais</div>
    <table class="items-table">
        <thead>
            <tr>
                <th width="60%">Produto</th>
                <th width="10%" class="text-center">Qtd.</th>
                <th width="15%" class="text-right">Valor Un.</th>
                <th width="15%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produtos as $item)
            <tr>
                <td>{{ strtoupper($item->nome) }}</td>
                <td class="text-center">{{ number_format($item->quantidade, 0, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                <td class="text-right">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- TOTAIS --}}
    @php
    $totalServicos = $servicos->sum('subtotal');
    $totalProdutos = $produtos->sum('subtotal');

    $desconto = $orcamento->desconto ?? 0;
    $taxas = $orcamento->taxas ?? 0;

    // Decodifica o JSON da descrição das taxas
    $descricaoTaxasJson = $orcamento->descricao_taxas ?? null;
    $descricaoTaxas = null;

    if ($descricaoTaxasJson) {
    $decoded = json_decode($descricaoTaxasJson, true);

    // Se for array no formato [{"nome":"NF","valor":15}]
    if (is_array($decoded) && isset($decoded[0]['nome'])) {
    $descricaoTaxas = $decoded[0]['nome'];
    }
    }

    $totalParcial = $totalServicos + $totalProdutos;
    $totalFinal = $totalParcial - $desconto + $taxas;
    @endphp

    <table class="totals-container">
        @if($totalServicos > 0)
        <tr class="total-row">
            <td class="total-label">Subtotal Serviços:</td>
            <td class="total-value">R$ {{ number_format($totalServicos, 2, ',', '.') }}</td>
        </tr>
        @endif
        @if($totalProdutos > 0)
        <tr class="total-row">
            <td class="total-label">Subtotal Produtos:</td>
            <td class="total-value">R$ {{ number_format($totalProdutos, 2, ',', '.') }}</td>
        </tr>
        @endif
        @if($orcamento->desconto > 0)
        <tr class="total-row color-desconto">
            <td class="total-label">(-) Descontos:</td>
            <td class="total-value">R$ {{ number_format($orcamento->desconto, 2, ',', '.') }}</td>
        </tr>
        @endif
        @if($orcamento->taxas > 0)
        <tr class="total-row color-taxa">
            <td class="total-label">(+) {{ $descricaoTaxas }}</td>
            <td class="total-value">R$ {{ number_format($orcamento->taxas, 2, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="total-row color-total">
            <td class="total-label">VALOR TOTAL DO ORÇAMENTO:</td>
            <td class="total-value">R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</td>
        </tr>
    </table>

    {{-- PAGAMENTO --}}
    <div class="section-title">Condições de Pagamento</div>
    <div class="payment-box">
        @php
        $fp = $orcamento->forma_pagamento;
        $prazo = $orcamento->prazo_pagamento;
        @endphp

        @if($fp == 'pix')
        <strong>Pix:</strong> {{ $empresa->cnpj ?? 'Consultar CNPJ' }}<br>
        50% de Entrada e Restante na Entrega

        @elseif($fp == 'boleto')
        <strong>Boleto Bancário:</strong>
        {{ $prazo ?? 1 }} vez(es)

        @elseif($fp == 'debito')
        <strong>Cartão de Débito:</strong> À vista

        @elseif($fp == 'credito')
        <strong>Cartão de Crédito:</strong>
        {{ $prazo ?? 1 }} vez(es)

        @elseif($fp == 'faturado')
        <strong>Boleto Bancário:</strong>
        Faturado para {{ $prazo ?? 'X' }} dias

        @else
        {{ $fp ?? 'A combinar com o vendedor' }}
        @endif
    </div>

    {{-- OBSERVAÇÕES --}}
    <div class="section-header">Observações e Termos</div>
    <div class="observations">
        @if($orcamento->observacoes)
        <div class="obs-item">
            <strong>OBSERVAÇÃO:</strong><br>
            {!! nl2br(e($orcamento->observacoes)) !!}
        </div>
        @endif

        <div class="fixed-terms">
            <ul>
                <li>Orçamento Válido até <strong>{{ $orcamento->validade ? \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') : '5 dias' }}</strong>;</li>
                <li>Produtos Novos com 12 meses de Garantia;</li>
                <li>Garantia Não Contempla Deslocamento;</li>
                <li>Garantia Não Coberta por atendimentos de terceiros;</li>
                <li>Serviços ou Materiais que não foram adicionadas no orçamento não inclusas;</li>
                <li>Prazo para execução padrão é de 3 dias após o pagamento da entrada;</li>
                <li>Valores com desconto para Recibo.</li>
            </ul>
        </div>
    </div>

    <div class="page-footer">
        Página 1/1 - Desenvolvido por <strong>Alfa Soluções</strong> Gerado em {{ now()->format('d/m/Y H:i') }}
    </div>

</body>

</html>