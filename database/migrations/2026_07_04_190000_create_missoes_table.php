<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Missões (objetivos temporários, diários/semanais) — referência global.
     */
    public function up(): void
    {
        Schema::create('missoes', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('descricao');
            $table->string('icone')->nullable();
            $table->string('tipo');    // responder, acertar
            $table->unsignedInteger('meta');
            $table->string('periodo'); // diaria, semanal
            $table->unsignedInteger('recompensa_pontos')->default(0);
            $table->unsignedInteger('recompensa_xp')->default(0);
            $table->string('status')->default('ativa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missoes');
    }
};
