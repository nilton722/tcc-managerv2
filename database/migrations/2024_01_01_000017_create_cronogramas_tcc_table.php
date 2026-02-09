<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cronogramas_tcc', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tcc_id')->constrained('tccs')->onDelete('cascade');
            $table->foreignUuid('template_cronograma_id')->nullable()->constrained('templates_cronograma')->onDelete('set null');
            
            $table->date('data_inicio');
            $table->date('data_fim_prevista');
            
            $table->timestamps();

            $table->index('tcc_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cronogramas_tcc');
    }
};
