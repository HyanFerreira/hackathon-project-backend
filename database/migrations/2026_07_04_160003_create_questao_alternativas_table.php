<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alternativas de cada questão. Regra: >= 2 alternativas e exatamente 1 correta.
     */
    public function up(): void
    {
        Schema::create('questao_alternativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questao_id')->constrained('questoes')->cascadeOnDelete();
            $table->text('texto');
            $table->boolean('correta')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questao_alternativas');
    }
};
