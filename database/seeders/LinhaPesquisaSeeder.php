<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\LinhaPesquisa;
use Illuminate\Database\Seeder;

class LinhaPesquisaSeeder extends Seeder
{
    public function run(): void
    {
        $cursoCC = Curso::where('codigo', 'CC')->first();
        $cursoES = Curso::where('codigo', 'ES')->first();
        $cursoSI = Curso::where('codigo', 'SI')->first();

        if (!$cursoCC || !$cursoES || !$cursoSI) {
            $this->command->error('Cursos não encontrados! Execute CursoSeeder primeiro.');
            return;
        }

        $linhas = [
            // Ciência da Computação
            [
                'curso_id' => $cursoCC->id,
                'nome' => 'Inteligência Artificial',
                'descricao' => 'Pesquisas em Machine Learning, Deep Learning e aplicações de IA',
                'area_conhecimento' => 'Ciência da Computação',
                'palavras_chave' => ['IA', 'Machine Learning', 'Deep Learning', 'Redes Neurais'],
                'ativo' => true,
            ],
            [
                'curso_id' => $cursoCC->id,
                'nome' => 'Banco de Dados',
                'descricao' => 'Pesquisas em SGBDs, NoSQL, Big Data e otimização de queries',
                'area_conhecimento' => 'Ciência da Computação',
                'palavras_chave' => ['Banco de Dados', 'SQL', 'NoSQL', 'Big Data'],
                'ativo' => true,
            ],
            [
                'curso_id' => $cursoCC->id,
                'nome' => 'Redes de Computadores',
                'descricao' => 'Pesquisas em protocolos, segurança de redes e IoT',
                'area_conhecimento' => 'Ciência da Computação',
                'palavras_chave' => ['Redes', 'Protocolos', 'Segurança', 'IoT'],
                'ativo' => true,
            ],

            // Engenharia de Software
            [
                'curso_id' => $cursoES->id,
                'nome' => 'Engenharia de Requisitos',
                'descricao' => 'Técnicas de elicitação, análise e validação de requisitos',
                'area_conhecimento' => 'Engenharia de Software',
                'palavras_chave' => ['Requisitos', 'UML', 'Casos de Uso', 'Modelagem'],
                'ativo' => true,
            ],
            [
                'curso_id' => $cursoES->id,
                'nome' => 'Arquitetura de Software',
                'descricao' => 'Padrões arquiteturais, microsserviços e cloud computing',
                'area_conhecimento' => 'Engenharia de Software',
                'palavras_chave' => ['Arquitetura', 'Padrões', 'Microsserviços', 'Cloud'],
                'ativo' => true,
            ],
            [
                'curso_id' => $cursoES->id,
                'nome' => 'Testes de Software',
                'descricao' => 'Estratégias de teste, automação e qualidade de software',
                'area_conhecimento' => 'Engenharia de Software',
                'palavras_chave' => ['Testes', 'Qualidade', 'Automação', 'TDD'],
                'ativo' => true,
            ],

            // Sistemas de Informação
            [
                'curso_id' => $cursoSI->id,
                'nome' => 'Sistemas Web',
                'descricao' => 'Desenvolvimento de aplicações web modernas e responsivas',
                'area_conhecimento' => 'Sistemas de Informação',
                'palavras_chave' => ['Web', 'Frontend', 'Backend', 'Full Stack'],
                'ativo' => true,
            ],
            [
                'curso_id' => $cursoSI->id,
                'nome' => 'Sistemas Mobile',
                'descricao' => 'Desenvolvimento de aplicativos móveis nativos e híbridos',
                'area_conhecimento' => 'Sistemas de Informação',
                'palavras_chave' => ['Mobile', 'Android', 'iOS', 'React Native'],
                'ativo' => true,
            ],
            [
                'curso_id' => $cursoSI->id,
                'nome' => 'Business Intelligence',
                'descricao' => 'Análise de dados, dashboards e tomada de decisão',
                'area_conhecimento' => 'Sistemas de Informação',
                'palavras_chave' => ['BI', 'Análise de Dados', 'Dashboard', 'KPI'],
                'ativo' => true,
            ],
        ];

        foreach ($linhas as $linha) {
            LinhaPesquisa::create($linha);
        }
    }
}
