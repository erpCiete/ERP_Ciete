<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolPermisosSeeder extends Seeder
{
    /**
     * Seed permisos por rol.
     */
    public function run(): void
    {
        $roles = Role::query()->pluck('id_rol', 'slug');
        $permisos = Permission::query()->pluck('id_permiso', 'slug');

        if ($roles->isEmpty() || $permisos->isEmpty()) {
            return;
        }

        $map = [
            'admin' => array_keys($permisos->all()),
            'control_cierre' => [
                'proyectos.ver',
                'proyectos.cerrar',
                'proyectos.reabrir',
                'proyectos_cerrados.editar',
                'proyectos_cerrados.reabrir',
                'pedidos.ver',
                'legalizaciones.ver',
                'estaciones.gestionar',
                'empresas_contactos.gestionar',
                'reportes.ver',
            ],
            'consulta' => [
                'proyectos.ver',
                'presupuestos.ver',
                'pedidos.ver',
                'facturas.ver',
                'legalizaciones.ver',
                'estaciones.ver',
                'tarifarios.ver',
                'reportes.ver',
            ],
            'tecnico' => [
                'proyectos.ver',
                'proyectos.editar',
                'presupuestos.ver',
                'pedidos.ver',
                'facturas.ver',
                'legalizaciones.ver',
                'legalizaciones.gestionar',
                'estaciones.ver',
            ],
            'gestor' => [
                'proyectos.ver',
                'proyectos.crear',
                'proyectos.editar',
                'proyectos.cerrar',
                'proyectos.reabrir',
                'presupuestos.ver',
                'presupuestos.gestionar',
                'pedidos.ver',
                'pedidos.gestionar',
                'facturas.ver',
                'facturas.gestionar',
                'cobros.gestionar',
                'legalizaciones.ver',
                'legalizaciones.gestionar',
                'estaciones.ver',
                'estaciones.gestionar',
                'tarifarios.ver',
                'tarifarios.gestionar',
                'empresas_contactos.gestionar',
                'reportes.ver',
            ],
        ];

        $rows = [];
        $now = now();

        foreach ($map as $roleSlug => $permissionSlugs) {
            $roleId = $roles->get($roleSlug);
            if (! $roleId) {
                continue;
            }

            foreach ($permissionSlugs as $permissionSlug) {
                $permissionId = $permisos->get($permissionSlug);
                if (! $permissionId) {
                    continue;
                }

                $rows[] = [
                    'id_rol' => $roleId,
                    'id_permiso' => $permissionId,
                    'created_at' => $now,
                ];
            }
        }

        if ($rows !== []) {
            DB::table('rol_permisos')->insertOrIgnore($rows);
        }
    }
}
