<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates_cronograma', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('curso_id')->constrained('cursos')->onDelete('cascade');
            
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['curso_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('templates_cronograma');
    }
};
