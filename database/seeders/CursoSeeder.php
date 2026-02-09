<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Departamento;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class CursoSeeder extends Seeder
{
    public function run(): void
    {
        $dcc = Departamento::where('codigo', 'DCC')->first();
        $des = Departamento::where('codigo', 'DES')->first();
        $dsi = Departamento::where('codigo', 'DSI')->first();

        if (!$dcc || !$des || !$dsi) {
            $this->command->error('Departamentos não encontrados! Execute DepartamentoSeeder primeiro.');
            return;
        }

        $coordenador = Usuario::where('tipo_usuario', 'COORDENADOR')->first();

        if (!$coordenador) {
            $this->command->error('Coordenador não encontrado! Execute UsuarioSeeder primeiro.');
            return;
        }

        $cursos = [
            [
                'departamento_id' => $dcc->id,
                'coordenador_id' => $coordenador->id,
                'nome' => 'Ciência da Computação',
                'codigo' => 'CC',
                'nivel' => 'GRADUACAO',
                'duracao_semestres' => 8,
                'ativo' => true,
            ],
            [
                'departamento_id' => $dcc->id,
                'coordenador_id' => $coordenador->id,
                'nome' => 'Mestrado em Ciência da Computação',
                'codigo' => 'MCC',
                'nivel' => 'MESTRADO',
                'duracao_semestres' => 4,
                'ativo' => true,
            ],
            [
                'departamento_id' => $des->id,
                'coordenador_id' => $coordenador->id,
                'nome' => 'Engenharia de Software',
                'codigo' => 'ES',
                'nivel' => 'GRADUACAO',
                'duracao_semestres' => 10,
                'ativo' => true,
            ],
            [
                'departamento_id' => $des->id,
                'coordenador_id' => $coordenador->id,
                'nome' => 'Doutorado em Engenharia de Software',
                'codigo' => 'DES',
                'nivel' => 'DOUTORADO',
                'duracao_semestres' => 8,
                'ativo' => true,
            ],
            [
                'departamento_id' => $dsi->id,
                'coordenador_id' => $coordenador->id,
                'nome' => 'Sistemas de Informação',
                'codigo' => 'SI',
                'nivel' => 'GRADUACAO',
                'duracao_semestres' => 8,
                'ativo' => true,
            ],
            [
                'departamento_id' => $dsi->id,
                'coordenador_id' => $coordenador->id,
                'nome' => 'Especialização em Desenvolvimento Web',
                'codigo' => 'EDW',
                'nivel' => 'ESPECIALIZACAO',
                'duracao_semestres' => 3,
                'ativo' => true,
            ],
        ];

        foreach ($cursos as $curso) {
            Curso::create($curso);
        }
    }
}
