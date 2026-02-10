<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Matriz de Defesa</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 150px; }
        h1 { margin: 10px 0; font-size: 16pt; }
        .info-box { border: 2px solid #333; padding: 15px; margin: 20px 0; }
        .info-row { margin: 8px 0; }
        .label { font-weight: bold; display: inline-block; width: 200px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #333; padding: 10px; }
        th { background-color: #e0e0e0; font-weight: bold; text-align: left; }
        .criterios { min-height: 80px; }
        .assinaturas { margin-top: 50px; }
        .assinatura-box { display: inline-block; width: 45%; text-align: center; margin: 20px 0; }
        .linha-assinatura { border-top: 1px solid #000; margin-top: 50px; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>MATRIZ DA DEFESA DO CANDIDATO</h1>
        <p>{{ $banca->tcc->curso->departamento->instituicao->nome }}</p>
        <p>{{ $banca->tcc->curso->nome }}</p>
    </div>

    <div class="info-box">
        <div class="info-row">
            <span class="label">NOME DO CANDIDATO:</span>
            {{ $tcc->aluno->usuario->nome_completo }}
        </div>
        <div class="info-row">
            <span class="label">MATRÍCULA:</span>
            {{ $tcc->aluno->matricula }}
        </div>
        <div class="info-row">
            <span class="label">CURSO:</span>
            {{ $tcc->curso->nome }} ({{ $tcc->curso->codigo }})
        </div>
        <div class="info-row">
            <span class="label">ORIENTADOR:</span>
            {{ $tcc->orientador->usuario->nome_completo ?? 'N/A' }}
        </div>
    </div>

    <h3>AVALIAÇÃO DOS JURIS</h3>
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%;"></th>
                <th style="width: 25%;">Critério</th>
                <th style="width: 15%;">Data</th>
                <th style="width: 55%;">Observações</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1.</td>
                <td><strong>Apresentação (Prof.)</strong></td>
                <td>{{ $banca->data_agendada->format('d/m/Y') }}</td>
                <td style="height: 40px;"></td>
            </tr>
            <tr>
                <td>2.</td>
                <td><strong>Metodologia (Prof.)</strong></td>
                <td></td>
                <td style="height: 40px;"></td>
            </tr>
            <tr>
                <td>3.</td>
                <td><strong>Conteúdo (Prof.)</strong></td>
                <td></td>
                <td style="height: 40px;"></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;"><strong>APRECIAÇÃO GERAL</strong></td>
                <td colspan="2" style="height: 40px;"></td>
            </tr>
        </tbody>
    </table>

    <h3>RECOMENDAÇÃO DOS JURIS</h3>
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%;"></th>
                <th style="width: 95%;">Membro</th>
            </tr>
        </thead>
        <tbody>
            @foreach($banca->membros as $index => $membro)
            <tr>
                <td>{{ $index + 1 }}.</td>
                <td style="height: 100px;">
                    <strong>{{ $membro->usuario->nome_completo }}</strong> - {{ $membro->getTipoFormatado() }}<br>
                    @if($membro->instituicao_externa)
                        <small>{{ $membro->instituicao_externa }}</small>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="assinaturas">
        <h3>ASSINATURA DOS JÚRIS</h3>
        <div style="margin-top: 40px;">
            @foreach($banca->membros as $membro)
            <div class="assinatura-box">
                <div class="linha-assinatura">
                    {{ $membro->usuario->nome_completo }}<br>
                    <small>{{ $membro->getTipoFormatado() }}</small>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div style="margin-top: 50px; text-align: center; font-size: 9pt;">
        <p>Gerado em {{ $data_geracao->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
