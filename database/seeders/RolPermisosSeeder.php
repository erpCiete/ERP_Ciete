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
            'trabajos.cerrar',
            'trabajos.reabrir',
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
            'contratos.ver',
            'contratos.gestionar',
            'empresas_contactos.gestionar',
            'importaciones.ver',
            'importaciones.ejecutar',
            'reportes.ver',
        ];

        $map = [
            'admin' => array_keys($permisos->all()),
            'usuario' => $operativos,
            'cierre' => [
                'trabajos.ver',
                'trabajos.cerrar',
                'trabajos.reabrir',
                'trabajos_cerrados.editar',
                'trabajos_cerrados.reabrir',
                'pedidos.ver',
                'legalizaciones.ver',
                'estaciones.ver',
                'estaciones.gestionar',
                'empresas_contactos.gestionar',
                'reportes.ver',
            ],
            'gestor_moeve' => $operativos,
            'gestor_repsol' => $operativos,
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
