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
            ['id_rol' => 2, 'nombre' => 'gestor', 'slug' => 'gestor', 'descripcion' => 'Gestion operativa de negocio', 'activo' => true],
            ['id_rol' => 3, 'nombre' => 'tecnico', 'slug' => 'tecnico', 'descripcion' => 'Trabajo tecnico y seguimiento', 'activo' => true],
            ['id_rol' => 4, 'nombre' => 'consulta', 'slug' => 'consulta', 'descripcion' => 'Solo lectura', 'activo' => true],
            [
                'id_rol' => 5,
                'nombre' => 'control_cierre',
                'slug' => 'control_cierre',
                'descripcion' => 'Control de proyectos cerrados y bloqueados',
                'activo' => true,
            ],
        ];

        foreach ($rows as $row) {
            Role::query()->updateOrCreate(
                ['id_rol' => $row['id_rol']],
                $row
            );
        }
    }
}
