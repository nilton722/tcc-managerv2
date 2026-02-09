<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('usuario_id')->constrained('usuarios')->onDelete('cascade');
            
            $table->enum('tipo', [
                'INFO',
                'ALERTA',
                'PRAZO',
                'APROVACAO',
                'REJEICAO',
                'CONVITE',
                'LEMBRETE'
            ]);
            
            $table->string('titulo');
            $table->text('mensagem');
            
            $table->string('link_referencia', 500)->nullable();
            $table->string('entidade_tipo', 50)->nullable();
            $table->uuid('entidade_id')->nullable();
            
            $table->boolean('lida')->default(false);
            $table->timestamp('data_leitura')->nullable();
            
            $table->enum('canal', ['SISTEMA', 'EMAIL', 'SMS', 'PUSH'])->default('SISTEMA');
            $table->boolean('enviado')->default(false);
            
            $table->timestamps();

            $table->index(['usuario_id', 'lida']);
            $table->index(['entidade_tipo', 'entidade_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacoes');
    }
};
