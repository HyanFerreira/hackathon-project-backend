<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Questões criadas pelo professor. A avaliação é por habilidade (BNCC),
     * não por disciplina — o vínculo com disciplina é derivado das habilidades.
     */
    public function up(): void
    {
        Schema::create('questoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escola_id')->constrained('escolas')->cascadeOnDelete();
            $table->foreignId('professor_id')->constrained('users')->cascadeOnDelete();
            $table->text('enunciado');
            $table->string('dificuldade')->default('media'); // facil, media, dificil
            $table->unsignedInteger('pontos')->default(10);
            $table->string('status')->default('ativa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questoes');
    }
};
