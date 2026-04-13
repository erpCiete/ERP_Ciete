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
        $rows = [
            ['id_permiso' => 1, 'nombre' => 'Ver usuarios', 'slug' => 'usuarios.ver', 'descripcion' => 'Consulta de usuarios'],
            ['id_permiso' => 2, 'nombre' => 'Gestionar usuarios', 'slug' => 'usuarios.gestionar', 'descripcion' => 'Alta, baja y edicion de usuarios'],
            ['id_permiso' => 3, 'nombre' => 'Gestionar roles', 'slug' => 'roles.gestionar', 'descripcion' => 'Gestion de roles y permisos'],
            ['id_permiso' => 4, 'nombre' => 'Ver trabajos', 'slug' => 'trabajos.ver', 'descripcion' => 'Consulta de trabajos'],
            ['id_permiso' => 5, 'nombre' => 'Crear trabajos', 'slug' => 'trabajos.crear', 'descripcion' => 'Creacion de trabajos'],
            ['id_permiso' => 6, 'nombre' => 'Editar trabajos', 'slug' => 'trabajos.editar', 'descripcion' => 'Edicion de trabajos'],
            ['id_permiso' => 7, 'nombre' => 'Cerrar trabajos', 'slug' => 'trabajos.cerrar', 'descripcion' => 'Cierre de trabajos'],
            ['id_permiso' => 8, 'nombre' => 'Reabrir trabajos', 'slug' => 'trabajos.reabrir', 'descripcion' => 'Reapertura de trabajos'],
            ['id_permiso' => 9, 'nombre' => 'Editar trabajos cerrados', 'slug' => 'trabajos_cerrados.editar', 'descripcion' => 'Edicion de trabajos cerrados'],
            ['id_permiso' => 10, 'nombre' => 'Reabrir trabajos cerrados', 'slug' => 'trabajos_cerrados.reabrir', 'descripcion' => 'Reapertura de trabajos cerrados'],
            ['id_permiso' => 11, 'nombre' => 'Ver presupuestos', 'slug' => 'presupuestos.ver', 'descripcion' => 'Consulta de presupuestos'],
            ['id_permiso' => 12, 'nombre' => 'Gestionar presupuestos', 'slug' => 'presupuestos.gestionar', 'descripcion' => 'Creacion y edicion de presupuestos'],
            ['id_permiso' => 13, 'nombre' => 'Ver pedidos', 'slug' => 'pedidos.ver', 'descripcion' => 'Consulta de pedidos'],
            ['id_permiso' => 14, 'nombre' => 'Gestionar pedidos', 'slug' => 'pedidos.gestionar', 'descripcion' => 'Creacion y edicion de pedidos'],
            ['id_permiso' => 15, 'nombre' => 'Ver facturas', 'slug' => 'facturas.ver', 'descripcion' => 'Consulta de facturas'],
            ['id_permiso' => 16, 'nombre' => 'Gestionar facturas', 'slug' => 'facturas.gestionar', 'descripcion' => 'Creacion y edicion de facturas'],
            ['id_permiso' => 17, 'nombre' => 'Gestionar cobros', 'slug' => 'cobros.gestionar', 'descripcion' => 'Registro y conciliacion de cobros'],
            ['id_permiso' => 18, 'nombre' => 'Ver legalizaciones', 'slug' => 'legalizaciones.ver', 'descripcion' => 'Consulta de legalizaciones'],
            ['id_permiso' => 19, 'nombre' => 'Gestionar legalizaciones', 'slug' => 'legalizaciones.gestionar', 'descripcion' => 'Gestion de legalizaciones'],
            ['id_permiso' => 20, 'nombre' => 'Ver estaciones', 'slug' => 'estaciones.ver', 'descripcion' => 'Consulta de estaciones de servicio'],
            ['id_permiso' => 21, 'nombre' => 'Gestionar estaciones', 'slug' => 'estaciones.gestionar', 'descripcion' => 'Gestion de estaciones'],
            ['id_permiso' => 22, 'nombre' => 'Ver tarifarios', 'slug' => 'tarifarios.ver', 'descripcion' => 'Consulta de tarifarios'],
            ['id_permiso' => 23, 'nombre' => 'Gestionar tarifarios', 'slug' => 'tarifarios.gestionar', 'descripcion' => 'Gestion de tarifarios'],
            ['id_permiso' => 24, 'nombre' => 'Gestionar empresas y contactos', 'slug' => 'empresas_contactos.gestionar', 'descripcion' => 'Gestion de empresas y contactos'],
            ['id_permiso' => 25, 'nombre' => 'Ver reportes', 'slug' => 'reportes.ver', 'descripcion' => 'Consulta de reportes'],
            ['id_permiso' => 26, 'nombre' => 'Ver importaciones', 'slug' => 'importaciones.ver', 'descripcion' => 'Consulta del historial de importaciones'],
            ['id_permiso' => 27, 'nombre' => 'Ejecutar importaciones', 'slug' => 'importaciones.ejecutar', 'descripcion' => 'Ejecutar importaciones de datos'],
            ['id_permiso' => 28, 'nombre' => 'Ver auditoria', 'slug' => 'auditoria.ver', 'descripcion' => 'Consulta del log de auditoria'],
            ['id_permiso' => 29, 'nombre' => 'Gestionar configuracion', 'slug' => 'config.gestionar', 'descripcion' => 'Gestion de maestros y configuracion del sistema'],
            ['id_permiso' => 30, 'nombre' => 'Ver contratos', 'slug' => 'contratos.ver', 'descripcion' => 'Consulta de contratos'],
            ['id_permiso' => 31, 'nombre' => 'Gestionar contratos', 'slug' => 'contratos.gestionar', 'descripcion' => 'Gestion de contratos'],
        ];

        foreach ($rows as $row) {
            Permission::query()->updateOrCreate(
                ['id_permiso' => $row['id_permiso']],
                $row + ['activo' => true]
            );
        }
    }
}
