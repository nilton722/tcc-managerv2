<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\Aluno;
use App\Models\Orientador;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthService
{
    public function register(array $data)
    {
        DB::beginTransaction();

        try {
            $usuario = Usuario::create([
                'instituicao_id' => $data['instituicao_id'],
                'nome_completo' => $data['nome_completo'],
                'email' => $data['email'],
                'bi' => $data['bi'],
                'telefone' => $data['telefone'] ?? null,
                'password' => Hash::make($data['password']),
                'tipo_usuario' => $data['tipo_usuario'],
                'status' => 'PENDENTE',
                'email_verificado' => false,
                'token_verificacao' => Str::random(64),
            ]);

            if ($data['tipo_usuario'] === 'ALUNO' && isset($data['curso_id'])) {
                Aluno::create([
                    'usuario_id' => $usuario->id,
                    'curso_id' => $data['curso_id'],
                    'matricula' => $data['matricula'],
                    'data_ingresso' => $data['data_ingresso'] ?? now(),
                    'data_prevista_conclusao' => $data['data_prevista_conclusao'] ?? now()->addYears(4),
                ]);
            }

            $usuario->assignRole(strtolower($data['tipo_usuario']));

            DB::commit();

            return $usuario;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function verificarEmail(string $token)
    {
        $usuario = Usuario::where('token_verificacao', $token)->first();

        if (!$usuario) {
            throw new \Exception('Token inválido');
        }

        $usuario->update([
            'email_verificado' => true,
            'token_verificacao' => null,
            'status' => 'ATIVO',
        ]);

        return $usuario;
    }
}