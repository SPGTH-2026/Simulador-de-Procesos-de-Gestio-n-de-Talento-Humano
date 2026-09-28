<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const PROVIDERS = ['google', 'github'];

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);
        $front = rtrim(config('app.frontend_url'), '/');

        try {
            $social = Socialite::driver($provider)->stateless()->user();
        } catch (\Throwable $e) {
            Log::error('Social login error: '.$e->getMessage());
            return redirect("$front/login?error=oauth");
        }

        $account = SocialAccount::where('provider', $provider)->where('provider_id', (string) $social->getId())->first();

        if ($account) {
            $user = $account->user;
        } else {
            if (! $social->getEmail()) {
                return redirect("$front/login?error=sin_correo");
            }
            // No se vincula por correo a cuentas existentes: evita que alguien tome una cuenta ajena.
            if (User::where('email', $social->getEmail())->exists()) {
                return redirect("$front/login?error=correo_en_uso");
            }

            $user = User::forceCreate([
                'name' => $social->getName() ?: ($social->getNickname() ?: $social->getEmail()),
                'email' => $social->getEmail(),
                'password' => Str::random(40), // no se usa; la cuenta entra por el proveedor
                'role' => Role::Aspirante,     // el rol NUNCA viene del proveedor
                'active' => true,
            ]);
            $user->socialAccounts()->create(['provider' => $provider, 'provider_id' => (string) $social->getId()]);
        }

        if (! $user->active) {
            return redirect("$front/login?error=desactivado");
        }

        [$plain] = AuthController::issueToken($user);

        // El token va en el fragmento (#): no llega a logs del servidor ni a cabeceras Referer.
        return redirect("$front/login#token=".urlencode($plain));
    }
}