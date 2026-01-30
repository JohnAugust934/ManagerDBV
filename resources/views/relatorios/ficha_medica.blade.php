<!DOCTYPE html>
<html>

<head>
    <title>Ficha Médica - {{ $desbravador->nome }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        h1,
        h2 {
            text-align: center;
            text-transform: uppercase;
        }

        .box {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 15px;
        }

        .label {
            font-weight: bold;
            background-color: #eee;
            padding: 2px 5px;
            display: inline-block;
            margin-bottom: 5px;
        }

        .row {
            width: 100%;
            display: table;
            margin-bottom: 5px;
        }

        .col {
            display: table-cell;
            padding-right: 10px;
        }

        .alert {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h1>Ficha Médica de Emergência</h1>
    <h2>{{ Auth::user()->club->nome ?? 'Clube de Desbravadores' }}</h2>
    <hr>

    <div class="box">
        <div class="label">DADOS PESSOAIS</div>
        <div class="row">
            <div class="col"><strong>Nome:</strong> {{ $desbravador->nome }}</div>
            <div class="col"><strong>Nascimento:</strong> {{ $desbravador->data_nascimento->format('d/m/Y') }} ({{ \Carbon\Carbon::parse($desbravador->data_nascimento)->age }} anos)</div>
        </div>
        <div class="row">
            <div class="col"><strong>Unidade:</strong> {{ $desbravador->unidade->nome ?? '-' }}</div>
            <div class="col"><strong>Sexo:</strong> {{ $desbravador->sexo == 'M' ? 'Masculino' : 'Feminino' }}</div>
        </div>
    </div>

    <div class="box">
        <div class="label">CONTATO DE EMERGÊNCIA</div>
        <div class="row">
            <div class="col"><strong>Responsável Legal:</strong> {{ $desbravador->nome_responsavel }}</div>
        </div>
        <div class="row">
            <div class="col"><strong>Telefone (Principal):</strong> {{ $desbravador->telefone_responsavel }}</div>
            <div class="col"><strong>Telefone (Desbravador):</strong> {{ $desbravador->telefone ?? '-' }}</div>
        </div>
        <div class="row">
            <div class="col"><strong>Endereço:</strong> {{ $desbravador->endereco }}</div>
        </div>
    </div>

    <div class="box" style="border: 2px solid red;">
        <div class="label" style="background: red; color: white;">INFORMAÇÕES CLÍNICAS</div>

        <div class="row" style="margin-top: 10px;">
            <div class="col">
                <strong>Tipo Sanguíneo:</strong>
                <span style="font-size: 16px; font-weight: bold;">{{ $desbravador->tipo_sanguineo ?? 'Não informado' }}</span>
            </div>
            <div class="col">
                <strong>Cartão SUS:</strong>
                <span style="font-size: 14px;">{{ $desbravador->numero_sus ?? '---' }}</span>
            </div>
        </div>

        <div style="margin-top: 10px;">
            <strong>Alergias:</strong><br>
            @if($desbravador->alergias)
            <span class="alert">{{ $desbravador->alergias }}</span>
            @else
            Nenhuma alergia conhecida.
            @endif
        </div>

        <div style="margin-top: 10px;">
            <strong>Medicamentos de Uso Contínuo:</strong><br>
            @if($desbravador->medicamentos_continuos)
            <span class="alert">{{ $desbravador->medicamentos_continuos }}</span>
            @else
            Nenhum.
            @endif
        </div>

        <div style="margin-top: 10px;">
            <strong>Plano de Saúde:</strong> {{ $desbravador->plano_saude ?? 'Não possui particular' }}
        </div>
    </div>

    <div class="box">
        <div class="label">AUTORIZAÇÃO MÉDICA</div>
        <p style="text-align: justify;">
            Em caso de emergência, se não for possível contatar os responsáveis listados acima, autorizo os líderes do clube ou profissionais de saúde a tomarem as medidas necessárias, incluindo hospitalização, cirurgias e administração de medicamentos.
        </p>
        <br><br><br>
        <div style="text-align: center;">
            _________________________________________________________<br>
            Assinatura do Responsável
        </div>
        <div style="text-align: center; margin-top: 5px;">Data: ____/____/________</div>
    </div>
</body>

</html>