<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0.5cm;
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
            padding: 10px 20px;
        }

        /* Header */
        .header-table {
            width: 100%;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .header-table td {
            vertical-align: top;
        }

        .logo-container img {
            height: 100px;
        }

        .company-details {
            font-size: 9px;
            line-height: 1.2;
        }

        .company-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 2px;
            display: block;
        }

        .header-contacts {
            font-size: 9px;
            text-align: right;
        }

        .date-box {
            text-align: right;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* Social Bar */
        .social-bar {
            background-color: #f8f8f8;
            padding: 5px 15px;
            font-size: 9px;
            margin-bottom: 15px;
        }

        /* Title Bar */
        .title-bar {
            background-color: #eee;
            padding: 8px 15px;
            margin-bottom: 15px;
        }

        .title-bar h2 {
            margin: 0;
            font-size: 14px;
            color: #000;
        }

        .title-bar p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #444;
        }

        /* Section Titles */
        .section-title {
            background-color: #f4f4f4;
            color: #000;
            padding: 4px 10px;
            font-weight: bold;
            font-size: 11px;
            margin-top: 15px;
            margin-bottom: 10px;
        }

        /* Info Grids */
        .info-grid {
            width: 100%;
            margin-bottom: 10px;
        }

        .info-grid td {
            vertical-align: top;
            padding: 2px 0;
        }

        .label {
            font-weight: bold;
        }

        /* Items Table */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table.items th {
            text-align: left;
            color: #777;
            font-weight: normal;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }

        table.items td {
            padding: 8px 0;
            vertical-align: top;
        }

        .item-name {
            font-weight: bold;
            display: block;
        }

        .item-desc {
            font-size: 9px;
            color: #666;
        }

        /* Totals */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 4px 10px;
        }

        .totals-table .label-cell {
            text-align: left;
            width: 80%;
        }

        .totals-table .value-cell {
            text-align: right;
        }

        .total-row {
            background-color: #eee;
            font-weight: bold;
            color: #000;
        }

        /* Signatures */
        .signatures {
            margin-top: 40px;
            width: 100%;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-bottom: 5px;
        }

        .signature-img {
            height: 40px;
            margin-bottom: -10px;
        }

        /* Utilities */
        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .page-footer {
            position: fixed;
            bottom: 10px;
            right: 20px;
            font-size: 9px;
        }
    </style>
</head>

