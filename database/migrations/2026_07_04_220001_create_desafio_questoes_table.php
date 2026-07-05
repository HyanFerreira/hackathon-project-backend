<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Questões sorteadas para cada desafio, na ordem em que aparecem.
     */
    public function up(): void
    {
        Schema::create('desafio_questoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desafio_id')->constrained('desafios')->cascadeOnDelete();
            $table->foreignId('questao_id')->constrained('questoes')->cascadeOnDelete();
            $table->unsignedInteger('ordem');
            $table->timestamps();

            $table->unique(['desafio_id', 'ordem']);
            $table->unique(['desafio_id', 'questao_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desafio_questoes');
    }
};
