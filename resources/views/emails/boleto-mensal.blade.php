<!DOCTYPE html>
<html>

<body style="font-family: Arial, sans-serif">

    <p>Olá, {{ $boleto->cliente->nome }}!</p>

    <p>
        Segue em anexo o Faturamento referentes ao contrato mensal de
        <strong>{{ str_pad($boleto->mes, 2, '0', STR_PAD_LEFT) }}/{{ $boleto->ano }}</strong>.
    </p>

    <p>
        📅 <strong>Vencimento:</strong>
        {{ $boleto->data_vencimento->format('d/m/Y') }}<br>

        💵 <strong>Valor:</strong>
        R$ {{ number_format($boleto->valor, 2, ',', '.') }}
    </p>

    <p>
        👉 <a href="{{ route('portal.index') }}">
            Clique aqui para acessar o portal e ter acesso e poder baixar o boleto
        </a>
    </p>

    <p>
        Fique à vontade para entrar em contato caso precise de qualquer apoio ou tenha alguma dúvida.
        Estamos por aqui para ajudar!

        Um grande abraço. Se já realizou o pagamento, desconsidere esta mensagem.
    </p>

    <p>
        Atenciosamente,<br>
        <strong>Grupo Soluções</strong><br>
        Setor Administrativo
    </p>

</body>

</html>