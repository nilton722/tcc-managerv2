<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aluno;
use App\Models\Usuario;
use App\Http\Resources\AlunoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlunoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Aluno::with(['usuario', 'curso.departamento']);

        // Filtros
        if ($request->curso_id) {
            $query->where('curso_id', $request->curso_id);
        }

        if ($request->search) {
            $query->whereHas('usuario', function ($q) use ($request) {
                $q->where('nome_completo', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            })->orWhere('matricula', 'like', "%{$request->search}%");
        }

        if ($request->status) {
            $query->whereHas('usuario', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        if ($request->com_tcc_ativo === 'true') {
            $query->whereHas('tccs', function ($q) {
                $q->whereIn('status', [
                    'RASCUNHO', 'EM_ORIENTACAO', 'AGUARDANDO_BANCA', 
                    'BANCA_AGENDADA', 'EM_AVALIACAO'
                ]);
            });
        }

        $alunos = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => AlunoResource::collection($alunos),
            'meta' => [
                'current_page' => $alunos->currentPage(),
                'last_page' => $alunos->lastPage(),
                'per_page' => $alunos->perPage(),
                'total' => $alunos->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        // $this->authorize('create', Aluno::class);

        $validated = $request->validate([
            'nome_completo' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'numero_matricula' => 'required|string|size:14|unique:usuarios,numero_matricula',
            'telefone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'curso_id' => 'required|uuid|exists:cursos,id',
            'matricula' => 'required|string|max:50|unique:alunos,matricula',
            'data_ingresso' => 'required|date',
            'data_prevista_conclusao' => 'required|date|after:data_ingresso',
            'lattes_url' => 'nullable|url|max:500',
            'orcid' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            // Criar usuário
            $usuario = Usuario::create([
                'instituicao_id' => auth()->user()->instituicao_id,
                'nome_completo' => $validated['nome_completo'],
                'email' => $validated['email'],
                'numero_matricula' => $validated['numero_matricula'],
                'telefone' => $validated['telefone'] ?? null,
                'password' => bcrypt($validated['password']),
                'tipo_usuario' => 'ALUNO',
                'status' => 'ATIVO',
                'email_verificado' => true,
            ]);

            // Criar aluno
            $aluno = Aluno::create([
                'usuario_id' => $usuario->id,
                'curso_id' => $validated['curso_id'],
                'matricula' => $validated['matricula'],
                'data_ingresso' => $validated['data_ingresso'],
                'data_prevista_conclusao' => $validated['data_prevista_conclusao'],
                'lattes_url' => $validated['lattes_url'] ?? null,
                'orcid' => $validated['orcid'] ?? null,
            ]);

            // Atribuir role
            $usuario->assignRole('aluno');

            DB::commit();

            $aluno->load(['usuario', 'curso']);

            return response()->json([
                'success' => true,
                'message' => 'Aluno criado com sucesso',
                'data' => new AlunoResource($aluno),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar aluno: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        $aluno = Aluno::with([
            'usuario',
            'curso.departamento.instituicao',
            'tccs.orientacoes.orientador.usuario',
            'tccs.curso',
        ])->findOrFail($id);

        // $this->authorize('view', $aluno);

        return response()->json([
            'success' => true,
            'data' => new AlunoResource($aluno),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $aluno = Aluno::findOrFail($id);

        // $this->authorize('update', $aluno);

        $validated = $request->validate([
            'nome_completo' => 'sometimes|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'curso_id' => 'sometimes|uuid|exists:cursos,id',
            'data_prevista_conclusao' => 'sometimes|date',
            'lattes_url' => 'nullable|url|max:500',
            'orcid' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            // Atualizar usuário
            if (isset($validated['nome_completo']) || isset($validated['telefone'])) {
                $aluno->usuario->update([
                    'nome_completo' => $validated['nome_completo'] ?? $aluno->usuario->nome_completo,
                    'telefone' => $validated['telefone'] ?? $aluno->usuario->telefone,
                ]);
            }

            // Atualizar aluno
            $aluno->update([
                'curso_id' => $validated['curso_id'] ?? $aluno->curso_id,
                'data_prevista_conclusao' => $validated['data_prevista_conclusao'] ?? $aluno->data_prevista_conclusao,
                'lattes_url' => $validated['lattes_url'] ?? $aluno->lattes_url,
                'orcid' => $validated['orcid'] ?? $aluno->orcid,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aluno atualizado com sucesso',
                'data' => new AlunoResource($aluno->fresh(['usuario', 'curso'])),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar aluno: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        $aluno = Aluno::findOrFail($id);

        // $this->authorize('delete', $aluno);

        // Verificar se tem TCCs ativos
        if ($aluno->temTccAtivo()) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir aluno com TCC ativo',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $usuario = $aluno->usuario;
            $aluno->delete();
            $usuario->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aluno excluído com sucesso',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir aluno: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function estatisticas(string $id): JsonResponse
    {
        $aluno = Aluno::with(['tccs'])->findOrFail($id);

        // $this->authorize('view', $aluno);

        return response()->json([
            'success' => true,
            'data' => [
                'total_tccs' => $aluno->tccs->count(),
                'tem_tcc_ativo' => $aluno->temTccAtivo(),
                'tccs_aprovados' => $aluno->getTotalTccsAprovados(),
                'media_notas' => $aluno->getMediaNotas(),
                'tempo_curso_dias' => now()->diffInDays($aluno->data_ingresso),
                'dias_para_conclusao' => $aluno->data_prevista_conclusao ? 
                    now()->diffInDays($aluno->data_prevista_conclusao, false) : null,
            ],
        ]);
    }

    public function tccAtivo(string $id): JsonResponse
    {
        $aluno = Aluno::findOrFail($id);

        // $this->authorize('view', $aluno);

        $tccAtivo = $aluno->getTccEmAndamento();

        if (!$tccAtivo) {
            return response()->json([
                'success' => false,
                'message' => 'Aluno não possui TCC ativo',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tccAtivo,
        ]);
    }
}
