<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatosBaseSeeder extends Seeder
{
    /**
     * Seed de catalogos y datos base operativos.
     */
    public function run(): void
    {
        $now = now();

        DB::table('empresas')->upsert([
            [
                'id_empresa' => 1,
                'id_contexto' => 1,
                'nombre' => 'CEPSA',
                'nombre_comercial' => 'CEPSA',
                'razon_social' => 'Compania Espanola de Petroleos S.A.',
                'cif' => 'A28003119',
                'tipo_empresa' => 'cliente',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_empresa' => 2,
                'id_contexto' => 2,
                'nombre' => 'REPSOL',
                'nombre_comercial' => 'REPSOL',
                'razon_social' => 'Repsol S.A.',
                'cif' => 'A78374725',
                'tipo_empresa' => 'cliente',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_empresa' => 3,
                'id_contexto' => 3,
                'nombre' => 'CIETE INGENIEROS SA',
                'nombre_comercial' => 'Ciete',
                'razon_social' => 'Ciete Ingenieros S.A.',
                'cif' => 'A12345678',
                'tipo_empresa' => 'interna',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id_empresa'], ['nombre', 'nombre_comercial', 'razon_social', 'cif', 'tipo_empresa', 'activo', 'updated_at']);

        DB::table('contactos')->upsert([
            [
                'id_contacto' => 1,
                'nombre' => 'Administrador',
                'apellidos' => 'Sistema',
                'dni' => null,
                'cargo_general' => 'Administrador ERP',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id_contacto'], ['nombre', 'apellidos', 'cargo_general', 'activo', 'updated_at']);

        DB::table('contactos_empresas')->upsert([
            [
                'id_contacto_empresa' => 1,
                'id_contexto' => 3,
                'id_empresa' => 3,
                'id_contacto' => 1,
                'puesto' => 'Administrador ERP',
                'categoria' => 'interno',
                'es_responsable_principal' => true,
                'recibe_avisos' => true,
                'recibe_presupuestos' => true,
                'recibe_facturas' => true,
                'es_usuario' => true,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id_contacto_empresa'], ['puesto', 'categoria', 'es_usuario', 'activo', 'updated_at']);

        DB::table('unidades')->upsert([
            ['id_unidad' => 1, 'nombre' => 'Unidad', 'abreviatura' => 'ud', 'descripcion' => 'Unidad general', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_unidad' => 2, 'nombre' => 'Hora', 'abreviatura' => 'h', 'descripcion' => 'Hora de trabajo', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_unidad' => 3, 'nombre' => 'Metro cuadrado', 'abreviatura' => 'm2', 'descripcion' => 'Superficie', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_unidad' => 4, 'nombre' => 'Metro lineal', 'abreviatura' => 'ml', 'descripcion' => 'Longitud', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_unidad'], ['nombre', 'abreviatura', 'descripcion', 'activo', 'updated_at']);

        DB::table('servicios')->upsert([
            [
                'id_servicio' => 1,
                'codigo' => 'SERV-INS-001',
                'nombre' => 'Inspeccion tecnica',
                'descripcion_seleccionable' => 'Inspeccion',
                'descripcion_libre' => 'Inspeccion tecnica inicial en estacion',
                'id_unidad' => 2,
                'tipo_servicio' => 'inspeccion',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_servicio' => 2,
                'codigo' => 'SERV-MPR-001',
                'nombre' => 'Mantenimiento preventivo',
                'descripcion_seleccionable' => 'Mantenimiento preventivo',
                'descripcion_libre' => 'Mantenimiento preventivo periodico',
                'id_unidad' => 2,
                'tipo_servicio' => 'mantenimiento',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_servicio' => 3,
                'codigo' => 'SERV-MCO-001',
                'nombre' => 'Mantenimiento correctivo',
                'descripcion_seleccionable' => 'Mantenimiento correctivo',
                'descripcion_libre' => 'Actuacion correctiva por incidencia',
                'id_unidad' => 2,
                'tipo_servicio' => 'mantenimiento',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_servicio' => 4,
                'codigo' => 'SERV-OBR-001',
                'nombre' => 'Obra civil menor',
                'descripcion_seleccionable' => 'Obra civil',
                'descripcion_libre' => 'Adecuacion de instalaciones',
                'id_unidad' => 3,
                'tipo_servicio' => 'obra',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_servicio' => 5,
                'codigo' => 'SERV-LEG-001',
                'nombre' => 'Gestion de legalizacion',
                'descripcion_seleccionable' => 'Legalizacion',
                'descripcion_libre' => 'Gestion documental y seguimiento legalizacion',
                'id_unidad' => 1,
                'tipo_servicio' => 'legalizacion',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_servicio' => 6,
                'codigo' => 'SERV-TEC-001',
                'nombre' => 'Soporte tecnico',
                'descripcion_seleccionable' => 'Soporte tecnico',
                'descripcion_libre' => 'Asistencia tecnica especializada',
                'id_unidad' => 2,
                'tipo_servicio' => 'otro',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id_servicio'], ['codigo', 'nombre', 'descripcion_seleccionable', 'descripcion_libre', 'id_unidad', 'tipo_servicio', 'activo', 'updated_at']);

        DB::table('tarifarios')->upsert([
            [
                'id_tarifario' => 1,
                'id_contexto' => 1,
                'nombre' => 'Tarifario Base CEPSA',
                'version' => '2026.1',
                'fecha_inicio_vigencia' => '2026-01-01',
                'moneda' => 'EUR',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_tarifario' => 2,
                'id_contexto' => 2,
                'nombre' => 'Tarifario Base REPSOL',
                'version' => '2026.1',
                'fecha_inicio_vigencia' => '2026-01-01',
                'moneda' => 'EUR',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_tarifario' => 3,
                'id_contexto' => 3,
                'nombre' => 'Tarifario Base OTRO',
                'version' => '2026.1',
                'fecha_inicio_vigencia' => '2026-01-01',
                'moneda' => 'EUR',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id_tarifario'], ['id_contexto', 'nombre', 'version', 'fecha_inicio_vigencia', 'moneda', 'activo', 'updated_at']);

        DB::table('tarifario_servicios')->upsert([
            ['id_tarifario_servicio' => 1, 'id_contexto' => 1, 'id_tarifario' => 1, 'id_servicio' => 1, 'precio_unitario' => 95, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 2, 'id_contexto' => 1, 'id_tarifario' => 1, 'id_servicio' => 2, 'precio_unitario' => 38, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 3, 'id_contexto' => 1, 'id_tarifario' => 1, 'id_servicio' => 3, 'precio_unitario' => 52, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 4, 'id_contexto' => 1, 'id_tarifario' => 1, 'id_servicio' => 4, 'precio_unitario' => 27, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 5, 'id_contexto' => 1, 'id_tarifario' => 1, 'id_servicio' => 5, 'precio_unitario' => 180, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 6, 'id_contexto' => 1, 'id_tarifario' => 1, 'id_servicio' => 6, 'precio_unitario' => 60, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 7, 'id_contexto' => 2, 'id_tarifario' => 2, 'id_servicio' => 1, 'precio_unitario' => 98, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 8, 'id_contexto' => 2, 'id_tarifario' => 2, 'id_servicio' => 2, 'precio_unitario' => 40, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 9, 'id_contexto' => 2, 'id_tarifario' => 2, 'id_servicio' => 3, 'precio_unitario' => 55, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 10, 'id_contexto' => 2, 'id_tarifario' => 2, 'id_servicio' => 4, 'precio_unitario' => 29, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 11, 'id_contexto' => 2, 'id_tarifario' => 2, 'id_servicio' => 5, 'precio_unitario' => 185, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 12, 'id_contexto' => 2, 'id_tarifario' => 2, 'id_servicio' => 6, 'precio_unitario' => 62, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 13, 'id_contexto' => 3, 'id_tarifario' => 3, 'id_servicio' => 1, 'precio_unitario' => 90, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 14, 'id_contexto' => 3, 'id_tarifario' => 3, 'id_servicio' => 2, 'precio_unitario' => 35, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 15, 'id_contexto' => 3, 'id_tarifario' => 3, 'id_servicio' => 3, 'precio_unitario' => 50, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 16, 'id_contexto' => 3, 'id_tarifario' => 3, 'id_servicio' => 4, 'precio_unitario' => 25, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 17, 'id_contexto' => 3, 'id_tarifario' => 3, 'id_servicio' => 5, 'precio_unitario' => 170, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_servicio' => 18, 'id_contexto' => 3, 'id_tarifario' => 3, 'id_servicio' => 6, 'precio_unitario' => 58, 'iva_porcentaje' => 21, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_tarifario_servicio'], ['precio_unitario', 'iva_porcentaje', 'activo', 'updated_at']);

        DB::table('estaciones_servicio')->upsert([
            [
                'id_estacion_servicio' => 1,
                'id_contexto' => 1,
                'id_empresa_cliente' => 1,
                'nombre' => 'Estacion CEPSA Demo 01',
                'codigo_estacion_interno' => 'CEPSA-EST-001',
                'cod_cepsa' => 'CEPSA-0001',
                'concesion' => 'Concesion CEPSA 1',
                'tipo' => 'Abanderada',
                'direccion' => 'Calle Energia 1',
                'codigo_postal' => '41001',
                'poblacion' => 'Sevilla',
                'provincia' => 'Sevilla',
                'pais' => 'Espana',
                'latitud_wgs84' => 37.38910000,
                'longitud_wgs84' => -5.98450000,
                'delegacion' => 'Andalucia',
                'delegado' => 'Delegado CEPSA',
                'tecnico_gestion' => 'Tecnico CEPSA',
                'telefono_tecnico_gestion' => '600000001',
                'email_tecnico_gestion' => 'tecnico.cepsa@demo.local',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_estacion_servicio' => 2,
                'id_contexto' => 2,
                'id_empresa_cliente' => 2,
                'nombre' => 'Estacion REPSOL Demo 01',
                'codigo_estacion_interno' => 'REPSOL-EST-001',
                'cod_repsol' => 'REPSOL-0001',
                'concesion' => 'Concesion REPSOL 1',
                'tipo' => 'Abanderada',
                'direccion' => 'Avenida Industria 2',
                'codigo_postal' => '28001',
                'poblacion' => 'Madrid',
                'provincia' => 'Madrid',
                'pais' => 'Espana',
                'latitud_wgs84' => 40.41680000,
                'longitud_wgs84' => -3.70380000,
                'delegacion' => 'Centro',
                'delegado' => 'Delegado REPSOL',
                'tecnico_gestion' => 'Tecnico REPSOL',
                'telefono_tecnico_gestion' => '600000002',
                'email_tecnico_gestion' => 'tecnico.repsol@demo.local',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id_estacion_servicio'], ['nombre', 'direccion', 'activo', 'updated_at']);
    }
}
