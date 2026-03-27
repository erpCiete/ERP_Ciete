<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ContextosClienteSeeder::class,
            RolesSeeder::class,
            PermisosSeeder::class,
            RolPermisosSeeder::class,
            DatosBaseSeeder::class,
            UsuariosInicialesSeeder::class,
        ]);
    }
}
