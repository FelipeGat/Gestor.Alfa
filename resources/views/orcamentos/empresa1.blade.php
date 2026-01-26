<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0.2cm 0.5cm 0.5cm 0.5cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 0px 10px 40px 10px;
        }

        .header {
            text-align: center;
            margin-top: 0;
            margin-bottom: 1px;
        }

        .header img {
            height: 180px;
            margin: 0 auto;
        }

        .title-bar {
            background-color: #4d613a;
            color: white;
            padding: 8px 15px;
            margin-bottom: 15px;
        }

        .title-bar h2 {
            margin: 0;
            font-size: 15px;
        }

        .title-bar p {
            margin: 2px 0 0 0;
            font-size: 13px;
        }

        .section-title {
            background-color: #f4f4f4;
            color: #4d613a;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 12px;
            margin-top: 15px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .info-grid {
            width: 100%;
            margin-bottom: 10px;
            font-size: 11px;
        }

        .info-grid td {
            vertical-align: top;
            padding: 2px 0;
        }

        .label {
            font-weight: bold;
            color: #4d613a;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table.items th {
            text-align: left;
            background-color: #f9f9f9;
            color: #4d613a;
            font-weight: bold;
            padding: 10px 5px;
            border-bottom: 2px solid #4d613a;
        }

        table.items td {
            padding: 8px 5px;
            vertical-align: top;
            border-bottom: 1px solid #eee;
        }

        .item-name {
            font-weight: bold;
            display: block;
            font-size: 11px;
        }

        .item-desc {
            font-size: 9px;
            color: #666;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .totals-table td {
            padding: 5px 10px;
        }

        .totals-table .label-cell {
            text-align: right;
            width: 80%;
            font-size: 11px;
            font-weight: bold;
        }

        .totals-table .value-cell {
            text-align: right;
            font-size: 11px;
            font-weight: bold;
            width: 20%;
        }

        .color-desconto {
            color: #0056b3;
        }

        .color-taxa {
            color: #e67e22;
        }

        .total-row {
            background-color: #4d613a;
            color: white;
            font-weight: bold;
            font-size: 13px;
        }

        .payment-box {
            background-color: #f9f9f9;
            border-left: 4px solid #4d613a;
            padding: 10px;
            margin-bottom: 15px;
        }

        .fixed-terms {
            margin-top: 15px;
            font-size: 9px;
            color: #555;
            background-color: #fff;
            padding: 10px;
            border: 1px solid #eee;
        }

        .fixed-terms ul {
            padding-left: 15px;
            margin: 5px 0;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }
    </style>

    <div class="container">
        {{-- HEADER --}}
        <div class="header">
            @if($empresa->logo)
            <img src="{{ public_path('images/logo/'.$empresa->logo) }}">
            @endif
        </div>

        {{-- TITLE BAR --}}
        <div class="title-bar">
            <h2>Orçamento {{ $orcamento->numero_orcamento }}</h2>
            <p>{{ $orcamento->descricao ?? '-' }}</p>
        </div>

        {{-- CLIENTE --}}
        @php $pessoa = $orcamento->cliente ?? $orcamento->preCliente; @endphp
        <table class="info-grid">
            <tr>
                <td width="60%">
                    <span class="label">CLIENTE:</span> {{ strtoupper($pessoa->nome_fantasia ?? $pessoa->nome ?? $pessoa->razao_social ?? 'Não informado') }}<br>
                    <span class="label">CPF/CNPJ:</span> {{ $pessoa->cpf_cnpj ?? '-' }}<br>
                    <span class="label">ENDEREÇO:</span> {{ $pessoa->logradouro ?? $pessoa->endereco ?? '-' }}, {{ $pessoa->numero ?? '-' }} - {{ $pessoa->bairro ?? '-' }}<br>
                    {{ $pessoa->cidade ?? '-' }} - {{ $pessoa->estado ?? '-' }} | CEP: {{ $pessoa->cep ?? '-' }}
                </td>
                <td width="40%" class="right">
                    <span class="label">DATA:</span> {{ $orcamento->created_at->format('d/m/Y') }}<br>
                    <span class="label">FONE:</span>
                    @if($orcamento->cliente)
                    {{ optional($orcamento->cliente->telefones->first())->valor ?? '-' }}
                    @else
                    {{ $pessoa->telefone ?? '-' }}
                    @endif
                    <br>
                    <span class="label">EMAIL:</span>
                    @if($orcamento->cliente)
                    {{ optional($orcamento->cliente->emails->first())->valor ?? '-' }}
                    @else
                    {{ $pessoa->email ?? '-' }}
                    @endif
                </td>
            </tr>
        </table>

        @php
        $servicos = $orcamento->itens->where('tipo', 'servico');
        $produtos = $orcamento->itens->where('tipo', 'produto');
        $totalServicos = $servicos->sum('subtotal');
        $totalProdutos = $produtos->sum('subtotal');
        @endphp

        {{-- SERVIÇOS --}}
        @if($servicos->count())
        <div class="section-title">Serviços</div>
        <table class="items">
            <thead>
                <tr>
                    <th width="55%">Descrição</th>
                    <th width="10%" class="center">Qtd.</th>
                    <th width="15%" class="right">Preço Unit.</th>
                    <th width="20%" class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($servicos as $item)
                <tr>
                    <td>
                        <span class="item-name">{{ strtoupper($item->nome) }}</span>
                        @if($item->descricao)
                        <span class="item-desc">{{ $item->descricao }}</span>
                        @endif
                    </td>
                    <td class="center">{{ number_format($item->quantidade, 0, ',', '.') }}</td>
                    <td class="right">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                    <td class="right">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- MATERIAIS --}}
        @if($produtos->count())
        <div class="section-title">Materiais / Produtos</div>
        <table class="items">
            <thead>
                <tr>
                    <th width="55%">Descrição</th>
                    <th width="10%" class="center">Qtd.</th>
                    <th width="15%" class="right">Preço Unit.</th>
                    <th width="20%" class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($produtos as $item)
                <tr>
                    <td>
                        <span class="item-name">{{ strtoupper($item->nome) }}</span>
                        @if($item->descricao)
                        <span class="item-desc">{{ $item->descricao }}</span>
                        @endif
                    </td>
                    <td class="center">{{ number_format($item->quantidade, 0, ',', '.') }}</td>
                    <td class="right">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                    <td class="right">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
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

        {{-- TOTAIS --}}
        <table class="totals-table">
            @if($totalServicos > 0)
            <tr>
                <td class="label-cell">Subtotal Serviços:</td>
                <td class="value-cell">R$ {{ number_format($totalServicos, 2, ',', '.') }}</td>
            </tr>
            @endif
            @if($totalProdutos > 0)
            <tr>
                <td class="label-cell">Subtotal Materiais:</td>
                <td class="value-cell">R$ {{ number_format($totalProdutos, 2, ',', '.') }}</td>
            </tr>
            @endif
            @if($orcamento->desconto > 0)
            <tr class="color-desconto">
                <td class="label-cell">(-) Descontos:</td>
                <td class="value-cell">R$ {{ number_format($desconto, 2, ',', '.') }}</td>
            </tr>
            @endif
            @if($orcamento->taxas > 0)
            <tr class="color-taxa">
                <td class="label-cell">(+){{ $descricaoTaxas }}</td>
                <td class="value-cell">R$ {{ number_format($orcamento->taxas, 2, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td class="label-cell">VALOR TOTAL:</td>
                <td class="value-cell">R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</td>
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
        <div class="section-title">Observações e Termos</div>
        <div style="padding: 0px;">
            @if($orcamento->observacoes)
            <p><strong>OBSERVAÇÃO:</strong><br>{!! nl2br(e($orcamento->observacoes)) !!}</p>
            @endif

            <div class="fixed-terms">
                <ul>
                    <li>Orçamento Válido até <strong>{{ $orcamento->validade ? \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') : '5 dias' }}</strong>;</li>
                    <li>Serviço conforme Conversado/Visita Técnica;</li>
                    <li>Garantia Não Contempla Deslocamento;</li>
                    <li>Serviços ou Materiais que não foram adicionadas no orçamento não inclusas;</li>
                    <li>Prazo para execução padrão é de 3 dias após o pagamento da entrada;</li>
                    <li>Valores com desconto para Recibo.</li>
                </ul>
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="footer-info" style="margin-top: 30px; text-align: center;">
            <p>{{ $empresa->razao_social }} <br> CNPJ: {{ $empresa->cnpj }}<br>
                {{ $empresa->endereco }} - 79 {{ $empresa->cidade ?? 'Vila Velha' }} / {{ $empresa->estado ?? 'E.S'}}<br>
                Fone: {{ $empresa->ddd ?? '(27) 4042 - 4157' }} | WhatsApp: {{ $empresa->ddd ?? '(27) 3109 - 3265'  }} {{ $empresa->whatsapp }}
            </p>
        </div>
    </div>

    </body>

</html>