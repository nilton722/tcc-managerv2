<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Instituicao;
use Illuminate\Database\Seeder;

class DepartamentoSeeder extends Seeder
{
    public function run(): void
    {
        $uft = Instituicao::where('sigla', 'UFT')->first();
        $ict = Instituicao::where('sigla', 'ICT')->first();

        if (!$uft || !$ict) {
            $this->command->error('Instituições não encontradas! Execute InstituicaoSeeder primeiro.');
            return;
        }

        $departamentos = [
            // UFT
            [
                'instituicao_id' => $uft->id,
                'nome' => 'Departamento de Ciência da Computação',
                'codigo' => 'DCC',
                'descricao' => 'Departamento responsável pelos cursos de Ciência da Computação',
                'ativo' => true,
            ],
            [
                'instituicao_id' => $uft->id,
                'nome' => 'Departamento de Engenharia de Software',
                'codigo' => 'DES',
                'descricao' => 'Departamento responsável pelos cursos de Engenharia de Software',
                'ativo' => true,
            ],
            [
                'instituicao_id' => $uft->id,
                'nome' => 'Departamento de Sistemas de Informação',
                'codigo' => 'DSI',
                'descricao' => 'Departamento responsável pelos cursos de Sistemas de Informação',
                'ativo' => true,
            ],

            // ICT
            [
                'instituicao_id' => $ict->id,
                'nome' => 'Departamento de Tecnologia da Informação',
                'codigo' => 'DTI',
                'descricao' => 'Departamento de TI do ICT',
                'ativo' => true,
            ],
            [
                'instituicao_id' => $ict->id,
                'nome' => 'Departamento de Análise e Desenvolvimento',
                'codigo' => 'DAD',
                'descricao' => 'Departamento de Análise e Desenvolvimento de Sistemas',
                'ativo' => true,
            ],
        ];

        foreach ($departamentos as $departamento) {
            Departamento::create($departamento);
        }
    }
}
