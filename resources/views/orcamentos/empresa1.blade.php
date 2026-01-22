<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
</head>
<body>

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
                padding: 0px 10px 40px 10px;
            }
            .header {
                text-align: center;
                margin-bottom: 1px;
            }
            .header img {
                height: 120px;
            }
            .header h1 {
                font-size: 18px;
                color: #4f6f2f;
                margin: 5px 0;
            }
            
            .title-bar {
                background-color: #4d613a;
                color: white;
                padding: 8px 15px;
                margin-bottom: 15px;
            }
            .title-bar h2 {
                margin: 0;
                font-size: 18px;
            }
            .title-bar p {
                margin: 2px 0 0 0;
                font-size: 15px;
            }

            .section-title {
                background-color: #f4f4f4;
                color: #4f6f2f;
                padding: 5px 10px;
                font-weight: bold;
                font-size: 12px;
                margin-top: 15px;
                margin-bottom: 10px;
            }

            .info-grid {
                width: 100%;
                margin-bottom: 10px;
                font-size: 12px;
            }
            .info-grid td {
                vertical-align: top;
                padding: 2px 0;
            }
            .label {
                font-weight: bold;
            }

            table.items {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 10px;
            }
            table.items th {
                text-align: left;
                color: #777;
                font-weight: normal;
                padding: 15px 0;
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

            .totals-table {
                width: 100%;
                border-collapse: collapse;
            }
            .totals-table td {
                padding: 5px 10px;
            }
            .totals-table .label-cell {
                text-align: left;
                width: 80%;
                font-size: 12px;
            }
            .totals-table .value-cell {
                text-align: right;
                font-size: 12px;
            }
            .total-row {
                background-color: #4f6f2f;
                color: white;
                font-weight: bold;
                font-size: 13px;
            }

            .footer-info {
                border-top: 1px solid #ccc;
                margin-top: 0;
                padding-top: 10px;
                font-size: 9px;
                color: #666;
            }
            .footer-grid {
                width: 100%;
            }
            .footer-grid td {
                vertical-align: middle;
            }
            .icon-text {
                display: inline-block;
                vertical-align: middle;
                margin-left: 5px;
            }

            .page-break {
                page-break-before: always;
            }

            .signatures {
                margin-top: 50px;
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

            .photos-grid {
                width: 100%;
                margin-top: 10px;
            }
            .photo-item {
                width: 32%;
                display: inline-block;
                margin-bottom: 15px;
                margin-right: 1%;
                vertical-align: top;
            }
            .photo-item img {
                width: 100%;
                height: 150px;
                object-fit: cover;
                border-radius: 5px;
            }
            .photo-date {
                font-size: 9px;
                margin-top: 3px;
            }

            .right { text-align: right; }
            .center { text-align: center; }

    </style>

<div class="container">
    {{-- HEADER --}}
    <div class="header">
        <img src="{{ public_path('images/logo/'.$empresa->logo) }}">
    </div>

    {{-- TITLE BAR --}}
    <div class="title-bar">
        <h2>Orçamento {{ $orcamento->numero_orcamento }}</h2>
        <p>Serralheria</p>
    </div>

    {{-- CLIENTE --}}
    <table class="info-grid">
        <tr>
            <td width="60%">
                <span class="label">Cliente: {{ $orcamento->cliente->nome ?? 'Não informado' }}</span><br>
                {{ $orcamento->cliente->razao_social ?? $orcamento->cliente->nome }}<br>
                CPF / CNPJ: {{ $orcamento->cliente->cpf_cnpj ?? '-' }}<br>
                {{ $orcamento->cliente->logradouro ?? '-' }} - {{ $orcamento->cliente->numero ?? '-' }}<br>
                {{ $orcamento->cliente->cidade ?? '-' }} - {{ $orcamento->cliente->estado ?? '-' }}<br>
                CEP: {{ $orcamento->cliente->cep ?? '-' }}<br>
                COMPLEMENTO: {{ $orcamento->cliente->complemento ?? '-' }}
            </td>
            <td width="40%" class="right">
                <div style="display: inline-block; text-align: left;">
                    <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAXUlEQVR42mNgGAWjYBSMAtIAmIGYEYiZgZgZiJmBmBmImYGYGYiZgZgZiJmBmBmImYGYGYiZgZgZiJmBmBmImYGYGYiZgZgZiJmBmBmImYGYGYiZgZgZiJmBmBmImYGYGQAAtA8v+X6mYAAAAABJRU5ErkJggg==" style="width:12px; vertical-align:middle;"> {{ optional($orcamento->cliente->emails->first())->valor ?? '-' }}<br>
                    <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAXUlEQVR42mNgGAWjYBSMAtIAmIGYEYiZgZgZiJmBmBmImYGYGYiZgZgZiJmBmBmImYGYGYiZgZgZiJmBmBmImYGYGYiZgZgZiJmBmBmImYGYGYiZgZgZiJmBmBmImYGYGQAAtA8v+X6mYAAAAABJRU5ErkJggg==" style="width:12px; vertical-align:middle;"> {{ optional($orcamento->cliente->telefones->first())->valor ?? '-' }}
                </div>
            </td>
        </tr>
    </table>

    {{-- INFORMAÇÕES BÁSICAS --}}
    <div class="section-title">Informações básicas</div>
    <div style="margin-bottom: 15px;">
        <span class="label">Validade do orçamento</span><br>
        {{ $orcamento->validade ? \Carbon\Carbon::parse($orcamento->validade)->diffInDays($orcamento->created_at) : '—' }} dias
    </div>

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
                        <span class="item-desc">- {{ $item->descricao }}</span>
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
                        <span class="item-desc">- {{ $item->descricao }}</span>
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
    @endphp
    <table class="totals-table">
        <tr>
            <td class="label-cell">Serviços</td>
            <td class="value-cell">R$ {{ number_format($totalServicos,2,',','.') }}</td>
        </tr>
        <tr>
            <td class="label-cell">Materiais</td>
            <td class="value-cell">R$ {{ number_format($totalProdutos,2,',','.') }}</td>
        </tr>
        <tr class="total-row">
            <td class="label-cell">Total</td>
            <td class="value-cell">R$ {{ number_format($orcamento->valor_total,2,',','.') }}</td>
        </tr>
    </table>

    {{-- PAGAMENTO --}}
    <div class="section-title">Pagamento</div>
    <table class="info-grid">
        <tr>
            <td width="50%">
                <span class="label">Meios de pagamento</span><br>
                Boleto, Transferência Bancária, Dinheiro, <br>
                Cartão de Crédito, Cartão de Débito ou Pix.
            </td>
            <td width="50%">
                <span class="label">PIX</span><br>
                {{ $empresa->cnpj ?? '—' }}
            </td>
        </tr>
    </table>
    <div style="margin-top: 10px;">
        <span class="label">Dados bancários</span><br>
        Banco: Sicoob<br>
        Agência: 3008<br>
        Conta: 251.876-7<br>
        Tipo de conta: Corrente<br>
    </div>

        {{-- INFORMAÇÕES ADICIONAIS --}}
    <div class="section-title">Informações adicionais</div>
    <div style="font-size: 9px;">
        Valores em Reais;<br>
        Garantia não cobre defeitos ou Manutenções dadas por outros técnicos.<br>
        Prazo para Início é de 5 dias úteis a contar da data de Pagamento da entrada.<br>
        Orçamento conforme Visita/Conversa Prévia. Caso necessário a inclusão de materiais ou mão de obra não orçada será cobrado ao final do serviço.
    </div>

    {{-- FOOTER --}}
    <div class="footer-info">
        <table class="footer-grid">
            <tr>
                <td width="50%">
                    {{ $empresa->nome_fantasia ?? $empresa->nome }}<br>
                    CNPJ: {{ $empresa->cnpj ?? '—' }}<br>
                    {{ $empresa->endereco ?? 'Rua Ceciliano Abel de Almeida, 25' }}<br>
                    {{ $empresa->bairro ?? 'Residencial Jacaraípe' }}, {{ $empresa->cidade ?? 'Serra' }}-{{ $empresa->estado ?? 'ES' }}<br>
                    CEP {{ $empresa->cep ?? '29175-444' }}
                </td>
                <td width="50%" class="right">
                    {{ $empresa->email ?? 'comercial.delta2024@gmail.com' }}<br>
                    +55 (27) 4042-4157<br>
                    +55 (27) 3109-3265
                </td>
            </tr>
        </table>
    </div>
</div>


    
<div class="container">
    {{-- DATA E LOCAL --}}
    <div class="center" style="margin-top: 20px; font-weight: bold;">
        {{ $empresa->cidade ?? 'Serra' }}, {{ $orcamento->created_at->format('d/m/Y') }}
    </div>

    {{-- ASSINATURAS --}}
    <table class="signatures">
        <tr>
            <td class="signature-box">
                @if(isset($empresa->assinatura_digital))
                    <img src="{{ public_path('storage/'.$empresa->assinatura_digital) }}" style="height: 40px;"><br>
                @else
                    <div style="height: 40px;"></div>
                @endif
                <div class="signature-line"></div>
                <span class="label">{{ $empresa->nome_fantasia ?? $empresa->nome }}</span><br>
                Diretor Comercial
            </td>
            <td width="10%"></td>
            <td class="signature-box">
                <div style="height: 40px;"></div>
                <div class="signature-line"></div>
                <span class="label">{{ $orcamento->cliente->nome ?? 'Cliente' }}</span><br>
                CNPJ {{ $orcamento->cliente->cnpj ?? '-' }}
            </td>
        </tr>
    </table>

</div>

</body>
</html>
