<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Curso::with(['departamento.instituicao', 'coordenador', 'linhasPesquisa']);

        if ($request->departamento_id) {
            $query->where('departamento_id', $request->departamento_id);
        }

        if ($request->nivel) {
            $query->where('nivel', $request->nivel);
        }

        if ($request->ativo !== null) {
            $query->where('ativo', $request->ativo === 'true');
        }

        $cursos = $query->orderBy('nome')->get();

        return response()->json(['success' => true, 'data' => $cursos]);
    }

    public function show(string $id): JsonResponse
    {
        $curso = Curso::with(['departamento', 'coordenador', 'linhasPesquisa', 'templatesCronograma'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $curso]);
    }

    public function tccs(string $id): JsonResponse
    {
        $curso = Curso::findOrFail($id);
        $tccs = $curso->tccs()->with(['aluno.usuario', 'orientacoes.orientador.usuario'])->get();
        return response()->json(['success' => true, 'data' => $tccs]);
    }

    public function estatisticas(string $id): JsonResponse
    {
        $curso = Curso::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => [
                'total_alunos' => $curso->getTotalAlunos(),
                'alunos_ativos' => $curso->getTotalAlunosAtivos(),
                'total_tccs' => $curso->getTotalTccs(),
                'media_notas' => $curso->getMediaNotas(),
                'taxa_aprovacao' => $curso->getTaxaAprovacao(),
                'tccs_por_status' => [
                    'RASCUNHO' => $curso->getTotalTccsPorStatus('RASCUNHO'),
                    'EM_ORIENTACAO' => $curso->getTotalTccsPorStatus('EM_ORIENTACAO'),
                    'APROVADO' => $curso->getTotalTccsPorStatus('APROVADO'),
                    'REPROVADO' => $curso->getTotalTccsPorStatus('REPROVADO'),
                ],
            ],
        ]);
    }
}