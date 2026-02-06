<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">

    <style>
        @page {
            margin: 0.3cm 0.5cm 0.5cm 0.5cm;
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
            margin-bottom: 5px;
        }

        .header img {
            height: 160px;
            margin: 0 auto;
        }

        .title-bar {
            background-color: #1d4ed8;
            color: white;
            padding: 8px 15px;
            margin-bottom: 12px;
        }

        .title-bar h2 {
            margin: 0;
            font-size: 15px;
        }

        .title-bar p {
            margin: 2px 0 0 0;
            font-size: 12px;
        }

        .section-title {
            background-color: #f4f4f4;
            color: #1d4ed8;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 11px;
            margin-top: 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        table.info-grid {
            width: 100%;
            font-size: 10px;
        }

        table.info-grid td {
            vertical-align: top;
            padding: 2px 0;
        }

        .label {
            font-weight: bold;
            color: #1d4ed8;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .totals-table td {
            padding: 6px 10px;
            font-size: 11px;
        }

        .total-row {
            background-color: #1d4ed8;
            color: white;
            font-weight: bold;
            font-size: 13px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }
    </style>
</head>

<body>
    @php
    $empresa = $cobranca->orcamento->empresa ?? $cobranca->contaFixa->empresa ?? null;
    @endphp

    <div class="container">

        {{-- HEADER --}}
        <div class="header">
            @if($empresa->logo)
            <img src="{{ public_path('images/logo/'.$empresa->logo) }}">
            @endif
        </div>

        {{-- TITLE BAR --}}
        <div class="title-bar">
            <h2>RECIBO DE PAGAMENTO</h2>
            <p>Nº {{ str_pad($cobranca->id, 6, '0', STR_PAD_LEFT) }}</p>
        </div>

        {{-- CLIENTE --}}
        <div class="section-title">Dados do Cliente</div>
        <table class="info-grid">
            <tr>
                <td width="60%">
                    <span class="label">CLIENTE:</span>
                    {{ strtoupper($cobranca->cliente?->nome_fantasia ?? $cobranca->cliente?->razao_social ?? '-') }}<br>

                    @if($cobranca->cliente?->cpf_cnpj)
                    <span class="label">CPF/CNPJ:</span> {{ $cobranca->cliente->cpf_cnpj }}
                    @endif
                </td>
                <td width="40%" class="right">
                    <span class="label">DATA DO PAGAMENTO:</span>
                    {{ optional($cobranca->pago_em)->format('d/m/Y H:i') }}<br>

                    <span class="label">VENCIMENTO:</span>
                    {{ $cobranca->data_vencimento->format('d/m/Y') }}
                </td>
            </tr>
        </table>

        {{-- PAGAMENTO --}}
        <div class="section-title">Dados do Pagamento</div>
        <table class="info-grid">
            <tr>
                <td width="60%">
                    <span class="label">DESCRIÇÃO:</span><br>
                    {{ $cobranca->descricao }}<br>

                    @if($cobranca->parcela_num)
                    <span class="label">PARCELA:</span>
                    {{ $cobranca->parcela_num }}/{{ $cobranca->parcelas_total }}
                    @endif
                </td>

                <td width="40%" class="right">
                    <span class="label">FORMA DE PAGAMENTO:</span><br>
                    {{ strtoupper(str_replace('_',' ',$cobranca->forma_pagamento)) }}<br><br>

                    <span class="label">BANCO / CONTA:</span><br>
                    {{ $cobranca->contaFinanceira?->nome ?? '-' }}
                </td>
            </tr>
        </table>

        {{-- VALOR --}}
        <table class="totals-table">
            <tr class="total-row">
                <td class="right" width="80%">VALOR RECEBIDO:</td>
                <td class="right" width="20%">
                    R$ {{ number_format($cobranca->valor, 2, ',', '.') }}
                </td>
            </tr>
        </table>

        {{-- FOOTER --}}
        <div style="margin-top: 25px; text-align: center; font-size: 9px;">
            <p>
                {{ $empresa->razao_social }} — CNPJ {{ $empresa->cnpj }}<br>
                {{ $empresa->endereco }}<br>
                Documento gerado eletronicamente em {{ now()->format('d/m/Y H:i') }}
            </p>
        </div>

    </div>

    <script>
        // Auto imprimir como orçamento
        window.onload = function() {
            window.print();
        }
    </script>

</body>

</html>