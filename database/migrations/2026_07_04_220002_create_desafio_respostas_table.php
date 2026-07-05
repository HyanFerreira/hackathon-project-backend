<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Respostas dos dois alunos durante o desafio (com o tempo de resposta).
     */
    public function up(): void
    {
        Schema::create('desafio_respostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desafio_id')->constrained('desafios')->cascadeOnDelete();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('questao_id')->constrained('questoes')->cascadeOnDelete();
            $table->foreignId('alternativa_id')->nullable()->constrained('questao_alternativas')->nullOnDelete();
            $table->boolean('correta')->default(false);
            $table->unsignedInteger('tempo_resposta_ms')->default(0);
            $table->timestamp('respondido_em');
            $table->timestamps();

            $table->unique(['desafio_id', 'aluno_id', 'questao_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desafio_respostas');
    }
};
