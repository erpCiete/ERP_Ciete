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
            ['id_rol' => 1, 'nombre' => 'Admin', 'slug' => 'admin', 'descripcion' => 'Administrador tecnico del ERP con soporte, mantenimiento y lectura operativa sin mutacion funcional por defecto', 'activo' => true],
            ['id_rol' => 2, 'nombre' => 'Ejecución', 'slug' => 'ejecucion', 'descripcion' => 'Perfil operativo general con acceso a los contextos asignados', 'activo' => true],
            ['id_rol' => 3, 'nombre' => 'Dirección', 'slug' => 'director', 'descripcion' => 'Supervisión global, revisión y control de cierre operativo', 'activo' => true],
            ['id_rol' => 4, 'nombre' => 'Ejecución Moeve', 'slug' => 'ejecucion_moeve', 'descripcion' => 'Perfil operativo restringido al contexto Moeve/Cepsa', 'activo' => true],
            ['id_rol' => 5, 'nombre' => 'Ejecución Repsol', 'slug' => 'ejecucion_repsol', 'descripcion' => 'Perfil operativo restringido al contexto Repsol', 'activo' => true],
            ['id_rol' => 6, 'nombre' => 'Contabilidad', 'slug' => 'contable', 'descripcion' => 'Perfil económico para pedidos y facturas', 'activo' => true],
        ];

        foreach ($rows as $row) {
            Role::query()->updateOrCreate(
                ['id_rol' => $row['id_rol']],
                $row
            );
        }
    }
}
