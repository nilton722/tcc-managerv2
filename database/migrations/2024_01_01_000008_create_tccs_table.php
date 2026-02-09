<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tccs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('aluno_id')->constrained('alunos')->onDelete('restrict');
            $table->foreignUuid('curso_id')->constrained('cursos')->onDelete('restrict');
            $table->foreignUuid('linha_pesquisa_id')->nullable()->constrained('linhas_pesquisa')->onDelete('set null');
            
            $table->string('titulo', 500);
            $table->string('titulo_ingles', 500)->nullable();
            $table->enum('tipo_trabalho', ['TCC', 'MONOGRAFIA', 'DISSERTACAO', 'TESE'])->default('MONOGRAFIA');
            
            $table->text('resumo')->nullable();
            $table->text('abstract')->nullable();
            $table->json('palavras_chave')->nullable();
            $table->json('keywords')->nullable();
            
            $table->enum('fase_tcc', ['PRE_PROJETO', 'MONOGRAFIA', 'DEFESA']);
            $table->enum('status', [
                'RASCUNHO',
                'EM_ORIENTACAO',
                'AGUARDANDO_BANCA',
                'BANCA_AGENDADA',
                'EM_AVALIACAO',
                'APROVADO',
                'APROVADO_COM_RESSALVAS',
                'REPROVADO',
                'CANCELADO'
            ])->default('RASCUNHO');

            
            $table->date('data_inicio')->nullable();
            $table->date('data_qualificacao')->nullable();
            $table->date('data_defesa')->nullable();
            $table->date('data_entrega_final')->nullable();
            
            $table->decimal('nota_final', 4, 2)->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['aluno_id', 'deleted_at']);
            $table->index(['status', 'deleted_at']);
            $table->index('curso_id');
            $table->fullText('titulo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tccs');
    }
};
