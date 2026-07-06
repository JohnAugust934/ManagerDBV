<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #1e293b;">
    <div style="background: #002F6C; padding: 24px; border-radius: 12px 12px 0 0;">
        <h1 style="color: #FCD116; margin: 0; font-size: 20px;">{{ $nomeClube }}</h1>
        <p style="color: rgba(255,255,255,0.7); margin: 4px 0 0; font-size: 13px;">Comunicado oficial</p>
    </div>
    <div style="border: 1px solid #e2e8f0; border-top: none; padding: 28px; border-radius: 0 0 12px 12px;">
        <h2 style="font-size: 18px; margin: 0 0 16px;">{{ $comunicado->titulo }}</h2>
        <div style="line-height: 1.7; color: #334155;">
            {!! nl2br(e($comunicado->corpo)) !!}
        </div>
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">
        <p style="font-size: 12px; color: #94a3b8; margin: 0;">
            Este comunicado foi enviado ao responsável de <strong>{{ $nomeDesbravador }}</strong>.
        </p>
    </div>
</body>
</html>
