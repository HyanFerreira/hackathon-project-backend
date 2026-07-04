<?php

use App\Http\Controllers\Api\Admin\EscolaController;
use App\Http\Controllers\Api\Admin\GestorController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Gestor\AlunoController;
use App\Http\Controllers\Api\Gestor\ProfessorController;
use App\Http\Controllers\Api\Gestor\TurmaController;
use App\Http\Controllers\Api\Gestor\VinculoController;
use App\Http\Controllers\Api\Role\RoleController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/login/token', [AuthController::class, 'loginWithToken'])->name('auth.login.token');
Route::post('/login/aluno', [AuthController::class, 'loginAluno'])->name('auth.login.aluno');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::get('/aluno/me', [AuthController::class, 'meAluno'])->name('auth.aluno.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

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
    });
});
