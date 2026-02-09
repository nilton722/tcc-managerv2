<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Usuario;
use App\Services\AuthService;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Registro de novo usuário
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            
            $usuario = $this->authService->register($request->validated());

            return response()->json([
                'message' => 'Usuário registrado com sucesso. Verifique seu email.',
                'data' => [
                    'usuario' => $usuario,
                    'token' => $usuario->createToken('auth_token')->plainTextToken,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao registrar usuário',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login de usuário
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        if ($usuario->status === 'BLOQUEADO') {
            return response()->json([
                'message' => 'Usuário bloqueado. Entre em contato com o administrador.',
            ], 403);
        }

        if ($usuario->status === 'INATIVO') {
            return response()->json([
                'message' => 'Usuário inativo.',
            ], 403);
        }

        // Registrar acesso
        $usuario->registrarAcesso();

        // Criar token
        $token = $usuario->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso',
            'data' => [
                'usuario' => $usuario->load(['instituicao', 'aluno', 'orientador']),
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout de usuário
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso',
        ]);
    }

    /**
     * Obter dados do usuário autenticado
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()->load(['instituicao', 'aluno.curso', 'orientador.departamento']),
        ]);
    }

    /**
     * Atualizar perfil do usuário autenticado
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nome_completo' => 'sometimes|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'foto_perfil_url' => 'nullable|url',
        ]);

        $request->user()->update($validated);

        return response()->json([
            'message' => 'Perfil atualizado com sucesso',
            'data' => $request->user(),
        ]);
    }

    /**
     * Alterar senha
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'senha_atual' => 'required|string',
            'nova_senha' => 'required|string|min:8|confirmed',
        ]);

        $usuario = $request->user();

        if (!Hash::check($validated['senha_atual'], $usuario->password)) {
            throw ValidationException::withMessages([
                'senha_atual' => ['Senha atual incorreta.'],
            ]);
        }

        $usuario->update([
            'password' => Hash::make($validated['nova_senha']),
        ]);

        return response()->json([
            'message' => 'Senha alterada com sucesso',
        ]);
    }

    /**
     * Verificar email
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $usuario = Usuario::where('token_verificacao', $validated['token'])->first();

        if (!$usuario) {
            return response()->json([
                'message' => 'Token inválido',
            ], 404);
        }

        $usuario->verificarEmail();

        return response()->json([
            'message' => 'Email verificado com sucesso',
        ]);
    }

    /**
     * Solicitar recuperação de senha
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $usuario = Usuario::where('email', $validated['email'])->first();

        if ($usuario) {
            // Implementar lógica de envio de email
            // $this->authService->sendPasswordResetEmail($usuario);
        }

        return response()->json([
            'message' => 'Se o email existir, você receberá instruções para recuperação de senha.',
        ]);
    }
}
