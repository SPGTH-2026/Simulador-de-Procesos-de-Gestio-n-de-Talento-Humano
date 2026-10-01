<?php

// Prueba del callback de Google SIN red: se falsea Socialite y se verifica que
// la ruta abre sesión con cookie y manda OTP (nunca un token en la URL).

namespace App\Tests\Feature;

use App\Enums\Role;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class SocialCallbackSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // El callback de Google es un GET sin sesión previa (viene del navegador).
        // Este test comprueba que aun así se crea la sesión.
    }

    private function fakeSocialite(): void
    {
        $social = new \Laravel\Socialite\Two\User;
        $social->id = 'google-abc123';
        $social->name = 'Ana Google';
        $social->email = 'ana.'.getmypid().'@test.com';
        $social->user = ['email' => $social->email];

        $provider = \Mockery::mock(Provider::class);
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($social);

        Socialite::shouldReceive('driver')->andReturn($provider);
    }

    public function test_callback_google_abre_sesion_y_manda_otp(): void
    {
        $this->fakeSocialite();

        $response = $this->get('/api/auth/google/callback');

        $response->assertRedirect();

        $url = $response->headers->get('Location');

        // 1. NO debe haber token en la URL ni en el fragmento.
        $this->assertStringNotContainsString('token', $url);
        $this->assertStringNotContainsString('#', $url);

        // 2. Redirige a la pantalla de verificación.
        $this->assertStringEndsWith('/verificar-correo', $url);

        // 3. Se creó el usuario y la cuenta social.
        $user = User::where('email', 'ana.'.getmypid().'@test.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(Role::Aspirante, $user->role);

        // 4. Google NO marca el correo como verificado en nuestro sistema.
        $this->assertNull($user->email_verified_at);

        // 5. Se generó el OTP de verificación.
        $this->assertDatabaseHas('email_otps', [
            'email' => $user->email,
            'purpose' => OtpService::VERIFY_EMAIL,
            'consumed_at' => null,
        ]);
    }

    public function test_no_se_vincula_por_correo_a_cuenta_existente(): void
    {
        // Ya existe un usuario con ese correo, pero sin cuenta social vinculada.
        User::factory()->create(['email' => 'ana.'.getmypid().'@test.com']);

        $this->fakeSocialite();

        $response = $this->get('/api/auth/google/callback');

        $response->assertRedirect();
        $this->assertStringContainsString('error=correo_en_uso', $response->headers->get('Location'));

        // No se creó una sesión para la cuenta existente.
        $this->assertGuest();
    }

    public function test_cuenta_google_existente_reutiliza_el_usuario(): void
    {
        $user = User::factory()->create(['email' => 'ana.'.getmypid().'@test.com']);
        SocialAccount::forceCreate([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-abc123',
        ]);

        $this->fakeSocialite();

        $response = $this->get('/api/auth/google/callback');

        $response->assertRedirect();

        $this->assertAuthenticatedAs($user);

        // Sigue exigiendo verificación por OTP.
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertDatabaseHas('email_otps', [
            'email' => $user->email,
            'purpose' => OtpService::VERIFY_EMAIL,
        ]);
    }

    public function test_cuenta_inactiva_no_entra(): void
    {
        $user = User::factory()->create([
            'email' => 'ana.'.getmypid().'@test.com',
            'active' => false,
        ]);
        SocialAccount::forceCreate([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-abc123',
        ]);

        $this->fakeSocialite();

        $response = $this->get('/api/auth/google/callback');

        $response->assertRedirect();
        $this->assertStringContainsString('error=desactivado', $response->headers->get('Location'));
        $this->assertGuest();
    }
}
