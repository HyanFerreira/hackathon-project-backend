<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Questões selecionadas para a sessão, na ordem de envio.
     */
    public function up(): void
    {
        Schema::create('sessao_ao_vivo_questoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sessao_ao_vivo_id')->constrained('sessoes_ao_vivo')->cascadeOnDelete();
            $table->foreignId('questao_id')->constrained('questoes')->cascadeOnDelete();
            $table->unsignedInteger('ordem');
            $table->boolean('atual')->default(false);
            $table->timestamp('enviada_em')->nullable();
            $table->timestamp('encerrada_em')->nullable();
            $table->timestamps();

            $table->unique(['sessao_ao_vivo_id', 'ordem']);
            $table->unique(['sessao_ao_vivo_id', 'questao_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessao_ao_vivo_questoes');
    }
};
