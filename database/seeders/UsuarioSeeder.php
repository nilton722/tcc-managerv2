<?php

namespace Database\Seeders;

use App\Models\Instituicao;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $uft = Instituicao::where('sigla', 'UFT')->first();

        if (!$uft) {
            $this->command->error('Instituição UFT não encontrada! Execute InstituicaoSeeder primeiro.');
            return;
        }

        $usuarios = [
            [
                'instituicao_id' => $uft->id,
                'nome_completo' => 'Administrador do Sistema',
                'email' => 'admin@tccmanager.com',
                'password' => Hash::make('password123'),
                'numero_matricula' => '111.111.111-11',
                'telefone' => '245 95 660 55 25',
                'tipo_usuario' => 'ADMIN',
                'status' => 'ATIVO',
                'email_verificado' => true,
            ],
            [
                'instituicao_id' => $uft->id,
                'nome_completo' => 'Prof. Dr. João Silva',
                'email' => 'coordenador@tccmanager.com',
                'password' => Hash::make('password123'),
                'numero_matricula' => '222.222.222-22',
                'telefone' => '(11) 92222-2222',
                'tipo_usuario' => 'COORDENADOR',
                'status' => 'ATIVO',
                'email_verificado' => true,
            ],
            [
                'instituicao_id' => $uft->id,
                'nome_completo' => 'Prof. Dra. Maria Santos',
                'email' => 'orientador@tccmanager.com',
                'password' => Hash::make('password123'),
                'numero_matricula' => '333.333.333-33',
                'telefone' => '(11) 93333-3333',
                'tipo_usuario' => 'ORIENTADOR',
                'status' => 'ATIVO',
                'email_verificado' => true,
            ],
            [
                'instituicao_id' => $uft->id,
                'nome_completo' => 'Carlos Eduardo Oliveira',
                'email' => 'aluno@tccmanager.com',
                'password' => Hash::make('password123'),
                'numero_matricula' => '444.444.444-44',
                'telefone' => '(11) 94444-4444',
                'tipo_usuario' => 'ALUNO',
                'status' => 'ATIVO',
                'email_verificado' => true,
            ],
            [
                'instituicao_id' => $uft->id,
                'nome_completo' => 'Ana Paula Costa',
                'email' => 'aluno2@tccmanager.com',
                'password' => Hash::make('password123'),
                'numero_matricula' => '555.555.555-55',
                'telefone' => '(11) 95555-5555',
                'tipo_usuario' => 'ALUNO',
                'status' => 'ATIVO',
                'email_verificado' => true,
            ],
            [
                'instituicao_id' => $uft->id,
                'nome_completo' => 'Prof. Dr. Ricardo Mendes',
                'email' => 'orientador2@tccmanager.com',
                'password' => Hash::make('password123'),
                'numero_matricula' => '666.666.666-66',
                'telefone' => '(11) 96666-6666',
                'tipo_usuario' => 'ORIENTADOR',
                'status' => 'ATIVO',
                'email_verificado' => true,
            ],
        ];

        foreach ($usuarios as $usuario) {
            Usuario::create($usuario);
        }
    }
}
