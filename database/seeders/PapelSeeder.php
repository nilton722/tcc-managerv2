<?php
// database/seeders/PapelSeeder.php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PapelSeeder extends Seeder
{
    public function run(): void
    {
        $papeis = [
            ['nome' => 'ADMIN', 'descricao' => 'Administrador do sistema'],
            ['nome' => 'COORDENADOR', 'descricao' => 'Coordenador de curso'],
            ['nome' => 'ORIENTADOR', 'descricao' => 'Professor orientador'],
            ['nome' => 'ALUNO', 'descricao' => 'Estudante'],
        ];

        foreach ($papeis as $papel) {
            DB::table('papeis')->insert([
                'id' => Str::id(),
                'nome' => $papel['nome'],
                'descricao' => $papel['descricao'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

