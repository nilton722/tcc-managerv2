<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Lista de Presença - Banca Examinadora</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 16pt; }
        .info-box { border: 2px solid #333; padding: 15px; margin: 20px 0; background: #f9f9f9; }
        .info-row { margin: 8px 0; }
        .label { font-weight: bold; display: inline-block; width: 150px; }
        table { width: 100%; border-collapse: collapse; margin: 30px 0; }
        th, td { border: 1px solid #333; padding: 12px; }
        th { background-color: #4a5568; color: white; font-weight: bold; }
        .assinatura-col { width: 40%; }
        .numero-col { width: 5%; text-align: center; }
        .observacoes { margin-top: 30px; border: 1px solid #333; padding: 15px; min-height: 100px; }
        .rodape { margin-top: 30px; font-size: 9pt; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LISTA DE PRESENÇA - BANCA EXAMINADORA</h1>
        <p>{{ $tcc->curso->departamento->instituicao->nome }}</p>
    </div>

    <div class="info-box">
        <div class="info-row">
            <span class="label">TIPO DE DEFESA:</span>
            {{ $banca->tipo_banca === 'QUALIFICACAO' ? 'Qualificação' : 'Defesa Final' }}
        </div>
        <div class="info-row">
            <span class="label">CANDIDATO:</span>
            {{ $tcc->aluno->usuario->nome_completo }}
        </div>
        <div class="info-row">
            <span class="label">MATRÍCULA:</span>
            {{ $tcc->aluno->matricula }}
        </div>
        <div class="info-row">
            <span class="label">CURSO:</span>
            {{ $tcc->curso->nome }}
        </div>
        <div class="info-row">
            <span class="label">TÍTULO DO TRABALHO:</span>
            {{ $tcc->titulo }}
        </div>
        <div class="info-row">
            <span class="label">DATA E HORA:</span>
            {{ $banca->data_agendada->format('d/m/Y') }} às {{ $banca->data_agendada->format('H:i') }}
        </div>
        <div class="info-row">
            <span class="label">LOCAL:</span>
            {{ $banca->local ?: ($banca->formato === 'REMOTA' ? 'Videoconferência' : 'A definir') }}
        </div>
        <div class="info-row">
            <span class="label">FORMATO:</span>
            {{ $banca->getFormatoFormatado() }}
        </div>
    </div>

    <h3>MEMBROS DA BANCA EXAMINADORA</h3>

    <table>
        <thead>
            <tr>
                <th class="numero-col">Nº</th>
                <th>Nome Completo</th>
                <th>Função na Banca</th>
                <th>Instituição</th>
                <th class="assinatura-col">Assinatura</th>
            </tr>
        </thead>
        <tbody>
            @foreach($banca->membros as $index => $membro)
            <tr style="height: 50px;">
                <td class="numero-col">{{ $index + 1 }}</td>
                <td>{{ $membro->usuario->nome_completo }}</td>
                <td>{{ $membro->getTipoFormatado() }}</td>
                <td>{{ $membro->instituicao_externa ?: $tcc->curso->departamento->instituicao->sigla }}</td>
                <td class="assinatura-col"></td>
            </tr>
            @endforeach
            
            <!-- Linha extra para o candidato -->
            <tr style="height: 50px; background-color: #f3f4f6;">
                <td class="numero-col">{{ $banca->membros->count() + 1 }}</td>
                <td><strong>{{ $tcc->aluno->usuario->nome_completo }}</strong></td>
                <td><strong>CANDIDATO</strong></td>
                <td>{{ $tcc->curso->departamento->instituicao->sigla }}</td>
                <td class="assinatura-col"></td>
            </tr>
        </tbody>
    </table>

    <div class="observacoes">
        <strong>OBSERVAÇÕES:</strong>
        <br><br>
        ___________________________________________________________________________
        <br><br>
        ___________________________________________________________________________
        <br><br>
        ___________________________________________________________________________
    </div>

    <div class="rodape">
        <p>Documento gerado em {{ $data_geracao->format('d/m/Y H:i') }}</p>
        <p>Sistema de Gestão de TCCs</p>
    </div>
</body>
</html>
