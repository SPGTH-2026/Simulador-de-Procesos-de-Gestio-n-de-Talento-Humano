<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', Password::min(8)->letters()->numbers()],
        ]);

        // Todo registro público es Aspirante; el rol nunca viene del request.
        $user = User::forceCreate([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => Role::Aspirante,
            'active' => true,
        ]);

        return response()->json($this->issue($user), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'Correo o contraseña incorrectos']);
        }
        if (! $user->active) {
            throw ValidationException::withMessages(['email' => 'Tu usuario está desactivado. Contacta al instructor']);
        }

        return response()->json($this->issue($user));
    }

    /** También sirve de "latido": el front lo llama cuando hay actividad. */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json($this->body($user, null, $user->currentAccessToken()->expires_at));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }

    public static function issueToken(User $user): array
    {
        $expiresAt = now()->addMinutes((int) config('sanctum.expiration', 480));
        $token = $user->createToken('spa', ['*'], $expiresAt);

        return [$token->plainTextToken, $expiresAt];
    }

    private function issue(User $user): array
    {
        [$plain, $expiresAt] = self::issueToken($user);

        return $this->body($user, $plain, $expiresAt);
    }

    private function body(User $user, ?string $token, $expiresAt): array
    {
        return [
            'token' => $token,
            'expires_at' => $expiresAt?->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'subrole' => $user->subrole,
                'permissions' => $user->permissions(),
            ],
        ];
    }
}
