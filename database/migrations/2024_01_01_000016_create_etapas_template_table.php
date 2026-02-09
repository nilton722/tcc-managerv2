<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etapas_template', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_cronograma_id')->constrained('templates_cronograma')->onDelete('cascade');
            
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->integer('ordem');
            $table->integer('duracao_dias')->nullable();
            $table->boolean('obrigatoria')->default(true);
            $table->json('documentos_exigidos')->nullable();
            
            $table->timestamps();

            $table->index(['template_cronograma_id', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etapas_template');
    }
};
