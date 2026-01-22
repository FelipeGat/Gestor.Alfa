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
            font-size: 16px;
            margin: 20px 0 10px 0;
            color: #333;
        }

        .client-data {
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .items-table th {
            background-color: #d9d9d9;
            padding: 5px;
            text-align: left;
            font-weight: normal;
            border: 1px solid #fff;
        }
        .items-table td {
            padding: 4px 5px;
            border-bottom: 1px solid #eee;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .totals-container {
            width: 100%;
            margin-top: 5px;
        }
        .total-row {
            width: 100%;
        }
        .total-label {
            text-align: right;
            padding: 3px 10px;
            font-weight: bold;
            width: 80%;
        }
        .total-value {
            text-align: right;
            padding: 3px 5px;
            font-weight: bold;
            width: 20%;
        }

        .footer-note {
            font-size: 10px;
            text-align: right;
            margin-top: 5px;
            font-style: italic;
        }

        .payment-info {
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .observations {
            margin-top: 20px;
            line-height: 1.6;
        }
        .obs-item {
            margin-bottom: 10px;
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
                {{ $empresa->endereco ?? '' }}<br>
                {{ $empresa->bairro ?? '' }}, {{ $empresa->cidade ?? '' }} - {{ $empresa->estado ?? '' }} - CEP {{ $empresa->cep ?? '' }}<br>
                ({{ $empresa->ddd ?? '27' }}) {{ $empresa->telefone ?? '' }} . WHATSAPP: ({{ $empresa->ddd ?? '27' }}) {{ $empresa->whatsapp ?? '' }}
            </td>
            <td class="budget-info">
                <h1 class="budget-title">Orçamento nº {{ $orcamento->numero_orcamento }}</h1>
                <div class="budget-date">{{ $orcamento->created_at->format('d/m/Y') }}</div>
                <div class="vendedor-info">
                    Vendedor:<br>
                    <strong>{{ strtoupper($orcamento->vendedor->name ?? 'INVEST') }} ({{ strtoupper($orcamento->vendedor->name ?? 'INVEST') }})</strong>
                </div>
            </td>
        </tr>
    </table>

    {{-- CLIENTE (CAMPOS ESPECÍFICOS FORNECIDOS) --}}
    @php
        $pessoa = $orcamento->cliente ?? $orcamento->preCliente;
    @endphp

        <div style="margin-bottom: 5px;">
            <span class="label">
                Cliente: {{ $pessoa->nome_fantasia ?? $pessoa->nome ?? $pessoa->razao_social ?? 'Não informado' }}
            </span>
        </div>

        <table class="info-grid">
            <tr>
                <td width="60%">
                    {{ $pessoa->razao_social ?? $pessoa->nome ?? '-' }}<br>
                    CPF / CNPJ: {{ $pessoa->cpf_cnpj ?? '-' }}<br>
                    {{ $pessoa->logradouro ?? '-' }} - {{ $pessoa->numero ?? '-' }}<br>
                    {{ $pessoa->cidade ?? '-' }} - {{ $pessoa->estado ?? '-' }}<br>
                    CEP: {{ $pessoa->cep ?? '-' }}<br>
                    COMPLEMENTO: {{ $pessoa->complemento ?? '-' }}
                </td>

                <td width="40%" class="right">
                    <div style="display: inline-block; text-align: left;">

                        {{-- EMAIL --}}
                        @if($orcamento->cliente)
                            {{ optional($orcamento->cliente->emails->first())->valor ?? '-' }}
                        @else
                            {{ $pessoa->email ?? '-' }}
                        @endif
                        <br>

                        {{-- TELEFONE --}}
                        @if($orcamento->cliente)
                            {{ optional($orcamento->cliente->telefones->first())->valor ?? '-' }}
                        @else
                            {{ $pessoa->telefone ?? '-' }}
                        @endif

                    </div>
                </td>
            </tr>
        </table>

    {{-- PRODUTOS E SERVIÇOS --}}
    <div class="section-header">Produtos e Serviços</div>
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">Item</th>
                <th width="55%">Descrição</th>
                <th width="5%" class="text-center">T</th>
                <th width="10%" class="text-right">Qtd.</th>
                <th width="5%" class="text-center">Unid.</th>
                <th width="10%" class="text-right">Valor Un.</th>
                <th width="10%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $i = 1; 
                $totalProdutos = 0;
                $totalServicos = 0;
                $qtdProdutos = 0;
                $qtdServicos = 0;
            @endphp
            @foreach($orcamento->itens as $item)
                @php
                    $tipo = $item->tipo == 'produto' ? 'P' : 'S';
                    if($item->tipo == 'produto') {
                        $totalProdutos += $item->subtotal;
                        $qtdProdutos += $item->quantidade;
                    } else {
                        $totalServicos += $item->subtotal;
                        $qtdServicos += $item->quantidade;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $i++ }}</td>
                    <td>{{ strtoupper($item->nome) }} *</td>
                    <td class="text-center">{{ $tipo }}</td>
                    <td class="text-right">{{ number_format($item->quantidade, 0, ',', '.') }}</td>
                    <td class="text-center">un.</td>
                    <td class="text-right">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTAIS --}}
    <table class="totals-container">
        <tr>
            <td class="total-label">Totais Produtos</td>
            <td class="total-value" style="width: 5%; text-align: center;">{{ number_format($qtdProdutos, 0, ',', '.') }}</td>
            <td class="total-value">{{ number_format($totalProdutos, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="total-label">Totais Serviços</td>
            <td class="total-value" style="width: 5%; text-align: center;">{{ number_format($qtdServicos, 0, ',', '.') }}</td>
            <td class="total-value">{{ number_format($totalServicos, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="total-label">Totais Produtos/Serviços</td>
            <td class="total-value" style="width: 5%; text-align: center;">{{ number_format($qtdProdutos + $qtdServicos, 0, ',', '.') }}</td>
            <td class="total-value">{{ number_format($orcamento->valor_total, 2, ',', '.') }}</td>
        </tr>
    </table>

    {{-- FORMA DE PAGAMENTO --}}
    <div class="section-header">Forma de Pagamento</div>
    <div class="payment-info">
        {{ $orcamento->forma_pagamento ?? '5% para Dinheiro ou Pix (Entrada + restante ao final do atendimento)' }}
    </div>

    {{-- OBSERVAÇÕES --}}
    <div class="section-header">Observações</div>
    <div class="observations">
        <div class="obs-item">
            Orçamento Válido até {{ $orcamento->validade_dias ?? '5' }} dias corridos;<br>
            Produtos Novos com 12 meses de Garantia
        </div>
        <div class="obs-item">
            Garantia Não Contempla Deslocamento;<br>
            Garantia Não Coberta por outros Técnicos;
        </div>
        <div class="obs-item">
            Peças não inclusas, Caso necessário será reposto a peça e passado ao cliente ao final o valor.
        </div>
        <div class="obs-item">
            Prazo para execução {{ $orcamento->prazo_execucao ?? '3' }} dias após o pagamebto da entrada.
        </div>
        <div class="obs-item">
            Valores com recibo
        </div>
    </div>

    {{-- RODAPÉ DE PÁGINA --}}
    <div class="page-footer">
        1/1
    </div>

</body>
</html>
