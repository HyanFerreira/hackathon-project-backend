<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Conquistas (marcos permanentes de gamificação) — referência global.
     */
    public function up(): void
    {
        Schema::create('conquistas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('descricao');
            $table->string('icone')->nullable();
            $table->string('tipo'); // questoes_respondidas, acertos, sequencia_acertos, pontos, nivel
            $table->unsignedInteger('meta');
            $table->unsignedInteger('recompensa_pontos')->default(0);
            $table->unsignedInteger('recompensa_xp')->default(0);
            $table->string('status')->default('ativa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conquistas');
    }
};
