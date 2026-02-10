<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Certificado</title>
    <style>
        @page { size: A4 landscape; margin: 0; }
        body { 
            font-family: 'Georgia', serif; 
            margin: 0; 
            padding: 40px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .certificado {
            background: white;
            border: 15px solid #1e3a8a;
            border-image: linear-gradient(45deg, #1e3a8a, #3b82f6) 1;
            padding: 50px;
            text-align: center;
            min-height: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .brasao {
            max-width: 100px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 48pt;
            color: #1e3a8a;
            margin: 20px 0;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        .subtitulo {
            font-size: 16pt;
            color: #666;
            margin: 10px 0 30px;
            font-style: italic;
        }
        .texto-principal {
            font-size: 14pt;
            line-height: 1.8;
            text-align: justify;
            margin: 30px auto;
            max-width: 90%;
        }
        .nome-destaque {
            font-size: 18pt;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
        }
        .titulo-tcc {
            font-style: italic;
            font-weight: bold;
        }
        .nota {
            font-weight: bold;
            color: #059669;
        }
        .info-box {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-left: 5px solid #1e3a8a;
        }
        .assinaturas {
            margin-top: 60px;
            display: flex;
            justify-content: space-around;
        }
        .assinatura {
            text-align: center;
            width: 30%;
        }
        .linha {
            border-top: 2px solid #333;
            margin: 50px auto 10px;
            width: 80%;
        }
        .rodape {
            margin-top: 40px;
            font-size: 10pt;
            color: #666;
        }
        .numero-certificado {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 10pt;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="numero-certificado">
        Certificado Nº: {{ app(\App\Services\RelatorioService::class)->gerarNumeroCertificado($tcc) }}
    </div>

    <div class="certificado">
        @if($instituicao->logo_url)
            <img src="{{ $instituicao->logo_url }}" class="brasao" alt="Logo">
        @endif

        <h1>CERTIFICADO</h1>
        
        <div class="subtitulo">
            {{ $instituicao->nome }}
        </div>

        <div class="texto-principal">
            Certificamos que <span class="nome-destaque">{{ $aluno->usuario->nome_completo }}</span>, 
            portador(a) da matrícula <strong>{{ $aluno->matricula }}</strong>, 
            concluiu com êxito o curso de <strong>{{ $curso->nome }}</strong> 
            ({{ $curso->getNivelFormatado() }}), 
            com a apresentação do trabalho intitulado 
            <span class="titulo-tcc">"{{ $tcc->titulo }}"</span>, 
            tendo obtido a <span class="nota">nota final {{ number_format($tcc->nota_final, 2, ',', '.') }}</span>, 
            em <strong>{{ $tcc->data_defesa ? $tcc->data_defesa->format('d') }} de {{ $tcc->data_defesa ? ucfirst($tcc->data_defesa->locale('pt_BR')->translatedFormat('F')) : '' }} de {{ $tcc->data_defesa ? $tcc->data_defesa->format('Y') : now()->format('Y') }}</strong>.
        </div>

        <div class="info-box">
            <strong>Grau Acadêmico Conferido:</strong> {{ $tcc->status === 'APROVADO' ? 'Licenciatura' : 'Aprovado com Ressalvas' }}
        </div>

        <div class="assinaturas">
            <div class="assinatura">
                <div class="linha"></div>
                <strong>Secretário Geral</strong><br>
                <small>{{ $curso->departamento->nome }}</small>
            </div>

            @if($orientador)
            <div class="assinatura">
                <div class="linha"></div>
                <strong>{{ $orientador->usuario->nome_completo }}</strong><br>
                <small>Orientador - {{ $orientador->getTitulacaoFormatada() }}</small>
            </div>
            @endif

            <div class="assinatura">
                <div class="linha"></div>
                <strong>Coordenador do Curso</strong><br>
                <small>{{ $curso->nome }}</small>
            </div>
        </div>

        <div class="rodape">
            <p>Emitido em {{ $data_geracao->format('d/m/Y') }}</p>
            <p style="font-size: 8pt;">{{ $instituicao->endereco['cidade'] ?? '' }}, {{ $instituicao->endereco['estado'] ?? '' }}</p>
        </div>
    </div>
</body>
</html>
