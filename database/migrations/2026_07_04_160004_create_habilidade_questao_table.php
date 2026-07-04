<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Habilidades (BNCC) avaliadas por cada questão.
     */
    public function up(): void
    {
        Schema::create('habilidade_questao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questao_id')->constrained('questoes')->cascadeOnDelete();
            $table->foreignId('habilidade_id')->constrained('habilidades')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['questao_id', 'habilidade_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habilidade_questao');
    }
};
