<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('departamento_id')->constrained('departamentos')->onDelete('cascade');
            $table->foreignUuid('coordenador_id')->nullable()->constrained('usuarios')->onDelete('set null');
            
            $table->string('nome');
            $table->string('codigo', 50)->unique();
            $table->enum('nivel', ['GRADUACAO', 'ESPECIALIZACAO', 'MESTRADO', 'DOUTORADO']);
            $table->integer('duracao_semestres')->nullable();
            $table->boolean('ativo')->default(true);
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['departamento_id', 'ativo']);
            $table->index('nivel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
