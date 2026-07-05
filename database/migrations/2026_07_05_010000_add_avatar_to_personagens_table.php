<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nome do arquivo da imagem de perfil (cabeça) do personagem — ex.: lumi_perfil.svg.
     */
    public function up(): void
    {
        Schema::table('personagens', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('chave');
        });
    }

    public function down(): void
    {
        Schema::table('personagens', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
    }
};
