<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Orientador;
use App\Models\Usuario;
use App\Http\Resources\OrientadorResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrientadorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Orientador::with(['usuario', 'departamento.instituicao']);

        if ($request->departamento_id) {
            $query->where('departamento_id', $request->departamento_id);
        }

        if ($request->titulacao) {
            $query->where('titulacao', $request->titulacao);
        }

        if ($request->ativo !== null) {
            $query->where('ativo', $request->ativo === 'true');
        }

        if ($request->disponiveis === 'true') {
            $query->disponiveis();
        }

        if ($request->aceita_coorientacao === 'true') {
            $query->aceitaCoorientacao();
        }

        if ($request->search) {
            $query->whereHas('usuario', function ($q) use ($request) {
                $q->where('nome_completo', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $orientadores = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => OrientadorResource::collection($orientadores),
            'meta' => [
                'current_page' => $orientadores->currentPage(),
                'last_page' => $orientadores->lastPage(),
                'per_page' => $orientadores->perPage(),
                'total' => $orientadores->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nome_completo' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'numero_matricula' => 'required|string|size:14|unique:usuarios,numero_matricula',
            'telefone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'departamento_id' => 'required|uuid|exists:departamentos,id',
            'titulacao' => 'required|in:ESPECIALISTA,MESTRE,DOUTOR,POS_DOUTOR',
            'areas_atuacao' => 'required|array|min:1',
            'lattes_url' => 'nullable|url|max:500',
            'orcid' => 'nullable|string|max:50',
            'max_orientandos' => 'nullable|integer|min:1|max:20',
            'aceita_coorientacao' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $usuario = Usuario::create([
                'instituicao_id' => auth()->user()->instituicao_id,
                'nome_completo' => $validated['nome_completo'],
                'email' => $validated['email'],
                'numero_matricula' => $validated['numero_matricula'],
                'telefone' => $validated['telefone'] ?? null,
                'password' => bcrypt($validated['password']),
                'tipo_usuario' => 'ORIENTADOR',
                'status' => 'ATIVO',
                'email_verificado' => true,
            ]);

            $orientador = Orientador::create([
                'usuario_id' => $usuario->id,
                'departamento_id' => $validated['departamento_id'],
                'titulacao' => $validated['titulacao'],
                'areas_atuacao' => $validated['areas_atuacao'],
                'lattes_url' => $validated['lattes_url'] ?? null,
                'orcid' => $validated['orcid'] ?? null,
                'max_orientandos' => $validated['max_orientandos'] ?? 10,
                'orientandos_atuais' => 0,
                'aceita_coorientacao' => $validated['aceita_coorientacao'] ?? true,
                'ativo' => true,
            ]);

            $usuario->assignRole('orientador');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orientador criado com sucesso',
                'data' => new OrientadorResource($orientador->load(['usuario', 'departamento'])),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        $orientador = Orientador::with([
            'usuario',
            'departamento.instituicao',
            'orientacoes.tcc.aluno.usuario'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new OrientadorResource($orientador),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $orientador = Orientador::findOrFail($id);

        $validated = $request->validate([
            'nome_completo' => 'sometimes|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'departamento_id' => 'sometimes|uuid|exists:departamentos,id',
            'titulacao' => 'sometimes|in:ESPECIALISTA,MESTRE,DOUTOR,POS_DOUTOR',
            'areas_atuacao' => 'sometimes|array|min:1',
            'lattes_url' => 'nullable|url|max:500',
            'orcid' => 'nullable|string|max:50',
            'max_orientandos' => 'sometimes|integer|min:1|max:20',
            'aceita_coorientacao' => 'sometimes|boolean',
        ]);

        DB::beginTransaction();

        try {
            if (isset($validated['nome_completo']) || isset($validated['telefone'])) {
                $orientador->usuario->update([
                    'nome_completo' => $validated['nome_completo'] ?? $orientador->usuario->nome_completo,
                    'telefone' => $validated['telefone'] ?? $orientador->usuario->telefone,
                ]);
            }

            $orientador->update([
                'departamento_id' => $validated['departamento_id'] ?? $orientador->departamento_id,
                'titulacao' => $validated['titulacao'] ?? $orientador->titulacao,
                'areas_atuacao' => $validated['areas_atuacao'] ?? $orientador->areas_atuacao,
                'lattes_url' => $validated['lattes_url'] ?? $orientador->lattes_url,
                'orcid' => $validated['orcid'] ?? $orientador->orcid,
                'max_orientandos' => $validated['max_orientandos'] ?? $orientador->max_orientandos,
                'aceita_coorientacao' => $validated['aceita_coorientacao'] ?? $orientador->aceita_coorientacao,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orientador atualizado com sucesso',
                'data' => new OrientadorResource($orientador->fresh(['usuario', 'departamento'])),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        $orientador = Orientador::findOrFail($id);

        if ($orientador->orientandos_atuais > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir orientador com orientandos ativos',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $usuario = $orientador->usuario;
            $orientador->delete();
            $usuario->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Orientador excluído com sucesso']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function tccs(string $id): JsonResponse
    {
        $orientador = Orientador::findOrFail($id);

        $tccs = $orientador->tccsOrientando()
            ->with(['aluno.usuario', 'curso', 'linhaPesquisa'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tccs,
            'meta' => [
                'total' => $tccs->count(),
                'por_status' => $orientador->getTccsPorStatus(),
            ],
        ]);
    }

    public function disponiveis(Request $request): JsonResponse
    {
        $orientadores = Orientador::with(['usuario', 'departamento'])
            ->ativos()
            ->disponiveis()
            ->when($request->departamento_id, function ($q, $departamentoId) {
                $q->where('departamento_id', $departamentoId);
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => OrientadorResource::collection($orientadores),
        ]);
    }

    public function estatisticas(string $id): JsonResponse
    {
        $orientador = Orientador::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'vagas_disponiveis' => $orientador->getVagasDisponiveis(),
                'taxa_ocupacao' => $orientador->getTaxaOcupacao(),
                'total_orientandos' => $orientador->orientandos_atuais,
                'max_orientandos' => $orientador->max_orientandos,
                'total_tccs_orientados' => $orientador->getTotalTccsOrientados(),
                'total_tccs_concluidos' => $orientador->getTotalTccsConcluidos(),
                'media_notas' => $orientador->getMediaNotasOrientados(),
                'tccs_por_status' => $orientador->getTccsPorStatus(),
            ],
        ]);
    }
}