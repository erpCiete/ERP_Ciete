<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermisosSeeder extends Seeder
{
    /**
     * Seed permisos base.
     */
    public function run(): void
    {
        $legacySlugs = [
                'trabajos.cerrar',
                'trabajos_cerrados.editar',
                'trabajos.editar_cerrado',
                'pedidos.gestionar',
                'facturas.gestionar',
                'estaciones.gestionar',
                'empresas_contactos.gestionar',
            ];

        $legacyPermissionIds = Permission::query()
            ->whereIn('slug', $legacySlugs)
            ->pluck('id_permiso');

        if ($legacyPermissionIds->isNotEmpty()) {
            \Illuminate\Support\Facades\DB::table('rol_permisos')
                ->whereIn('id_permiso', $legacyPermissionIds)
                ->delete();
        }

        Permission::query()
            ->whereIn('slug', $legacySlugs)
            ->delete();

        $rows = [
            ['id_permiso' => 1, 'nombre' => 'Ver usuarios', 'slug' => 'usuarios.ver', 'descripcion' => 'Consulta de usuarios'],
            ['id_permiso' => 2, 'nombre' => 'Gestionar usuarios', 'slug' => 'usuarios.gestionar', 'descripcion' => 'Alta, baja y edicion de usuarios'],
            ['id_permiso' => 3, 'nombre' => 'Gestionar roles', 'slug' => 'roles.gestionar', 'descripcion' => 'Gestion de roles y permisos'],
            ['id_permiso' => 4, 'nombre' => 'Ver trabajos', 'slug' => 'trabajos.ver', 'descripcion' => 'Consulta de trabajos'],
            ['id_permiso' => 5, 'nombre' => 'Crear trabajos', 'slug' => 'trabajos.crear', 'descripcion' => 'Creacion de trabajos'],
            ['id_permiso' => 6, 'nombre' => 'Editar trabajos', 'slug' => 'trabajos.editar', 'descripcion' => 'Edicion de trabajos'],
            ['id_permiso' => 7, 'nombre' => 'Finalizar trabajos', 'slug' => 'trabajos.finalizar', 'descripcion' => 'Finalizacion funcional de trabajos'],
            ['id_permiso' => 11, 'nombre' => 'Ver presupuestos', 'slug' => 'presupuestos.ver', 'descripcion' => 'Consulta de presupuestos'],
            ['id_permiso' => 12, 'nombre' => 'Gestionar presupuestos', 'slug' => 'presupuestos.gestionar', 'descripcion' => 'Creacion y edicion de presupuestos'],
            ['id_permiso' => 13, 'nombre' => 'Ver pedidos', 'slug' => 'pedidos.ver', 'descripcion' => 'Consulta de pedidos'],
            ['id_permiso' => 15, 'nombre' => 'Ver facturas', 'slug' => 'facturas.ver', 'descripcion' => 'Consulta de facturas'],
            ['id_permiso' => 17, 'nombre' => 'Gestionar cobros', 'slug' => 'cobros.gestionar', 'descripcion' => 'Registro y conciliacion de cobros'],
            ['id_permiso' => 18, 'nombre' => 'Ver legalizaciones', 'slug' => 'legalizaciones.ver', 'descripcion' => 'Consulta de legalizaciones'],
            ['id_permiso' => 19, 'nombre' => 'Gestionar legalizaciones', 'slug' => 'legalizaciones.gestionar', 'descripcion' => 'Gestion de legalizaciones'],
            ['id_permiso' => 20, 'nombre' => 'Ver estaciones', 'slug' => 'estaciones.ver', 'descripcion' => 'Consulta de estaciones de servicio'],
            ['id_permiso' => 22, 'nombre' => 'Ver tarifarios', 'slug' => 'tarifarios.ver', 'descripcion' => 'Consulta de tarifarios'],
            ['id_permiso' => 23, 'nombre' => 'Gestionar tarifarios', 'slug' => 'tarifarios.gestionar', 'descripcion' => 'Gestion de tarifarios'],
            ['id_permiso' => 25, 'nombre' => 'Ver reportes', 'slug' => 'reportes.ver', 'descripcion' => 'Consulta de reportes'],
            ['id_permiso' => 26, 'nombre' => 'Ver importaciones', 'slug' => 'importaciones.ver', 'descripcion' => 'Consulta del historial de importaciones'],
            ['id_permiso' => 27, 'nombre' => 'Ejecutar importaciones', 'slug' => 'importaciones.ejecutar', 'descripcion' => 'Ejecutar importaciones de datos'],
            ['id_permiso' => 28, 'nombre' => 'Ver auditoria', 'slug' => 'auditoria.ver', 'descripcion' => 'Consulta del log de auditoria'],
            ['id_permiso' => 29, 'nombre' => 'Gestionar configuracion', 'slug' => 'config.gestionar', 'descripcion' => 'Gestion de maestros y configuracion del sistema'],
            ['id_permiso' => 30, 'nombre' => 'Ver contratos', 'slug' => 'contratos.ver', 'descripcion' => 'Consulta de contratos'],
            ['id_permiso' => 31, 'nombre' => 'Gestionar contratos', 'slug' => 'contratos.gestionar', 'descripcion' => 'Gestion de contratos'],
            ['id_permiso' => 32, 'nombre' => 'Crear usuarios', 'slug' => 'usuarios.crear', 'descripcion' => 'Alta de usuarios'],
            ['id_permiso' => 33, 'nombre' => 'Editar usuarios', 'slug' => 'usuarios.editar', 'descripcion' => 'Edicion, activacion y baja logica de usuarios'],
            ['id_permiso' => 34, 'nombre' => 'Eliminar trabajos', 'slug' => 'trabajos.eliminar', 'descripcion' => 'Cancelacion o eliminacion restringida de trabajos'],
            ['id_permiso' => 35, 'nombre' => 'Editar trabajo finalizado', 'slug' => 'trabajos.editar_finalizado', 'descripcion' => 'Edicion excepcional de trabajos finalizados'],
            ['id_permiso' => 36, 'nombre' => 'Cambiar estado de trabajos', 'slug' => 'trabajos.cambiar_estado', 'descripcion' => 'Cambio de estado operativo de trabajos'],
            ['id_permiso' => 37, 'nombre' => 'Marcar trabajos terminados', 'slug' => 'trabajos.marcar_terminado', 'descripcion' => 'Marcado tecnico de trabajos como terminados'],
            ['id_permiso' => 38, 'nombre' => 'Crear pedidos', 'slug' => 'pedidos.crear', 'descripcion' => 'Alta de pedidos'],
            ['id_permiso' => 39, 'nombre' => 'Editar pedidos', 'slug' => 'pedidos.editar', 'descripcion' => 'Edicion de pedidos'],
            ['id_permiso' => 40, 'nombre' => 'Eliminar pedidos', 'slug' => 'pedidos.eliminar', 'descripcion' => 'Cancelacion o eliminacion restringida de pedidos'],
            ['id_permiso' => 41, 'nombre' => 'Crear facturas', 'slug' => 'facturas.crear', 'descripcion' => 'Alta de facturas'],
            ['id_permiso' => 42, 'nombre' => 'Editar facturas', 'slug' => 'facturas.editar', 'descripcion' => 'Edicion de facturas'],
            ['id_permiso' => 43, 'nombre' => 'Eliminar facturas', 'slug' => 'facturas.eliminar', 'descripcion' => 'Anulacion o eliminacion restringida de facturas'],
            ['id_permiso' => 44, 'nombre' => 'Crear estaciones', 'slug' => 'estaciones.crear', 'descripcion' => 'Alta de estaciones'],
            ['id_permiso' => 45, 'nombre' => 'Editar estaciones', 'slug' => 'estaciones.editar', 'descripcion' => 'Edicion de estaciones'],
            ['id_permiso' => 46, 'nombre' => 'Eliminar estaciones', 'slug' => 'estaciones.eliminar', 'descripcion' => 'Desactivacion o eliminacion restringida de estaciones'],
            ['id_permiso' => 47, 'nombre' => 'Ver clientes', 'slug' => 'clientes.ver', 'descripcion' => 'Consulta de clientes y empresas contextuales'],
            ['id_permiso' => 48, 'nombre' => 'Crear clientes', 'slug' => 'clientes.crear', 'descripcion' => 'Alta de clientes y empresas contextuales'],
            ['id_permiso' => 49, 'nombre' => 'Editar clientes', 'slug' => 'clientes.editar', 'descripcion' => 'Edicion de clientes y empresas contextuales'],
            ['id_permiso' => 50, 'nombre' => 'Eliminar clientes', 'slug' => 'clientes.eliminar', 'descripcion' => 'Desactivacion o eliminacion restringida de clientes'],
            ['id_permiso' => 51, 'nombre' => 'Exportar auditoria', 'slug' => 'auditoria.exportar', 'descripcion' => 'Exportacion del registro de auditoria'],
            ['id_permiso' => 52, 'nombre' => 'Limpiar auditoria', 'slug' => 'auditoria.limpiar', 'descripcion' => 'Limpieza controlada del registro de auditoria'],
            ['id_permiso' => 53, 'nombre' => 'Confirmar importaciones', 'slug' => 'importaciones.confirmar', 'descripcion' => 'Confirmacion de importaciones revisadas'],
            ['id_permiso' => 54, 'nombre' => 'Gestionar soporte', 'slug' => 'soporte.gestionar', 'descripcion' => 'Gestion tecnica de solicitudes de soporte'],
            ['id_permiso' => 55, 'nombre' => 'Exportar facturas', 'slug' => 'facturas.exportar', 'descripcion' => 'Exportacion de listados y detalle de facturas'],
            ['id_permiso' => 56, 'nombre' => 'Ver maestros', 'slug' => 'maestros.ver', 'descripcion' => 'Acceso al panel separado de datos maestros'],
            ['id_permiso' => 57, 'nombre' => 'Gestionar maestros', 'slug' => 'maestros.gestionar', 'descripcion' => 'Gestion global de datos maestros'],
            ['id_permiso' => 58, 'nombre' => 'Crear contratos', 'slug' => 'contratos.crear', 'descripcion' => 'Alta de contratos maestros'],
            ['id_permiso' => 59, 'nombre' => 'Editar contratos', 'slug' => 'contratos.editar', 'descripcion' => 'Edicion de contratos maestros'],
            ['id_permiso' => 60, 'nombre' => 'Eliminar contratos', 'slug' => 'contratos.eliminar', 'descripcion' => 'Desactivacion de contratos maestros'],
            ['id_permiso' => 61, 'nombre' => 'Ver sociedades facturadoras permitidas', 'slug' => 'sociedades_facturadoras.ver', 'descripcion' => 'Consulta de sociedades facturadoras permitidas por contrato'],
            ['id_permiso' => 62, 'nombre' => 'Crear sociedades facturadoras permitidas', 'slug' => 'sociedades_facturadoras.crear', 'descripcion' => 'Asignacion de sociedades facturadoras a contratos'],
            ['id_permiso' => 63, 'nombre' => 'Editar sociedades facturadoras permitidas', 'slug' => 'sociedades_facturadoras.editar', 'descripcion' => 'Edicion de sociedades facturadoras permitidas'],
            ['id_permiso' => 64, 'nombre' => 'Eliminar sociedades facturadoras permitidas', 'slug' => 'sociedades_facturadoras.eliminar', 'descripcion' => 'Desactivacion de sociedades facturadoras permitidas'],
            ['id_permiso' => 65, 'nombre' => 'Crear tarifarios', 'slug' => 'tarifarios.crear', 'descripcion' => 'Alta de tarifarios maestros'],
            ['id_permiso' => 66, 'nombre' => 'Editar tarifarios', 'slug' => 'tarifarios.editar', 'descripcion' => 'Edicion de tarifarios maestros'],
            ['id_permiso' => 67, 'nombre' => 'Eliminar tarifarios', 'slug' => 'tarifarios.eliminar', 'descripcion' => 'Desactivacion de tarifarios maestros'],
            ['id_permiso' => 68, 'nombre' => 'Ver lineas de tarifario', 'slug' => 'tarifario_lineas.ver', 'descripcion' => 'Consulta de lineas de tarifario'],
            ['id_permiso' => 69, 'nombre' => 'Crear lineas de tarifario', 'slug' => 'tarifario_lineas.crear', 'descripcion' => 'Alta de lineas de tarifario'],
            ['id_permiso' => 70, 'nombre' => 'Editar lineas de tarifario', 'slug' => 'tarifario_lineas.editar', 'descripcion' => 'Edicion de lineas de tarifario'],
            ['id_permiso' => 71, 'nombre' => 'Eliminar lineas de tarifario', 'slug' => 'tarifario_lineas.eliminar', 'descripcion' => 'Desactivacion de lineas de tarifario'],
        ];

        foreach ($rows as $row) {
            Permission::query()->updateOrCreate(
                ['id_permiso' => $row['id_permiso']],
                $row + ['activo' => true]
            );
        }
    }
}
