<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Declaração de Orientação</title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12pt; margin: 40px; line-height: 1.8; }
        .header { text-align: center; margin-bottom: 40px; }
        .logo { max-width: 100px; margin-bottom: 15px; }
        .instituicao { font-size: 14pt; font-weight: bold; margin: 5px 0; }
        h1 { font-size: 16pt; margin: 20px 0; text-transform: uppercase; text-decoration: underline; }
        .texto { text-align: justify; margin: 30px 0; }
        .paragrafo { margin: 20px 0; text-indent: 50px; }
        .destaque { font-weight: bold; }
        .info-box { border: 1px solid #333; padding: 15px; margin: 25px 0; background: #f9f9f9; }
        .assinatura { margin-top: 80px; text-align: center; }
        .linha-assinatura { border-top: 1px solid #000; width: 60%; margin: 0 auto; padding-top: 5px; }
        .rodape { margin-top: 50px; text-align: center; font-size: 10pt; }
        .numero-declaracao { text-align: right; font-size: 10pt; margin-bottom: 20px; color: #666; }
    </style>
</head>
<body>
    <div class="numero-declaracao">
        Declaração Nº {{ str_pad($orientacao->id, 6, '0', STR_PAD_LEFT) }}/{{ now()->format('Y') }}
    </div>

    <div class="header">
        @if($tcc->curso->departamento->instituicao->logo_url)
            <img src="{{ $tcc->curso->departamento->instituicao->logo_url }}" class="logo" alt="Logo">
        @endif
        <div class="instituicao">{{ $tcc->curso->departamento->instituicao->nome }}</div>
        <div>{{ $tcc->curso->departamento->nome }}</div>
        <div>{{ $tcc->curso->nome }}</div>
        <h1>Declaração de Orientação</h1>
    </div>

    <div class="texto">
        <p class="paragrafo">
            Declaro, para os devidos fins, que o(a) aluno(a) 
            <span class="destaque">{{ $tcc->aluno->usuario->nome_completo }}</span>, 
            portador(a) da matrícula <span class="destaque">{{ $tcc->aluno->matricula }}</span>, 
            regularmente matriculado(a) no curso de <span class="destaque">{{ $tcc->curso->nome }}</span>, 
            está sob minha orientação {{ $orientacao->tipo_orientacao === 'ORIENTADOR' ? 'principal' : 'de coorientação' }} 
            para desenvolvimento do Trabalho de Conclusão de Curso (TCC).
        </p>

        <div class="info-box">
            <p style="margin: 5px 0;"><strong>Título do Trabalho:</strong></p>
            <p style="margin: 5px 0; padding-left: 20px;">{{ $tcc->titulo }}</p>
            
            <p style="margin: 15px 0 5px 0;"><strong>Tipo de Trabalho:</strong> {{ $tcc->getTipoFormatado() }}</p>
            
            @if($tcc->linhaPesquisa)
            <p style="margin: 5px 0;"><strong>Linha de Pesquisa:</strong> {{ $tcc->linhaPesquisa->nome }}</p>
            @endif
            
            <p style="margin: 5px 0;"><strong>Tipo de Orientação:</strong> {{ $orientacao->getTipoFormatado() }}</p>
            
            <p style="margin: 5px 0;"><strong>Data de Início:</strong> {{ $orientacao->data_inicio->format('d/m/Y') }}</p>
            
            @if($orientacao->data_fim)
            <p style="margin: 5px 0;"><strong>Data de Término:</strong> {{ $orientacao->data_fim->format('d/m/Y') }}</p>
            @else
            <p style="margin: 5px 0;"><strong>Situação:</strong> Orientação em andamento</p>
            @endif
            
            @if($orientacao->getDuracaoEmMeses())
            <p style="margin: 5px 0;"><strong>Duração:</strong> {{ $orientacao->getDuracaoEmMeses() }} meses</p>
            @endif
        </div>

        <p class="paragrafo">
            O desenvolvimento do trabalho encontra-se 
            @if($tcc->status === 'APROVADO' || $tcc->status === 'APROVADO_COM_RESSALVAS')
                <span class="destaque">CONCLUÍDO</span>, tendo sido aprovado em banca de defesa.
            @elseif($tcc->status === 'EM_AVALIACAO' || $tcc->status === 'BANCA_AGENDADA')
                em fase de <span class="destaque">AVALIAÇÃO FINAL</span>.
            @elseif($tcc->status === 'EM_ORIENTACAO' || $tcc->status === 'AGUARDANDO_BANCA')
                <span class="destaque">EM ANDAMENTO</span>, dentro do cronograma estabelecido.
            @else
                em fase de <span class="destaque">DESENVOLVIMENTO</span>.
            @endif
        </p>

        @if($tcc->status === 'APROVADO' || $tcc->status === 'APROVADO_COM_RESSALVAS')
        <p class="paragrafo">
            O trabalho foi apresentado em banca examinadora no dia 
            <span class="destaque">{{ $tcc->data_defesa ? $tcc->data_defesa->format('d/m/Y') : '__/__/____' }}</span>, 
            tendo obtido a nota final <span class="destaque">{{ $tcc->nota_final ? number_format($tcc->nota_final, 2, ',', '.') : '____' }}</span>, 
            sendo <span class="destaque">{{ $tcc->status === 'APROVADO' ? 'APROVADO' : 'APROVADO COM RESSALVAS' }}</span>.
        </p>
        @endif

        <p class="paragrafo">
            Declaro ainda que o(a) referido(a) aluno(a) demonstrou 
            @if($tcc->status === 'APROVADO' || $tcc->status === 'APROVADO_COM_RESSALVAS')
                dedicação e comprometimento
            @else
                estar dedicado(a) e comprometido(a)
            @endif
            ao desenvolvimento do trabalho, cumprindo as orientações e prazos estabelecidos.
        </p>
    </div>

    <div class="rodape">
        <p>{{ $tcc->curso->departamento->instituicao->endereco['cidade'] ?? 'Cidade' }}, 
           {{ $data_geracao->format('d') }} de 
           {{ ucfirst($data_geracao->locale('pt_BR')->translatedFormat('F')) }} de 
           {{ $data_geracao->format('Y') }}</p>
    </div>

    <div class="assinatura">
        <div class="linha-assinatura">
            <strong>{{ $orientacao->orientador->usuario->nome_completo }}</strong><br>
            {{ $orientacao->orientador->getTitulacaoFormatada() }}<br>
            {{ $orientacao->tipo_orientacao === 'ORIENTADOR' ? 'Orientador(a)' : 'Coorientador(a)' }}
        </div>
    </div>

    <div style="margin-top: 40px; font-size: 9pt; text-align: center; color: #666;">
        <p>Declaração emitida eletronicamente pelo Sistema de Gestão de TCCs</p>
        <p>Documento autenticado - Código de verificação: {{ strtoupper(substr(md5($orientacao->id . $data_geracao), 0, 12)) }}</p>
    </div>
</body>
</html>
