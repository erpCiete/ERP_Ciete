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
            'trabajos.cambiar_estado',
            'trabajos.marcar_terminado',
            'presupuestos.ver',
            'presupuestos.gestionar',
            'estaciones.ver',
            'clientes.ver',
            'pedidos.ver',
            'pedidos.crear',
            'pedidos.editar',
        ];

        $direction = [
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.gestionar',
            'trabajos.ver',
            'trabajos.crear',
            'trabajos.editar',
            'trabajos.finalizar',
            'trabajos.eliminar',
            'trabajos.editar_finalizado',
            'trabajos.cambiar_estado',
            'trabajos.marcar_terminado',
            'pedidos.ver',
            'pedidos.crear',
            'pedidos.editar',
            'pedidos.eliminar',
            'facturas.ver',
            'facturas.crear',
            'facturas.editar',
            'facturas.eliminar',
            'facturas.exportar',
            'estaciones.ver',
            'estaciones.crear',
            'estaciones.editar',
            'estaciones.eliminar',
            'clientes.ver',
            'clientes.crear',
            'clientes.editar',
            'clientes.eliminar',
            'maestros.ver',
            'maestros.gestionar',
            'contratos.ver',
            'contratos.crear',
            'contratos.editar',
            'contratos.eliminar',
            'sociedades_facturadoras.ver',
            'sociedades_facturadoras.crear',
            'sociedades_facturadoras.editar',
            'sociedades_facturadoras.eliminar',
            'tarifarios.ver',
            'tarifarios.crear',
            'tarifarios.editar',
            'tarifarios.eliminar',
            'tarifario_lineas.ver',
            'tarifario_lineas.crear',
            'tarifario_lineas.editar',
            'tarifario_lineas.eliminar',
            'auditoria.ver',
            'auditoria.exportar',
            'auditoria.limpiar',
        ];

        $accounting = [
            'pedidos.ver',
            'facturas.ver',
            'facturas.crear',
            'facturas.editar',
            'facturas.eliminar',
            'facturas.exportar',
            'contratos.ver',
            'sociedades_facturadoras.ver',
            'tarifarios.ver',
            'tarifario_lineas.ver',
        ];

        $technicalAdmin = [
            'admin.panel.ver',
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'soporte.gestionar',
            'auditoria.ver',
            'mantenimiento.gestionar',
            'avisos.gestionar',
            'importaciones.ver',
            'importaciones.ejecutar',
            'importaciones.confirmar',
            'maestros.ver',
            'contratos.ver',
            'sociedades_facturadoras.ver',
            'tarifarios.ver',
            'tarifario_lineas.ver',
            'trabajos.ver',
            'pedidos.ver',
            'facturas.ver',
            'clientes.ver',
            'estaciones.ver',
        ];

        $direction = array_values(array_unique([
            ...$direction,
            'avisos.gestionar',
        ]));

        $map = [
            'admin' => $technicalAdmin,
            'ejecucion' => $operativos,
            'director' => $direction,
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
