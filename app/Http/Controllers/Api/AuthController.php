<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AlunoLoginRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Aluno\AlunoResource;
use App\Http\Resources\Aluno\PerfilAlunoResource;
use App\Http\Resources\User\UserResource;
use App\Models\Aluno;
use App\Models\User;
use App\Services\Aluno\LoginStreakService;
use App\Services\Personagem\PersonagemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'cpf' => ['As credenciais informadas estão incorretas.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'user' => new UserResource(Auth::guard('web')->user()?->load('roles')),
        ]);
    }

    public function loginWithToken(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::query()->where('cpf', $credentials['cpf'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'cpf' => ['As credenciais informadas estão incorretas.'],
            ]);
        }

        $token = $user->createToken('insomnia')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user->load('roles')),
            'token' => $token,
        ]);
    }

    public function loginAluno(AlunoLoginRequest $request): JsonResponse
    {
        $codigo = $request->validated()['codigo'];

        $aluno = Aluno::query()->where('codigo', $codigo)->first();

        if (! $aluno) {
            throw ValidationException::withMessages([
                'codigo' => ['Código de acesso inválido.'],
            ]);
        }

        // Todo aluno começa com o personagem inicial gratuito (Lumi) equipado.
        app(PersonagemService::class)->garantirInicial($aluno);
        $streak = app(LoginStreakService::class)->registrarEntrada($aluno);

        $token = $aluno->createToken('aluno')->plainTextToken;

        return response()->json([
            'aluno' => new AlunoResource($aluno),
            'token' => $token,
            'perfil' => ['data' => new PerfilAlunoResource($streak['perfil'])],
            'streak' => $streak['streak'],
        ]);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user()->load('roles'));
    }

    public function meAluno(Request $request): AlunoResource
    {
        return new AlunoResource($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();

            return response()->json(['message' => 'Token revogado com sucesso.']);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }
}
