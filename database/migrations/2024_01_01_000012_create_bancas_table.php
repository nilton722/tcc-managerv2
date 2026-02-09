<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bancas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tcc_id')->constrained('tccs')->onDelete('cascade');
            $table->foreignUuid('ata_documento_id')->nullable()->constrained('documentos')->onDelete('set null');
            
            $table->enum('tipo_banca', ['QUALIFICACAO', 'DEFESA_FINAL']);
            $table->timestamp('data_agendada');
            $table->string('local')->nullable();
            $table->enum('formato', ['PRESENCIAL', 'REMOTA', 'HIBRIDA'])->nullable();
            $table->string('link_reuniao', 500)->nullable();
            
            $table->enum('status', [
                'AGENDADA',
                'CONFIRMADA',
                'EM_ANDAMENTO',
                'CONCLUIDA',
                'CANCELADA',
                'REAGENDADA'
            ])->default('AGENDADA');
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tcc_id', 'deleted_at']);
            $table->index(['data_agendada', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bancas');
    }
};
