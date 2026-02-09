<?php

namespace Database\Seeders;

use App\Models\Instituicao;
use Illuminate\Database\Seeder;

class InstituicaoSeeder extends Seeder
{
    public function run(): void
    {
        $instituicoes = [
            [
                'nome' => 'Universidade Federal de Tecnologia',
                'sigla' => 'UFT',
                'nif' => '12.345.678/0001-90',
                'endereco' => [
                    'logradouro' => 'Avenida Universitária',
                    'numero' => '1000',
                    'complemento' => 'Campus Central',
                    'bairro' => 'Centro',
                    'cidade' => 'São Paulo',
                    'estado' => 'SP',
                    'cep' => '01000-000',
                ],
                'logo_url' => null,
                'ativo' => true,
            ],
            [
                'nome' => 'Instituto de Ciências e Tecnologia',
                'sigla' => 'ICT',
                'nif' => '98.765.432/0001-10',
                'endereco' => [
                    'logradouro' => 'Rua das Ciências',
                    'numero' => '500',
                    'complemento' => 'Prédio A',
                    'bairro' => 'Jardim Universitário',
                    'cidade' => 'Rio de Janeiro',
                    'estado' => 'RJ',
                    'cep' => '20000-000',
                ],
                'logo_url' => null,
                'ativo' => true,
            ],
        ];

        foreach ($instituicoes as $instituicao) {
            Instituicao::create($instituicao);
        }
    }
}
