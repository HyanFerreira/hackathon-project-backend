<?php

use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\EscolaController;
use App\Http\Controllers\Api\Admin\GestorController;
use App\Http\Controllers\Api\Admin\ImpersonateController;
use App\Http\Controllers\Api\Aluno\DashboardController as AlunoDashboardController;
use App\Http\Controllers\Api\Aluno\PerfilController as AlunoPerfilController;
use App\Http\Controllers\Api\Aluno\QuestaoController as AlunoQuestaoController;
use App\Http\Controllers\Api\Aluno\RankingController as AlunoRankingController;
use App\Http\Controllers\Api\Aluno\RespostaController as AlunoRespostaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Gestor\AlunoController;
use App\Http\Controllers\Api\Gestor\DashboardController as GestorDashboardController;
use App\Http\Controllers\Api\Gestor\ProfessorController;
use App\Http\Controllers\Api\Gestor\RankingController as GestorRankingController;
use App\Http\Controllers\Api\Gestor\TurmaController;
use App\Http\Controllers\Api\Gestor\VinculoController;
use App\Http\Controllers\Api\Professor\DashboardController as ProfessorDashboardController;
use App\Http\Controllers\Api\Professor\QuestaoController;
use App\Http\Controllers\Api\Professor\RankingController as ProfessorRankingController;
use App\Http\Controllers\Api\Referencia\DisciplinaController;
use App\Http\Controllers\Api\Referencia\HabilidadeController;
use App\Http\Controllers\Api\Role\RoleController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/login/token', [AuthController::class, 'loginWithToken'])->name('auth.login.token');
Route::post('/login/aluno', [AuthController::class, 'loginAluno'])->name('auth.login.aluno');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    /*
     * Área do aluno — perfil gamificado, responder questões e histórico.
     * Restrita a tokens de aluno (middleware `aluno`).
     */
    Route::middleware('aluno')->prefix('aluno')->group(function () {
        Route::get('me', [AuthController::class, 'meAluno'])->name('auth.aluno.me');
        Route::get('perfil', [AlunoPerfilController::class, 'show'])->name('aluno.perfil');
        Route::get('questoes', [AlunoQuestaoController::class, 'index'])->name('aluno.questoes.index');
        Route::post('questoes/{questao}/responder', [AlunoQuestaoController::class, 'responder'])->name('aluno.questoes.responder');
        Route::get('respostas', [AlunoRespostaController::class, 'index'])->name('aluno.respostas.index');
        Route::get('dashboard', [AlunoDashboardController::class, 'index'])->name('aluno.dashboard');
        Route::get('ranking/turma', [AlunoRankingController::class, 'turma'])->name('aluno.ranking.turma');
        Route::get('ranking/escola', [AlunoRankingController::class, 'escola'])->name('aluno.ranking.escola');
    });

    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);

    /*
     * Área do administrador — gerencia escolas e gestores.
     */
    Route::prefix('admin')->group(function () {
        Route::middleware('permission:gerenciar escolas,sanctum')
            ->apiResource('escolas', EscolaController::class);

        Route::middleware('permission:gerenciar gestores,sanctum')
            ->apiResource('gestores', GestorController::class)
            ->parameters(['gestores' => 'gestor']);

        Route::get('dashboard', [AdminDashboardController::class, 'index'])
            ->middleware('role:admin,sanctum')->name('admin.dashboard');
    });

    /*
     * Impersonação — apenas admin vira outra conta (gestor, professor ou aluno).
     * `parar` roda com o token de impersonação (qualquer autenticado).
     */
    Route::prefix('impersonate')->group(function () {
        Route::post('user/{user}', [ImpersonateController::class, 'user'])
            ->middleware('role:admin,sanctum')->name('impersonate.user');
        Route::post('aluno/{aluno}', [ImpersonateController::class, 'aluno'])
            ->middleware('role:admin,sanctum')->name('impersonate.aluno');
        Route::post('parar', [ImpersonateController::class, 'parar'])->name('impersonate.parar');
    });

    /*
     * Referência BNCC — disciplinas e habilidades (somente leitura),
     * disponíveis para qualquer usuário autenticado.
     */
    Route::get('disciplinas', [DisciplinaController::class, 'index'])->name('disciplinas.index');
    Route::get('disciplinas/{disciplina}', [DisciplinaController::class, 'show'])->name('disciplinas.show');
    Route::get('habilidades', [HabilidadeController::class, 'index'])->name('habilidades.index');
    Route::get('habilidades/{habilidade}', [HabilidadeController::class, 'show'])->name('habilidades.show');

    /*
     * Área do professor — gerencia o próprio banco de questões (avaliadas
     * por habilidade da BNCC), no escopo da sua escola.
     */
    Route::middleware('permission:gerenciar questoes,sanctum')->prefix('professor')->group(function () {
        Route::apiResource('questoes', QuestaoController::class)->parameters(['questoes' => 'questao']);
    });

    Route::middleware('role:professor,sanctum')->prefix('professor')->group(function () {
        Route::get('dashboard', [ProfessorDashboardController::class, 'index'])->name('professor.dashboard');
        Route::get('ranking/turmas/{turma}', [ProfessorRankingController::class, 'turma'])->name('professor.ranking.turma');
    });

    /*
     * Área do gestor — gerencia turmas, professores, alunos e os vínculos
     * (turma↔professor e turma↔aluno), sempre no escopo da própria escola.
     */
    Route::prefix('gestor')->group(function () {
        Route::middleware('permission:gerenciar turmas,sanctum')
            ->apiResource('turmas', TurmaController::class);

        Route::middleware('permission:gerenciar professores,sanctum')
            ->apiResource('professores', ProfessorController::class)
            ->parameters(['professores' => 'professor']);

        Route::middleware('permission:gerenciar alunos,sanctum')
            ->apiResource('alunos', AlunoController::class);

        Route::middleware('permission:gerenciar vinculos,sanctum')->group(function () {
            Route::post('turmas/{turma}/professores', [VinculoController::class, 'vincularProfessor'])
                ->name('gestor.turmas.professores.vincular');
            Route::delete('turmas/{turma}/professores/{professor}', [VinculoController::class, 'desvincularProfessor'])
                ->name('gestor.turmas.professores.desvincular');
            Route::post('turmas/{turma}/alunos', [VinculoController::class, 'vincularAluno'])
                ->name('gestor.turmas.alunos.vincular');
            Route::delete('turmas/{turma}/alunos/{aluno}', [VinculoController::class, 'desvincularAluno'])
                ->name('gestor.turmas.alunos.desvincular');
        });

        Route::middleware('role:gestor,sanctum')->group(function () {
            Route::get('dashboard', [GestorDashboardController::class, 'index'])->name('gestor.dashboard');
            Route::get('ranking/escola', [GestorRankingController::class, 'escola'])->name('gestor.ranking.escola');
            Route::get('ranking/turmas/{turma}', [GestorRankingController::class, 'turma'])->name('gestor.ranking.turma');
        });
    });
});
