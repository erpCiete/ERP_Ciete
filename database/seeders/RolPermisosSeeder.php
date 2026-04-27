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

        $operativos = [
            'trabajos.ver',
            'trabajos.crear',
            'trabajos.editar',
            'presupuestos.ver',
            'presupuestos.gestionar',
            'pedidos.ver',
            'pedidos.gestionar',
        ];

        $direction = [
            'trabajos.ver',
            'trabajos.cerrar',
            'trabajos.reabrir',
            'trabajos_cerrados.editar',
            'trabajos_cerrados.reabrir',
            'pedidos.ver',
            'pedidos.gestionar',
            'facturas.ver',
            'facturas.gestionar',
        ];

        $accounting = [
            'pedidos.ver',
            'facturas.ver',
            'facturas.gestionar',
        ];

        $map = [
            'admin' => array_keys($permisos->all()),
            'ejecucion' => $operativos,
            'director' => array_values(array_unique([...$direction])),
            'ejecucion_moeve' => $operativos,
            'ejecucion_repsol' => $operativos,
            'contable' => $accounting,
        ];

        $rows = [];
        $now = now();
        $roleIdsToSync = [];

        foreach ($map as $roleSlug => $permissionSlugs) {
            $roleId = $roles->get($roleSlug);
            if (! $roleId) {
                continue;
            }

            $roleIdsToSync[] = $roleId;

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
            DB::table('rol_permisos')->whereIn('id_rol', array_unique($roleIdsToSync))->delete();
            DB::table('rol_permisos')->insert($rows);
        }
    }
}
