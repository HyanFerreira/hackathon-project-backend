<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Frequência de entrada do aluno (streak diário).
     */
    public function up(): void
    {
        Schema::table('perfis_alunos', function (Blueprint $table) {
            $table->unsignedInteger('dias_seguidos_login')->default(0)->after('energia_atualizada_em');
            $table->unsignedInteger('maior_dias_seguidos_login')->default(0)->after('dias_seguidos_login');
            $table->timestamp('ultimo_login_em')->nullable()->after('maior_dias_seguidos_login');
        });
    }

    public function down(): void
    {
        Schema::table('perfis_alunos', function (Blueprint $table) {
            $table->dropColumn([
                'dias_seguidos_login',
                'maior_dias_seguidos_login',
                'ultimo_login_em',
            ]);
        });
    }
};
