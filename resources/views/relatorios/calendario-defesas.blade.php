<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Calendário de Defesas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18pt; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .tipo-qualificacao { background-color: #fff3cd; }
        .tipo-defesa { background-color: #d1ecf1; }
        .footer { margin-top: 30px; text-align: center; font-size: 8pt; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CALENDÁRIO DE DEFESAS</h1>
        <p>Período: {{ $filtros['data_inicio'] ?? 'Todas' }} até {{ $filtros['data_fim'] ?? 'Todas' }}</p>
        <p>Gerado em: {{ $data_geracao->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">Nº</th>
                <th style="width: 25%;">Candidato</th>
                <th style="width: 20%;">Curso</th>
                <th style="width: 20%;">Data e Hora</th>
                <th style="width: 15%;">Tipo</th>
                <th style="width: 15%;">Sala</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bancas as $index => $banca)
            <tr class="{{ $banca->tipo_banca === 'QUALIFICACAO' ? 'tipo-qualificacao' : 'tipo-defesa' }}">
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $banca->tcc->aluno->usuario->nome_completo }}</strong><br>
                    <small>Orientador: {{ $banca->tcc->orientador->usuario->nome_completo ?? 'N/A' }}</small>
                </td>
                <td>{{ $banca->tcc->curso->codigo }}</td>
                <td>
                    {{ $banca->data_agendada->format('d/m/Y') }}<br>
                    <strong>{{ $banca->data_agendada->format('H:i') }}</strong>
                </td>
                <td>{{ $banca->tipo_banca === 'QUALIFICACAO' ? 'Qualificação' : 'Defesa Final' }}</td>
                <td>{{ $banca->local ?: ($banca->formato === 'REMOTA' ? 'Online' : 'A definir') }}</td>
            </tr>
            <tr>
                <td colspan="6" style="background-color: #f9f9f9; font-size: 9pt;">
                    <strong>Tema:</strong> {{ $banca->tcc->titulo }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">
                    Nenhuma defesa agendada para o período selecionado.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total de defesas agendadas: {{ $bancas->count() }}</p>
        <p>Documento gerado automaticamente pelo Sistema de Gestão de TCCs</p>
    </div>
</body>
</html>
