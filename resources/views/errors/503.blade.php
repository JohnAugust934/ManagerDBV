<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Em manutenção &middot; Desbravadores Manager</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon.ico" sizes="any">

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --azul-escuro: #001D42;
            --azul: #002F6C;
            --vermelho: #D9222A;
            --amarelo: #FCD116;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--azul-escuro);
            color: #f1f5f9;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 24px;
            padding: 32px 20px;
            position: relative;
            -webkit-font-smoothing: antialiased;
        }

        /* ===== Fundo: blobs desfocados ===== */
        .bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .blob {
            position: absolute;
            border-radius: 9999px;
            filter: blur(120px);
        }

        .blob-1 {
            top: -10%;
            right: -5%;
            width: 600px;
            height: 600px;
            background: var(--azul);
            opacity: .6;
            animation: pulse 8s ease-in-out infinite;
        }

        .blob-2 {
            bottom: -10%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: var(--vermelho);
            opacity: .3;
            animation: pulse 10s ease-in-out infinite 2s;
        }

        .blob-3 {
            top: 40%;
            left: 20%;
            width: 400px;
            height: 400px;
            background: var(--amarelo);
            opacity: .12;
            filter: blur(150px);
        }

        .grid-overlay {
            position: absolute;
            inset: 0;
            opacity: .5;
            background-image:
                linear-gradient(rgba(255, 255, 255, .03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .03) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: var(--o, .5);
                transform: scale(1);
            }

            50% {
                opacity: calc(var(--o, .5) * .6);
                transform: scale(1.05);
            }
        }

        /* ===== Card ===== */
        .card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 480px;
            text-align: center;
            padding: 40px 32px;
            border-radius: 32px;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, .5);
            animation: fadeUp .7s cubic-bezier(.16, 1, .3, 1) both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== Logo + engrenagens ===== */
        .gear-wrap {
            position: relative;
            width: 96px;
            height: 96px;
            margin: 0 auto 28px;
        }

        .logo-badge {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 24px;
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .2);
            box-shadow: 0 8px 30px rgba(0, 0, 0, .4);
            padding: 18px;
        }

        .logo-badge img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .gear {
            position: absolute;
            color: var(--amarelo);
            filter: drop-shadow(0 2px 6px rgba(0, 0, 0, .4));
        }

        .gear-a {
            top: -14px;
            right: -14px;
            width: 40px;
            height: 40px;
            animation: spin 6s linear infinite;
        }

        .gear-b {
            bottom: -10px;
            left: -12px;
            width: 30px;
            height: 30px;
            color: var(--vermelho);
            animation: spin 5s linear infinite reverse;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ===== Texto ===== */
        .eyebrow {
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--amarelo);
            margin-bottom: 12px;
        }

        h1 {
            font-size: 28px;
            line-height: 1.2;
            font-weight: 800;
            color: #fff;
            margin-bottom: 14px;
            letter-spacing: -.01em;
        }

        p.lead {
            font-size: 16px;
            line-height: 1.6;
            color: #cbd5e1;
            font-weight: 500;
            max-width: 40ch;
            margin: 0 auto;
        }

        /* ===== Status / rodapé ===== */
        .status {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-top: 28px;
            padding: 9px 18px;
            border-radius: 9999px;
            background: rgba(252, 209, 22, .1);
            border: 1px solid rgba(252, 209, 22, .25);
            font-size: 13px;
            font-weight: 700;
            color: var(--amarelo);
        }

        .dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--amarelo);
            box-shadow: 0 0 0 0 rgba(252, 209, 22, .6);
            animation: ping 1.8s cubic-bezier(0, 0, .2, 1) infinite;
        }

        @keyframes ping {
            0% {
                box-shadow: 0 0 0 0 rgba(252, 209, 22, .5);
            }

            70%,
            100% {
                box-shadow: 0 0 0 10px rgba(252, 209, 22, 0);
            }
        }

        .footer {
            position: relative;
            z-index: 10;
            text-align: center;
            font-size: 12px;
            font-weight: 500;
            color: #94a3b8;
        }

        @media (max-width: 480px) {
            body {
                padding: 24px 16px;
            }

            .card {
                padding: 32px 22px;
                border-radius: 26px;
            }

            h1 {
                font-size: 23px;
            }

            p.lead {
                font-size: 15px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .blob,
            .gear,
            .dot,
            .card {
                animation: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="bg" aria-hidden="true">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
        <div class="grid-overlay"></div>
    </div>

    <main class="card" role="main">
        <div class="gear-wrap">
            <svg class="gear gear-a" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z" />
            </svg>
            <svg class="gear gear-b" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z" />
            </svg>
            <div class="logo-badge">
                <img src="/favicon.svg" alt="Desbravadores Manager">
            </div>
        </div>

        <p class="eyebrow">Desbravadores Manager</p>
        <h1>Estamos em manutenção</h1>
        <p class="lead">
            O sistema está passando por uma atualização rápida para ficar ainda melhor.
            Voltamos em instantes &mdash; obrigado pela paciência!
        </p>

        <div class="status">
            <span class="dot" aria-hidden="true"></span>
            Manutenção em andamento
        </div>
    </main>

    <p class="footer">
        &copy; {{ date('Y') }} ManagerDBV &middot; Todos os direitos reservados.
    </p>
</body>

</html>
