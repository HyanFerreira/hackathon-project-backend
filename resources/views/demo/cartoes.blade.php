<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 8mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; margin: 0; color: #111827; }

        table { width: 100%; border-collapse: collapse; }
        tr { page-break-inside: avoid; }
        td { width: 33.33%; padding: 5px; vertical-align: top; }

        .card {
            border: 2px dashed #9751e5;
            border-radius: 10px;
            padding: 8px 6px 10px;
            text-align: center;
            height: 300px;
        }
        .marca { margin: 2px 0 5px; }
        .marca .logo { height: 34px; vertical-align: middle; }
        .conquista { width: 60px; height: 60px; margin: 2px 0 0; }
        .msg { font-size: 9px; font-weight: bold; color: #7335bf; margin: 2px 6px 4px; line-height: 1.25; }
        .qr { margin: 2px 0; }
        .qr img { width: 104px; height: 104px; }
        .aluno-nome { font-size: 11px; color: #111827; margin-bottom: 2px; }
        .codigo-label { font-size: 7px; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px; }
        .codigo {
            display: inline-block;
            font-size: 19px; font-weight: bold; letter-spacing: 3px;
            color: #7335bf; background: #f7f2fe;
            border-radius: 6px; padding: 3px 10px; margin-top: 2px;
        }
    </style>
</head>
<body>
    <table>
        @foreach ($alunos->chunk(3) as $linha)
            <tr>
                @foreach ($linha as $aluno)
                    <td>
                        <div class="card">
                            <div class="marca">
                                <img class="logo" src="{{ $logo }}" alt="Paideia">
                            </div>
                            @if (! empty($aluno['conquista']))
                                <img class="conquista" src="{{ $aluno['conquista'] }}" alt="conquista">
                            @endif
                            <div class="msg">Você é o aluno!<br>Escaneie o QR code para jogar.</div>
                            <div class="qr">
                                <img src="{{ $aluno['qr'] }}" alt="QR de acesso">
                            </div>
                            <div class="aluno-nome">{{ $aluno['nome'] }}</div>
                            <div class="codigo-label">seu código</div>
                            <div class="codigo">{{ $aluno['codigo'] }}</div>
                        </div>
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>
</body>
</html>
