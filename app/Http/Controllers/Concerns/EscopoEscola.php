<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * Garante que o gestor autenticado só opere sobre dados da própria escola.
 */
trait EscopoEscola
{
    protected function escolaDoGestor(Request $request): int
    {
        return $this->escolaDoUsuario($request);
    }

    protected function escolaDoUsuario(Request $request): int
    {
        return (int) $request->user()->escola_id;
    }

    /**
     * Aborta com 403 se o recurso não pertencer à escola do gestor.
     */
    protected function garantirMesmaEscola(Request $request, ?int $escolaIdDoRecurso): void
    {
        abort_unless(
            $escolaIdDoRecurso !== null && $escolaIdDoRecurso === $this->escolaDoGestor($request),
            403,
            'Recurso não pertence à sua escola.',
        );
    }
}
