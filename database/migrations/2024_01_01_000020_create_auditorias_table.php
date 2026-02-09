<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            
            $table->string('acao', 50);
            $table->string('entidade', 50);
            $table->uuid('entidade_id')->nullable();
            
            $table->json('dados_anteriores')->nullable();
            $table->json('dados_novos')->nullable();
            
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamp('created_at')->useCurrent();

            $table->index(['usuario_id', 'created_at']);
            $table->index(['entidade', 'entidade_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
