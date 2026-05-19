<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed minimo de instalacion inicial del ERP.
     *
     * Sin datos demo ni operativa ficticia.
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
            HomeNoticeSeeder::class,
        ]);
    }
}
