<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('instituicao_id')->constrained('instituicoes')->onDelete('cascade');
            
            $table->string('email')->unique();
            $table->string('password');
            $table->string('nome_completo');
            $table->string('numero_matricula', 20);
            $table->unique(['numero_matricula', 'instituicao_id']);
            
            $table->string('telefone', 20)->nullable();
            $table->string('foto_perfil_url')->nullable();
            
            $table->enum('tipo_usuario', ['ALUNO', 'ORIENTADOR', 'COORDENADOR', 'ADMIN']);
            $table->enum('status', ['ATIVO', 'INATIVO', 'BLOQUEADO', 'PENDENTE'])->default('PENDENTE');
            
            $table->timestamp('ultimo_acesso')->nullable();
            $table->boolean('email_verificado')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('token_verificacao', 100)->nullable();
            
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email', 'deleted_at']);
            $table->index(['tipo_usuario', 'status']);
            $table->index('instituicao_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
