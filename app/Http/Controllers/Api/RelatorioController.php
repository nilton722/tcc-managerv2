<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banca;
use App\Models\Tcc;
use App\Models\Curso;
use App\Services\RelatorioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    protected RelatorioService $relatorioService;

    public function __construct(RelatorioService $relatorioService)
    {
        $this->relatorioService = $relatorioService;
    }

    /**
     * Gerar Calendário de Defesas Agendadas
     */
    public function calendarioDefesas(Request $request)
    {
        $validated = $request->validate([
            'curso_id' => 'nullable|uuid|exists:cursos,id',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'tipo_banca' => 'nullable|in:QUALIFICACAO,DEFESA_FINAL',
            'formato' => 'required|in:pdf,excel,json',
        ]);

        $query = Banca::with([
            'tcc.aluno.usuario',
            'tcc.curso',
            'tcc.linhaPesquisa',
            'membros.usuario',
        ])->whereIn('status', ['AGENDADA', 'CONFIRMADA']);

        // Filtros
        if ($request->curso_id) {
            $query->whereHas('tcc', function ($q) use ($request) {
                $q->where('curso_id', $request->curso_id);
            });
        }

        if ($request->data_inicio) {
            $query->where('data_agendada', '>=', $request->data_inicio);
        }

        if ($request->data_fim) {
            $query->where('data_agendada', '<=', $request->data_fim);
        }

        if ($request->tipo_banca) {
            $query->where('tipo_banca', $request->tipo_banca);
        }

        $bancas = $query->orderBy('data_agendada')->get();

        // Formato de saída
        if ($validated['formato'] === 'json') {
            return response()->json([
                'success' => true,
                'data' => $bancas,
                'total' => $bancas->count(),
            ]);
        }

        if ($validated['formato'] === 'excel') {
            return $this->relatorioService->exportarCalendarioExcel($bancas);
        }

        // PDF
        $pdf = Pdf::loadView('relatorios.calendario-defesas', [
            'bancas' => $bancas,
            'filtros' => $validated,
            'data_geracao' => now(),
        ]);

        return $pdf->download('calendario-defesas-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Gerar Relatório de TCCs com Recomendações de Correções
     */
    public function tccsComRecomendacoes(Request $request)
    {
        $validated = $request->validate([
            'curso_id' => 'nullable|uuid|exists:cursos,id',
            'status' => 'nullable|in:APROVADO_COM_RESSALVAS,EM_ORIENTACAO',
            'formato' => 'required|in:pdf,excel,json',
        ]);

        $query = Tcc::with([
            'aluno.usuario',
            'curso',
            'orientacoes.orientador.usuario',
            'bancas.avaliacoes.membroBanca.usuario',
            'documentos' => function ($q) {
                $q->where('status', 'REJEITADO')->orWhere('status', 'REVISAO');
            },
        ]);

        // dd("tccs-recomendacoes: ", $query);

        // TCCs que precisam de correção
        $query->where(function ($q) {
            $q->where('status', 'APROVADO_COM_RESSALVAS')
              ->orWhereHas('documentos', function ($doc) {
                  $doc->whereIn('status', ['REJEITADO', 'REVISAO']);
              });
        });

        if ($request->curso_id) {
            $query->where('curso_id', $request->curso_id);
        }

        $tccs = $query->get();

        // Processar recomendações
        $dados = $tccs->map(function ($tcc) {
            return [
                'tcc' => $tcc,
                'recomendacoes_bancas' => $this->relatorioService->extrairRecomendacoesBancas($tcc),
                'documentos_rejeitados' => $tcc->documentos->where('status', 'REJEITADO'),
                'documentos_revisao' => $tcc->documentos->where('status', 'REVISAO'),
            ];
        });

        if ($validated['formato'] === 'json') {
            return response()->json([
                'success' => true,
                'data' => $dados,
                'total' => $dados->count(),
            ]);
        }

        if ($validated['formato'] === 'excel') {
            return $this->relatorioService->exportarRecomendacoesExcel($dados);
        }

        // PDF
        $pdf = Pdf::loadView('relatorios.tccs-recomendacoes', [
            'dados' => $dados,
            'filtros' => $validated,
            'data_geracao' => now(),
        ]);

        return $pdf->download('tccs-recomendacoes-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Gerar Matriz de Defesa (Ficha de Avaliação)
     */
    public function matrizDefesa(string $bancaId)
    {
        $banca = Banca::with([
            'tcc.aluno.usuario',
            'tcc.curso',
            'tcc.orientacoes.orientador.usuario',
            'membros.usuario',
            'avaliacoes.membroBanca.usuario',
        ])->findOrFail($bancaId);

        // $this->authorize('view', $banca);

        $pdf = Pdf::loadView('relatorios.matriz-defesa', [
            'banca' => $banca,
            'tcc' => $banca->tcc,
            'data_geracao' => now(),
        ]);

        $nomeArquivo = 'matriz-defesa-' . 
                       str_slug($banca->tcc->aluno->usuario->nome_completo) . 
                       '.pdf';

        return $pdf->download($nomeArquivo);
    }

    /**
     * Gerar Certificado de Conclusão
     */
    public function certificado(string $tccId)
    {
        $tcc = Tcc::with([
            'aluno.usuario',
            'curso.departamento.instituicao',
            'orientacoes.orientador.usuario',
        ])->findOrFail($tccId);

        // $this->authorize('view', $tcc);

        // Só gera certificado para TCCs aprovados
        if (!in_array($tcc->status, ['APROVADO', 'APROVADO_COM_RESSALVAS'])) {
            return response()->json([
                'success' => false,
                'message' => 'Certificado só pode ser gerado para TCCs aprovados',
            ], 422);
        }

        $pdf = Pdf::loadView('relatorios.certificado', [
            'tcc' => $tcc,
            'aluno' => $tcc->aluno,
            'curso' => $tcc->curso,
            'instituicao' => $tcc->curso->departamento->instituicao,
            'orientador' => $tcc->orientador,
            'data_geracao' => now(),
        ])->setPaper('a4', 'landscape'); // Certificado horizontal

        $nomeArquivo = 'certificado-' . 
                       str_slug($tcc->aluno->usuario->nome_completo) . 
                       '.pdf';

        return $pdf->download($nomeArquivo);
    }

    /**
     * Gerar Ata de Defesa
     */
    public function ataDefesa(string $bancaId)
    {
        $banca = Banca::with([
            'tcc.aluno.usuario',
            'tcc.curso.departamento.instituicao',
            'tcc.orientacoes.orientador.usuario',
            'membros.usuario',
            'avaliacoes.membroBanca.usuario',
        ])->findOrFail($bancaId);

        // $this->authorize('view', $banca);

        if ($banca->status !== 'CONCLUIDA') {
            return response()->json([
                'success' => false,
                'message' => 'Ata só pode ser gerada para bancas concluídas',
            ], 422);
        }

        $pdf = Pdf::loadView('relatorios.ata-defesa', [
            'banca' => $banca,
            'tcc' => $banca->tcc,
            'resultado' => $this->relatorioService->calcularResultadoFinal($banca),
            'data_geracao' => now(),
        ]);

        $nomeArquivo = 'ata-defesa-' . 
                       str_slug($banca->tcc->aluno->usuario->nome_completo) . 
                       '.pdf';

        return $pdf->download($nomeArquivo);
    }

    /**
     * Gerar Lista de Presença da Banca
     */
    public function listaPresenca(string $bancaId)
    {
        $banca = Banca::with([
            'tcc.aluno.usuario',
            'tcc.curso',
            'membros.usuario',
        ])->findOrFail($bancaId);

        // $this->authorize('view', $banca);

        $pdf = Pdf::loadView('relatorios.lista-presenca', [
            'banca' => $banca,
            'tcc' => $banca->tcc,
            'data_geracao' => now(),
        ]);

        return $pdf->download('lista-presenca-' . $banca->id . '.pdf');
    }


    /**
     * Relatório Estatístico por Curso
     */
    public function estatisticasCurso(string $cursoId)
    {
        $curso = Curso::with([
            'departamento',
            'tccs',
            'alunos',
        ])->findOrFail($cursoId);

        // $this->authorize('viewAny', Tcc::class);

        $estatisticas = $this->relatorioService->calcularEstatisticasCurso($curso);

        $pdf = Pdf::loadView('relatorios.estatisticas-curso', [
            'curso' => $curso,
            'estatisticas' => $estatisticas,
            'data_geracao' => now(),
        ]);

        return $pdf->download('estatisticas-' . str_slug($curso->nome) . '.pdf');
    }


    /**
     * Relatório de Orientações (para orientador)
     */
    public function relatorioOrientacoes(Request $request)
    {
        $user = auth()->user();

        if (!$user->isOrientador()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas orientadores podem gerar este relatório',
            ], 403);
        }

        $orientador = $user->orientador;

        $tccs = $orientador->tccsOrientando()
            ->with(['aluno.usuario', 'curso', 'bancas', 'cronograma.etapas'])
            ->get();

        $dados = $this->relatorioService->processarDadosOrientacoes($tccs);

        $pdf = Pdf::loadView('relatorios.orientacoes-orientador', [
            'orientador' => $orientador,
            'tccs' => $tccs,
            'dados' => $dados,
            'data_geracao' => now(),
        ]);

        return $pdf->download('relatorio-orientacoes-' . now()->format('Y-m-d') . '.pdf');
    }


    /**
     * Declaração de Orientação
     */
    public function declaracaoOrientacao(string $orientacaoId)
    {
        $orientacao = \App\Models\Orientacao::with([
            'tcc.aluno.usuario',
            'tcc.curso',
            'orientador.usuario',
        ])->findOrFail($orientacaoId);

        // $this->authorize('view', $orientacao);

        $pdf = Pdf::loadView('relatorios.declaracao-orientacao', [
            'orientacao' => $orientacao,
            'tcc' => $orientacao->tcc,
            'data_geracao' => now(),
        ]);

        return $pdf->download('declaracao-orientacao-' . $orientacao->id . '.pdf');
    }



    /**
     * Relatório Geral do Sistema (Admin)
     */
    public function relatorioGeral(Request $request)
    {
        // $this->authorize('viewAny', Tcc::class);

        $validated = $request->validate([
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
        ]);

        $dados = $this->relatorioService->gerarRelatorioGeral(
            $validated['data_inicio'] ?? null,
            $validated['data_fim'] ?? null
        );

        $pdf = Pdf::loadView('relatorios.relatorio-geral', [
            'dados' => $dados,
            'periodo' => $validated,
            'data_geracao' => now(),
        ]);

        return $pdf->download('relatorio-geral-' . now()->format('Y-m-d') . '.pdf');
    }


    /**
     * Comprovante de Submissão de TCC
     */
    public function comprovanteSubmissao(string $tccId)
    {
        $tcc = Tcc::with([
            'aluno.usuario',
            'curso',
            'orientacoes.orientador.usuario',
        ])->findOrFail($tccId);

        // $this->authorize('view', $tcc);

        $pdf = Pdf::loadView('relatorios.comprovante-submissao', [
            'tcc' => $tcc,
            'data_geracao' => now(),
        ]);

        return $pdf->download('comprovante-submissao-' . $tcc->id . '.pdf');
    }
}

