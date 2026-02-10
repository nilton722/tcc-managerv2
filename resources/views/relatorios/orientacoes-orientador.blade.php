<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Orientações</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #7c3aed; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 18pt; color: #7c3aed; }
        .orientador-info { background: #ede9fe; padding: 15px; margin: 20px 0; border-left: 5px solid #7c3aed; }
        .resumo-box { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 20px 0; }
        .resumo-item { background: white; border: 1px solid #ddd; padding: 12px; text-align: center; }
        .resumo-valor { font-size: 20pt; font-weight: bold; color: #7c3aed; }
        .resumo-label { font-size: 8pt; color: #666; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 9pt; }
        th { background-color: #7c3aed; color: white; font-weight: bold; }
        .status-badge { padding: 3px 8px; border-radius: 3px; font-size: 7pt; font-weight: bold; display: inline-block; }
        .status-rascunho { background: #e5e7eb; color: #374151; }
        .status-em-orientacao { background: #dbeafe; color: #1e40af; }
        .status-aprovado { background: #d1fae5; color: #065f46; }
        .status-reprovado { background: #fee2e2; color: #991b1b; }
        .prioridade-alta { background: #fef3c7; border-left: 3px solid #f59e0b; }
        .footer { margin-top: 30px; text-align: center; font-size: 8pt; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RELATÓRIO DE ORIENTAÇÕES</h1>
        <p>Período de Referência: {{ $data_geracao->format('Y') }}</p>
        <p>Gerado em: {{ $data_geracao->format('d/m/Y H:i') }}</p>
    </div>

    <div class="orientador-info">
        <h2 style="margin: 0 0 10px 0;">{{ $orientador->usuario->nome_completo }}</h2>
        <p style="margin: 5px 0;"><strong>Titulação:</strong> {{ $orientador->getTitulacaoFormatada() }}</p>
        <p style="margin: 5px 0;"><strong>Departamento:</strong> {{ $orientador->departamento->nome }}</p>
        <p style="margin: 5px 0;"><strong>Áreas de Atuação:</strong> {{ implode(', ', $orientador->areas_atuacao) }}</p>
        @if($orientador->lattes_url)
        <p style="margin: 5px 0;"><strong>Lattes:</strong> {{ $orientador->lattes_url }}</p>
        @endif
    </div>

    <h3>RESUMO DE ORIENTAÇÕES</h3>

    <div class="resumo-box">
        <div class="resumo-item">
            <div class="resumo-valor">{{ $dados['total_orientandos'] }}</div>
            <div class="resumo-label">TOTAL DE ORIENTANDOS</div>
        </div>
        <div class="resumo-item">
            <div class="resumo-valor">{{ $dados['por_status']['em_andamento'] }}</div>
            <div class="resumo-label">EM ANDAMENTO</div>
        </div>
        <div class="resumo-item">
            <div class="resumo-valor">{{ $dados['por_status']['concluidos'] }}</div>
            <div class="resumo-label">CONCLUÍDOS</div>
        </div>
        <div class="resumo-item">
            <div class="resumo-valor">{{ number_format($dados['media_notas'] ?? 0, 2, ',', '.') }}</div>
            <div class="resumo-label">MÉDIA DE NOTAS</div>
        </div>
    </div>

    <h3>ORIENTANDOS ATIVOS</h3>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">Nº</th>
                <th style="width: 25%;">Aluno</th>
                <th style="width: 10%;">Matrícula</th>
                <th style="width: 35%;">Título do TCC</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 13%;">Próxima Defesa</th>
            </tr>
        </thead>
        <tbody>
            @php $contador = 1; @endphp
            @foreach($tccs as $tcc)
            @if(in_array($tcc->status, ['RASCUNHO', 'EM_ORIENTACAO', 'AGUARDANDO_BANCA', 'BANCA_AGENDADA', 'EM_AVALIACAO']))
            @php
                $proximaBanca = $tcc->bancas()
                    ->whereIn('status', ['AGENDADA', 'CONFIRMADA'])
                    ->orderBy('data_agendada')
                    ->first();
                $temAtraso = $tcc->cronograma && $tcc->cronograma->temAtrasos();
            @endphp
            <tr class="{{ $temAtraso ? 'prioridade-alta' : '' }}">
                <td style="text-align: center;">{{ $contador++ }}</td>
                <td>{{ $tcc->aluno->usuario->nome_completo }}</td>
                <td>{{ $tcc->aluno->matricula }}</td>
                <td>{{ $tcc->titulo }}</td>
                <td>
                    <span class="status-badge status-{{ strtolower(str_replace('_', '-', $tcc->status)) }}">
                        {{ $tcc->status }}
                    </span>
                    @if($temAtraso)
                        <br><small style="color: #f59e0b;">⚠ Com atrasos</small>
                    @endif
                </td>
                <td>
                    @if($proximaBanca)
                        {{ $proximaBanca->data_agendada->format('d/m/Y') }}<br>
                        <small>{{ $proximaBanca->getTipoFormatado() }}</small>
                    @else
                        <span style="color: #999;">Não agendada</span>
                    @endif
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <h3>HISTÓRICO DE ORIENTAÇÕES CONCLUÍDAS</h3>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">Nº</th>
                <th style="width: 25%;">Aluno</th>
                <th style="width: 35%;">Título do TCC</th>
                <th style="width: 12%;">Data Defesa</th>
                <th style="width: 10%;">Nota Final</th>
                <th style="width: 13%;">Resultado</th>
            </tr>
        </thead>
        <tbody>
            @php $contador = 1; @endphp
            @foreach($tccs as $tcc)
            @if(in_array($tcc->status, ['APROVADO', 'APROVADO_COM_RESSALVAS', 'REPROVADO']))
            <tr>
                <td style="text-align: center;">{{ $contador++ }}</td>
                <td>{{ $tcc->aluno->usuario->nome_completo }}</td>
                <td>{{ $tcc->titulo }}</td>
                <td>{{ $tcc->data_defesa ? $tcc->data_defesa->format('d/m/Y') : 'N/A' }}</td>
                <td style="text-align: center;">{{ $tcc->nota_final ? number_format($tcc->nota_final, 2, ',', '.') : '-' }}</td>
                <td>
                    <span class="status-badge status-{{ strtolower(str_replace('_', '-', $tcc->status)) }}">
                        {{ $tcc->status }}
                    </span>
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    <h3>ESTATÍSTICAS</h3>

    <table>
        <thead>
            <tr>
                <th>Indicador</th>
                <th style="width: 20%; text-align: center;">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total de Orientandos (Histórico)</td>
                <td style="text-align: center;"><strong>{{ $dados['total_orientandos'] }}</strong></td>
            </tr>
            <tr>
                <td>Orientações em Andamento</td>
                <td style="text-align: center;"><strong>{{ $dados['por_status']['em_andamento'] }}</strong></td>
            </tr>
            <tr>
                <td>Orientações Concluídas</td>
                <td style="text-align: center;"><strong>{{ $dados['por_status']['concluidos'] }}</strong></td>
            </tr>
            <tr>
                <td>Orientações Reprovadas</td>
                <td style="text-align: center;"><strong>{{ $dados['por_status']['reprovados'] }}</strong></td>
            </tr>
            <tr>
                <td>Média de Notas dos Orientandos</td>
                <td style="text-align: center;"><strong>{{ number_format($dados['media_notas'] ?? 0, 2, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td>TCCs com Atrasos</td>
                <td style="text-align: center;"><strong>{{ $dados['com_atrasos'] }}</strong></td>
            </tr>
            <tr style="background-color: #ede9fe;">
                <td><strong>Próximas Defesas Agendadas</strong></td>
                <td style="text-align: center;"><strong>{{ $dados['proximas_defesas'] }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Relatório de Orientações - {{ $orientador->usuario->nome_completo }}</strong></p>
        <p>Gerado em {{ $data_geracao->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
