<?php

namespace Database\Seeders;

use App\Models\Aluno;
use App\Models\Curso;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class AlunoSeeder extends Seeder
{
    public function run(): void
    {
        $curso = Curso::where('codigo', 'CC')->first();

        if (!$curso) {
            $this->command->error('Curso não encontrado! Execute CursoSeeder primeiro.');
            return;
        }

        $usuariosAlunos = Usuario::where('tipo_usuario', 'ALUNO')->get();

        if ($usuariosAlunos->isEmpty()) {
            $this->command->error('Usuários alunos não encontrados! Execute UsuarioSeeder primeiro.');
            return;
        }

        $contador = 1;

        foreach ($usuariosAlunos as $usuario) {
            Aluno::create([
                'usuario_id' => $usuario->id,
                'curso_id' => $curso->id,
                'matricula' => '2024' . str_pad($contador, 5, '0', STR_PAD_LEFT),
                'data_ingresso' => now()->subYears(2),
                'data_prevista_conclusao' => now()->addYears(2),
                'lattes_url' => null,
                'orcid' => null,
            ]);

            $contador++;
        }
    }
}
