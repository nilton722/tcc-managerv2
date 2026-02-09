<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('linhas_pesquisa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('curso_id')->constrained('cursos')->onDelete('cascade');
            
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('area_conhecimento', 100)->nullable();
            $table->json('palavras_chave')->nullable();
            $table->boolean('ativo')->default(true);
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['curso_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linhas_pesquisa');
    }
};
