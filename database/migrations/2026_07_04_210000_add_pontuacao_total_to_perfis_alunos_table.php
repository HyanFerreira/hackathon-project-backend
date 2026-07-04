<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `pontos` passa a ser o saldo gastável (loja) e `pontuacao_total` a
     * pontuação de desempenho (ranking), que nunca diminui.
     */
    public function up(): void
    {
        Schema::table('perfis_alunos', function (Blueprint $table) {
            $table->unsignedInteger('pontuacao_total')->default(0)->after('pontos');
        });

        // Backfill: perfis existentes começam com pontuação total igual ao saldo atual.
        DB::table('perfis_alunos')->update(['pontuacao_total' => DB::raw('pontos')]);
    }

    public function down(): void
    {
        Schema::table('perfis_alunos', function (Blueprint $table) {
            $table->dropColumn('pontuacao_total');
        });
    }
};
