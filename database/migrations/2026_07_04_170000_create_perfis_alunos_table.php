<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perfil gamificado do aluno (pontos, XP, nível e energia).
     */
    public function up(): void
    {
        Schema::create('perfis_alunos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->unique()->constrained('alunos')->cascadeOnDelete();
            $table->unsignedInteger('pontos')->default(0);
            $table->unsignedInteger('xp')->default(0);
            $table->unsignedInteger('nivel')->default(1);
            $table->unsignedInteger('energia')->default(10);
            $table->unsignedInteger('energia_maxima')->default(10);
            $table->timestamp('energia_atualizada_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfis_alunos');
    }
};
