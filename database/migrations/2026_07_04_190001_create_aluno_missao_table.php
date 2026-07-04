<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Progresso do aluno em cada missão, por período (referencia).
     */
    public function up(): void
    {
        Schema::create('aluno_missao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('missao_id')->constrained('missoes')->cascadeOnDelete();
            $table->string('referencia'); // ex.: 2026-07-04 (diária) ou 2026-W27 (semanal)
            $table->unsignedInteger('progresso')->default(0);
            $table->boolean('concluida')->default(false);
            $table->timestamp('concluida_em')->nullable();
            $table->timestamps();

            $table->unique(['aluno_id', 'missao_id', 'referencia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aluno_missao');
    }
};
