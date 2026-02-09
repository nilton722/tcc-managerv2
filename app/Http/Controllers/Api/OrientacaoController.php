<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Orientacao;
use App\Models\Orientador;
use App\Models\Tcc;
use App\Http\Resources\OrientacaoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrientacaoController extends Controller
{
    public function index(Request $request, string $tccId): JsonResponse
    {
        $tcc = Tcc::findOrFail($tccId);
        
        // $this->authorize('view', $tcc);

        $orientacoes = Orientacao::where('tcc_id', $tccId)
            ->with(['orientador.usuario', 'orientador.departamento'])
            ->when($request->tipo_orientacao, function ($query, $tipo) {
                $query->where('tipo_orientacao', $tipo);
            })
            ->when($request->ativo !== null, function ($query) use ($request) {
                $query->where('ativo', $request->ativo === 'true');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => OrientacaoResource::collection($orientacoes),
        ]);
    }

    public function store(Request $request, string $tccId): JsonResponse
    {
        $tcc = Tcc::findOrFail($tccId);

        // $this->authorize('manageOrientacoes', $tcc);

        $validated = $request->validate([
            'orientador_id' => 'required|uuid|exists:orientadores,id',
            'tipo_orientacao' => 'required|in:ORIENTADOR,COORIENTADOR',
            'data_inicio' => 'nullable|date',
        ]);

        $orientador = Orientador::findOrFail($validated['orientador_id']);

        // Validações
        if ($validated['tipo_orientacao'] === 'ORIENTADOR') {
            if (!$orientador->podeOrientar()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Orientador atingiu o limite máximo de orientandos',
                ], 422);
            }

            // Desativar orientador anterior se existir
            Orientacao::where('tcc_id', $tccId)
                ->where('tipo_orientacao', 'ORIENTADOR')
                ->where('ativo', true)
                ->update([
                    'ativo' => false,
                    'data_fim' => now(),
                ]);
        } else {
            if (!$orientador->podeCoorientar()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Orientador não aceita coorientação ou atingiu o limite',
                ], 422);
            }
        }

        // Verificar se já existe orientação ativa do mesmo tipo
        $existente = Orientacao::where('tcc_id', $tccId)
            ->where('orientador_id', $validated['orientador_id'])
            ->where('tipo_orientacao', $validated['tipo_orientacao'])
            ->where('ativo', true)
            ->first();

        if ($existente) {
            return response()->json([
                'success' => false,
                'message' => 'Este orientador já está vinculado a este TCC com este tipo de orientação',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $orientacao = Orientacao::create([
                'tcc_id' => $tccId,
                'orientador_id' => $validated['orientador_id'],
                'tipo_orientacao' => $validated['tipo_orientacao'],
                'data_inicio' => $validated['data_inicio'] ?? now(),
                'ativo' => true,
            ]);

            // Incrementar contador de orientandos
            $orientador->incrementarOrientandos();

            // Atualizar status do TCC se estava em RASCUNHO
            if ($tcc->status === 'RASCUNHO' && $validated['tipo_orientacao'] === 'ORIENTADOR') {
                $tcc->update(['status' => 'EM_ORIENTACAO']);
            }

            DB::commit();

            $orientacao->load(['orientador.usuario', 'orientador.departamento']);

            return response()->json([
                'success' => true,
                'message' => 'Orientação criada com sucesso',
                'data' => new OrientacaoResource($orientacao),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar orientação: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $tccId, string $id): JsonResponse
    {
        $tcc = Tcc::findOrFail($tccId);
        
        // $this->authorize('view', $tcc);

        $orientacao = Orientacao::where('tcc_id', $tccId)
            ->with(['orientador.usuario', 'orientador.departamento', 'tcc'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new OrientacaoResource($orientacao),
        ]);
    }

    public function destroy(string $tccId, string $id): JsonResponse
    {
        $tcc = Tcc::findOrFail($tccId);

        // $this->authorize('manageOrientacoes', $tcc);

        $orientacao = Orientacao::where('tcc_id', $tccId)->findOrFail($id);

        if (!$orientacao->ativo) {
            return response()->json([
                'success' => false,
                'message' => 'Orientação já está inativa',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $orientacao->finalizar();

            // Decrementar contador de orientandos
            $orientacao->orientador->decrementarOrientandos();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orientação finalizada com sucesso',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar orientação: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function listarOrientandos(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user->isOrientador()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não é orientador',
            ], 403);
        }

        $orientador = $user->orientador;

        $orientacoes = Orientacao::where('orientador_id', $orientador->id)
            ->with(['tcc.aluno.usuario', 'tcc.curso', 'tcc.linhaPesquisa'])
            ->when($request->ativo !== null, function ($query) use ($request) {
                $query->where('ativo', $request->ativo === 'true');
            })
            ->when($request->tipo_orientacao, function ($query, $tipo) {
                $query->where('tipo_orientacao', $tipo);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => OrientacaoResource::collection($orientacoes),
            'meta' => [
                'total' => $orientacoes->count(),
                'ativas' => $orientacoes->where('ativo', true)->count(),
                'orientador' => $orientacoes->where('tipo_orientacao', 'ORIENTADOR')->count(),
                'coorientador' => $orientacoes->where('tipo_orientacao', 'COORIENTADOR')->count(),
            ],
        ]);
    }
}
