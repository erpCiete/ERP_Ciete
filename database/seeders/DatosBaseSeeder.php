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

        // ── Empresas ──
        DB::table('empresas')->upsert([
            [
                'id_empresa' => 1,
                'id_contexto' => 1,
                'nombre' => 'MOEVE',
                'nombre_comercial' => 'MOEVE',
                'razon_social' => 'Moeve Energy S.A.',
                'cif' => 'A28003119',
                'tipo_empresa' => 'cliente',
                'web' => 'https://www.moeve.es',
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
                'web' => 'https://www.repsol.es',
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
                'web' => 'https://www.ciete.es',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id_empresa'], ['nombre', 'nombre_comercial', 'razon_social', 'cif', 'tipo_empresa', 'web', 'activo', 'updated_at']);

        // ── Contactos base ──
        DB::table('contactos')->upsert([
            ['id_contacto' => 1, 'nombre' => 'Administrador', 'apellidos' => 'Sistema', 'dni' => null, 'cargo_general' => 'Administrador ERP', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_contacto' => 2, 'nombre' => 'Carlos', 'apellidos' => 'Martinez Gil', 'dni' => '12345678A', 'cargo_general' => 'Director Tecnico', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_contacto' => 3, 'nombre' => 'Ana', 'apellidos' => 'Lopez Ruiz', 'dni' => '23456789B', 'cargo_general' => 'Responsable de Obras', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_contacto' => 4, 'nombre' => 'Pedro', 'apellidos' => 'Garcia Navarro', 'dni' => '34567890C', 'cargo_general' => 'Director Tecnico', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_contacto' => 5, 'nombre' => 'Laura', 'apellidos' => 'Sanchez Vega', 'dni' => '45678901D', 'cargo_general' => 'Gestora de Proyectos', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_contacto'], ['nombre', 'apellidos', 'cargo_general', 'activo', 'updated_at']);

        DB::table('contactos_empresas')->upsert([
            ['id_contacto_empresa' => 1, 'id_contexto' => 3, 'id_empresa' => 3, 'id_contacto' => 1, 'puesto' => 'Administrador ERP', 'categoria' => 'interno', 'es_responsable_principal' => true, 'recibe_avisos' => true, 'recibe_presupuestos' => true, 'recibe_facturas' => true, 'es_usuario' => true, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_contacto_empresa' => 2, 'id_contexto' => 1, 'id_empresa' => 1, 'id_contacto' => 2, 'puesto' => 'Director Tecnico MOEVE', 'categoria' => 'cliente', 'es_responsable_principal' => true, 'recibe_avisos' => true, 'recibe_presupuestos' => true, 'recibe_facturas' => false, 'es_usuario' => false, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_contacto_empresa' => 3, 'id_contexto' => 1, 'id_empresa' => 1, 'id_contacto' => 3, 'puesto' => 'Responsable Obras MOEVE', 'categoria' => 'cliente', 'es_responsable_principal' => false, 'recibe_avisos' => true, 'recibe_presupuestos' => false, 'recibe_facturas' => false, 'es_usuario' => false, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_contacto_empresa' => 4, 'id_contexto' => 2, 'id_empresa' => 2, 'id_contacto' => 4, 'puesto' => 'Director Tecnico REPSOL', 'categoria' => 'cliente', 'es_responsable_principal' => true, 'recibe_avisos' => true, 'recibe_presupuestos' => true, 'recibe_facturas' => true, 'es_usuario' => false, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_contacto_empresa' => 5, 'id_contexto' => 2, 'id_empresa' => 2, 'id_contacto' => 5, 'puesto' => 'Gestora Proyectos REPSOL', 'categoria' => 'cliente', 'es_responsable_principal' => false, 'recibe_avisos' => true, 'recibe_presupuestos' => false, 'recibe_facturas' => false, 'es_usuario' => false, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_contacto_empresa'], ['puesto', 'categoria', 'es_usuario', 'activo', 'updated_at']);

        // ── Unidades ──
        DB::table('unidades')->upsert([
            ['id_unidad' => 1, 'nombre' => 'Unidad', 'abreviatura' => 'ud', 'descripcion' => 'Unidad general', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_unidad' => 2, 'nombre' => 'Hora', 'abreviatura' => 'h', 'descripcion' => 'Hora de trabajo', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_unidad' => 3, 'nombre' => 'Metro cuadrado', 'abreviatura' => 'm2', 'descripcion' => 'Superficie', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_unidad' => 4, 'nombre' => 'Metro lineal', 'abreviatura' => 'ml', 'descripcion' => 'Longitud', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_unidad'], ['nombre', 'abreviatura', 'descripcion', 'activo', 'updated_at']);

        // ── Tipos de documento ──
        DB::table('tipos_documento')->upsert([
            ['id_tipo_documento' => 1, 'id_contexto' => 1, 'codigo' => 'CONTROL_TRABAJOS', 'nombre' => 'Control de Trabajos Moeve', 'tiene_doble_factura' => false, 'tiene_orden_mto' => false, 'tiene_num_tarifa' => false, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' => 2, 'id_contexto' => 2, 'codigo' => 'DISENO', 'nombre' => 'Diseno Repsol', 'tiene_doble_factura' => false, 'tiene_orden_mto' => false, 'tiene_num_tarifa' => true, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' => 3, 'id_contexto' => 2, 'codigo' => 'EDIFICACION', 'nombre' => 'Edificacion Repsol', 'tiene_doble_factura' => true, 'tiene_orden_mto' => false, 'tiene_num_tarifa' => true, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' => 4, 'id_contexto' => 2, 'codigo' => 'OBRAS', 'nombre' => 'Obras Repsol', 'tiene_doble_factura' => true, 'tiene_orden_mto' => false, 'tiene_num_tarifa' => true, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' => 5, 'id_contexto' => 2, 'codigo' => 'LICENCIAS', 'nombre' => 'Licencias Repsol', 'tiene_doble_factura' => false, 'tiene_orden_mto' => false, 'tiene_num_tarifa' => true, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' => 6, 'id_contexto' => 2, 'codigo' => 'FV', 'nombre' => 'Fotovoltaica Repsol', 'tiene_doble_factura' => false, 'tiene_orden_mto' => false, 'tiene_num_tarifa' => true, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' => 7, 'id_contexto' => 2, 'codigo' => 'ESTRUCTURAS', 'nombre' => 'Estructuras y Vertidos Repsol', 'tiene_doble_factura' => true, 'tiene_orden_mto' => false, 'tiene_num_tarifa' => true, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' => 8, 'id_contexto' => 2, 'codigo' => 'MTO', 'nombre' => 'Mantenimiento Repsol', 'tiene_doble_factura' => true, 'tiene_orden_mto' => true, 'tiene_num_tarifa' => true, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_documento' => 9, 'id_contexto' => 2, 'codigo' => 'PUNTOS_RECARGA', 'nombre' => 'Puntos de Recarga Repsol', 'tiene_doble_factura' => false, 'tiene_orden_mto' => false, 'tiene_num_tarifa' => true, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_tipo_documento'], ['codigo', 'nombre', 'tiene_doble_factura', 'tiene_orden_mto', 'tiene_num_tarifa', 'activo', 'updated_at']);

        // ── Tipos de trabajo (Rangos) — ejemplos demo ──
        DB::table('tipos_trabajo')->upsert([
            ['id_tipo_trabajo' => 1, 'id_contexto' => 1, 'id_tipo_documento' => 1, 'codigo' => 'NPV', 'nombre' => 'Nueva Propuesta de Valor', 'responsable_ciete_defecto' => null, 'responsable_cliente_defecto' => null, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_trabajo' => 2, 'id_contexto' => 1, 'id_tipo_documento' => 1, 'codigo' => 'REFORMA', 'nombre' => 'Reforma General', 'responsable_ciete_defecto' => null, 'responsable_cliente_defecto' => null, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_trabajo' => 3, 'id_contexto' => 1, 'id_tipo_documento' => 1, 'codigo' => 'INDUSTRIA', 'nombre' => 'Industria', 'responsable_ciete_defecto' => null, 'responsable_cliente_defecto' => null, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_trabajo' => 4, 'id_contexto' => 2, 'id_tipo_documento' => 2, 'codigo' => 'NPV', 'nombre' => 'Nueva Propuesta de Valor', 'responsable_ciete_defecto' => null, 'responsable_cliente_defecto' => null, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tipo_trabajo' => 5, 'id_contexto' => 2, 'id_tipo_documento' => 2, 'codigo' => 'REFORMA', 'nombre' => 'Reforma General', 'responsable_ciete_defecto' => null, 'responsable_cliente_defecto' => null, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_tipo_trabajo'], ['codigo', 'nombre', 'activo', 'updated_at']);

        // ── Contratos ──
        DB::table('contratos')->upsert([
            [
                'id_contrato' => 1,
                'id_contexto' => 1,
                'id_empresa_cliente' => 1,
                'codigo_contrato' => 'CTR-MOEVE-2026',
                'nombre' => 'Contrato Marco MOEVE 2026',
                'tipo' => 'marco',
                'fecha_inicio' => '2026-01-01',
                'fecha_fin' => '2026-12-31',
                'estado' => 'vigente',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_contrato' => 2,
                'id_contexto' => 2,
                'id_empresa_cliente' => 2,
                'codigo_contrato' => 'CTR-REPSOL-2026',
                'nombre' => 'Contrato Marco REPSOL 2026',
                'tipo' => 'marco',
                'fecha_inicio' => '2026-01-01',
                'fecha_fin' => '2026-12-31',
                'estado' => 'vigente',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id_contrato'], ['nombre', 'tipo', 'estado', 'activo', 'updated_at']);

        // ── Tarifarios ──
        DB::table('tarifarios')->upsert([
            [
                'id_tarifario' => 1,
                'id_contexto' => 1,
                'id_contrato' => 1,
                'nombre' => 'Tarifario Base MOEVE',
                'version' => '2026.1',
                'fecha_inicio_vigencia' => '2026-01-01',
                'factor_multiplicador' => 1.0000,
                'moneda' => 'EUR',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_tarifario' => 2,
                'id_contexto' => 2,
                'id_contrato' => 2,
                'nombre' => 'Tarifario Base REPSOL',
                'version' => '2026.1',
                'fecha_inicio_vigencia' => '2026-01-01',
                'factor_multiplicador' => 1.0000,
                'moneda' => 'EUR',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id_tarifario'], ['id_contrato', 'nombre', 'version', 'factor_multiplicador', 'moneda', 'activo', 'updated_at']);

        // ── Tarifario lineas (ejemplo demo) ──
        DB::table('tarifario_lineas')->upsert([
            ['id_tarifario_linea' => 1, 'id_contexto' => 1, 'id_tarifario' => 1, 'codigo_tarifa' => 'T001', 'grupo' => 'Inspeccion', 'actuacion' => 'Inspeccion tecnica inicial', 'tarifa_anterior' => null, 'tarifa_base' => 95.00, 'tarifa_aplicada' => 95.00, 'id_unidad' => 2, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_linea' => 2, 'id_contexto' => 1, 'id_tarifario' => 1, 'codigo_tarifa' => 'T002', 'grupo' => 'Mantenimiento', 'actuacion' => 'Mantenimiento preventivo', 'tarifa_anterior' => null, 'tarifa_base' => 38.00, 'tarifa_aplicada' => 38.00, 'id_unidad' => 2, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_linea' => 3, 'id_contexto' => 1, 'id_tarifario' => 1, 'codigo_tarifa' => 'T003', 'grupo' => 'Obra civil', 'actuacion' => 'Adecuacion de instalaciones', 'tarifa_anterior' => null, 'tarifa_base' => 27.00, 'tarifa_aplicada' => 27.00, 'id_unidad' => 3, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_linea' => 4, 'id_contexto' => 2, 'id_tarifario' => 2, 'codigo_tarifa' => 'T001', 'grupo' => 'Inspeccion', 'actuacion' => 'Inspeccion tecnica inicial', 'tarifa_anterior' => null, 'tarifa_base' => 98.00, 'tarifa_aplicada' => 98.00, 'id_unidad' => 2, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_linea' => 5, 'id_contexto' => 2, 'id_tarifario' => 2, 'codigo_tarifa' => 'T002', 'grupo' => 'Mantenimiento', 'actuacion' => 'Mantenimiento preventivo', 'tarifa_anterior' => null, 'tarifa_base' => 40.00, 'tarifa_aplicada' => 40.00, 'id_unidad' => 2, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id_tarifario_linea' => 6, 'id_contexto' => 2, 'id_tarifario' => 2, 'codigo_tarifa' => 'T003', 'grupo' => 'Obra civil', 'actuacion' => 'Adecuacion de instalaciones', 'tarifa_anterior' => null, 'tarifa_base' => 29.00, 'tarifa_aplicada' => 29.00, 'id_unidad' => 3, 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_tarifario_linea'], ['codigo_tarifa', 'grupo', 'actuacion', 'tarifa_base', 'tarifa_aplicada', 'id_unidad', 'activo', 'updated_at']);

        // ── Estaciones de servicio (demo) ──
        DB::table('estaciones_servicio')->upsert([
            [
                'id_estacion_servicio' => 1,
                'id_contexto' => 1,
                'id_empresa_cliente' => 1,
                'codigo_estacion' => 'MOEVE-EST-001',
                'nombre' => 'Estacion MOEVE Demo 01',
                'direccion' => 'Calle Energia 1',
                'codigo_postal' => '41001',
                'poblacion' => 'Sevilla',
                'provincia' => 'Sevilla',
                'pais' => 'Espana',
                'latitud_wgs84' => 37.38910000,
                'longitud_wgs84' => -5.98450000,
                'estado' => 'Activa',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_estacion_servicio' => 2,
                'id_contexto' => 2,
                'id_empresa_cliente' => 2,
                'codigo_estacion' => 'REPSOL-EST-001',
                'nombre' => 'Estacion REPSOL Demo 01',
                'direccion' => 'Avenida Industria 2',
                'codigo_postal' => '28001',
                'poblacion' => 'Madrid',
                'provincia' => 'Madrid',
                'pais' => 'Espana',
                'latitud_wgs84' => 40.41680000,
                'longitud_wgs84' => -3.70380000,
                'estado' => 'Activa',
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id_estacion_servicio'], ['nombre', 'direccion', 'estado', 'activo', 'updated_at']);

        // ── Extensiones de estacion (demo) ──
        DB::table('estaciones_moeve_ext')->upsert([
            [
                'id_estacion_servicio' => 1,
                'tecnico_gestion' => 'Tecnico MOEVE',
                'telefono_tecnico' => '600000001',
                'email_tecnico' => 'tecnico.moeve@demo.local',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id_estacion_servicio'], ['tecnico_gestion', 'telefono_tecnico', 'email_tecnico', 'updated_at']);

        DB::table('estaciones_repsol_ext')->upsert([
            [
                'id_estacion_servicio' => 2,
                'codigo_solred' => 'SOLRED-0001',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id_estacion_servicio'], ['codigo_solred', 'updated_at']);

        // ── Direcciones (demo) ──
        DB::table('direcciones')->upsert([
            ['id_direccion' => 1, 'id_contexto' => 1, 'id_empresa' => 1, 'id_contacto' => null, 'id_contacto_empresa' => null, 'id_usuario' => null, 'tipo' => 'principal', 'linea1' => 'Paseo de la Castellana 259A', 'linea2' => 'Torre Cepsa, Planta 30', 'codigo_postal' => '28046', 'localidad' => 'Madrid', 'provincia' => 'Madrid', 'pais' => 'Espana', 'es_principal' => true, 'descripcion' => 'Sede central MOEVE', 'created_at' => $now, 'updated_at' => $now],
            ['id_direccion' => 2, 'id_contexto' => 1, 'id_empresa' => 1, 'id_contacto' => null, 'id_contacto_empresa' => null, 'id_usuario' => null, 'tipo' => 'delegacion', 'linea1' => 'Avenida de la Palmera 19', 'linea2' => null, 'codigo_postal' => '41012', 'localidad' => 'Sevilla', 'provincia' => 'Sevilla', 'pais' => 'Espana', 'es_principal' => false, 'descripcion' => 'Delegacion Sur MOEVE', 'created_at' => $now, 'updated_at' => $now],
            ['id_direccion' => 3, 'id_contexto' => 2, 'id_empresa' => 2, 'id_contacto' => null, 'id_contacto_empresa' => null, 'id_usuario' => null, 'tipo' => 'principal', 'linea1' => 'Calle Mendez Alvaro 44', 'linea2' => 'Campus Repsol', 'codigo_postal' => '28045', 'localidad' => 'Madrid', 'provincia' => 'Madrid', 'pais' => 'Espana', 'es_principal' => true, 'descripcion' => 'Sede central REPSOL', 'created_at' => $now, 'updated_at' => $now],
            ['id_direccion' => 4, 'id_contexto' => 2, 'id_empresa' => 2, 'id_contacto' => null, 'id_contacto_empresa' => null, 'id_usuario' => null, 'tipo' => 'delegacion', 'linea1' => 'Poligono Industrial Tarragona', 'linea2' => 'Complejo Quimico', 'codigo_postal' => '43006', 'localidad' => 'Tarragona', 'provincia' => 'Tarragona', 'pais' => 'Espana', 'es_principal' => false, 'descripcion' => 'Delegacion Tarragona REPSOL', 'created_at' => $now, 'updated_at' => $now],
        ], ['id_direccion'], ['linea1', 'linea2', 'tipo', 'es_principal', 'updated_at']);

        // ── Telefonos (demo) ──
        DB::table('telefonos')->upsert([
            ['id_telefono' => 1, 'id_contexto' => 1, 'id_empresa' => 1, 'id_contacto' => null, 'id_contacto_empresa' => null, 'id_usuario' => null, 'prefijo' => '+34', 'numero' => '913456789', 'tipo' => 'oficina', 'es_principal' => true, 'descripcion' => 'Centralita MOEVE', 'created_at' => $now, 'updated_at' => $now],
            ['id_telefono' => 2, 'id_contexto' => 1, 'id_empresa' => null, 'id_contacto' => 2, 'id_contacto_empresa' => null, 'id_usuario' => null, 'prefijo' => '+34', 'numero' => '625001001', 'tipo' => 'movil', 'es_principal' => true, 'descripcion' => 'Movil Carlos Martinez', 'created_at' => $now, 'updated_at' => $now],
            ['id_telefono' => 3, 'id_contexto' => 2, 'id_empresa' => 2, 'id_contacto' => null, 'id_contacto_empresa' => null, 'id_usuario' => null, 'prefijo' => '+34', 'numero' => '917654321', 'tipo' => 'oficina', 'es_principal' => true, 'descripcion' => 'Centralita REPSOL', 'created_at' => $now, 'updated_at' => $now],
            ['id_telefono' => 4, 'id_contexto' => 2, 'id_empresa' => null, 'id_contacto' => 4, 'id_contacto_empresa' => null, 'id_usuario' => null, 'prefijo' => '+34', 'numero' => '625002001', 'tipo' => 'movil', 'es_principal' => true, 'descripcion' => 'Movil Pedro Garcia', 'created_at' => $now, 'updated_at' => $now],
        ], ['id_telefono'], ['numero', 'tipo', 'es_principal', 'updated_at']);

        // ── Emails (demo) ──
        DB::table('emails')->upsert([
            ['id_email' => 1, 'id_contexto' => 1, 'id_empresa' => 1, 'id_contacto' => null, 'id_contacto_empresa' => null, 'id_usuario' => null, 'email' => 'info@moeve.es', 'tipo' => 'profesional', 'es_principal' => true, 'descripcion' => 'Email corporativo MOEVE', 'created_at' => $now, 'updated_at' => $now],
            ['id_email' => 2, 'id_contexto' => 1, 'id_empresa' => null, 'id_contacto' => null, 'id_contacto_empresa' => 2, 'id_usuario' => null, 'email' => 'carlos.martinez@moeve.es', 'tipo' => 'profesional', 'es_principal' => true, 'descripcion' => 'Email Carlos director tecnico', 'created_at' => $now, 'updated_at' => $now],
            ['id_email' => 3, 'id_contexto' => 2, 'id_empresa' => 2, 'id_contacto' => null, 'id_contacto_empresa' => null, 'id_usuario' => null, 'email' => 'info@repsol.es', 'tipo' => 'profesional', 'es_principal' => true, 'descripcion' => 'Email corporativo REPSOL', 'created_at' => $now, 'updated_at' => $now],
            ['id_email' => 4, 'id_contexto' => 2, 'id_empresa' => null, 'id_contacto' => null, 'id_contacto_empresa' => 4, 'id_usuario' => null, 'email' => 'pedro.garcia@repsol.es', 'tipo' => 'profesional', 'es_principal' => true, 'descripcion' => 'Email Pedro director tecnico', 'created_at' => $now, 'updated_at' => $now],
        ], ['id_email'], ['email', 'tipo', 'es_principal', 'updated_at']);

        // ── Trabajos (demo — ampliados) ──
        DB::table('trabajos')->upsert([
            [
                'id_trabajo' => 1,
                'id_contexto' => 1,
                'id_empresa_cliente' => 1,
                'id_estacion_servicio' => 1,
                'id_tipo_documento' => 1,
                'id_tipo_trabajo' => 1,
                'id_contrato' => 1,
                'id_tarifario' => 1,
                'id_responsable_ciete' => null,
                'id_usuario_cierre' => null,
                'numero_trabajo' => 1001,
                'numero_estacion' => 'MOEVE-EST-001',
                'zona' => 'SUR',
                'descripcion_trabajo' => 'NPV Estacion MOEVE Sevilla — nueva propuesta de valor',
                'fecha_encargo' => '2026-02-01',
                'fecha_terminacion' => null,
                'observaciones' => null,
                'numero_aviso' => null,
                'orden_mantenimiento' => null,
                'categoria' => null,
                'responsable_cliente' => 'Carlos Martinez',
                'estado' => 'en_curso',
                'cerrado' => false,
                'bloqueado_cierre' => false,
                'fecha_cierre' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_trabajo' => 2,
                'id_contexto' => 1,
                'id_empresa_cliente' => 1,
                'id_estacion_servicio' => 1,
                'id_tipo_documento' => 1,
                'id_tipo_trabajo' => 2,
                'id_contrato' => 1,
                'id_tarifario' => 1,
                'id_responsable_ciete' => null,
                'id_usuario_cierre' => null,
                'numero_trabajo' => 1002,
                'numero_estacion' => 'MOEVE-EST-001',
                'zona' => 'SUR',
                'descripcion_trabajo' => 'Reforma general marquesina Estacion MOEVE Sevilla',
                'fecha_encargo' => '2026-03-10',
                'fecha_terminacion' => null,
                'observaciones' => null,
                'numero_aviso' => null,
                'orden_mantenimiento' => null,
                'categoria' => null,
                'responsable_cliente' => 'Ana Lopez',
                'estado' => 'borrador',
                'cerrado' => false,
                'bloqueado_cierre' => false,
                'fecha_cierre' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_trabajo' => 3,
                'id_contexto' => 2,
                'id_empresa_cliente' => 2,
                'id_estacion_servicio' => 2,
                'id_tipo_documento' => 2,
                'id_tipo_trabajo' => 4,
                'id_contrato' => 2,
                'id_tarifario' => 2,
                'id_responsable_ciete' => null,
                'id_usuario_cierre' => null,
                'numero_trabajo' => 2001,
                'numero_estacion' => 'REPSOL-EST-001',
                'zona' => 'CENTRO',
                'descripcion_trabajo' => 'Diseno NPV Estacion REPSOL Madrid — nueva imagen',
                'fecha_encargo' => '2026-01-15',
                'fecha_terminacion' => '2026-03-20',
                'observaciones' => null,
                'numero_aviso' => null,
                'orden_mantenimiento' => null,
                'categoria' => null,
                'responsable_cliente' => 'Pedro Garcia',
                'estado' => 'terminado',
                'cerrado' => false,
                'bloqueado_cierre' => false,
                'fecha_cierre' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_trabajo' => 4,
                'id_contexto' => 2,
                'id_empresa_cliente' => 2,
                'id_estacion_servicio' => 2,
                'id_tipo_documento' => 8,
                'id_tipo_trabajo' => null,
                'id_contrato' => 2,
                'id_tarifario' => 2,
                'id_responsable_ciete' => null,
                'id_usuario_cierre' => null,
                'numero_trabajo' => 2002,
                'numero_estacion' => 'REPSOL-EST-001',
                'zona' => 'CENTRO',
                'descripcion_trabajo' => 'Mantenimiento preventivo instalaciones REPSOL Madrid',
                'fecha_encargo' => '2026-03-01',
                'fecha_terminacion' => null,
                'observaciones' => null,
                'numero_aviso' => 'AV-2026-0045',
                'orden_mantenimiento' => 'OM-2026-0012',
                'categoria' => null,
                'responsable_cliente' => 'Laura Sanchez',
                'estado' => 'en_curso',
                'cerrado' => false,
                'bloqueado_cierre' => false,
                'fecha_cierre' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_trabajo' => 5,
                'id_contexto' => 1,
                'id_empresa_cliente' => 1,
                'id_estacion_servicio' => 1,
                'id_tipo_documento' => 1,
                'id_tipo_trabajo' => 3,
                'id_contrato' => 1,
                'id_tarifario' => 1,
                'id_responsable_ciete' => null,
                'id_usuario_cierre' => null,
                'numero_trabajo' => 1003,
                'numero_estacion' => 'MOEVE-EST-001',
                'zona' => 'SUR',
                'descripcion_trabajo' => 'Industria MOEVE Sevilla — adecuacion tecnica de instalaciones',
                'fecha_encargo' => '2026-01-20',
                'fecha_terminacion' => '2026-02-18',
                'observaciones' => 'Trabajo demo finalizado para pruebas de columnas de fecha de terminacion.',
                'numero_aviso' => 'AV-MOEVE-0103',
                'orden_mantenimiento' => null,
                'categoria' => 'Industria',
                'responsable_cliente' => 'Carlos Martinez',
                'estado' => 'terminado',
                'cerrado' => false,
                'bloqueado_cierre' => false,
                'fecha_cierre' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_trabajo' => 6,
                'id_contexto' => 1,
                'id_empresa_cliente' => 1,
                'id_estacion_servicio' => 1,
                'id_tipo_documento' => 1,
                'id_tipo_trabajo' => 2,
                'id_contrato' => 1,
                'id_tarifario' => 1,
                'id_responsable_ciete' => null,
                'id_usuario_cierre' => null,
                'numero_trabajo' => 1004,
                'numero_estacion' => 'MOEVE-EST-001',
                'zona' => 'SUR',
                'descripcion_trabajo' => 'Reforma cerrada MOEVE Sevilla — trabajo demo para cierre bloqueado',
                'fecha_encargo' => '2025-12-10',
                'fecha_terminacion' => '2026-01-12',
                'observaciones' => 'Trabajo demo cerrado y bloqueado.',
                'numero_aviso' => 'AV-MOEVE-0104',
                'orden_mantenimiento' => null,
                'categoria' => 'Reforma',
                'responsable_cliente' => 'Ana Lopez',
                'estado' => 'cerrado',
                'cerrado' => true,
                'bloqueado_cierre' => true,
                'fecha_cierre' => '2026-01-15 09:00:00',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_trabajo' => 7,
                'id_contexto' => 2,
                'id_empresa_cliente' => 2,
                'id_estacion_servicio' => 2,
                'id_tipo_documento' => 3,
                'id_tipo_trabajo' => null,
                'id_contrato' => 2,
                'id_tarifario' => 2,
                'id_responsable_ciete' => null,
                'id_usuario_cierre' => null,
                'numero_trabajo' => 2003,
                'numero_estacion' => 'REPSOL-EST-001',
                'zona' => 'CENTRO',
                'descripcion_trabajo' => 'Edificacion REPSOL Madrid — obra demo pendiente de inicio',
                'fecha_encargo' => '2026-04-01',
                'fecha_terminacion' => null,
                'observaciones' => 'Trabajo demo en borrador para validar columnas vacias.',
                'numero_aviso' => null,
                'orden_mantenimiento' => null,
                'categoria' => 'Edificacion',
                'responsable_cliente' => 'Pedro Garcia',
                'estado' => 'borrador',
                'cerrado' => false,
                'bloqueado_cierre' => false,
                'fecha_cierre' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_trabajo' => 8,
                'id_contexto' => 2,
                'id_empresa_cliente' => 2,
                'id_estacion_servicio' => 2,
                'id_tipo_documento' => 8,
                'id_tipo_trabajo' => null,
                'id_contrato' => 2,
                'id_tarifario' => 2,
                'id_responsable_ciete' => null,
                'id_usuario_cierre' => null,
                'numero_trabajo' => 2004,
                'numero_estacion' => 'REPSOL-EST-001',
                'zona' => 'CENTRO',
                'descripcion_trabajo' => 'Mantenimiento REPSOL Madrid — trabajo demo cerrado con OM',
                'fecha_encargo' => '2026-02-05',
                'fecha_terminacion' => '2026-02-28',
                'observaciones' => 'Demo REPSOL con orden de mantenimiento y cierre.',
                'numero_aviso' => 'AV-2026-0088',
                'orden_mantenimiento' => 'OM-2026-0044',
                'categoria' => 'Mantenimiento',
                'responsable_cliente' => 'Laura Sanchez',
                'estado' => 'cerrado',
                'cerrado' => true,
                'bloqueado_cierre' => false,
                'fecha_cierre' => '2026-03-02 12:30:00',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['id_trabajo'], [
            'id_tipo_documento',
            'id_tipo_trabajo',
            'id_contrato',
            'id_tarifario',
            'id_responsable_ciete',
            'id_usuario_cierre',
            'numero_trabajo',
            'numero_estacion',
            'zona',
            'descripcion_trabajo',
            'fecha_encargo',
            'fecha_terminacion',
            'observaciones',
            'numero_aviso',
            'orden_mantenimiento',
            'categoria',
            'responsable_cliente',
            'estado',
            'cerrado',
            'bloqueado_cierre',
            'fecha_cierre',
            'updated_at'
        ]);

        // @TODO: Unificar Pedidos demo con pedidos mas abajo para evitar duplicidades y confusiones en datos de ejemplo. Por ahora se mantienen separados para ilustrar distintos escenarios.
        // ── Pedidos (demo — 1 por trabajo) ──
        DB::table('pedidos')->upsert([
            ['id_pedido' => 1, 'id_contexto' => 1, 'id_trabajo' => 1, 'id_tarifario' => 1, 'numero_pedido' => 'PED-M-001', 'fecha_solicitud' => '2026-02-05', 'fecha_recepcion' => '2026-02-10', 'importe_pedido' => 950.00, 'importe_solicitado' => 950.00, 'importe_facturado' => 950.00, 'unidades_pedido' => 10.000, 'unidades_solicitadas' => 10.000, 'estado' => 'facturado', 'pedido_completo' => true, 'tiene_mas_de_1_item' => false, 'facturado_completo' => true, 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id_pedido' => 2, 'id_contexto' => 1, 'id_trabajo' => 2, 'id_tarifario' => 1, 'numero_pedido' => 'PED-M-002', 'fecha_solicitud' => '2026-03-12', 'fecha_recepcion' => null, 'importe_pedido' => 380.00, 'importe_solicitado' => null, 'importe_facturado' => null, 'unidades_pedido' => 10.000, 'unidades_solicitadas' => null, 'estado' => 'pendiente', 'pedido_completo' => null, 'tiene_mas_de_1_item' => false, 'facturado_completo' => null, 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id_pedido' => 3, 'id_contexto' => 2, 'id_trabajo' => 3, 'id_tarifario' => 2, 'numero_pedido' => 'PED-R-001', 'fecha_solicitud' => '2026-01-20', 'fecha_recepcion' => '2026-01-25', 'importe_pedido' => 980.00, 'importe_solicitado' => 980.00, 'importe_facturado' => 980.00, 'unidades_pedido' => 10.000, 'unidades_solicitadas' => 10.000, 'estado' => 'facturado', 'pedido_completo' => true, 'tiene_mas_de_1_item' => false, 'facturado_completo' => true, 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id_pedido' => 4, 'id_contexto' => 2, 'id_trabajo' => 4, 'id_tarifario' => 2, 'numero_pedido' => 'PED-R-002', 'fecha_solicitud' => '2026-03-05', 'fecha_recepcion' => null, 'importe_pedido' => 400.00, 'importe_solicitado' => null, 'importe_facturado' => null, 'unidades_pedido' => 10.000, 'unidades_solicitadas' => null, 'estado' => 'pendiente', 'pedido_completo' => null, 'tiene_mas_de_1_item' => false, 'facturado_completo' => null, 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_pedido'], ['numero_pedido', 'estado', 'updated_at']);

        // ── Pedido items (demo) ──
        DB::table('pedido_items')->upsert([
            ['id_pedido_item' => 1, 'id_contexto' => 1, 'id_pedido' => 1, 'id_tarifario_linea' => 1, 'codigo_servicio' => 'T001', 'numero_tarifa' => null, 'descripcion_servicio' => 'Inspeccion tecnica inicial', 'precio_unitario' => 95.00, 'cantidad' => 10.000, 'total_linea' => 950.00, 'created_at' => $now, 'updated_at' => $now],
            ['id_pedido_item' => 2, 'id_contexto' => 1, 'id_pedido' => 2, 'id_tarifario_linea' => 2, 'codigo_servicio' => 'T002', 'numero_tarifa' => null, 'descripcion_servicio' => 'Mantenimiento preventivo', 'precio_unitario' => 38.00, 'cantidad' => 10.000, 'total_linea' => 380.00, 'created_at' => $now, 'updated_at' => $now],
            ['id_pedido_item' => 3, 'id_contexto' => 2, 'id_pedido' => 3, 'id_tarifario_linea' => 4, 'codigo_servicio' => 'T001', 'numero_tarifa' => null, 'descripcion_servicio' => 'Inspeccion tecnica inicial', 'precio_unitario' => 98.00, 'cantidad' => 10.000, 'total_linea' => 980.00, 'created_at' => $now, 'updated_at' => $now],
            ['id_pedido_item' => 4, 'id_contexto' => 2, 'id_pedido' => 4, 'id_tarifario_linea' => 5, 'codigo_servicio' => 'T002', 'numero_tarifa' => null, 'descripcion_servicio' => 'Mantenimiento preventivo', 'precio_unitario' => 40.00, 'cantidad' => 10.000, 'total_linea' => 400.00, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_pedido_item'], ['descripcion_servicio', 'total_linea', 'updated_at']);

        // Demo de Facturas eliminado (Unificado al final del seeder para evitar conflictos de claves foraneas con cobros y presupuestos)

        // ── Cobros (demo — solo para facturas emitidas/cobradas) ──
        DB::table('cobros')->upsert([
            ['id_cobro' => 1, 'id_contexto' => 1, 'id_factura' => 1, 'id_usuario_registro' => null, 'fecha_cobro' => '2026-03-15', 'importe' => 1149.50, 'metodo_cobro' => 'transferencia', 'referencia' => 'TRF-MOEVE-2026-001', 'estado' => 'pendiente', 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id_cobro' => 2, 'id_contexto' => 2, 'id_factura' => 3, 'id_usuario_registro' => null, 'fecha_cobro' => '2026-03-20', 'importe' => 1185.80, 'metodo_cobro' => 'transferencia', 'referencia' => 'TRF-REPSOL-2026-001', 'estado' => 'recibido', 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_cobro'], ['importe', 'estado', 'updated_at']);

        // ── Presupuestos (demo — 1 por trabajo) ──
        DB::table('presupuestos')->upsert([
            ['id_presupuesto' => 1, 'id_contexto' => 1, 'id_trabajo' => 1, 'id_empresa_cliente' => 1, 'id_contacto_empresa_cliente' => 2, 'id_estacion_servicio' => 1, 'id_tarifario' => 1, 'id_usuario_responsable' => null, 'id_usuario_cierre' => null, 'codigo_presupuesto' => 'PRES-M-001', 'nombre_presupuesto' => 'Presupuesto NPV Moeve Sevilla', 'estado' => 'aprobado', 'fecha_emision' => '2026-02-03', 'fecha_validez' => '2026-05-03', 'base_imponible' => 1140.00, 'iva' => 239.40, 'retencion' => 0, 'total' => 1379.40, 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id_presupuesto' => 2, 'id_contexto' => 1, 'id_trabajo' => 2, 'id_empresa_cliente' => 1, 'id_contacto_empresa_cliente' => 3, 'id_estacion_servicio' => 1, 'id_tarifario' => 1, 'id_usuario_responsable' => null, 'id_usuario_cierre' => null, 'codigo_presupuesto' => 'PRES-M-002', 'nombre_presupuesto' => 'Presupuesto Reforma Moeve Sevilla', 'estado' => 'borrador', 'fecha_emision' => '2026-03-11', 'fecha_validez' => '2026-06-11', 'base_imponible' => 844.00, 'iva' => 177.24, 'retencion' => 0, 'total' => 1021.24, 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id_presupuesto' => 3, 'id_contexto' => 2, 'id_trabajo' => 3, 'id_empresa_cliente' => 2, 'id_contacto_empresa_cliente' => 4, 'id_estacion_servicio' => 2, 'id_tarifario' => 2, 'id_usuario_responsable' => null, 'id_usuario_cierre' => null, 'codigo_presupuesto' => 'PRES-R-001', 'nombre_presupuesto' => 'Presupuesto Diseno NPV Repsol Madrid', 'estado' => 'enviado', 'fecha_emision' => '2026-01-18', 'fecha_validez' => '2026-04-18', 'base_imponible' => 1180.00, 'iva' => 247.80, 'retencion' => 0, 'total' => 1427.80, 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id_presupuesto' => 4, 'id_contexto' => 2, 'id_trabajo' => 4, 'id_empresa_cliente' => 2, 'id_contacto_empresa_cliente' => 5, 'id_estacion_servicio' => 2, 'id_tarifario' => 2, 'id_usuario_responsable' => null, 'id_usuario_cierre' => null, 'codigo_presupuesto' => 'PRES-R-002', 'nombre_presupuesto' => 'Presupuesto Mto Repsol Madrid', 'estado' => 'borrador', 'fecha_emision' => '2026-03-03', 'fecha_validez' => '2026-06-03', 'base_imponible' => 835.00, 'iva' => 175.35, 'retencion' => 0, 'total' => 1010.35, 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_presupuesto'], ['nombre_presupuesto', 'estado', 'updated_at']);

        // ── Presupuesto lineas (demo — 2 por presupuesto) ──
        DB::table('presupuesto_lineas')->upsert([
            ['id_linea_presupuesto' => 1, 'id_contexto' => 1, 'id_presupuesto' => 1, 'id_tarifario_linea' => 1, 'orden' => 1, 'concepto_seleccionable' => 'Inspeccion tecnica inicial', 'concepto_libre' => null, 'cantidad' => 10.000, 'precio_unitario' => 95.00, 'iva_porcentaje' => 21.00, 'total_linea' => 950.00, 'created_at' => $now, 'updated_at' => $now],
            ['id_linea_presupuesto' => 2, 'id_contexto' => 1, 'id_presupuesto' => 1, 'id_tarifario_linea' => 2, 'orden' => 2, 'concepto_seleccionable' => 'Mantenimiento preventivo', 'concepto_libre' => null, 'cantidad' => 5.000, 'precio_unitario' => 38.00, 'iva_porcentaje' => 21.00, 'total_linea' => 190.00, 'created_at' => $now, 'updated_at' => $now],
            ['id_linea_presupuesto' => 3, 'id_contexto' => 1, 'id_presupuesto' => 2, 'id_tarifario_linea' => 3, 'orden' => 1, 'concepto_seleccionable' => 'Adecuacion de instalaciones', 'concepto_libre' => null, 'cantidad' => 20.000, 'precio_unitario' => 27.00, 'iva_porcentaje' => 21.00, 'total_linea' => 540.00, 'created_at' => $now, 'updated_at' => $now],
            ['id_linea_presupuesto' => 4, 'id_contexto' => 1, 'id_presupuesto' => 2, 'id_tarifario_linea' => 2, 'orden' => 2, 'concepto_seleccionable' => 'Mantenimiento preventivo', 'concepto_libre' => null, 'cantidad' => 8.000, 'precio_unitario' => 38.00, 'iva_porcentaje' => 21.00, 'total_linea' => 304.00, 'created_at' => $now, 'updated_at' => $now],
            ['id_linea_presupuesto' => 5, 'id_contexto' => 2, 'id_presupuesto' => 3, 'id_tarifario_linea' => 4, 'orden' => 1, 'concepto_seleccionable' => 'Inspeccion tecnica inicial', 'concepto_libre' => null, 'cantidad' => 10.000, 'precio_unitario' => 98.00, 'iva_porcentaje' => 21.00, 'total_linea' => 980.00, 'created_at' => $now, 'updated_at' => $now],
            ['id_linea_presupuesto' => 6, 'id_contexto' => 2, 'id_presupuesto' => 3, 'id_tarifario_linea' => 5, 'orden' => 2, 'concepto_seleccionable' => 'Mantenimiento preventivo', 'concepto_libre' => null, 'cantidad' => 5.000, 'precio_unitario' => 40.00, 'iva_porcentaje' => 21.00, 'total_linea' => 200.00, 'created_at' => $now, 'updated_at' => $now],
            ['id_linea_presupuesto' => 7, 'id_contexto' => 2, 'id_presupuesto' => 4, 'id_tarifario_linea' => 6, 'orden' => 1, 'concepto_seleccionable' => 'Adecuacion de instalaciones', 'concepto_libre' => null, 'cantidad' => 15.000, 'precio_unitario' => 29.00, 'iva_porcentaje' => 21.00, 'total_linea' => 435.00, 'created_at' => $now, 'updated_at' => $now],
            ['id_linea_presupuesto' => 8, 'id_contexto' => 2, 'id_presupuesto' => 4, 'id_tarifario_linea' => 5, 'orden' => 2, 'concepto_seleccionable' => 'Mantenimiento preventivo', 'concepto_libre' => null, 'cantidad' => 10.000, 'precio_unitario' => 40.00, 'iva_porcentaje' => 21.00, 'total_linea' => 400.00, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_linea_presupuesto'], ['concepto_seleccionable', 'total_linea', 'updated_at']);

        // ── Legalizaciones (demo — 1 por trabajo) ──
        DB::table('legalizaciones')->upsert([
            ['id_legalizacion' => 1, 'id_contexto' => 1, 'id_trabajo' => 1, 'id_usuario_responsable' => null, 'tipo_legalizacion' => 'Licencia de apertura', 'numero_expediente' => 'EXP-M-001', 'organismo' => 'Ayuntamiento de Sevilla', 'estado' => 'en_tramite', 'descripcion_seleccionable' => 'Tramitacion licencia apertura estacion', 'descripcion_libre' => null, 'fecha_inicio' => '2026-02-10', 'fecha_limite' => '2026-06-10', 'fecha_resolucion' => null, 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id_legalizacion' => 2, 'id_contexto' => 1, 'id_trabajo' => 2, 'id_usuario_responsable' => null, 'tipo_legalizacion' => 'Licencia de obras', 'numero_expediente' => 'EXP-M-002', 'organismo' => 'Ayuntamiento de Sevilla', 'estado' => 'pendiente', 'descripcion_seleccionable' => 'Licencia obra menor marquesina', 'descripcion_libre' => null, 'fecha_inicio' => null, 'fecha_limite' => '2026-07-01', 'fecha_resolucion' => null, 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id_legalizacion' => 3, 'id_contexto' => 2, 'id_trabajo' => 3, 'id_usuario_responsable' => null, 'tipo_legalizacion' => 'Licencia de apertura', 'numero_expediente' => 'EXP-R-001', 'organismo' => 'Ayuntamiento de Madrid', 'estado' => 'resuelta', 'descripcion_seleccionable' => 'Tramitacion licencia apertura estacion', 'descripcion_libre' => null, 'fecha_inicio' => '2026-01-20', 'fecha_limite' => '2026-05-20', 'fecha_resolucion' => '2026-03-18', 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id_legalizacion' => 4, 'id_contexto' => 2, 'id_trabajo' => 4, 'id_usuario_responsable' => null, 'tipo_legalizacion' => 'Licencia de actividad', 'numero_expediente' => 'EXP-R-002', 'organismo' => 'Com. Autonoma de Madrid', 'estado' => 'pendiente', 'descripcion_seleccionable' => 'Tramitacion licencia actividad', 'descripcion_libre' => null, 'fecha_inicio' => null, 'fecha_limite' => '2026-08-01', 'fecha_resolucion' => null, 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_legalizacion'], ['tipo_legalizacion', 'estado', 'updated_at']);

        // ── Legalizaciones ↔ Contactos (pivot) ──
        DB::table('legalizaciones_contactos')->upsert([
            ['id_legalizacion_contacto' => 1, 'id_contexto' => 1, 'id_legalizacion' => 1, 'id_contacto_empresa' => 2, 'rol_en_legalizacion' => 'Responsable tecnico', 'principal' => true, 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id_legalizacion_contacto' => 2, 'id_contexto' => 1, 'id_legalizacion' => 2, 'id_contacto_empresa' => 3, 'rol_en_legalizacion' => 'Coordinadora obras', 'principal' => true, 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id_legalizacion_contacto' => 3, 'id_contexto' => 2, 'id_legalizacion' => 3, 'id_contacto_empresa' => 4, 'rol_en_legalizacion' => 'Responsable tecnico', 'principal' => true, 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id_legalizacion_contacto' => 4, 'id_contexto' => 2, 'id_legalizacion' => 4, 'id_contacto_empresa' => 5, 'rol_en_legalizacion' => 'Gestora tramites', 'principal' => true, 'observaciones' => null, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_legalizacion_contacto'], ['rol_en_legalizacion', 'principal', 'updated_at']);

        // ── Comentarios legalizaciones (demo) ──
        DB::table('comentarios_legalizaciones')->upsert([
            ['id_comentario_legalizacion' => 1, 'id_contexto' => 1, 'id_legalizacion' => 1, 'id_usuario' => null, 'fecha_comentario' => '2026-02-12 09:30:00', 'comentario' => 'Documentacion presentada en el Ayuntamiento de Sevilla.', 'created_at' => $now, 'updated_at' => $now],
            ['id_comentario_legalizacion' => 2, 'id_contexto' => 1, 'id_legalizacion' => 2, 'id_usuario' => null, 'fecha_comentario' => '2026-03-12 11:00:00', 'comentario' => 'Pendiente de informe tecnico municipal para aprobacion.', 'created_at' => $now, 'updated_at' => $now],
            ['id_comentario_legalizacion' => 3, 'id_contexto' => 2, 'id_legalizacion' => 3, 'id_usuario' => null, 'fecha_comentario' => '2026-03-18 16:45:00', 'comentario' => 'Expediente resuelto favorablemente. Licencia concedida.', 'created_at' => $now, 'updated_at' => $now],
            ['id_comentario_legalizacion' => 4, 'id_contexto' => 2, 'id_legalizacion' => 4, 'id_usuario' => null, 'fecha_comentario' => '2026-03-05 10:15:00', 'comentario' => 'Inicio de tramitacion de licencia de actividad ante la Comunidad.', 'created_at' => $now, 'updated_at' => $now],
        ], ['id_comentario_legalizacion'], ['comentario', 'updated_at']);
    
        // ── Pedidos (Actualizado a esquema final) ──
        DB::table('pedidos')->upsert([
            [
                'id_pedido' => 1, 'id_contexto' => 1, 'id_trabajo' => 1, 'id_tarifario' => 1,
                'numero_pedido' => 'PED-M-001', 'fecha_solicitud' => '2026-02-05', 'fecha_recepcion' => '2026-02-10',
                'importe_pedido' => 1149.50, 'importe_solicitado' => 1149.50, 'importe_facturado' => 1149.50,
                'unidades_pedido' => 1.000, 'unidades_solicitadas' => 1.000,
                'estado' => 'cerrado', 'pedido_completo' => 1, 'tiene_mas_de_1_item' => 0, 'facturado_completo' => 1,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_pedido' => 2, 'id_contexto' => 1, 'id_trabajo' => 2, 'id_tarifario' => 1,
                'numero_pedido' => 'PED-M-002', 'fecha_solicitud' => '2026-03-12', 'fecha_recepcion' => null,
                'importe_pedido' => 459.80, 'importe_solicitado' => 459.80, 'importe_facturado' => 0.00,
                'unidades_pedido' => 1.000, 'unidades_solicitadas' => 1.000,
                'estado' => 'pendiente', 'pedido_completo' => 1, 'tiene_mas_de_1_item' => 0, 'facturado_completo' => 0,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_pedido' => 3, 'id_contexto' => 2, 'id_trabajo' => 3, 'id_tarifario' => 2,
                'numero_pedido' => 'PED-R-001', 'fecha_solicitud' => '2026-01-20', 'fecha_recepcion' => '2026-01-25',
                'importe_pedido' => 1185.80, 'importe_solicitado' => 1185.80, 'importe_facturado' => 1185.80,
                'unidades_pedido' => 1.000, 'unidades_solicitadas' => 1.000,
                'estado' => 'cerrado', 'pedido_completo' => 1, 'tiene_mas_de_1_item' => 0, 'facturado_completo' => 1,
                'created_at' => $now, 'updated_at' => $now
            ],
            [
                'id_pedido' => 4, 'id_contexto' => 2, 'id_trabajo' => 4, 'id_tarifario' => 2,
                'numero_pedido' => 'PED-R-002', 'fecha_solicitud' => '2026-03-05', 'fecha_recepcion' => null,
                'importe_pedido' => 484.00, 'importe_solicitado' => 0.00, 'importe_facturado' => 0.00,
                'unidades_pedido' => 1.000, 'unidades_solicitadas' => 0.000,
                'estado' => 'pendiente', 'pedido_completo' => 0, 'tiene_mas_de_1_item' => 0, 'facturado_completo' => 0,
                'created_at' => $now, 'updated_at' => $now
            ],
        ], ['id_pedido'], ['id_trabajo', 'id_tarifario', 'estado', 'importe_pedido', 'importe_solicitado', 'importe_facturado', 'unidades_pedido', 'unidades_solicitadas', 'pedido_completo', 'tiene_mas_de_1_item', 'facturado_completo', 'updated_at']);

        // ── Líneas de Pedido (pedido_items adaptado al esquema real) ──
        DB::table('pedido_items')->upsert([
            ['id_pedido_item' => 1, 'id_contexto' => 1, 'id_pedido' => 1, 'id_tarifario_linea' => 1, 'codigo_servicio' => 'T001', 'descripcion_servicio' => 'Inspeccion tecnica inicial', 'precio_unitario' => 95.00, 'cantidad' => 10.000, 'total_linea' => 950.00, 'created_at' => $now, 'updated_at' => $now],
            ['id_pedido_item' => 2, 'id_contexto' => 1, 'id_pedido' => 2, 'id_tarifario_linea' => 2, 'codigo_servicio' => 'T002', 'descripcion_servicio' => 'Mantenimiento preventivo', 'precio_unitario' => 38.00, 'cantidad' => 10.000, 'total_linea' => 380.00, 'created_at' => $now, 'updated_at' => $now],
            ['id_pedido_item' => 3, 'id_contexto' => 2, 'id_pedido' => 3, 'id_tarifario_linea' => 4, 'codigo_servicio' => 'T001', 'descripcion_servicio' => 'Inspeccion tecnica inicial', 'precio_unitario' => 98.00, 'cantidad' => 10.000, 'total_linea' => 980.00, 'created_at' => $now, 'updated_at' => $now],
            ['id_pedido_item' => 4, 'id_contexto' => 2, 'id_pedido' => 4, 'id_tarifario_linea' => 5, 'codigo_servicio' => 'T002', 'descripcion_servicio' => 'Mantenimiento preventivo', 'precio_unitario' => 40.00, 'cantidad' => 10.000, 'total_linea' => 400.00, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_pedido_item'], ['codigo_servicio', 'descripcion_servicio', 'precio_unitario', 'cantidad', 'total_linea', 'updated_at']);


        // ── Facturas (Unificado Sprint 04) ──
        DB::table('facturas')->upsert([
            [
                'id_factura'         => 1,
                'id_contexto'        => 1,
                'id_trabajo'         => 1,
                'id_empresa_cliente' => 1,
                'numero_factura'     => 'F-2026-001',
                'numero_factura_ccp' => 'CCP-001-2026',
                'serie'              => 'M',
                'orden_factura'      => 1,
                'fecha_solicitud'    => '2026-02-15',
                'fecha_emision'      => '2026-04-10',
                'fecha_vencimiento'  => '2026-06-10',
                'importe'            => 950.00,
                'base_imponible'     => 950.00,
                'iva'                => 199.50,
                'retencion'          => null,
                'total'              => 1149.50,
                'estado'             => 'emitida',
                'autofactura'        => false,
                'sociedad'           => 'MOEVE S.A.',
                'observaciones'      => null,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
            [
                'id_factura'         => 2,
                'id_contexto'        => 1,
                'id_trabajo'         => 2,
                'id_empresa_cliente' => 1,
                'numero_factura'     => 'F-2026-002',
                'numero_factura_ccp' => null,
                'serie'              => 'M',
                'orden_factura'      => 1,
                'fecha_solicitud'    => '2026-03-15',
                'fecha_emision'      => null,
                'fecha_vencimiento'  => null,
                'importe'            => 380.00,
                'base_imponible'     => 380.00,
                'iva'                => 79.80,
                'retencion'          => null,
                'total'              => 459.80,
                'estado'             => 'pendiente',
                'autofactura'        => false,
                'sociedad'           => null,
                'observaciones'      => null,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
            [
                'id_factura'         => 3,
                'id_contexto'        => 2,
                'id_trabajo'         => 3,
                'id_empresa_cliente' => 2,
                'numero_factura'     => 'F-2026-003',
                'numero_factura_ccp' => null,
                'serie'              => 'R',
                'orden_factura'      => 1,
                'fecha_solicitud'    => '2026-02-01',
                'fecha_emision'      => '2026-04-12',
                'fecha_vencimiento'  => '2026-06-12',
                'importe'            => 980.00,
                'base_imponible'     => 980.00,
                'iva'                => 205.80,
                'retencion'          => null,
                'total'              => 1185.80,
                'estado'             => 'cobrada',
                'autofactura'        => true,
                'sociedad'           => null,
                'observaciones'      => null,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
            [
                'id_factura'         => 4,
                'id_contexto'        => 2,
                'id_trabajo'         => 4,
                'id_empresa_cliente' => 2,
                'numero_factura'     => 'F-2026-004',
                'numero_factura_ccp' => null,
                'serie'              => 'R',
                'orden_factura'      => 1,
                'fecha_solicitud'    => '2026-03-10',
                'fecha_emision'      => null,
                'fecha_vencimiento'  => null,
                'importe'            => 400.00,
                'base_imponible'     => 400.00,
                'iva'                => 84.00,
                'retencion'          => null,
                'total'              => 484.00,
                'estado'             => 'pendiente',
                'autofactura'        => false,
                'sociedad'           => null,
                'observaciones'      => null,
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
        ], ['id_factura'], [
            'numero_factura', 'numero_factura_ccp', 'serie', 'orden_factura', 
            'fecha_emision', 'fecha_vencimiento', 'estado', 
            'autofactura', 'sociedad', 'updated_at'
        ]);

        // ── Vinculación Factura-Pedido (pivot factura_pedidos unificado) ──
        DB::table('factura_pedidos')->upsert([
            ['id_factura_pedido' => 1, 'id_factura' => 1, 'id_pedido' => 1, 'importe_aplicado' => 1149.50, 'created_at' => $now, 'updated_at' => $now],
            ['id_factura_pedido' => 2, 'id_factura' => 2, 'id_pedido' => 2, 'importe_aplicado' => 380.00, 'created_at' => $now, 'updated_at' => $now],
            ['id_factura_pedido' => 3, 'id_factura' => 3, 'id_pedido' => 3, 'importe_aplicado' => 1185.80, 'created_at' => $now, 'updated_at' => $now],
            ['id_factura_pedido' => 4, 'id_factura' => 4, 'id_pedido' => 4, 'importe_aplicado' => 400.00, 'created_at' => $now, 'updated_at' => $now],
        ], ['id_factura_pedido'], ['importe_aplicado', 'updated_at']);
    }
}