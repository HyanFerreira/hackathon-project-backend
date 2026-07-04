<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Aluno\AlunoResource;
use App\Http\Resources\User\UserResource;
use App\Models\Aluno;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Impersonação (apenas admin). Emite um token agindo como a conta-alvo;
 * o token é marcado com a habilidade "impersonado" para poder ser encerrado
 * via /impersonate/parar, sem afetar o token original do admin.
 */
class ImpersonateController extends Controller
{
    private const ABILITY = 'impersonado';

    public function user(Request $request, User $user): JsonResponse
    {
        $this->impedirImpersonacaoAninhada($request);

        $token = $user->createToken($this->nomeToken($request), [self::ABILITY])->plainTextToken;

        return response()->json([
            'impersonando' => true,
            'tipo' => 'user',
            'token' => $token,
            'user' => new UserResource($user->load(['roles', 'escola'])),
        ]);
    }

    public function aluno(Request $request, Aluno $aluno): JsonResponse
    {
        $this->impedirImpersonacaoAninhada($request);

        $token = $aluno->createToken($this->nomeToken($request), [self::ABILITY])->plainTextToken;

        return response()->json([
            'impersonando' => true,
            'tipo' => 'aluno',
            'token' => $token,
            'aluno' => new AlunoResource($aluno),
        ]);
    }

    public function parar(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        abort_unless(
            $this->ehTokenDeImpersonacao($token),
            422,
            'Esta sessão não é uma impersonação.',
        );

        $token->delete();

        return response()->json(['message' => 'Impersonação encerrada.']);
    }

    private function impedirImpersonacaoAninhada(Request $request): void
    {
        abort_if(
            $this->ehTokenDeImpersonacao($request->user()->currentAccessToken()),
            403,
            'Não é possível impersonar durante uma impersonação.',
        );
    }

    /**
     * Detecta a impersonação pela habilidade explícita (tokens normais têm "*",
     * então não podemos usar tokenCan/can, que tratam "*" como curinga).
     */
    private function ehTokenDeImpersonacao(mixed $token): bool
    {
        return $token instanceof PersonalAccessToken
            && in_array(self::ABILITY, (array) $token->abilities, true);
    }

    private function nomeToken(Request $request): string
    {
        return 'impersonado_por:'.$request->user()->id;
    }
}
