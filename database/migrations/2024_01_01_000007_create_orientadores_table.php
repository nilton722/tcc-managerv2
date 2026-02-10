<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orientadores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('usuario_id')->unique()->constrained('usuarios')->onDelete('cascade');
            $table->foreignUuid('departamento_id')->constrained('departamentos')->onDelete('restrict');
            
            $table->enum('titulacao', ['ESPECIALISTA', 'MESTRE', 'DOUTOR', 'POS_DOUTOR']);
            $table->json('areas_atuacao')->nullable();
            $table->string('lattes_url')->nullable();
            $table->string('orcid', 50)->nullable();
            
            $table->integer('max_orientandos')->default(10);
            $table->integer('orientandos_atuais')->default(0);
            $table->boolean('aceita_coorientacao')->default(true);
            $table->boolean('ativo')->default(true);
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['departamento_id', 'ativo']);
            $table->index('titulacao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orientadores');
    }
};
