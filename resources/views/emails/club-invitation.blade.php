<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Convite DBV Manager</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f4f5; padding: 20px; color: #333;">
    <div
        style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="background-color: #dc2626; padding: 20px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">DBV Manager</h1>
        </div>

        <div style="padding: 30px;">
            <h2 style="margin-top: 0; color: #1f2937;">Olá!</h2>
            <p style="line-height: 1.6; font-size: 16px;">
                Você foi convidado(a) para fazer parte do sistema de gestão do seu clube como
                <strong>{{ strtoupper($invitation->role) }}</strong>.
            </p>

            <p style="line-height: 1.6; font-size: 16px;">
                Para aceitar o convite e criar sua conta com segurança, clique no botão abaixo:
            </p>

            <div style="text-align: center; margin: 35px 0;">
                <a href="{{ $url }}"
                    style="background-color: #dc2626; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; display: inline-block;">
                    Aceitar Convite e Cadastrar
                </a>
            </div>

            <div style="background-color: #f3f4f6; padding: 15px; border-radius: 6px; font-size: 14px; color: #4b5563;">
                <p style="margin-top: 0;"><strong>O botão não funcionou?</strong> Copie e cole o link abaixo no seu
                    navegador:</p>
                <a href="{{ $url }}" style="word-break: break-all; color: #2563eb;">{{ $url }}</a>
            </div>

            <p style="font-size: 13px; color: #9ca3af; margin-top: 30px; text-align: center;">
                Este link é exclusivo para você e expira em 7 dias.<br>Se você desconhece este convite, basta ignorar
                este e-mail.
            </p>
        </div>
    </div>
</body>

</html>
