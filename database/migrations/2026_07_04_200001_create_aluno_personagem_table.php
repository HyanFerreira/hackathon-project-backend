<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Personagens que o aluno comprou. O personagem evolui de nível conforme
     * o aluno responde questões com ele equipado.
     */
    public function up(): void
    {
        Schema::create('aluno_personagem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('personagem_id')->constrained('personagens')->cascadeOnDelete();
            $table->unsignedInteger('nivel')->default(1);
            $table->unsignedInteger('questoes_respondidas')->default(0);
            $table->boolean('equipado')->default(false);
            $table->timestamp('comprado_em');
            $table->timestamps();

            $table->unique(['aluno_id', 'personagem_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aluno_personagem');
    }
};
