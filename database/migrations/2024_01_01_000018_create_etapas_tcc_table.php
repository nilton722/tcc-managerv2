<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etapas_tcc', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cronograma_tcc_id')->constrained('cronogramas_tcc')->onDelete('cascade');
            $table->foreignUuid('etapa_template_id')->nullable()->constrained('etapas_template')->onDelete('set null');
            
            $table->string('nome');
            $table->integer('ordem');
            
            $table->date('data_inicio_prevista')->nullable();
            $table->date('data_fim_prevista')->nullable();
            $table->date('data_inicio_real')->nullable();
            $table->date('data_conclusao')->nullable();
            
            $table->enum('status', [
                'PENDENTE',
                'EM_ANDAMENTO',
                'CONCLUIDA',
                'ATRASADA',
                'BLOQUEADA',
                'CANCELADA'
            ])->default('PENDENTE');
            
            $table->integer('progresso_percentual')->default(0);
            $table->text('observacoes')->nullable();
            
            $table->timestamps();

            $table->index(['cronograma_tcc_id', 'ordem']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etapas_tcc');
    }
};
