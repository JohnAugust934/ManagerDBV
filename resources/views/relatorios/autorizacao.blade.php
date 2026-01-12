<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Autorização de Saída</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .titulo {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .texto {
            font-size: 14px;
            line-height: 1.6;
            text-align: justify;
            margin-bottom: 40px;
        }

        .assinaturas {
            width: 100%;
            margin-top: 100px;
        }

        .linha-assinatura {
            border-top: 1px solid #000;
            width: 40%;
            margin: 0 auto;
            text-align: center;
            padding-top: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="titulo">Clube de Desbravadores</div>
        <div>Autorização de Saída e Participação em Evento</div>
    </div>

    <div class="texto">
        <p>
            Eu, responsável legal pelo(a) desbravador(a) <strong>{{ $desbravador->nome }}</strong>,
            nascido(a) em {{ $desbravador->data_nascimento->format('d/m/Y') }},
            membro da Unidade <strong>{{ $desbravador->unidade->nome ?? 'S/ Unidade' }}</strong>,
            autorizo a sua participação nas atividades do clube.
        </p>
        <p>
            Declaro estar ciente de que as atividades envolvem requisitos físicos e recreativos,
            e autorizo a diretoria do clube a tomar as decisões necessárias em casos de emergência médica,
            caso eu não possa ser contatado imediatamente.
        </p>
        <p>
            Data: _____ / _____ / __________
        </p>
    </div>

    <table class="assinaturas">
        <tr>
            <td style="text-align: center;">
                <div class="linha-assinatura">Assinatura do Responsável</div>
            </td>
            <td style="text-align: center;">
                <div class="linha-assinatura">Diretor do Clube</div>
            </td>
        </tr>
    </table>
</body>

</html>