<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Habilidades da BNCC (ex.: EF06MA01), unidade de avaliação das questões.
     */
    public function up(): void
    {
        Schema::create('habilidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disciplina_id')->constrained('disciplinas')->cascadeOnDelete();
            $table->string('codigo', 20)->unique();
            $table->text('descricao');
            $table->string('etapa', 10)->nullable();  // EF, EM
            $table->string('ano', 10)->nullable();     // 6, 7, 8, 9...
            $table->timestamps();

            $table->index(['disciplina_id', 'ano']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habilidades');
    }
};
