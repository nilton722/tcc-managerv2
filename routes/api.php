<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TccController;
use App\Http\Controllers\Api\DocumentoController;
use App\Http\Controllers\Api\BancaController;
use App\Http\Controllers\Api\OrientacaoController;
use App\Http\Controllers\Api\AlunoController;
use App\Http\Controllers\Api\OrientadorController;
use App\Http\Controllers\Api\CursoController;
use App\Http\Controllers\Api\NotificacaoController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/


Route::get('/test-token', function () {
    return response()->json([
       'user' => auth()->user(),
    ]);
})->middleware('auth:sanctum');



Route::prefix('v1')->group(function () {
    
    // Autenticação
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('verify-email', [AuthController::class, 'verifyEmail']);
    });

});

// Rotas protegidas (com autenticação)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('dashboard/estatisticas', [DashboardController::class, 'estatisticas']);

    // TCCs
    Route::prefix('tccs')->group(function () {
        Route::get('/', [TccController::class, 'index']);
        Route::post('/', [TccController::class, 'store']);
        Route::get('{id}', [TccController::class, 'show']);
        Route::put('{id}', [TccController::class, 'update']);
        Route::delete('{id}', [TccController::class, 'destroy']);
        
        // Ações específicas
        Route::post('{id}/submeter', [TccController::class, 'submeterParaBanca']);
        Route::post('{id}/cancelar', [TccController::class, 'cancelar']);
        Route::get('{id}/dashboard', [TccController::class, 'dashboard']);
        
        // Orientações
        Route::prefix('{tccId}/orientacoes')->group(function () {
            Route::get('/', [OrientacaoController::class, 'index']);
            Route::post('/', [OrientacaoController::class, 'store']);
            Route::delete('{id}', [OrientacaoController::class, 'destroy']);
        });
        
        // Documentos
        Route::prefix('{tccId}/documentos')->group(function () {
            Route::get('/', [DocumentoController::class, 'index']);
            Route::post('/', [DocumentoController::class, 'store']);
            Route::get('{id}', [DocumentoController::class, 'show']);
            Route::put('{id}', [DocumentoController::class, 'update']);
            Route::delete('{id}', [DocumentoController::class, 'destroy']);
            Route::post('{id}/aprovar', [DocumentoController::class, 'aprovar']);
            Route::post('{id}/rejeitar', [DocumentoController::class, 'rejeitar']);
            Route::post('{id}/download', [DocumentoController::class, 'download']);
        });
        
        // Bancas
        Route::prefix('{tccId}/bancas')->group(function () {
            Route::get('/', [BancaController::class, 'index']);
            Route::post('/', [BancaController::class, 'store']);
            Route::get('{id}', [BancaController::class, 'show']);
            Route::put('{id}', [BancaController::class, 'update']);
            Route::delete('{id}', [BancaController::class, 'destroy']);
            Route::post('{id}/confirmar', [BancaController::class, 'confirmar']);
            Route::post('{id}/cancelar', [BancaController::class, 'cancelar']);
            
            // Membros da banca
            Route::post('{id}/membros', [BancaController::class, 'adicionarMembro']);
            Route::delete('{id}/membros/{membroId}', [BancaController::class, 'removerMembro']);
            Route::post('{id}/membros/{membroId}/confirmar', [BancaController::class, 'confirmarPresenca']);
            
            // Avaliações
            Route::post('{id}/avaliacoes', [BancaController::class, 'avaliar']);
            Route::get('{id}/avaliacoes', [BancaController::class, 'listarAvaliacoes']);
        });
    });

    // Relatórios
    Route::prefix('relatorios')->middleware('can:viewReports')->group(function () {
        Route::get('tccs', [TccController::class, 'relatorio']);
        Route::get('orientadores', [OrientadorController::class, 'relatorio']);
        Route::get('alunos', [AlunoController::class, 'relatorio']);
    });

    // Alunos
    Route::prefix('alunos')->group(function () {
        Route::get('/', [AlunoController::class, 'index']);
        Route::post('/', [AlunoController::class, 'store']);
        Route::get('{id}', [AlunoController::class, 'show']);
        Route::put('{id}', [AlunoController::class, 'update']);
        Route::delete('{id}', [AlunoController::class, 'destroy']);
    });

    // Orientadores
    Route::prefix('orientadores')->group(function () {
        Route::get('/', [OrientadorController::class, 'index']);
        Route::post('/', [OrientadorController::class, 'store']);
        Route::get('{id}', [OrientadorController::class, 'show']);
        Route::put('{id}', [OrientadorController::class, 'update']);
        Route::delete('{id}', [OrientadorController::class, 'destroy']);
        Route::get('{id}/tccs', [OrientadorController::class, 'tccs']);
        Route::get('disponiveis', [OrientadorController::class, 'disponiveis']);
    });

    // Cursos
    Route::prefix('cursos')->group(function () {
        Route::get('/', [CursoController::class, 'index']);
        Route::post('/', [CursoController::class, 'store']);
        Route::get('{id}', [CursoController::class, 'show']);
        Route::put('{id}', [CursoController::class, 'update']);
        Route::delete('{id}', [CursoController::class, 'destroy']);
        Route::get('{id}/tccs', [CursoController::class, 'tccs']);
        Route::get('{id}/estatisticas', [CursoController::class, 'estatisticas']);
    });

    // Notificações
    Route::prefix('notificacoes')->group(function () {
        Route::get('/', [NotificacaoController::class, 'index']);
        Route::get('nao-lidas', [NotificacaoController::class, 'naoLidas']);
        Route::post('{id}/marcar-lida', [NotificacaoController::class, 'marcarComoLida']);
        Route::post('marcar-todas-lidas', [NotificacaoController::class, 'marcarTodasComoLidas']);
        Route::delete('{id}', [NotificacaoController::class, 'destroy']);
    });

    // Busca global
    Route::get('buscar', [DashboardController::class, 'buscar']);
});
