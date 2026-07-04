<?php

namespace App\Http\Middleware;

use App\Models\Aluno;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garante que o usuário autenticado (via token Sanctum) é um Aluno.
 */
class GarantirAluno
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user() instanceof Aluno, 403, 'Acesso restrito a alunos.');

        return $next($request);
    }
}
