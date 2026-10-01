<?php

// Registro público: crea un Aspirante activo y NO abre sesión. El flujo es
// registrarse -> ir a iniciar sesión -> entrar con correo y contraseña -> OTP.

namespace App\Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_crea_aspirante_activo_y_sin_sesion(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Nuevo Aspirante',
            'email' => 'nuevo@test.com',
            'password' => 'Clave12345',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('user.email', 'nuevo@test.com')
            ->assertJsonPath('user.role', Role::Aspirante->value)
            ->assertJsonPath('user.email_verified', false)
            ->assertJsonMissingPath('token');

        $user = User::where('email', 'nuevo@test.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->active);
        $this->assertTrue(Hash::check('Clave12345', $user->password));

        // El registro no debe dejar al usuario autenticado.
        $this->assertGuest();
    }

    public function test_no_acepta_correo_duplicado(): void
    {
        User::factory()->create(['email' => 'repetido@test.com']);

        $this->postJson('/api/auth/register', [
            'name' => 'Otro',
            'email' => 'repetido@test.com',
            'password' => 'Clave12345',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_exige_contrasena_con_letras_y_numeros(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Debil',
            'email' => 'debil@test.com',
            'password' => 'corta',
        ])->assertStatus(422)->assertJsonValidationErrors('password');
    }
}
