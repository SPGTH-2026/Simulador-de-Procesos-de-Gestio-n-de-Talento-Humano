<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

// SOLO para desarrollo. Llámalo desde DatabaseSeeder únicamente si app()->environment('local').
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $demo = [
            ['Ana Aspirante', 'aspirante@demo.sena.edu.co', 'aspirante', null],
            ['Andrés Aprendiz', 'aprendiz@demo.sena.edu.co', 'aprendiz', 'general'],
            ['Elena Evaluadora', 'evaluador@demo.sena.edu.co', 'aprendiz', 'evaluador'],
            ['Sergio Seleccionador', 'seleccionador@demo.sena.edu.co', 'aprendiz', 'seleccionador'],
            ['Rosa Revisora', 'revisor@demo.sena.edu.co', 'aprendiz', 'revisor_documental'],
            ['Gabriel Gestor', 'gestor@demo.sena.edu.co', 'aprendiz', 'gestor_convocatorias'],
            ['Kevin Instructor', 'instructor@demo.sena.edu.co', 'instructor', null],
        ];

        foreach ($demo as [$name, $email, $role, $subrole]) {
            User::firstOrNew(['email' => $email])
                ->forceFill(['name' => $name, 'password' => 'Demo1234*', 'role' => $role, 'subrole' => $subrole, 'active' => true])
                ->save();
        }
    }
}
