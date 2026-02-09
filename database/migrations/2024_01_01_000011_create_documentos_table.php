<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tcc_id')->constrained('tccs')->onDelete('cascade');
            $table->foreignUuid('tipo_documento_id')->constrained('tipos_documento')->onDelete('restrict');
            $table->foreignUuid('versao_anterior_id')->nullable()->constrained('documentos')->onDelete('set null');
            $table->foreignUuid('upload_por')->constrained('usuarios')->onDelete('restrict');
            
            $table->string('nome_arquivo');
            $table->string('arquivo_url', 1000);
            $table->bigInteger('tamanho_bytes')->nullable();
            $table->string('hash_arquivo', 64)->nullable(); // SHA-256
            $table->string('mime_type', 100)->nullable();
            
            $table->integer('versao')->default(1);
            $table->enum('status', ['PENDENTE', 'APROVADO', 'REJEITADO', 'REVISAO'])->default('PENDENTE');
            $table->text('comentarios')->nullable();
            
            $table->timestamp('upload_em')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tcc_id', 'deleted_at']);
            $table->index(['tipo_documento_id', 'status']);
            $table->index('upload_por');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
