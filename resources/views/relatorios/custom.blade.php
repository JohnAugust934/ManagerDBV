<!DOCTYPE html>
<html>

<head>
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            color: #333;
        }

        .header h2 {
            margin: 0;
            font-size: 14px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #f3f3f3;
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }

        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ Auth::user()->club->nome ?? 'Clube de Desbravadores' }}</h1>
        <h2>{{ $titulo }}</h2>
        <p style="font-size: 10px; color: #999;">Gerado em: {{ date('d/m/Y H:i') }} por {{ Auth::user()->name }}</p>
    </div>

    @if(count($dados) > 0)
    <table>
        <thead>
            <tr>
                @foreach($colunas as $col)
                <th>{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($dados as $linha)
            <tr>
                @foreach($linha as $valor)
                <td>{{ $valor }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 20px; text-align: right;"><strong>Total de Registros:</strong> {{ count($dados) }}</p>
    @else
    <div style="text-align: center; padding: 50px; color: #666;">
        Nenhum registro encontrado com os filtros selecionados.
    </div>
    @endif

    <div class="footer">
        Sistema de Gestão Desbravadores Manager
    </div>
</body>

</html>