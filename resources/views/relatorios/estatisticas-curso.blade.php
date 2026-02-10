<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Estatístico do Curso</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #2563eb; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 18pt; color: #2563eb; }
        .curso-info { background: #dbeafe; padding: 15px; margin: 20px 0; border-left: 5px solid #2563eb; }
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .stat-card { border: 1px solid #ddd; padding: 15px; background: #f9fafb; }
        .stat-card h3 { margin: 0 0 10px 0; color: #1e40af; font-size: 12pt; border-bottom: 2px solid #2563eb; padding-bottom: 5px; }
        .stat-value { font-size: 24pt; font-weight: bold; color: #2563eb; margin: 10px 0; }
        .stat-label { font-size: 9pt; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #2563eb; color: white; font-size: 10pt; }
        .progress-bar { width: 100%; height: 20px; background: #e5e7eb; border-radius: 10px; overflow: hidden; }
        .progress-fill { height: 100%; background: #10b981; text-align: center; color: white; font-size: 9pt; line-height: 20px; }
        .chart-bar { background: #3b82f6; height: 30px; margin: 5px 0; position: relative; }
        .chart-label { position: absolute; right: 10px; top: 5px; color: white; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 9pt; color: #666; border-top: 1px solid #ddd; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RELATÓRIO ESTATÍSTICO DO CURSO</h1>
        <p>{{ $curso->departamento->instituicao->nome }}</p>
        <p>Gerado em: {{ $data_geracao->format('d/m/Y H:i') }}</p>
    </div>

    <div class="curso-info">
        <h2 style="margin: 0 0 10px 0;">{{ $curso->nome }}</h2>
        <p style="margin: 5px 0;"><strong>Código:</strong> {{ $curso->codigo }}</p>
        <p style="margin: 5px 0;"><strong>Nível:</strong> {{ $curso->getNivelFormatado() }}</p>
        <p style="margin: 5px 0;"><strong>Departamento:</strong> {{ $curso->departamento->nome }}</p>
        <p style="margin: 5px 0;"><strong>Duração:</strong> {{ $curso->duracao_semestres }} semestres</p>
    </div>

    <h3>INDICADORES PRINCIPAIS</h3>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>👥 Alunos</h3>
            <div class="stat-value">{{ $estatisticas['total_alunos'] }}</div>
            <div class="stat-label">Total de alunos matriculados</div>
            <div style="margin-top: 10px;">
                <small>Ativos: <strong>{{ $estatisticas['alunos_ativos'] }}</strong></small>
            </div>
        </div>

        <div class="stat-card">
            <h3>📚 TCCs</h3>
            <div class="stat-value">{{ $estatisticas['total_tccs'] }}</div>
            <div class="stat-label">Total de trabalhos</div>
            <div style="margin-top: 10px;">
                <small>Concluídos: <strong>{{ $estatisticas['tccs_concluidos'] }}</strong></small><br>
                <small>Em andamento: <strong>{{ $estatisticas['tccs_em_andamento'] }}</strong></small>
            </div>
        </div>

        <div class="stat-card">
            <h3>📊 Média de Notas</h3>
            <div class="stat-value">{{ number_format($estatisticas['media_notas'] ?? 0, 2, ',', '.') }}</div>
            <div class="stat-label">Média geral das notas finais</div>
        </div>

        <div class="stat-card">
            <h3>✓ Taxa de Aprovação</h3>
            <div class="stat-value">{{ number_format($estatisticas['taxa_aprovacao'] ?? 0, 1, ',', '.') }}%</div>
            <div class="stat-label">Percentual de aprovação</div>
            <div class="progress-bar" style="margin-top: 10px;">
                <div class="progress-fill" style="width: {{ $estatisticas['taxa_aprovacao'] ?? 0 }}%;">
                    {{ number_format($estatisticas['taxa_aprovacao'] ?? 0, 0) }}%
                </div>
            </div>
        </div>
    </div>

    <h3>DISTRIBUIÇÃO DE TCCs POR STATUS</h3>

    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th style="width: 15%; text-align: center;">Quantidade</th>
                <th style="width: 50%;">Proporção</th>
            </tr>
        </thead>
        <tbody>
            @foreach($estatisticas['tccs_por_status'] as $status => $qtd)
            @php
                $percentual = $estatisticas['total_tccs'] > 0 ? ($qtd / $estatisticas['total_tccs']) * 100 : 0;
            @endphp
            <tr>
                <td>{{ $status }}</td>
                <td style="text-align: center;"><strong>{{ $qtd }}</strong></td>
                <td>
                    <div class="chart-bar" style="width: {{ $percentual }}%; min-width: 50px;">
                        <span class="chart-label">{{ number_format($percentual, 1) }}%</span>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>RESUMO EXECUTIVO</h3>

    <table>
        <thead>
            <tr>
                <th>Indicador</th>
                <th style="width: 30%; text-align: center;">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total de Alunos Matriculados</td>
                <td style="text-align: center;"><strong>{{ $estatisticas['total_alunos'] }}</strong></td>
            </tr>
            <tr>
                <td>Alunos Ativos</td>
                <td style="text-align: center;"><strong>{{ $estatisticas['alunos_ativos'] }}</strong></td>
            </tr>
            <tr>
                <td>Total de TCCs</td>
                <td style="text-align: center;"><strong>{{ $estatisticas['total_tccs'] }}</strong></td>
            </tr>
            <tr>
                <td>TCCs Concluídos</td>
                <td style="text-align: center;"><strong>{{ $estatisticas['tccs_concluidos'] }}</strong></td>
            </tr>
            <tr>
                <td>TCCs em Andamento</td>
                <td style="text-align: center;"><strong>{{ $estatisticas['tccs_em_andamento'] }}</strong></td>
            </tr>
            <tr>
                <td>Média Geral de Notas</td>
                <td style="text-align: center;"><strong>{{ number_format($estatisticas['media_notas'] ?? 0, 2, ',', '.') }}</strong></td>
            </tr>
            <tr style="background-color: #dbeafe;">
                <td><strong>Taxa de Aprovação</strong></td>
                <td style="text-align: center;"><strong>{{ number_format($estatisticas['taxa_aprovacao'] ?? 0, 1, ',', '.') }}%</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Relatório gerado automaticamente pelo Sistema de Gestão de TCCs</strong></p>
        <p>Data: {{ $data_geracao->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
