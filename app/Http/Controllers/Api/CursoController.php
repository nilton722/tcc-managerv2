<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CursoResource;
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

    public function store(Request $request): JsonResponse
    {
        
        $validated = $request->validate([
            'departamento_id' => 'required|uuid|exists:departamentos,id',
            'coordenador_id' => 'required|uuid|exists:usuarios,id',
            'nome' => 'required|string|min:1',
            'codigo' => 'required|string|min:1|max:20',
            'nivel' => 'nullable|string|in:GRADUACAO,LICENCIATURA,ESPECIALIZACAO,MESTRADO,DOUTORADO',
            'duracao_semestres' => 'nullable|int',
            'ativo' => 'nullable|boolean|in:1,0',
        ]);

        try {
            $curso = Curso::create([
                'departamento_id' => $validated['departamento_id'],
                'coordenador_id' => $validated['coordenador_id'],
                'nome' => $validated['nome'],
                'codigo' => $validated['codigo'],
                'nivel' => $validated['nivel'] ?? 'LICENCIATURA',
                'duracao_semestres' => $validated['duracao_semestres'] ?? '2',
                'ativo' => $validated['ativo'] ?? true,
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Curso criado com sucesso',
                'data' => new CursoResource($curso->load(['departamento'])),
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        $curso = Curso::with(['departamento', 'coordenador', 'linhasPesquisa', 'templatesCronograma'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $curso]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $curso = Curso::findOrFail($id);

        $validated = $request->validate([
            'departamento_id' => 'required|uuid|exists:departamentos,id',
            'coordenador_id' => 'required|uuid|exists:usuarios,id',
            'nome' => 'required|string|min:1',
            'codigo' => 'required|string|min:1|max:20',
            'nivel' => 'nullable|string|in:GRADUACAO,LICENCIATURA,ESPECIALIZACAO,MESTRADO,DOUTORADO',
            'duracao_semestres' => 'nullable|int',
            'ativo' => 'nullable|boolean|in:1,0',
        ]);

        
        try {
            $curso->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Curso atualizar com sucesso',
                'data' => new CursoResource($curso->fresh(['departamento'])),
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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