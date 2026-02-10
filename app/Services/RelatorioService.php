<?php

namespace App\Services;

use App\Models\Banca;
use App\Models\Curso;
use App\Models\Tcc;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CalendarioDefesasExport;
use App\Exports\TccsRecomendacoesExport;

class RelatorioService
{
    /**
     * Extrair recomendações das bancas
     */
    public function extrairRecomendacoesBancas(Tcc $tcc): array
    {
        $recomendacoes = [];

        foreach ($tcc->bancas as $banca) {
            if ($banca->status === 'CONCLUIDA') {
                foreach ($banca->avaliacoes as $avaliacao) {
                    if ($avaliacao->recomendacoes) {
                        $recomendacoes[] = [
                            'tipo_banca' => $banca->tipo_banca,
                            'data' => $banca->data_agendada,
                            'avaliador' => $avaliacao->membroBanca->usuario->nome_completo,
                            'recomendacao' => $avaliacao->recomendacoes,
                            'resultado' => $avaliacao->resultado,
                        ];
                    }
                }
            }
        }

        return $recomendacoes;
    }

    /**
     * Calcular resultado final da banca
     */
    public function calcularResultadoFinal(Banca $banca): array
    {
        $avaliacoes = $banca->avaliacoes;
        
        if ($avaliacoes->isEmpty()) {
            return [
                'media_nota' => null,
                'resultado' => 'PENDENTE',
                'total_avaliacoes' => 0,
            ];
        }

        $mediaNotas = $avaliacoes->avg('nota');
        
        // Verificar se algum membro reprovou
        $reprovado = $avaliacoes->where('resultado', 'REPROVADO')->count() > 0;
        
        // Verificar se tem ressalvas
        $temRessalvas = $avaliacoes->where('resultado', 'APROVADO_COM_RESSALVAS')->count() > 0;

        $resultado = 'APROVADO';
        
        if ($reprovado || $mediaNotas < 7.0) {
            $resultado = 'REPROVADO';
        } elseif ($temRessalvas) {
            $resultado = 'APROVADO_COM_RESSALVAS';
        }

        return [
            'media_nota' => round($mediaNotas, 2),
            'resultado' => $resultado,
            'total_avaliacoes' => $avaliacoes->count(),
            'aprovados' => $avaliacoes->where('resultado', 'APROVADO')->count(),
            'aprovados_ressalvas' => $avaliacoes->where('resultado', 'APROVADO_COM_RESSALVAS')->count(),
            'reprovados' => $avaliacoes->where('resultado', 'REPROVADO')->count(),
        ];
    }

    /**
     * Calcular estatísticas do curso
     */
    public function calcularEstatisticasCurso(Curso $curso): array
    {
        $tccs = $curso->tccs;

        return [
            'total_alunos' => $curso->alunos->count(),
            'alunos_ativos' => $curso->alunos()->whereHas('usuario', function ($q) {
                $q->where('status', 'ATIVO');
            })->count(),
            'total_tccs' => $tccs->count(),
            'tccs_por_status' => [
                'RASCUNHO' => $tccs->where('status', 'RASCUNHO')->count(),
                'EM_ORIENTACAO' => $tccs->where('status', 'EM_ORIENTACAO')->count(),
                'AGUARDANDO_BANCA' => $tccs->where('status', 'AGUARDANDO_BANCA')->count(),
                'BANCA_AGENDADA' => $tccs->where('status', 'BANCA_AGENDADA')->count(),
                'APROVADO' => $tccs->where('status', 'APROVADO')->count(),
                'APROVADO_COM_RESSALVAS' => $tccs->where('status', 'APROVADO_COM_RESSALVAS')->count(),
                'REPROVADO' => $tccs->where('status', 'REPROVADO')->count(),
            ],
            'media_notas' => $tccs->whereNotNull('nota_final')->avg('nota_final'),
            'taxa_aprovacao' => $this->calcularTaxaAprovacao($tccs),
            'tccs_concluidos' => $tccs->whereIn('status', ['APROVADO', 'APROVADO_COM_RESSALVAS'])->count(),
            'tccs_em_andamento' => $tccs->whereIn('status', [
                'RASCUNHO', 'EM_ORIENTACAO', 'AGUARDANDO_BANCA', 'BANCA_AGENDADA'
            ])->count(),
        ];
    }

    /**
     * Calcular taxa de aprovação
     */
    private function calcularTaxaAprovacao(Collection $tccs): float
    {
        $finalizados = $tccs->whereIn('status', ['APROVADO', 'APROVADO_COM_RESSALVAS', 'REPROVADO']);
        
        if ($finalizados->isEmpty()) {
            return 0;
        }

        $aprovados = $finalizados->whereIn('status', ['APROVADO', 'APROVADO_COM_RESSALVAS'])->count();
        
        return round(($aprovados / $finalizados->count()) * 100, 2);
    }

