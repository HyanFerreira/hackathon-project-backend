<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sessões ao vivo conduzidas pelo professor para uma turma.
     */
    public function up(): void
    {
        Schema::create('sessoes_ao_vivo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->foreignId('professor_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo')->nullable();
            $table->string('status')->default('aguardando');
            $table->timestamp('iniciada_em')->nullable();
            $table->timestamp('pausada_em')->nullable();
            $table->timestamp('finalizada_em')->nullable();
            $table->timestamp('professor_online_em')->nullable();
            $table->string('motivo_encerramento')->nullable();
            $table->timestamps();

            $table->index(['professor_id', 'status']);
            $table->index(['turma_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessoes_ao_vivo');
    }
};
