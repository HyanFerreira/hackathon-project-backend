<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Conquistas desbloqueadas por cada aluno.
     */
    public function up(): void
    {
        Schema::create('aluno_conquista', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('conquista_id')->constrained('conquistas')->cascadeOnDelete();
            $table->timestamp('desbloqueada_em');
            $table->timestamps();

            $table->unique(['aluno_id', 'conquista_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aluno_conquista');
    }
};
