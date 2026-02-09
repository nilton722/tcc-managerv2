<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tcc\StoreTccRequest;
use App\Http\Requests\Tcc\UpdateTccRequest;
use App\Http\Resources\TccResource;
use App\Models\Tcc;
use App\Services\TccService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\QueryBuilder;

class TccController extends Controller
{
    public function __construct(
        private TccService $tccService
    ) {}

    /**
     * Listar TCCs com filtros e paginação
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $usuario = $request->user();

        $query = QueryBuilder::for(Tcc::class)
            ->allowedFilters([
                'status',
                'tipo_trabalho',
                'curso_id',
                'linha_pesquisa_id',
            ])
            ->allowedSorts(['created_at', 'titulo', 'data_defesa', 'nota_final'])
            ->allowedIncludes([
                'aluno.usuario',
                'curso',
                'linhaPesquisa',
                'orientacoes.orientador.usuario',
                'bancas',
            ]);

        // Filtros por tipo de usuário
        if ($usuario->isAluno()) {
            $query->where('aluno_id', $usuario->aluno->id);
        } elseif ($usuario->isOrientador()) {
            $query->porOrientador($usuario->orientador->id);
        } elseif ($usuario->isCoordenador()) {
            // Coordenador vê todos os TCCs do curso
            $cursosIds = $usuario->cursos()->pluck('id');
            $query->whereIn('curso_id', $cursosIds);
        }

        $tccs = $query->paginate($request->get('per_page', 15));

        return TccResource::collection($tccs);
    }

    /**
     * Criar novo TCC
     */
    public function store(StoreTccRequest $request): JsonResponse
    {
        try {
            $tcc = $this->tccService->create($request->validated());

            return response()->json([
                'message' => 'TCC criado com sucesso',
                'data' => new TccResource($tcc->load([
                    'aluno.usuario',
                    'curso',
                    'linhaPesquisa',
                ])),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar TCC',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exibir TCC específico
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $tcc = QueryBuilder::for(Tcc::class)
            ->allowedIncludes([
                'aluno.usuario',
                'curso',
                'linhaPesquisa',
                'orientacoes.orientador.usuario',
                'documentos.tipoDocumento',
                'bancas.membros.usuario',
                'cronograma.etapas',
            ])
            ->findOrFail($id);

        // Verificar permissão
        // $this->authorize('view', $tcc);

        return response()->json([
            'data' => new TccResource($tcc),
        ]);
    }

    /**
     * Atualizar TCC
     */
    public function update(UpdateTccRequest $request, string $id): JsonResponse
    {
        try {
            $tcc = Tcc::findOrFail($id);

            // $this->authorize('update', $tcc);

            $tcc = $this->tccService->update($tcc, $request->validated());

            return response()->json([
                'message' => 'TCC atualizado com sucesso',
                'data' => new TccResource($tcc),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar TCC',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Excluir TCC (soft delete)
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $tcc = Tcc::findOrFail($id);

        // $this->authorize('delete', $tcc);

        $tcc->delete();

        return response()->json([
            'message' => 'TCC excluído com sucesso',
        ]);
    }

    /**
     * Submeter TCC para banca
     */
    public function submeterParaBanca(Request $request, string $id): JsonResponse
    {
        try {
            $tcc = Tcc::findOrFail($id);

            // $this->authorize('update', $tcc);

            $this->tccService->submeterParaBanca($tcc);

            return response()->json([
                'message' => 'TCC submetido para banca com sucesso',
                'data' => new TccResource($tcc->fresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao submeter TCC',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancelar TCC
     */
    public function cancelar(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'motivo' => 'required|string|max:1000',
        ]);

        $tcc = Tcc::findOrFail($id);

        // $this->authorize('delete', $tcc);

        $tcc->cancelar($validated['motivo']);

        return response()->json([
            'message' => 'TCC cancelado com sucesso',
            'data' => new TccResource($tcc->fresh()),
        ]);
    }

    /**
     * Obter dashboard/estatísticas do TCC
     */
    public function dashboard(Request $request, string $id): JsonResponse
    {
        $tcc = Tcc::with([
            'aluno.usuario',
            'orientacoes.orientador.usuario',
            'cronograma.etapas',
            'documentos',
            'bancas',
        ])->findOrFail($id);

        // $this->authorize('view', $tcc);

        return response()->json([
            'data' => [
                'tcc' => new TccResource($tcc),
                'estatisticas' => [
                    'progresso_geral' => $tcc->calcularProgresso(),
                    'dias_restantes' => $tcc->getDiasRestantes(),
                    'total_documentos' => $tcc->documentos()->count(),
                    'documentos_aprovados' => $tcc->documentos()->where('status', 'APROVADO')->count(),
                    'etapas_concluidas' => $tcc->cronograma?->etapas()->where('status', 'CONCLUIDA')->count() ?? 0,
                    'etapas_atrasadas' => $tcc->cronograma?->etapas()->where('status', 'ATRASADA')->count() ?? 0,
                ],
            ],
        ]);
    }

    /**
     * Relatório de TCCs (para coordenadores e admins)
     */
    public function relatorio(Request $request): JsonResponse
    {
        // $this->authorize('viewAny', Tcc::class);

        $validated = $request->validate([
            'curso_id' => 'nullable|uuid|exists:cursos,id',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
        ]);

        $estatisticas = $this->tccService->gerarRelatorio($validated);

        return response()->json([
            'data' => $estatisticas,
        ]);
    }
}
