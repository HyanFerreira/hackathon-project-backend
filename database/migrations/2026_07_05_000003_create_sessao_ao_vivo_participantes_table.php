<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Presença dos alunos dentro da sessão ao vivo.
     */
    public function up(): void
    {
        Schema::create('sessao_ao_vivo_participantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sessao_ao_vivo_id')->constrained('sessoes_ao_vivo')->cascadeOnDelete();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->timestamp('entrou_em');
            $table->timestamp('saiu_em')->nullable();
            $table->timestamps();

            $table->unique(['sessao_ao_vivo_id', 'aluno_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessao_ao_vivo_participantes');
    }
};
