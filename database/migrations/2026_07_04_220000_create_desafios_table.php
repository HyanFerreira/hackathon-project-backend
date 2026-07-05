<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Desafios entre alunos (partida ao vivo, amistoso ou valendo pontos).
     */
    public function up(): void
    {
        Schema::create('desafios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->foreignId('turma_id')->nullable()->constrained('turmas')->nullOnDelete();
            $table->foreignId('desafiante_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('desafiado_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('disciplina_id')->nullable()->constrained('disciplinas')->nullOnDelete();
            $table->string('tipo')->default('amistoso'); // amistoso, valendo
            $table->string('status')->default('pendente'); // pendente, recusado, em_andamento, finalizado, cancelado, expirado
            $table->unsignedInteger('quantidade_questoes')->default(5);
            $table->unsignedInteger('questao_atual')->default(0);
            $table->timestamp('questao_iniciada_em')->nullable();
            $table->foreignId('vencedor_id')->nullable()->constrained('alunos')->nullOnDelete();
            $table->timestamp('expira_em')->nullable();
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('finalizado_em')->nullable();
            $table->timestamps();

            $table->index(['desafiado_id', 'status']);
            $table->index(['desafiante_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desafios');
    }
};
