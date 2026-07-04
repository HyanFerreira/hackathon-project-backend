<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo de personagens da loja. As imagens ficam no frontend, referenciadas
     * pela `chave` + nível (ex.: grunt_chibi_level_1.png).
     */
    public function up(): void
    {
        Schema::create('personagens', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->unique(); // grunt_chibi, leo_juv, luna_juv, pip_chibi_v2
            $table->string('nome');
            $table->string('descricao')->nullable();
            $table->string('tier')->default('comum'); // comum, raro, epico, lendario
            $table->unsignedInteger('preco');
            $table->unsignedInteger('nivel_maximo')->default(3);
            $table->string('status')->default('ativo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personagens');
    }
};
