<!DOCTYPE html>
<html>

<head>
    <title>Autorização</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .content {
            margin: 20px;
        }

        .signature {
            margin-top: 50px;
            text-align: center;
        }

        .line {
            border-bottom: 1px solid black;
            width: 60%;
            margin: 0 auto;
        }

        .box {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
            background: #f9f9f9;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>AUTORIZAÇÃO DE ATIVIDADE - CLUBE DE DESBRAVADORES</h2>
        <h3>{{ Auth::user()->club->nome ?? 'Clube' }}</h3>
    </div>

    @if(isset($evento))
    <div class="box">
        <strong>Evento:</strong> {{ $evento->nome }} <br>
        <strong>Local:</strong> {{ $evento->local }} <br>
        <strong>Data de Saída:</strong> {{ $evento->data_inicio->format('d/m/Y H:i') }} <br>
        <strong>Previsão de Retorno:</strong> {{ $evento->data_fim ? $evento->data_fim->format('d/m/Y H:i') : '---' }}
    </div>
    @endif

    <div class="content">
        <p>Eu, <strong>{{ $desbravador->nome_responsavel }}</strong>, portador(a) do RG/CPF ______________________,</p>

        <p>AUTORIZO o(a) desbravador(a) <strong>{{ $desbravador->nome }}</strong> (Nasc: {{ $desbravador->data_nascimento->format('d/m/Y') }}) a participar da atividade descrita acima.</p>

        <p>Declaro estar ciente das normas do clube e que, em caso de emergência médica, os líderes estão autorizados a tomar as providências necessárias.</p>

        <br>
        <p><strong>Dados de Saúde:</strong></p>
        <ul>
            <li>Tipo Sanguíneo: {{ $desbravador->tipo_sanguineo ?? 'Não informado' }}</li>
            <li>Alergias: {{ $desbravador->alergias ?? 'Nenhuma' }}</li>
            <li>Medicamentos: {{ $desbravador->medicamentos_continuos ?? 'Nenhum' }}</li>
            <li>SUS: {{ $desbravador->numero_sus ?? '---' }}</li>
            <li>Plano de Saúde: {{ $desbravador->plano_saude ?? 'Não' }}</li>
        </ul>
    </div>

    <br><br><br>

    <div class="signature">
        <div class="line"></div>
        <p>Assinatura do Responsável</p>
        <p>Data: _____/_____/__________</p>
    </div>
</body>

</html>