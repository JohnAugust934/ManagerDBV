<!DOCTYPE html>
<html>

<head>
    <title>Carteirinha - {{ $desbravador->nome }}</title>
    <style>
        body {
            font-family: sans-serif;
        }

        .page {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Tamanho padrão CR80 (Cartão de Crédito) */
        .card {
            width: 323px;
            /* ~8.5cm */
            height: 204px;
            /* ~5.4cm */
            border: 2px solid #000;
            border-radius: 10px;
            padding: 10px;
            margin: 20px auto;
            position: relative;
            background-color: #f8f9fa;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #eab308;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 14px;
            text-transform: uppercase;
            color: #1e3a8a;
        }

        .header h2 {
            margin: 0;
            font-size: 10px;
            color: #555;
        }

        .content {
            display: table;
            width: 100%;
        }

        .photo-area {
            display: table-cell;
            width: 70px;
            vertical-align: top;
        }

        .photo-box {
            width: 60px;
            height: 80px;
            border: 1px solid #999;
            background: #ddd;
            text-align: center;
            line-height: 80px;
            font-size: 9px;
            color: #666;
        }

        .info-area {
            display: table-cell;
            vertical-align: top;
            padding-left: 10px;
        }

        .label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 1px;
        }

        .value {
            font-size: 11px;
            font-weight: bold;
            color: #000;
            margin-bottom: 6px;
        }

        .footer {
            position: absolute;
            bottom: 5px;
            left: 10px;
            right: 10px;
            text-align: center;
            font-size: 8px;
            color: #1e3a8a;
            font-weight: bold;
            border-top: 1px solid #ccc;
            padding-top: 2px;
        }

        .logo-dbv {
            position: absolute;
            top: 5px;
            left: 5px;
            width: 25px;
        }
    </style>
</head>

<body>

    <div class="card">
        <div class="header">
            <h1>Clube de Desbravadores</h1>
            <h2>{{ Auth::user()->club->nome ?? 'Nome do Clube' }}</h2>
        </div>

        <div class="content">
            <div class="photo-area">
                <div class="photo-box">FOTO 3x4</div>
            </div>
            <div class="info-area">
                <div class="label">Nome</div>
                <div class="value">{{ Str::limit($desbravador->nome, 25) }}</div>

                <div class="label">Unidade</div>
                <div class="value">{{ $desbravador->unidade->nome ?? 'Sem Unidade' }}</div>

                <table width="100%">
                    <tr>
                        <td>
                            <div class="label">Nascimento</div>
                            <div class="value">{{ $desbravador->data_nascimento->format('d/m/Y') }}</div>
                        </td>
                        <td>
                            <div class="label">Tipo Sang.</div>
                            <div class="value">{{ $desbravador->tipo_sanguineo ?? '-' }}</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="footer">
            "A mensagem do advento a todo o mundo em minha geração"
        </div>
    </div>

    <div class="card">
        <div class="header">
            <h1>Dados de Emergência</h1>
        </div>
        <div style="font-size: 10px;">
            <p><strong>Responsável:</strong> {{ Str::limit($desbravador->nome_responsavel, 30) }}</p>
            <p><strong>Telefone:</strong> {{ $desbravador->telefone_responsavel }}</p>
            <p><strong>SUS:</strong> {{ $desbravador->numero_sus ?? 'Não informado' }}</p>
            <p><strong>Plano de Saúde:</strong> {{ $desbravador->plano_saude ?? 'Não possui' }}</p>
            <p><strong>Alergias:</strong> {{ Str::limit($desbravador->alergias ?? 'Nenhuma', 40) }}</p>
        </div>
        <div class="footer">
            Válido para o ano de {{ date('Y') }}
        </div>
    </div>

</body>

</html>