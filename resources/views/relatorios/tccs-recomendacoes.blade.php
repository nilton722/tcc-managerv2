<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>TCCs com Recomendações de Correções</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #dc2626; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 18pt; color: #dc2626; }
        .header p { margin: 5px 0; color: #666; }
        .tcc-item { margin-bottom: 30px; page-break-inside: avoid; border: 1px solid #ddd; padding: 15px; background: #f9f9f9; }
        .tcc-header { background: #fee2e2; padding: 10px; margin: -15px -15px 15px -15px; border-bottom: 2px solid #dc2626; }
        .tcc-title { font-weight: bold; font-size: 12pt; color: #991b1b; }
        .aluno-info { margin: 5px 0; color: #444; }
        .recomendacao-box { background: white; border-left: 4px solid #f59e0b; padding: 10px; margin: 10px 0; }
        .recomendacao-titulo { font-weight: bold; color: #92400e; margin-bottom: 5px; }
        .documento-status { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 8pt; font-weight: bold; }
        .status-rejeitado { background: #fee2e2; color: #991b1b; }
        .status-revisao { background: #fef3c7; color: #92400e; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #dc2626; color: white; font-size: 9pt; }
        .footer { margin-top: 30px; text-align: center; font-size: 8pt; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RELATÓRIO DE TCCs COM RECOMENDAÇÕES DE CORREÇÕES</h1>
        <p>TCCs que necessitam de ajustes e correções</p>
        <p>Gerado em: {{ $data_geracao->format('d/m/Y H:i') }}</p>
    </div>

    @forelse($dados as $item)
    <div class="tcc-item">
        <div class="tcc-header">
            <div class="tcc-title">{{ $item['tcc']->titulo }}</div>
            <div class="aluno-info">
                <strong>Aluno:</strong> {{ $item['tcc']->aluno->usuario->nome_completo }} 
                ({{ $item['tcc']->aluno->matricula }})
            </div>
            <div class="aluno-info">
                <strong>Curso:</strong> {{ $item['tcc']->curso->nome }} | 
                <strong>Orientador:</strong> {{ $item['tcc']->orientador->usuario->nome_completo ?? 'N/A' }}
            </div>
            <div class="aluno-info">
                <strong>Status:</strong> {{ $item['tcc']->status }}
            </div>
        </div>

        @if(count($item['recomendacoes_bancas']) > 0)
        <h4 style="color: #dc2626; margin-top: 15px;">📋 Recomendações das Bancas:</h4>
        @foreach($item['recomendacoes_bancas'] as $rec)
        <div class="recomendacao-box">
            <div class="recomendacao-titulo">
                {{ $rec['tipo_banca'] === 'QUALIFICACAO' ? 'Qualificação' : 'Defesa Final' }} - 
                {{ \Carbon\Carbon::parse($rec['data'])->format('d/m/Y') }}
            </div>
            <div><strong>Avaliador:</strong> {{ $rec['avaliador'] }}</div>
            <div><strong>Resultado:</strong> {{ $rec['resultado'] }}</div>
            <div style="margin-top: 5px;">{{ $rec['recomendacao'] }}</div>
        </div>
        @endforeach
        @endif

        @if($item['documentos_rejeitados']->count() > 0)
        <h4 style="color: #dc2626; margin-top: 15px;">❌ Documentos Rejeitados:</h4>
        <table>
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Versão</th>
                    <th>Data Upload</th>
                    <th>Motivo da Rejeição</th>
                </tr>
            </thead>
            <tbody>
                @foreach($item['documentos_rejeitados'] as $doc)
                <tr>
                    <td>{{ $doc->tipoDocumento->nome }}</td>
                    <td>v{{ $doc->versao }}</td>
                    <td>{{ $doc->upload_em->format('d/m/Y') }}</td>
                    <td>{{ $doc->comentarios ?: 'Sem comentários' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($item['documentos_revisao']->count() > 0)
        <h4 style="color: #f59e0b; margin-top: 15px;">⚠️ Documentos em Revisão:</h4>
        <table>
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Versão</th>
                    <th>Data Upload</th>
                    <th>Observações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($item['documentos_revisao'] as $doc)
                <tr>
                    <td>{{ $doc->tipoDocumento->nome }}</td>
                    <td>v{{ $doc->versao }}</td>
                    <td>{{ $doc->upload_em->format('d/m/Y') }}</td>
                    <td>{{ $doc->comentarios ?: 'Aguardando revisão' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @empty
    <div style="text-align: center; padding: 40px; color: #666;">
        <p>Nenhum TCC com recomendações de correções pendentes.</p>
    </div>
    @endforelse

    <div class="footer">
        <p><strong>Total de TCCs com pendências:</strong> {{ $dados->count() }}</p>
        <p>Documento gerado automaticamente pelo Sistema de Gestão de TCCs</p>
    </div>
</body>
</html>
