<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banca;
use App\Models\MembroBanca;
use App\Models\Tcc;
use App\Models\Avaliacao;
use App\Http\Resources\BancaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BancaController extends Controller
{
    public function index(Request $request, string $tccId): JsonResponse
    {
        $tcc = Tcc::findOrFail($tccId);
        
        // $this->authorize('view', $tcc);

        $bancas = Banca::where('tcc_id', $tccId)
            ->with(['membros.usuario', 'avaliacoes', 'ataDocumento'])
            ->when($request->tipo_banca, function ($query, $tipo) {
                $query->where('tipo_banca', $tipo);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('data_agendada', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => BancaResource::collection($bancas),
        ]);
    }

    public function store(Request $request, string $tccId): JsonResponse
    {
        $tcc = Tcc::findOrFail($tccId);

        // $this->authorize('manageBancas', $tcc);

        $validated = $request->validate([
            'tipo_banca' => 'required|in:QUALIFICACAO,DEFESA_FINAL',
            'data_agendada' => 'required|date|after:now',
            'local' => 'nullable|string|max:500',
            'formato' => 'required|in:PRESENCIAL,REMOTA,HIBRIDA',
            'link_reuniao' => 'nullable|url|max:500',
            'membros' => 'required|array|min:3',
            'membros.*.usuario_id' => 'required|uuid|exists:usuarios,id',
            'membros.*.tipo_participacao' => 'required|in:PRESIDENTE,ORIENTADOR,EXAMINADOR_INTERNO,EXAMINADOR_EXTERNO,SUPLENTE',
            'membros.*.instituicao_externa' => 'nullable|string|max:255',
        ]);

        // Validar se tem orientador
        if (!$tcc->temOrientador()) {
            return response()->json([
                'success' => false,
                'message' => 'TCC precisa ter um orientador antes de agendar banca',
            ], 422);
        }

        // Validar composição da banca
        $presidente = collect($validated['membros'])->where('tipo_participacao', 'PRESIDENTE')->count();
        if ($presidente !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'A banca deve ter exatamente 1 presidente',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $banca = Banca::create([
                'tcc_id' => $tccId,
                'tipo_banca' => $validated['tipo_banca'],
                'data_agendada' => $validated['data_agendada'],
                'local' => $validated['local'] ?? null,
                'formato' => $validated['formato'],
                'link_reuniao' => $validated['link_reuniao'] ?? null,
                'status' => 'AGENDADA',
            ]);

            // Adicionar membros
            foreach ($validated['membros'] as $membroData) {
                MembroBanca::create([
                    'banca_id' => $banca->id,
                    'usuario_id' => $membroData['usuario_id'],
                    'tipo_participacao' => $membroData['tipo_participacao'],
                    'instituicao_externa' => $membroData['instituicao_externa'] ?? null,
                    'confirmado' => false,
                    'presente' => false,
                ]);
            }

            // Atualizar status do TCC
            if ($validated['tipo_banca'] === 'QUALIFICACAO') {
                $tcc->update([
                    'status' => 'BANCA_AGENDADA',
                    'data_qualificacao' => $validated['data_agendada'],
                ]);
            } else {
                $tcc->update([
                    'status' => 'BANCA_AGENDADA',
                    'data_defesa' => $validated['data_agendada'],
                ]);
            }

            DB::commit();

            $banca->load(['membros.usuario', 'tcc']);

            return response()->json([
                'success' => true,
                'message' => 'Banca agendada com sucesso',
                'data' => new BancaResource($banca),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao agendar banca: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $tccId, string $id): JsonResponse
    {
        $tcc = Tcc::findOrFail($tccId);
        
        // $this->authorize('view', $tcc);

        $banca = Banca::where('tcc_id', $tccId)
            ->with(['membros.usuario', 'avaliacoes.membroBanca.usuario', 'ataDocumento'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new BancaResource($banca),
        ]);
    }

    public function update(Request $request, string $tccId, string $id): JsonResponse
    {
        $tcc = Tcc::findOrFail($tccId);

        // $this->authorize('manageBancas', $tcc);

        $banca = Banca::where('tcc_id', $tccId)->findOrFail($id);

        if (in_array($banca->status, ['CONCLUIDA', 'CANCELADA'])) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível atualizar banca concluída ou cancelada',
            ], 422);
        }

        $validated = $request->validate([
            'data_agendada' => 'sometimes|date|after:now',
            'local' => 'nullable|string|max:500',
            'formato' => 'sometimes|in:PRESENCIAL,REMOTA,HIBRIDA',
            'link_reuniao' => 'nullable|url|max:500',
        ]);

        $banca->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Banca atualizada com sucesso',
            'data' => new BancaResource($banca->fresh(['membros.usuario', 'avaliacoes'])),
        ]);
    }

    public function confirmar(string $tccId, string $id): JsonResponse
    {
        $banca = Banca::where('tcc_id', $tccId)->findOrFail($id);

        if (!$banca->temQuorumMinimo()) {
            return response()->json([
                'success' => false,
                'message' => 'Banca não possui quórum mínimo (3 membros confirmados)',
            ], 422);
        }

        $banca->confirmar();

        return response()->json([
            'success' => true,
            'message' => 'Banca confirmada com sucesso',
            'data' => new BancaResource($banca),
        ]);
    }

    public function iniciar(string $tccId, string $id): JsonResponse
    {
        $banca = Banca::where('tcc_id', $tccId)->findOrFail($id);

        if (!$banca->isConfirmada()) {
            return response()->json([
                'success' => false,
                'message' => 'Banca precisa estar confirmada para ser iniciada',
            ], 422);
        }

        $banca->iniciar();

        return response()->json([
            'success' => true,
            'message' => 'Banca iniciada com sucesso',
            'data' => new BancaResource($banca),
        ]);
    }

    public function concluir(string $tccId, string $id): JsonResponse
    {
        $banca = Banca::where('tcc_id', $tccId)->findOrFail($id);

        if (!$banca->isEmAndamento()) {
            return response()->json([
                'success' => false,
                'message' => 'Banca precisa estar em andamento para ser concluída',
            ], 422);
        }

        // Verificar se todas as avaliações foram feitas
        $totalMembros = $banca->membros()->count();
        $totalAvaliacoes = $banca->avaliacoes()->count();

        if ($totalAvaliacoes < $totalMembros) {
            return response()->json([
                'success' => false,
                'message' => 'Todas as avaliações devem ser registradas antes de concluir a banca',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $banca->concluir();

            // Calcular média das notas e atualizar TCC
            $mediaNotas = $banca->getMediaNotas();
            $tcc = $banca->tcc;

            if ($banca->tipo_banca === 'DEFESA_FINAL') {
                // Determinar resultado
                $resultado = $mediaNotas >= 7.0 ? 'APROVADO' : 'REPROVADO';
                
                // Verificar se tem ressalvas
                $temRessalvas = $banca->avaliacoes()
                    ->where('resultado', 'APROVADO_COM_RESSALVAS')
                    ->exists();

                if ($temRessalvas && $resultado === 'APROVADO') {
                    $resultado = 'APROVADO_COM_RESSALVAS';
                }

                $tcc->update([
                    'status' => $resultado,
                    'nota_final' => $mediaNotas,
                ]);
            } else {
                $tcc->update(['status' => 'EM_ORIENTACAO']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Banca concluída com sucesso',
                'data' => new BancaResource($banca->fresh(['membros', 'avaliacoes'])),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao concluir banca: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cancelar(Request $request, string $tccId, string $id): JsonResponse
    {
        $tcc = Tcc::findOrFail($tccId);

        // $this->authorize('manageBancas', $tcc);

        $banca = Banca::where('tcc_id', $tccId)->findOrFail($id);

        $banca->cancelar();

        // Reverter status do TCC
        $tcc->update(['status' => 'AGUARDANDO_BANCA']);

        return response()->json([
            'success' => true,
            'message' => 'Banca cancelada com sucesso',
        ]);
    }

    public function adicionarMembro(Request $request, string $tccId, string $id): JsonResponse
    {
        $banca = Banca::where('tcc_id', $tccId)->findOrFail($id);

        $validated = $request->validate([
            'usuario_id' => 'required|uuid|exists:usuarios,id',
            'tipo_participacao' => 'required|in:PRESIDENTE,ORIENTADOR,EXAMINADOR_INTERNO,EXAMINADOR_EXTERNO,SUPLENTE',
            'instituicao_externa' => 'nullable|string|max:255',
        ]);

        // Verificar duplicação
        $existe = MembroBanca::where('banca_id', $id)
            ->where('usuario_id', $validated['usuario_id'])
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Este usuário já é membro da banca',
            ], 422);
        }

        $membro = MembroBanca::create([
            'banca_id' => $id,
            'usuario_id' => $validated['usuario_id'],
            'tipo_participacao' => $validated['tipo_participacao'],
            'instituicao_externa' => $validated['instituicao_externa'] ?? null,
            'confirmado' => false,
            'presente' => false,
        ]);

        $membro->load('usuario');

        return response()->json([
            'success' => true,
            'message' => 'Membro adicionado à banca',
            'data' => $membro,
        ], 201);
    }

    public function removerMembro(string $tccId, string $bancaId, string $membroId): JsonResponse
    {
        $banca = Banca::where('tcc_id', $tccId)->findOrFail($bancaId);

        if ($banca->isConcluida()) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível remover membros de banca concluída',
            ], 422);
        }

        $membro = MembroBanca::where('banca_id', $bancaId)->findOrFail($membroId);
        $membro->delete();

        return response()->json([
            'success' => true,
            'message' => 'Membro removido da banca',
        ]);
    }

    public function confirmarMembro(string $tccId, string $bancaId, string $membroId): JsonResponse
    {
        $membro = MembroBanca::where('banca_id', $bancaId)->findOrFail($membroId);

        // Verificar se é o próprio usuário confirmando
        if (auth()->id() !== $membro->usuario_id) {
            return response()->json([
                'success' => false,
                'message' => 'Você só pode confirmar sua própria participação',
            ], 403);
        }

        $membro->confirmar();

        return response()->json([
            'success' => true,
            'message' => 'Participação confirmada com sucesso',
        ]);
    }

    public function avaliar(Request $request, string $tccId, string $id): JsonResponse
    {
        $banca = Banca::where('tcc_id', $tccId)->findOrFail($id);

        if (!$banca->isEmAndamento()) {
            return response()->json([
                'success' => false,
                'message' => 'Banca precisa estar em andamento para registrar avaliações',
            ], 422);
        }

        // Verificar se usuário é membro da banca
        $membro = MembroBanca::where('banca_id', $id)
            ->where('usuario_id', auth()->id())
            ->first();

        if (!$membro) {
            return response()->json([
                'success' => false,
                'message' => 'Você não é membro desta banca',
            ], 403);
        }

        $validated = $request->validate([
            'nota' => 'required|numeric|min:0|max:10',
            'parecer' => 'required|string',
            'criterios_avaliacao' => 'nullable|array',
            'resultado' => 'required|in:APROVADO,APROVADO_COM_RESSALVAS,REPROVADO',
            'recomendacoes' => 'nullable|string',
        ]);

        // Verificar se já avaliou
        $avaliacaoExistente = Avaliacao::where('banca_id', $id)
            ->where('membro_banca_id', $membro->id)
            ->first();

        if ($avaliacaoExistente) {
            $avaliacaoExistente->update(array_merge($validated, [
                'data_avaliacao' => now(),
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Avaliação atualizada com sucesso',
                'data' => $avaliacaoExistente,
            ]);
        }

        $avaliacao = Avaliacao::create([
            'banca_id' => $id,
            'membro_banca_id' => $membro->id,
            'nota' => $validated['nota'],
            'parecer' => $validated['parecer'],
            'criterios_avaliacao' => $validated['criterios_avaliacao'] ?? null,
            'resultado' => $validated['resultado'],
            'recomendacoes' => $validated['recomendacoes'] ?? null,
            'data_avaliacao' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Avaliação registrada com sucesso',
            'data' => $avaliacao,
        ], 201);
    }

    public function listarAvaliacoes(string $tccId, string $id): JsonResponse
    {
        $banca = Banca::where('tcc_id', $tccId)->findOrFail($id);

        $avaliacoes = Avaliacao::where('banca_id', $id)
            ->with(['membroBanca.usuario'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $avaliacoes,
            'meta' => [
                'total' => $avaliacoes->count(),
                'media_notas' => $banca->getMediaNotas(),
                'aprovados' => $avaliacoes->where('resultado', 'APROVADO')->count(),
                'reprovados' => $avaliacoes->where('resultado', 'REPROVADO')->count(),
            ],
        ]);
    }
}
