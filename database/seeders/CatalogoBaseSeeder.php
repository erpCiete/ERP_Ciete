<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Catálogo base: unidades, tipos de documento y tipos de trabajo.
 */
class CatalogoBaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ── Unidades ─────────────────────────────────────────────────────────
        DB::table('unidades')->upsert([
            ['id_unidad' => 1, 'nombre' => 'Unidad',         'abreviatura' => 'ud', 'descripcion' => 'Unidad general',    'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_unidad' => 2, 'nombre' => 'Hora',           'abreviatura' => 'h',  'descripcion' => 'Hora de trabajo',   'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_unidad' => 3, 'nombre' => 'Metro cuadrado', 'abreviatura' => 'm2', 'descripcion' => 'Superficie',        'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_unidad' => 4, 'nombre' => 'Metro lineal',   'abreviatura' => 'ml', 'descripcion' => 'Longitud',          'activo' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_unidad'], ['nombre', 'abreviatura', 'descripcion', 'activo', 'updated_at']);

        // ── Tipos de documento ────────────────────────────────────────────────
        DB::table('tipos_documento')->upsert([
            ['id_tipo_documento' =>  1, 'id_contexto' => 1, 'codigo' => 'CONTROL_TRABAJOS',  'nombre' => 'Control de Trabajos Moeve',          'tiene_doble_factura' => false, 'tiene_orden_mto' => false, 'tiene_num_tarifa' => false, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' =>  2, 'id_contexto' => 2, 'codigo' => 'DISENO',             'nombre' => 'Diseno Repsol',                      'tiene_doble_factura' => false, 'tiene_orden_mto' => false, 'tiene_num_tarifa' => true,  'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' =>  3, 'id_contexto' => 2, 'codigo' => 'EDIFICACION',        'nombre' => 'Edificacion Repsol',                 'tiene_doble_factura' => true,  'tiene_orden_mto' => false, 'tiene_num_tarifa' => true,  'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' =>  4, 'id_contexto' => 2, 'codigo' => 'OBRAS',              'nombre' => 'Obras Repsol',                       'tiene_doble_factura' => true,  'tiene_orden_mto' => false, 'tiene_num_tarifa' => true,  'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' =>  5, 'id_contexto' => 2, 'codigo' => 'LICENCIAS',          'nombre' => 'Licencias Repsol',                   'tiene_doble_factura' => false, 'tiene_orden_mto' => false, 'tiene_num_tarifa' => true,  'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' =>  6, 'id_contexto' => 2, 'codigo' => 'FV',                 'nombre' => 'Fotovoltaica Repsol',                'tiene_doble_factura' => false, 'tiene_orden_mto' => false, 'tiene_num_tarifa' => true,  'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' =>  7, 'id_contexto' => 2, 'codigo' => 'ESTRUCTURAS',        'nombre' => 'Estructuras y Vertidos Repsol',      'tiene_doble_factura' => true,  'tiene_orden_mto' => false, 'tiene_num_tarifa' => true,  'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' =>  8, 'id_contexto' => 2, 'codigo' => 'MTO',                'nombre' => 'Mantenimiento Repsol',               'tiene_doble_factura' => true,  'tiene_orden_mto' => true,  'tiene_num_tarifa' => true,  'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' =>  9, 'id_contexto' => 2, 'codigo' => 'PUNTOS_RECARGA',     'nombre' => 'Puntos de Recarga Repsol',           'tiene_doble_factura' => false, 'tiene_orden_mto' => false, 'tiene_num_tarifa' => true,  'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' => 10, 'id_contexto' => 3, 'codigo' => 'OTROS',              'nombre' => 'Trabajo otros clientes',             'tiene_doble_factura' => false, 'tiene_orden_mto' => false, 'tiene_num_tarifa' => false, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_tipo_documento'], ['codigo', 'nombre', 'tiene_doble_factura', 'tiene_orden_mto', 'tiene_num_tarifa', 'activo', 'updated_at']);

        // ── Tipos de trabajo ──────────────────────────────────────────────────
        DB::table('tipos_trabajo')->upsert([
            ['id_tipo_trabajo' => 1, 'id_contexto' => 1, 'id_tipo_documento' => 1,  'codigo' => 'NPV',      'nombre' => 'Nueva Propuesta de Valor', 'responsable_ciete_defecto' => null, 'responsable_cliente_defecto' => null, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_trabajo' => 2, 'id_contexto' => 1, 'id_tipo_documento' => 1,  'codigo' => 'REFORMA',  'nombre' => 'Reforma General',          'responsable_ciete_defecto' => null, 'responsable_cliente_defecto' => null, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_trabajo' => 3, 'id_contexto' => 1, 'id_tipo_documento' => 1,  'codigo' => 'INDUSTRIA','nombre' => 'Industria',                'responsable_ciete_defecto' => null, 'responsable_cliente_defecto' => null, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_trabajo' => 4, 'id_contexto' => 2, 'id_tipo_documento' => 2,  'codigo' => 'NPV',      'nombre' => 'Nueva Propuesta de Valor', 'responsable_ciete_defecto' => null, 'responsable_cliente_defecto' => null, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_trabajo' => 5, 'id_contexto' => 2, 'id_tipo_documento' => 2,  'codigo' => 'REFORMA',  'nombre' => 'Reforma General',          'responsable_ciete_defecto' => null, 'responsable_cliente_defecto' => null, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_trabajo' => 6, 'id_contexto' => 3, 'id_tipo_documento' => 10, 'codigo' => 'OBRA',     'nombre' => 'Obra otros clientes',       'responsable_ciete_defecto' => null, 'responsable_cliente_defecto' => null, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_trabajo' => 7, 'id_contexto' => 3, 'id_tipo_documento' => 10, 'codigo' => 'MTO',      'nombre' => 'Mantenimiento otros clientes', 'responsable_ciete_defecto' => null, 'responsable_cliente_defecto' => null, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_tipo_trabajo'], ['codigo', 'nombre', 'activo', 'updated_at']);
    }
}
