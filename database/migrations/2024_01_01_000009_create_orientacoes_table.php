<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orientacoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tcc_id')->constrained('tccs')->onDelete('cascade');
            $table->foreignUuid('orientador_id')->constrained('orientadores')->onDelete('restrict');
            
            $table->enum('tipo_orientacao', ['ORIENTADOR', 'COORIENTADOR']);
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->boolean('ativo')->default(true);
            
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tcc_id', 'orientador_id']);
            $table->index(['orientador_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orientacoes');
    }
};
