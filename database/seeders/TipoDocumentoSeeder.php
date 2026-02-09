<?php

namespace Database\Seeders;

use App\Models\TipoDocumento;
use Illuminate\Database\Seeder;

class TipoDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposDocumentos = [
            [
                'nome' => 'Projeto de Pesquisa',
                'descricao' => 'Documento inicial contendo a proposta do TCC',
                'extensoes_permitidas' => '[".pdf", ".doc", ".docx"]',
                'tamanho_maximo_mb' => 10,
                'obrigatorio' => true,
                'ordem_exibicao' => 1,
            ],
            [
                'nome' => 'Relatório de Qualificação',
                'descricao' => 'Versão preliminar do trabalho para banca de qualificação',
                'extensoes_permitidas' => '[".pdf", ".doc", ".docx"]',
                'tamanho_maximo_mb' => 50,
                'obrigatorio' => true,
                'ordem_exibicao' => 2,
            ],
            [
                'nome' => 'Versão Final',
                'descricao' => 'Versão completa e definitiva do TCC',
                'extensoes_permitidas' => '[".pdf"]',
                'tamanho_maximo_mb' => 50,
                'obrigatorio' => true,
                'ordem_exibicao' => 3,
            ],
            [
                'nome' => 'Artigo Científico',
                'descricao' => 'Artigo derivado do TCC (opcional)',
                'extensoes_permitidas' => '[".pdf", ".doc", ".docx"]',
                'tamanho_maximo_mb' => 10,
                'obrigatorio' => false,
                'ordem_exibicao' => 4,
            ],
            [
                'nome' => 'Código Fonte',
                'descricao' => 'Código fonte ou anexos técnicos do trabalho',
                'extensoes_permitidas' => '[".zip", ".rar", ".7z", ".tar.gz"]',
                'tamanho_maximo_mb' => 100,
                'obrigatorio' => false,
                'ordem_exibicao' => 5,
            ],
            [
                'nome' => 'Apresentação',
                'descricao' => 'Slides da apresentação para defesa',
                'extensoes_permitidas' => '[".ppt", ".pptx", ".pdf"]',
                'tamanho_maximo_mb' => 20,
                'obrigatorio' => false,
                'ordem_exibicao' => 6,
            ],
            [
                'nome' => 'Ficha Catalográfica',
                'descricao' => 'Ficha catalográfica do trabalho',
                'extensoes_permitidas' => '[".pdf"]',
                'tamanho_maximo_mb' => 5,
                'obrigatorio' => true,
                'ordem_exibicao' => 7,
            ],
            [
                'nome' => 'Termo de Autorização',
                'descricao' => 'Termo de autorização para publicação no repositório',
                'extensoes_permitidas' => '[".pdf"]',
                'tamanho_maximo_mb' => 5,
                'obrigatorio' => true,
                'ordem_exibicao' => 8,
            ],
            [
                'nome' => 'Folha de Aprovação',
                'descricao' => 'Folha de aprovação assinada pela banca',
                'extensoes_permitidas' => '[".pdf"]',
                'tamanho_maximo_mb' => 5,
                'obrigatorio' => true,
                'ordem_exibicao' => 9,
            ],
            [
                'nome' => 'Ata de Defesa',
                'descricao' => 'Ata da defesa do trabalho',
                'extensoes_permitidas' => '[".pdf"]',
                'tamanho_maximo_mb' => 5,
                'obrigatorio' => true,
                'ordem_exibicao' => 10,
            ],
        ];

        foreach ($tiposDocumentos as $tipo) {
            TipoDocumento::create($tipo);
        }
    }
}
