<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Contraseña solo para desarrollo. Se puede cambiar con SEED_PASSWORD en el .env local.
        // No hace falta Hash::make: el cast 'password' => 'hashed' del modelo la hashea solo.
        $password = env('SEED_PASSWORD', 'Dev12345');

        $usuarios = [
            [
                'name'    => 'Instructor Demo',
                'email'   => 'instructor@test.com',
                'role'    => Role::Instructor->value,
                'subrole' => null,
            ],
            [
                'name'    => 'Aprendiz General Demo',
                'email'   => 'general@test.com',
                'role'    => Role::Aprendiz->value,
                'subrole' => 'general',
            ],
            [
                'name'    => 'Aprendiz Evaluador Demo',
                'email'   => 'evaluador@test.com',
                'role'    => Role::Aprendiz->value,
                'subrole' => 'evaluador',
            ],
            [
                'name'    => 'Aprendiz Seleccionador Demo',
                'email'   => 'seleccionador@test.com',
                'role'    => Role::Aprendiz->value,
                'subrole' => 'seleccionador',
            ],
            [
                'name'    => 'Aprendiz Revisor Documental Demo',
                'email'   => 'revisor@test.com',
                'role'    => Role::Aprendiz->value,
                'subrole' => 'revisor_documental',
            ],
            [
                'name'    => 'Aprendiz Gestor Convocatorias Demo',
                'email'   => 'gestor@test.com',
                'role'    => Role::Aprendiz->value,
                'subrole' => 'gestor_convocatorias',
            ],
            [
                'name'    => 'Aspirante Demo',
                'email'   => 'aspirante@test.com',
                'role'    => Role::Aspirante->value,
                'subrole' => null,
            ],
        ];

        foreach ($usuarios as $datos) {
            // Si ya existe, no lo duplica (el seeder se puede correr varias veces).
            if (User::firstWhere('email', $datos['email'])) {
                continue;
            }

            // forceCreate: role, subrole y active no son asignables en masa.
            User::forceCreate($datos + [
                'active'   => true,
                'password' => $password,
            ]);
        }
    }
}