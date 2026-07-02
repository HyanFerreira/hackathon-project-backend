<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Role\RoleController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/login/token', [AuthController::class, 'loginWithToken'])->name('auth.login.token');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);
});
