<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\EtapaTemplate;
use App\Models\TemplateCronograma;
use Illuminate\Database\Seeder;

class TemplateCronogramaSeeder extends Seeder
{
    public function run(): void
    {
        $cursoCC = Curso::where('codigo', 'CC')->first();

        if (!$cursoCC) {
            $this->command->error('Curso não encontrado! Execute CursoSeeder primeiro.');
            return;
        }

        // Template para TCC de Graduação
        $template = TemplateCronograma::create([
            'curso_id' => $cursoCC->id,
            'nome' => 'Cronograma Padrão - TCC Graduação',
            'descricao' => 'Cronograma padrão para trabalhos de conclusão de curso de graduação',
            'ativo' => true,
        ]);

        $etapas = [
            [
                'template_cronograma_id' => $template->id,
                'nome' => 'Definição do Tema',
                'descricao' => 'Escolha e aprovação do tema do TCC',
                'ordem' => 1,
                'duracao_dias' => 14,
                'obrigatoria' => true,
                'documentos_exigidos' => [],
            ],
            [
                'template_cronograma_id' => $template->id,
                'nome' => 'Escolha do Orientador',
                'descricao' => 'Definição do orientador do trabalho',
                'ordem' => 2,
                'duracao_dias' => 7,
                'obrigatoria' => true,
                'documentos_exigidos' => [],
            ],
            [
                'template_cronograma_id' => $template->id,
                'nome' => 'Elaboração do Projeto',
                'descricao' => 'Desenvolvimento do projeto de pesquisa',
                'ordem' => 3,
                'duracao_dias' => 30,
                'obrigatoria' => true,
                'documentos_exigidos' => ['Projeto de Pesquisa'],
            ],
            [
                'template_cronograma_id' => $template->id,
                'nome' => 'Aprovação do Projeto',
                'descricao' => 'Submissão e aprovação do projeto pelo orientador',
                'ordem' => 4,
                'duracao_dias' => 14,
                'obrigatoria' => true,
                'documentos_exigidos' => ['Projeto de Pesquisa'],
            ],
            [
                'template_cronograma_id' => $template->id,
                'nome' => 'Desenvolvimento do TCC',
                'descricao' => 'Fase de desenvolvimento do trabalho',
                'ordem' => 5,
                'duracao_dias' => 90,
                'obrigatoria' => true,
                'documentos_exigidos' => [],
            ],
            [
                'template_cronograma_id' => $template->id,
                'nome' => 'Qualificação',
                'descricao' => 'Apresentação do trabalho na banca de qualificação',
                'ordem' => 6,
                'duracao_dias' => 7,
                'obrigatoria' => true,
                'documentos_exigidos' => ['Relatório de Qualificação'],
            ],
            [
                'template_cronograma_id' => $template->id,
                'nome' => 'Ajustes Pós-Qualificação',
                'descricao' => 'Correções solicitadas na qualificação',
                'ordem' => 7,
                'duracao_dias' => 30,
                'obrigatoria' => true,
                'documentos_exigidos' => [],
            ],
            [
                'template_cronograma_id' => $template->id,
                'nome' => 'Finalização do TCC',
                'descricao' => 'Redação final e revisão do documento',
                'ordem' => 8,
                'duracao_dias' => 30,
                'obrigatoria' => true,
                'documentos_exigidos' => ['Versão Final'],
            ],
            [
                'template_cronograma_id' => $template->id,
                'nome' => 'Preparação da Defesa',
                'descricao' => 'Preparação da apresentação para a banca',
                'ordem' => 9,
                'duracao_dias' => 14,
                'obrigatoria' => true,
                'documentos_exigidos' => ['Apresentação'],
            ],
            [
                'template_cronograma_id' => $template->id,
                'nome' => 'Defesa Final',
                'descricao' => 'Apresentação e defesa perante a banca examinadora',
                'ordem' => 10,
                'duracao_dias' => 1,
                'obrigatoria' => true,
                'documentos_exigidos' => ['Versão Final', 'Apresentação', 'Ficha Catalográfica', 'Termo de Autorização'],
            ],
            [
                'template_cronograma_id' => $template->id,
                'nome' => 'Ajustes Finais',
                'descricao' => 'Correções solicitadas pela banca',
                'ordem' => 11,
                'duracao_dias' => 14,
                'obrigatoria' => true,
                'documentos_exigidos' => [],
            ],
            [
                'template_cronograma_id' => $template->id,
                'nome' => 'Entrega Final',
                'descricao' => 'Entrega da versão definitiva do TCC',
                'ordem' => 12,
                'duracao_dias' => 7,
                'obrigatoria' => true,
                'documentos_exigidos' => ['Versão Final', 'Folha de Aprovação', 'Ata de Defesa'],
            ],
        ];

        foreach ($etapas as $etapa) {
            EtapaTemplate::create($etapa);
        }
    }
}
