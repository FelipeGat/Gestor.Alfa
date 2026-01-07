<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Redefinição de Senha</title>
</head>

<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial, Helvetica, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:20px 0;">
        <tr>
            <td align="center">

                <!-- CONTAINER -->
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.05);">

                    <!-- HEADER -->
                    <tr>
                        <td align="center" style="background-color:#1d4ed8; padding:20px;">
                            <img src="{{ asset('images/logo.svg') }}" alt="Logo" style="max-width:180px;">
                        </td>
                    </tr>

                    <!-- CONTEÚDO -->
                    <tr>
                        <td style="padding:30px; color:#111827;">

                            <h2 style="margin-top:0; color:#1f2937;">
                                Redefinição de Senha
                            </h2>

                            <p style="font-size:15px; line-height:1.6;">
                                Olá {{ $user->name ?? 'usuário' }},
                            </p>

                            <p style="font-size:15px; line-height:1.6;">
                                Recebemos uma solicitação para redefinir a senha da sua conta.
                                Para continuar, clique no botão abaixo:
                            </p>

                            <!-- BOTÃO -->
                            <p style="text-align:center; margin:30px 0;">
                                <a href="{{ $url }}" style="
                               background-color:#1d4ed8;
                               color:#ffffff;
                               padding:12px 24px;
                               text-decoration:none;
                               font-weight:bold;
                               border-radius:6px;
                               display:inline-block;">
                                    Redefinir Senha
                                </a>
                            </p>

                            <p style="font-size:14px; line-height:1.6; color:#374151;">
                                Se você não solicitou a redefinição de senha, basta ignorar este e-mail.
                                Sua conta continuará segura.
                            </p>

                            <p style="font-size:14px; margin-top:30px;">
                                Atenciosamente,<br>
                                <strong>Equipe Soluções</strong>
                            </p>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td align="center"
                            style="background-color:#f9fafb; padding:15px; font-size:12px; color:#6b7280;">
                            © {{ date('Y') }} Alfa Soluções. Todos os direitos reservados.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>