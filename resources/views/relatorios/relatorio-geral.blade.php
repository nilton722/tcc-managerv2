<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Geral do Sistema</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #dc2626; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 20pt; color: #dc2626; }
        .periodo { background: #fee2e2; padding: 10px; margin: 20px 0; text-align: center; font-weight: bold; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0; }
        .stat-card { border: 2px solid #ddd; padding: 15px; background: white; text-align: center; }
        .stat-card.destaque { border-color: #dc2626; background: #fef2f2; }
        .stat-value { font-size: 28pt; font-weight: bold; color: #dc2626; margin: 10px 0; }
        .stat-label { font-size: 9pt; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; }
        th { background-color: #dc2626; color: white; font-weight: bold; font-size: 10pt; }
        .secao { margin: 30px 0; page-break-inside: avoid; }
        .secao h2 { color: #dc2626; border-bottom: 2px solid #dc2626; padding-bottom: 5px; margin-bottom: 15px; }
        .chart-bar { height: 25px; background: #3b82f6; position: relative; margin: 3px 0; }
        .chart-label { position: absolute; right: 10px; top: 3px; color: white; font-weight: bold; font-size: 9pt; }
        .footer { margin-top: 40px; text-align: center; font-size: 9pt; color: #666; border-top: 2px solid #ddd; padding-top: 15px; }
        .resumo-executivo { background: #f9fafb; border: 2px solid #374151; padding: 20px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RELATÓRIO GERAL DO SISTEMA</h1>
        <p style="font-size: 14pt; margin: 10px 0;">Sistema de Gestão de TCCs</p>
        <p>Gerado em: {{ $data_geracao->format('d/m/Y H:i') }}</p>
    </div>

    @if(isset($periodo['inicio']) || isset($periodo['fim']))
    <div class="periodo">
        PERÍODO: 
        {{ isset($periodo['inicio']) ? \Carbon\Carbon::parse($periodo['inicio'])->format('d/m/Y') : 'Início' }} 
        até 
        {{ isset($periodo['fim']) ? \Carbon\Carbon::parse($periodo['fim'])->format('d/m/Y') : 'Atual' }}
    </div>
    @endif

    <div class="resumo-executivo">
        <h2 style="margin-top: 0; color: #374151;">📊 RESUMO EXECUTIVO</h2>
        <div class="stats-grid">
            <div class="stat-card destaque">
                <div class="stat-value">{{ $dados['total_tccs'] }}</div>
                <div class="stat-label">Total de TCCs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ number_format($dados['media_notas_geral'] ?? 0, 2, ',', '.') }}</div>
                <div class="stat-label">Média Geral</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ number_format($dados['taxa_aprovacao_geral'] ?? 0, 1) }}%</div>
                <div class="stat-label">Taxa de Aprovação</div>
            </div>
        </div>
    </div>

    <div class="secao">
        <h2>📚 DISTRIBUIÇÃO DE TCCs POR STATUS</h2>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th style="width: 15%; text-align: center;">Quantidade</th>
                    <th style="width: 15%; text-align: center;">Percentual</th>
                    <th style="width: 40%;">Visualização</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dados['por_status'] as $status => $qtd)
                @php
                    $percentual = $dados['total_tccs'] > 0 ? ($qtd / $dados['total_tccs']) * 100 : 0;
                @endphp
                <tr>
                    <td><strong>{{ $status }}</strong></td>
                    <td style="text-align: center;">{{ $qtd }}</td>
                    <td style="text-align: center;">{{ number_format($percentual, 1) }}%</td>
                    <td>
                        <div class="chart-bar" style="width: {{ $percentual }}%; min-width: 50px;">
                            <span class="chart-label">{{ $qtd }}</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="secao">
        <h2>📖 DISTRIBUIÇÃO POR TIPO DE TRABALHO</h2>
        <table>
            <thead>
                <tr>
                    <th>Tipo de Trabalho</th>
                    <th style="width: 15%; text-align: center;">Quantidade</th>
                    <th style="width: 15%; text-align: center;">Percentual</th>
                    <th style="width: 40%;">Visualização</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dados['por_tipo'] as $tipo => $qtd)
                @php
                    $percentual = $dados['total_tccs'] > 0 ? ($qtd / $dados['total_tccs']) * 100 : 0;
                @endphp
                <tr>
                    <td><strong>{{ $tipo }}</strong></td>
                    <td style="text-align: center;">{{ $qtd }}</td>
                    <td style="text-align: center;">{{ number_format($percentual, 1) }}%</td>
                    <td>
                        <div class="chart-bar" style="width: {{ $percentual }}%; min-width: 50px; background: #10b981;">
                            <span class="chart-label">{{ $qtd }}</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="secao">
        <h2>🎓 BANCAS EXAMINADORAS</h2>
        <table>
            <thead>
                <tr>
                    <th>Tipo de Banca</th>
                    <th style="width: 30%; text-align: center;">Quantidade</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Bancas Agendadas</strong></td>
                    <td style="text-align: center;"><strong>{{ $dados['total_bancas_agendadas'] }}</strong></td>
                </tr>
                <tr>
                    <td><strong>Bancas Concluídas</strong></td>
                    <td style="text-align: center;"><strong>{{ $dados['total_bancas_concluidas'] }}</strong></td>
                </tr>
                <tr style="background-color: #fef2f2;">
                    <td><strong>TOTAL DE BANCAS</strong></td>
                    <td style="text-align: center;"><strong>{{ $dados['total_bancas_agendadas'] + $dados['total_bancas_concluidas'] }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="secao">
        <h2>📈 INDICADORES DE DESEMPENHO</h2>
        <table>
            <thead>
                <tr>
                    <th>Indicador</th>
                    <th style="width: 30%; text-align: center;">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total de TCCs no Sistema</td>
                    <td style="text-align: center;"><strong>{{ $dados['total_tccs'] }}</strong></td>
                </tr>
                <tr>
                    <td>TCCs Aprovados</td>
                    <td style="text-align: center;"><strong>{{ $dados['por_status']['APROVADO'] ?? 0 }}</strong></td>
                </tr>
                <tr>
                    <td>TCCs Aprovados com Ressalvas</td>
                    <td style="text-align: center;"><strong>{{ $dados['por_status']['APROVADO_COM_RESSALVAS'] ?? 0 }}</strong></td>
                </tr>
                <tr>
                    <td>TCCs Reprovados</td>
                    <td style="text-align: center;"><strong>{{ $dados['por_status']['REPROVADO'] ?? 0 }}</strong></td>
                </tr>
                <tr>
                    <td>TCCs em Andamento</td>
                    <td style="text-align: center;"><strong>{{ $dados['por_status']['EM_ORIENTACAO'] ?? 0 }}</strong></td>
                </tr>
                <tr style="background-color: #dbeafe;">
                    <td><strong>Média Geral de Notas</strong></td>
                    <td style="text-align: center;"><strong>{{ number_format($dados['media_notas_geral'] ?? 0, 2, ',', '.') }}</strong></td>
                </tr>
                <tr style="background-color: #fef2f2;">
                    <td><strong>Taxa de Aprovação Geral</strong></td>
                    <td style="text-align: center;"><strong>{{ number_format($dados['taxa_aprovacao_geral'] ?? 0, 1, ',', '.') }}%</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="secao">
        <h2>📊 ANÁLISE ESTATÍSTICA</h2>
        <div style="background: #f3f4f6; padding: 15px; margin: 10px 0;">
            <p style="margin: 5px 0;"><strong>Total de TCCs Cadastrados:</strong> {{ $dados['total_tccs'] }}</p>
            <p style="margin: 5px 0;"><strong>Total de Bancas Realizadas:</strong> {{ $dados['total_bancas_concluidas'] }}</p>
            <p style="margin: 5px 0;"><strong>Média de Aprovação:</strong> {{ number_format($dados['taxa_aprovacao_geral'] ?? 0, 2, ',', '.') }}%</p>
            <p style="margin: 5px 0;"><strong>Nota Média do Sistema:</strong> {{ number_format($dados['media_notas_geral'] ?? 0, 2, ',', '.') }}</p>
        </div>
    </div>

    <div class="footer">
        <p><strong>RELATÓRIO GERAL DO SISTEMA DE GESTÃO DE TCCs</strong></p>
        <p>Documento gerado automaticamente em {{ $data_geracao->format('d/m/Y') }} às {{ $data_geracao->format('H:i') }}</p>
        <p style="font-size: 8pt; margin-top: 10px;">
            Este relatório contém informações consolidadas do sistema<br>
            Para informações detalhadas, consulte os relatórios específicos por curso ou orientador
        </p>
    </div>
</body>
</html>