<body>

    <div class="container">
        {{-- HEADER --}}
        <table class="header-table">
            <tr>
                <td class="logo-container" width="35%">
                    @if($empresa->logo)
                    <img src="{{ public_path('images/logo/'.$empresa->logo) }}">
                    @endif
                </td>
                <td class="company-details" width="35%">
                    <span class="company-name">{{ $empresa->nome_fantasia ?? $empresa->nome }}</span>
                    {{ $empresa->razao_social ?? '' }}<br>
                    CNPJ: {{ $empresa->cnpj ?? '-' }}<br>
                    {{ $empresa->endereco ?? '' }}, 79<br>
                    {{ $empresa->bairro ?? 'CENTRO' }}, {{ $empresa->cidade ?? 'VILA VELHA' }}-{{ $empresa->estado ?? 'ES' }}<br>
                    CEP {{ $empresa->cep ?? '29.100.190' }}
                </td>
                <td class="header-contacts" width="30%">
                    <div class="date-box">
                        {{ $orcamento->created_at->format('d/m/Y') }}
                    </div>
                    {{ $empresa->email ?? 'comercial.gw2024@gmail.com.br' }}<br>
                    +55 ({{ $empresa->ddd ?? '27' }}) {{ $empresa->telefone ?? '4042 - 4157' }}<br>
                    +55 ({{ $empresa->ddd ?? '27' }}) {{ $empresa->whatsapp ?? '3109 - 3265' }}
                </td>
            </tr>
        </table>

        {{-- SOCIAL BAR --}}
        <div class="social-bar">
            {{ $empresa->instagram ?? '@gwsolucoes' }}
        </div>

        {{-- TITLE BAR --}}
        <div class="title-bar">
            <h2>Orçamento {{ $orcamento->numero_orcamento }}</h2>
            <p>{{ $orcamento->descricao ?? '-' }}</p>
        </div>

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

        {{-- INFORMAÇÕES BÁSICAS --}}
        <div class="section-title">Informações básicas</div>
        <table class="info-grid">
            <tr>
                <td width="50%">
                    <span class="label">Validade do orçamento</span><br>
                    {{ $orcamento->validade ? \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') : '5 dias' }}
                </td>
                <td width="50%">
                    <span class="label">Prazo de execução</span><br>
                    {{ $orcamento->prazo_execucao ?? 'À Combinar' }}
                </td>
            </tr>
        </table>

        {{-- SERVIÇOS --}}
        @php $servicos = $orcamento->itens->where('tipo','servico'); @endphp
        @if($servicos->count())
        <div class="section-title">Serviços</div>
        <table class="items">
            <thead>
                <tr>
                    <th width="50%">Descrição</th>
                    <th width="10%">Unidade</th>
                    <th width="15%" class="right">Preço unitário</th>
                    <th width="10%" class="center">Qtd.</th>
                    <th width="15%" class="right">Preço</th>
                </tr>
            </thead>
            <tbody>
                @foreach($servicos as $item)
                <tr>
                    <td>
                        <span class="item-name">{{ $item->nome }}</span>
                        @if($item->descricao)
                        <span class="item-desc">{{ $item->descricao }}</span>
                        @endif
                    </td>
                    <td>un.</td>
                    <td class="right">R$ {{ number_format($item->valor_unitario,2,',','.') }}</td>
                    <td class="center">{{ $item->quantidade }}</td>
                    <td class="right">R$ {{ number_format($item->subtotal,2,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- MATERIAIS --}}
        @php $produtos = $orcamento->itens->where('tipo','produto'); @endphp
        @if($produtos->count())
        <div class="section-title">Materiais</div>
        <table class="items">
            <thead>
                <tr>
                    <th width="50%">Descrição</th>
                    <th width="10%">Unidade</th>
                    <th width="15%" class="right">Preço unitário</th>
                    <th width="10%" class="center">Qtd.</th>
                    <th width="15%" class="right">Preço</th>
                </tr>
            </thead>
            <tbody>
                @foreach($produtos as $item)
                <tr>
                    <td>
                        <span class="item-name">{{ $item->nome }}</span>
                        @if($item->descricao)
                        <span class="item-desc">{{ $item->descricao }}</span>
                        @endif
                    </td>
                    <td>un.</td>
                    <td class="right">R$ {{ number_format($item->valor_unitario,2,',','.') }}</td>
                    <td class="center">{{ $item->quantidade }}</td>
                    <td class="right">R$ {{ number_format($item->subtotal,2,',','.') }}</td>
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

        <div class="section-title">Totais</div>

        <table class="totals-table">
            <tr>
                <td class="label-cell">Serviços</td>
                <td class="value-cell">R$ {{ number_format($totalServicos, 2, ',', '.') }}</td>
            </tr>

            <tr>
                <td class="label-cell">Materiais</td>
                <td class="value-cell">R$ {{ number_format($totalProdutos, 2, ',', '.') }}</td>
            </tr>

            @if($desconto > 0)
            <tr>
                <td class="label-cell">Descontos</td>
                <td class="value-cell">- R$ {{ number_format($desconto, 2, ',', '.') }}</td>
            </tr>
            @endif

            @if($taxas > 0 && $descricaoTaxas)
            <tr>
                <td class="label-cell">{{ $descricaoTaxas }}</td>
                <td class="value-cell">R$ {{ number_format($taxas, 2, ',', '.') }}</td>
            </tr>
            @endif

            <tr class="total-row">
                <td class="label-cell">Total</td>
                <td class="value-cell">
                    R$ {{ number_format($totalFinal, 2, ',', '.') }}
                </td>
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
        <div style="padding: 0 10px;">
            @if($orcamento->observacoes)
            <p><strong>OBSERVAÇÕES:</strong><br>{!! nl2br(e($orcamento->observacoes)) !!}</p>
            @endif

            <div class="fixed-terms">

                Orçamento Válido até <strong>{{ $orcamento->validade ? \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') : '5 dias' }}</strong>;<br>
                Serviço conforme Conversado/Visita Técnica;<br>
                Garantia Não Contempla Deslocamento;<br>
                Serviços ou Materiais que não foram adicionadas no orçamento, <br>e forem necessários para andamento do serviço, será repassado como um orçamento adicional a este;<br>
                Prazo para execução padrão é de 3 dias após o pagamento da entrada;<br>
                Valores com desconto caso cliente opte para <strong>Recibo</strong>.<br>

            </div>
        </div>

        {{-- DATA E LOCAL --}}
        <div class="center" style="margin-top: 20px; font-weight: bold;">
            {{ $empresa->cidade ?? 'Vila Velha' }}, {{ $orcamento->created_at->format('d/m/Y') }}
        </div>

        {{-- ASSINATURAS --}}
        <table class="signatures">
            <tr>
                <td class="signature-box">
                    @if(isset($empresa->assinatura_digital))
                    <img src="{{ public_path('storage/'.$empresa->assinatura_digital) }}" class="signature-img"><br>
                    @endif
                    <div class="signature-line"></div>
                    <span class="label">{{ $empresa->nome_fantasia ?? $empresa->nome }}</span><br>
                    {{ $empresa->responsavel ?? 'Diretor Comercial' }}
                </td>
                <td width="10%"></td>
                <td class="signature-box">
                    <div style="height: 30px;"></div>
                    <div class="signature-line"></div>
                    <span class="label">{{ $orcamento->cliente->nome ?? 'Cliente' }}</span><br>
                    CNPJ {{ $orcamento->cliente->cpf_cnpj ?? '-' }}
                </td>
            </tr>
        </table>
    </div>

</body>

</html>