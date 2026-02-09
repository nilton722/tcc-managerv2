<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instituicoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome');
            $table->string('sigla', 20);
            $table->string('nif', 30)->unique()->nullable();
            $table->json('endereco')->nullable();
            $table->string('logo_url')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['ativo', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instituicoes');
    }
};