    /**
     * Processar dados de orientações
     */
    public function processarDadosOrientacoes(Collection $tccs): array
    {
        return [
            'total_orientandos' => $tccs->count(),
            'por_status' => [
                'em_andamento' => $tccs->whereIn('status', [
                    'RASCUNHO', 'EM_ORIENTACAO', 'AGUARDANDO_BANCA', 'BANCA_AGENDADA'
                ])->count(),
                'concluidos' => $tccs->whereIn('status', ['APROVADO', 'APROVADO_COM_RESSALVAS'])->count(),
                'reprovados' => $tccs->where('status', 'REPROVADO')->count(),
            ],
            'media_notas' => $tccs->whereNotNull('nota_final')->avg('nota_final'),
            'com_atrasos' => $tccs->filter(function ($tcc) {
                return $tcc->cronograma && $tcc->cronograma->temAtrasos();
            })->count(),
            'proximas_defesas' => $tccs->filter(function ($tcc) {
                return $tcc->bancas()->whereIn('status', ['AGENDADA', 'CONFIRMADA'])->exists();
            })->count(),
        ];
    }

    /**
     * Gerar relatório geral do sistema
     */
    public function gerarRelatorioGeral($dataInicio = null, $dataFim = null): array
    {
        $query = Tcc::query();

        if ($dataInicio) {
            $query->where('created_at', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->where('created_at', '<=', $dataFim);
        }

        $tccs = $query->get();

        return [
            'periodo' => [
                'inicio' => $dataInicio,
                'fim' => $dataFim,
            ],
            'total_tccs' => $tccs->count(),
            'por_status' => [
                'RASCUNHO' => $tccs->where('status', 'RASCUNHO')->count(),
                'EM_ORIENTACAO' => $tccs->where('status', 'EM_ORIENTACAO')->count(),
                'AGUARDANDO_BANCA' => $tccs->where('status', 'AGUARDANDO_BANCA')->count(),
                'BANCA_AGENDADA' => $tccs->where('status', 'BANCA_AGENDADA')->count(),
                'APROVADO' => $tccs->where('status', 'APROVADO')->count(),
                'APROVADO_COM_RESSALVAS' => $tccs->where('status', 'APROVADO_COM_RESSALVAS')->count(),
                'REPROVADO' => $tccs->where('status', 'REPROVADO')->count(),
            ],
            'por_tipo' => [
                'TCC' => $tccs->where('tipo_trabalho', 'TCC')->count(),
                'MONOGRAFIA' => $tccs->where('tipo_trabalho', 'MONOGRAFIA')->count(),
                'DISSERTACAO' => $tccs->where('tipo_trabalho', 'DISSERTACAO')->count(),
                'TESE' => $tccs->where('tipo_trabalho', 'TESE')->count(),
            ],
            'media_notas_geral' => $tccs->whereNotNull('nota_final')->avg('nota_final'),
            'taxa_aprovacao_geral' => $this->calcularTaxaAprovacao($tccs),
            'total_bancas_agendadas' => Banca::whereIn('status', ['AGENDADA', 'CONFIRMADA'])->count(),
            'total_bancas_concluidas' => Banca::where('status', 'CONCLUIDA')->count(),
        ];
    }

    /**
     * Exportar calendário para Excel
     */
    public function exportarCalendarioExcel(Collection $bancas)
    {
        return Excel::download(
            new CalendarioDefesasExport($bancas),
            'calendario-defesas-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Exportar recomendações para Excel
     */
    public function exportarRecomendacoesExcel(Collection $dados)
    {
        return Excel::download(
            new TccsRecomendacoesExport($dados),
            'tccs-recomendacoes-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Gerar número do certificado
     */
    public function gerarNumeroCertificado(Tcc $tcc): string
    {
        $ano = $tcc->data_defesa ? $tcc->data_defesa->format('Y') : now()->format('Y');
        $curso = $tcc->curso->codigo;
        $sequencial = str_pad($tcc->id, 4, '0', STR_PAD_LEFT);
        
        return "CERT-{$curso}-{$ano}-{$sequencial}";
    }

    /**
     * Gerar texto do certificado
     */
    public function gerarTextoCertificado(Tcc $tcc): string
    {
        $aluno = $tcc->aluno->usuario->nome_completo;
        $curso = $tcc->curso->nome;
        $instituicao = $tcc->curso->departamento->instituicao->nome;
        $titulo = $tcc->titulo;
        $nota = number_format($tcc->nota_final, 2, ',', '.');
        $data = $tcc->data_defesa ? $tcc->data_defesa->format('d/m/Y') : now()->format('d/m/Y');

        return "Certificamos que {$aluno} concluiu com êxito o {$curso} da {$instituicao}, " .
               "com a apresentação do trabalho intitulado \"{$titulo}\", " .
               "tendo obtido a nota final {$nota}, em {$data}.";
    }
}
