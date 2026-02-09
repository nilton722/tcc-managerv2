<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alunos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('usuario_id')->unique()->constrained('usuarios')->onDelete('cascade');
            $table->foreignUuid('curso_id')->constrained('cursos')->onDelete('restrict');
            
            $table->string('matricula', 50)->unique();
            $table->date('data_ingresso');
            $table->date('data_prevista_conclusao')->nullable();
            $table->string('lattes_url')->nullable();
            $table->string('orcid', 50)->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index('curso_id');
            $table->index('matricula');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alunos');
    }
};
