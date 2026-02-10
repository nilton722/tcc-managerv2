<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ata de Defesa</title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12pt; margin: 30px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { max-width: 120px; }
        h1 { font-size: 16pt; margin: 15px 0; text-transform: uppercase; }
        .instituicao { font-size: 14pt; font-weight: bold; }
        .numero-ata { text-align: right; font-size: 10pt; color: #666; margin-bottom: 20px; }
        .texto-ata { text-align: justify; margin: 20px 0; }
        .paragrafo { margin: 15px 0; text-indent: 50px; }
        .destaque { font-weight: bold; }
        .resultado-box { border: 2px solid #333; padding: 15px; margin: 20px 0; background: #f5f5f5; }
        .resultado-aprovado { border-color: #059669; background: #d1fae5; }
        .resultado-reprovado { border-color: #dc2626; background: #fee2e2; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #333; padding: 8px; }
        th { background-color: #e5e7eb; font-weight: bold; }
        .assinaturas { margin-top: 50px; }
        .assinatura-linha { border-top: 1px solid #000; margin: 60px 20px 5px; }
        .assinatura-nome { text-align: center; font-size: 10pt; }
        .rodape { margin-top: 40px; text-align: center; font-size: 10pt; }
    </style>
</head>
<body>
    <div class="numero-ata">
        ATA Nº {{ str_pad($banca->id, 6, '0', STR_PAD_LEFT) }}/{{ $banca->data_agendada->format('Y') }}
    </div>

    <div class="header">
        <div class="instituicao">{{ $tcc->curso->departamento->instituicao->nome }}</div>
        <div>{{ $tcc->curso->departamento->nome }}</div>
        <h1>Ata de Defesa de {{ $banca->tipo_banca === 'QUALIFICACAO' ? 'Qualificação' : 'Trabalho de Conclusão de Curso' }}</h1>
    </div>

    <div class="texto-ata">
        <p class="paragrafo">
            Aos <span class="destaque">{{ $banca->data_agendada->format('d') }}</span> dias do mês de 
            <span class="destaque">{{ ucfirst($banca->data_agendada->locale('pt_BR')->translatedFormat('F')) }}</span> de 
            <span class="destaque">{{ $banca->data_agendada->format('Y') }}</span>, 
            às <span class="destaque">{{ $banca->data_agendada->format('H:i') }}</span> horas, 
            na {{ $banca->local ?: 'sala virtual' }}, 
            reuniu-se a Banca Examinadora composta pelos professores:
        </p>

        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Função</th>
                    <th>Instituição</th>
                </tr>
            </thead>
            <tbody>
                @foreach($banca->membros as $membro)
                <tr>
                    <td>{{ $membro->usuario->nome_completo }}</td>
                    <td>{{ $membro->getTipoFormatado() }}</td>
                    <td>{{ $membro->getInstituicao() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p class="paragrafo">
            Para proceder à avaliação do Trabalho de Conclusão de Curso intitulado 
            <span class="destaque">"{{ $tcc->titulo }}"</span>, 
            de autoria de <span class="destaque">{{ $tcc->aluno->usuario->nome_completo }}</span>, 
            matrícula <span class="destaque">{{ $tcc->aluno->matricula }}</span>, 
            do curso de <span class="destaque">{{ $tcc->curso->nome }}</span>, 
            sob orientação de <span class="destaque">{{ $tcc->orientador->usuario->nome_completo ?? 'não informado' }}</span>.
        </p>

        <p class="paragrafo">
            O candidato apresentou seu trabalho em aproximadamente 
            <span class="destaque">{{ $banca->tipo_banca === 'QUALIFICACAO' ? '30' : '40' }}</span> minutos, 
            seguido de arguição pelos membros da banca.
        </p>

        @if($banca->avaliacoes->count() > 0)
        <h3>Avaliações:</h3>
        <table>
            <thead>
                <tr>
                    <th>Avaliador</th>
                    <th>Nota</th>
                    <th>Resultado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($banca->avaliacoes as $avaliacao)
                <tr>
                    <td>{{ $avaliacao->membroBanca->usuario->nome_completo }}</td>
                    <td>{{ number_format($avaliacao->nota, 2, ',', '.') }}</td>
                    <td>{{ $avaliacao->getResultadoFormatado() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div class="resultado-box {{ $resultado['resultado'] === 'APROVADO' ? 'resultado-aprovado' : ($resultado['resultado'] === 'REPROVADO' ? 'resultado-reprovado' : '') }}">
            <h3 style="margin-top: 0;">RESULTADO FINAL</h3>
            <p><strong>Média das Notas:</strong> {{ number_format($resultado['media_nota'] ?? 0, 2, ',', '.') }}</p>
            <p><strong>Resultado:</strong> 
                @if($resultado['resultado'] === 'APROVADO')
                    <span style="color: #059669; font-weight: bold;">✓ APROVADO</span>
                @elseif($resultado['resultado'] === 'APROVADO_COM_RESSALVAS')
                    <span style="color: #f59e0b; font-weight: bold;">⚠ APROVADO COM RESSALVAS</span>
                @else
                    <span style="color: #dc2626; font-weight: bold;">✗ REPROVADO</span>
                @endif
            </p>
        </div>

        @if($resultado['resultado'] === 'APROVADO_COM_RESSALVAS')
        <p class="paragrafo">
            O candidato deverá fazer as correções solicitadas pela banca no prazo de 
            <span class="destaque">30 (trinta) dias</span> a contar da data desta defesa.
        </p>
        @endif

        <p class="paragrafo">
            Nada mais havendo a tratar, foi lavrada a presente ata que, após lida e aprovada, 
            será assinada pelos membros da Banca Examinadora.
        </p>
    </div>

    <div class="assinaturas">
        @foreach($banca->membros as $membro)
        <div>
            <div class="assinatura-linha"></div>
            <div class="assinatura-nome">
                <strong>{{ $membro->usuario->nome_completo }}</strong><br>
                {{ $membro->getTipoFormatado() }}
            </div>
        </div>
        @endforeach
    </div>

    <div class="rodape">
        <p>{{ $tcc->curso->departamento->instituicao->endereco['cidade'] ?? 'Cidade' }}, 
           {{ $banca->data_agendada->format('d') }} de 
           {{ ucfirst($banca->data_agendada->locale('pt_BR')->translatedFormat('F')) }} de 
           {{ $banca->data_agendada->format('Y') }}</p>
    </div>
</body>
</html>
