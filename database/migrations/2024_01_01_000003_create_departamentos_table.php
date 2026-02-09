<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('instituicao_id')->constrained('instituicoes')->onDelete('cascade');
            
            $table->string('nome');
            $table->string('codigo', 50)->unique();
            $table->text('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['instituicao_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departamentos');
    }
};
