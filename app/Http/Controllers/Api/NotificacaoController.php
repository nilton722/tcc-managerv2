<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notificacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacaoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Notificacao::where('usuario_id', auth()->id());

        if ($request->tipo) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->lida !== null) {
            $query->where('lida', $request->lida === 'true');
        }

        $notificacoes = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $notificacoes->items(),
            'meta' => [
                'current_page' => $notificacoes->currentPage(),
                'total' => $notificacoes->total(),
                'nao_lidas' => Notificacao::where('usuario_id', auth()->id())->naoLidas()->count(),
            ],
        ]);
    }

    public function naoLidas(): JsonResponse
    {
        $notificacoes = Notificacao::where('usuario_id', auth()->id())
            ->naoLidas()
            ->recentes()
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notificacoes,
            'total' => $notificacoes->count(),
        ]);
    }

    public function marcarLida(string $id): JsonResponse
    {
        $notificacao = Notificacao::where('usuario_id', auth()->id())->findOrFail($id);
        $notificacao->marcarComoLida();

        return response()->json(['success' => true, 'message' => 'Notificação marcada como lida']);
    }

    public function marcarTodasLidas(): JsonResponse
    {
        Notificacao::where('usuario_id', auth()->id())
            ->naoLidas()
            ->update(['lida' => true, 'data_leitura' => now()]);

        return response()->json(['success' => true, 'message' => 'Todas as notificações foram marcadas como lidas']);
    }

    public function destroy(string $id): JsonResponse
    {
        $notificacao = Notificacao::where('usuario_id', auth()->id())->findOrFail($id);
        $notificacao->delete();

        return response()->json(['success' => true, 'message' => 'Notificação excluída']);
    }
}