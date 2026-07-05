<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Respostas dos alunos durante uma sessão ao vivo.
     */
    public function up(): void
    {
        Schema::create('sessao_ao_vivo_respostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sessao_ao_vivo_id')->constrained('sessoes_ao_vivo')->cascadeOnDelete();
            $table->foreignId('sessao_ao_vivo_questao_id')->constrained('sessao_ao_vivo_questoes')->cascadeOnDelete();
            $table->foreignId('questao_id')->constrained('questoes')->cascadeOnDelete();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('alternativa_id')->nullable()->constrained('questao_alternativas')->nullOnDelete();
            $table->boolean('correta')->default(false);
            $table->unsignedInteger('tempo_resposta_ms')->default(0);
            $table->unsignedInteger('pontos_ganhos')->default(0);
            $table->unsignedInteger('xp_ganho')->default(0);
            $table->timestamp('respondido_em');
            $table->timestamps();

            $table->unique(['sessao_ao_vivo_id', 'sessao_ao_vivo_questao_id', 'aluno_id'], 'sessao_resposta_unica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessao_ao_vivo_respostas');
    }
};
