<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Convite {{ config('app.name') }}</title>
</head>
<body style="margin:0;padding:24px;background:#f4f4f5;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 10px 30px rgba(15,23,42,0.08);">
        <div style="background:linear-gradient(135deg,#0f172a,#dc2626);padding:28px 24px;text-align:center;">
            <div style="font-size:13px;letter-spacing:1.5px;text-transform:uppercase;color:#fecaca;margin-bottom:8px;">Acesso ao sistema</div>
            <h1 style="margin:0;color:#ffffff;font-size:28px;">{{ config('app.name') }}</h1>
        </div>

        <div style="padding:32px 28px;">
            <p style="margin:0 0 16px;font-size:22px;font-weight:bold;color:#111827;">Olá!</p>

            <p style="margin:0 0 14px;line-height:1.7;font-size:16px;">
                Você recebeu um convite para acessar o sistema do clube com o perfil
                <strong>{{ strtoupper($invitation->role) }}</strong>.
            </p>

            <p style="margin:0 0 22px;line-height:1.7;font-size:16px;">
                Para concluir seu cadastro com segurança, clique no botão abaixo:
            </p>

            <div style="text-align:center;margin:28px 0 30px;">
                <a href="{{ $url }}" style="display:inline-block;background:#dc2626;color:#ffffff;text-decoration:none;padding:14px 26px;border-radius:999px;font-size:16px;font-weight:bold;">
                    Aceitar convite
                </a>
            </div>

            <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:18px 16px;margin-bottom:22px;">
                <p style="margin:0 0 10px;font-size:14px;color:#374151;">
                    <strong>Se o botão não abrir:</strong> copie e cole este link no navegador.
                </p>
                <a href="{{ $url }}" style="font-size:14px;word-break:break-all;color:#2563eb;text-decoration:none;">{{ $url }}</a>
            </div>

            <div style="background:#fff7ed;border-left:4px solid #f97316;padding:14px 16px;border-radius:8px;">
                <p style="margin:0;line-height:1.6;font-size:14px;color:#7c2d12;">
                    Este convite é pessoal e expira em 7 dias. Se você não reconhece este envio, basta ignorar este e-mail.
                </p>
            </div>
        </div>
    </div>
</body>
</html>


