<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tcc;
use App\Models\Curso;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->isAluno()) {
            return $this->dashboardAluno();
        } elseif ($user->isOrientador()) {
            return $this->dashboardOrientador();
        } elseif ($user->isCoordenador()) {
            return $this->dashboardCoordenador();
        } else {
            return $this->dashboardAdmin();
        }
    }

    private function dashboardAluno(): JsonResponse
    {
        $aluno = auth()->user()->aluno;
        $tccAtivo = $aluno->getTccEmAndamento();

        $data = [
            'tipo' => 'ALUNO',
            'tcc_ativo' => $tccAtivo,
            'total_tccs' => $aluno->tccs->count(),
            'tccs_concluidos' => $aluno->getTotalTccsAprovados(),
            'media_notas' => $aluno->getMediaNotas(),
        ];

        if ($tccAtivo) {
            $data['progresso'] = $tccAtivo->calcularProgresso();
            $data['dias_restantes'] = $tccAtivo->getDiasRestantes();
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    private function dashboardOrientador(): JsonResponse
    {
        $orientador = auth()->user()->orientador;

        return response()->json([
            'success' => true,
            'data' => [
                'tipo' => 'ORIENTADOR',
                'orientandos_atuais' => $orientador->orientandos_atuais,
                'vagas_disponiveis' => $orientador->getVagasDisponiveis(),
                'taxa_ocupacao' => $orientador->getTaxaOcupacao(),
                'total_orientados' => $orientador->getTotalTccsOrientados(),
                'total_concluidos' => $orientador->getTotalTccsConcluidos(),
                'media_notas' => $orientador->getMediaNotasOrientados(),
                'tccs_por_status' => $orientador->getTccsPorStatus(),
            ],
        ]);
    }

    private function dashboardCoordenador(): JsonResponse
    {
        $totalTccs = Tcc::count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'tipo' => 'COORDENADOR',
                'total_tccs' => $totalTccs,
                'total_alunos' => Usuario::where('tipo_usuario', 'ALUNO')->count(),
                'total_orientadores' => Usuario::where('tipo_usuario', 'ORIENTADOR')->count(),
                'tccs_por_status' => [
                    'RASCUNHO' => Tcc::where('status', 'RASCUNHO')->count(),
                    'EM_ORIENTACAO' => Tcc::where('status', 'EM_ORIENTACAO')->count(),
                    'AGUARDANDO_BANCA' => Tcc::where('status', 'AGUARDANDO_BANCA')->count(),
                    'APROVADO' => Tcc::where('status', 'APROVADO')->count(),
                    'REPROVADO' => Tcc::where('status', 'REPROVADO')->count(),
                ],
            ],
        ]);
    }

    private function dashboardAdmin(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'tipo' => 'ADMIN',
                'total_usuarios' => Usuario::count(),
                'total_tccs' => Tcc::count(),
                'total_cursos' => Curso::count(),
                'usuarios_por_tipo' => [
                    'ADMIN' => Usuario::where('tipo_usuario', 'ADMIN')->count(),
                    'COORDENADOR' => Usuario::where('tipo_usuario', 'COORDENADOR')->count(),
                    'ORIENTADOR' => Usuario::where('tipo_usuario', 'ORIENTADOR')->count(),
                    'ALUNO' => Usuario::where('tipo_usuario', 'ALUNO')->count(),
                ],
            ],
        ]);
    }

    public function estatisticas(): JsonResponse
    {
        $totalTccs = Tcc::count();
        $aprovados = Tcc::whereIn('status', ['APROVADO', 'APROVADO_COM_RESSALVAS'])->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_tccs' => $totalTccs,
                'aprovados' => $aprovados,
                'reprovados' => Tcc::where('status', 'REPROVADO')->count(),
                'em_andamento' => Tcc::whereIn('status', ['RASCUNHO', 'EM_ORIENTACAO', 'AGUARDANDO_BANCA', 'BANCA_AGENDADA'])->count(),
                'taxa_aprovacao' => $totalTccs > 0 ? round(($aprovados / $totalTccs) * 100, 2) : 0,
                'media_notas' => Tcc::whereNotNull('nota_final')->avg('nota_final'),
            ],
        ]);
    }
}