<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Orientador;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class OrientadorSeeder extends Seeder
{
    public function run(): void
    {
        $departamento = Departamento::where('codigo', 'DCC')->first();

        if (!$departamento) {
            $this->command->error('Departamento não encontrado! Execute DepartamentoSeeder primeiro.');
            return;
        }

        $usuariosOrientadores = Usuario::where('tipo_usuario', 'ORIENTADOR')->get();

        if ($usuariosOrientadores->isEmpty()) {
            $this->command->error('Usuários orientadores não encontrados! Execute UsuarioSeeder primeiro.');
            return;
        }

        $titulacoes = ['MESTRE', 'DOUTOR', 'POS_DOUTOR'];
        $areasAtuacao = [
            ['Inteligência Artificial', 'Machine Learning'],
            ['Banco de Dados', 'Big Data'],
            ['Engenharia de Software', 'Arquitetura de Software'],
        ];

        $contador = 0;

        foreach ($usuariosOrientadores as $usuario) {
            Orientador::create([
                'usuario_id' => $usuario->id,
                'departamento_id' => $departamento->id,
                'titulacao' => $titulacoes[$contador % count($titulacoes)],
                'areas_atuacao' => $areasAtuacao[$contador % count($areasAtuacao)],
                'lattes_url' => 'http://lattes.cnpq.br/' . str_pad($contador + 1, 16, '0', STR_PAD_LEFT),
                'orcid' => '0000-0000-0000-' . str_pad($contador + 1, 4, '0', STR_PAD_LEFT),
                'max_orientandos' => 10,
                'orientandos_atuais' => 0,
                'aceita_coorientacao' => true,
                'ativo' => true,
            ]);

            $contador++;
        }
    }
}
