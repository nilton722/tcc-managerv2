<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membros_banca', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('banca_id')->constrained('bancas')->onDelete('cascade');
            $table->foreignUuid('usuario_id')->constrained('usuarios')->onDelete('restrict');
            
            $table->enum('tipo_participacao', [
                'PRESIDENTE',
                'ORIENTADOR',
                'EXAMINADOR_INTERNO',
                'EXAMINADOR_EXTERNO',
                'SUPLENTE'
            ]);
            
            $table->string('instituicao_externa')->nullable();
            $table->boolean('confirmado')->default(false);
            $table->boolean('presente')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['banca_id', 'usuario_id']);
            $table->index(['usuario_id', 'confirmado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membros_banca');
    }
};
