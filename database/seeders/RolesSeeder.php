<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Seed roles base.
     */
    public function run(): void
    {
        $rows = [
            ['id_rol' => 1, 'nombre' => 'admin', 'slug' => 'admin', 'descripcion' => 'Acceso total al ERP', 'activo' => true],
            ['id_rol' => 2, 'nombre' => 'usuario', 'slug' => 'usuario', 'descripcion' => 'Gestion operativa ambos clientes', 'activo' => true],
            ['id_rol' => 3, 'nombre' => 'cierre', 'slug' => 'cierre', 'descripcion' => 'Control de cierre de trabajos', 'activo' => true],
            ['id_rol' => 4, 'nombre' => 'gestor_moeve', 'slug' => 'gestor_moeve', 'descripcion' => 'Gestion operativa exclusiva MOEVE', 'activo' => true],
            ['id_rol' => 5, 'nombre' => 'gestor_repsol', 'slug' => 'gestor_repsol', 'descripcion' => 'Gestion operativa exclusiva REPSOL', 'activo' => true],
        ];

        foreach ($rows as $row) {
            Role::query()->updateOrCreate(
                ['id_rol' => $row['id_rol']],
                $row
            );
        }
    }
}
