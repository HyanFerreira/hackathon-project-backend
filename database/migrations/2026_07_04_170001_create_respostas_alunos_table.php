<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Respostas dos alunos às questões (uma resposta por questão por aluno).
     */
    public function up(): void
    {
        Schema::create('respostas_alunos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('questao_id')->constrained('questoes')->cascadeOnDelete();
            $table->foreignId('alternativa_id')->constrained('questao_alternativas')->cascadeOnDelete();
            $table->boolean('correta');
            $table->unsignedInteger('pontos_ganhos')->default(0);
            $table->unsignedInteger('xp_ganho')->default(0);
            $table->unsignedInteger('energia_gasta')->default(0);
            $table->timestamp('respondido_em');
            $table->timestamps();

            $table->unique(['aluno_id', 'questao_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respostas_alunos');
    }
};
