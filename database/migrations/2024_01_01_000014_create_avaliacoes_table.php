<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avaliacoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('banca_id')->constrained('bancas')->onDelete('cascade');
            $table->foreignUuid('membro_banca_id')->constrained('membros_banca')->onDelete('cascade');
            
            $table->decimal('nota', 4, 2)->nullable();
            $table->text('parecer')->nullable();
            $table->json('criterios_avaliacao')->nullable();
            
            $table->enum('resultado', ['APROVADO', 'APROVADO_COM_RESSALVAS', 'REPROVADO'])->nullable();
            $table->text('recomendacoes')->nullable();
            
            $table->timestamp('data_avaliacao')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['banca_id', 'membro_banca_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avaliacoes');
    }
};
