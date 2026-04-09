<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\EstacionServicio;
use App\Models\Empresa;
use App\Models\ContextoCliente;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EstacionServicioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a context for testing
        ContextoCliente::create([
            'nombre' => 'Test Context',
            'codigo' => 'TEST',
            'activo' => true,
        ]);
    }

    /**
     * Test: EstacionServicio has correct fillable attributes
     */
    public function test_estacion_servicio_fillable_attributes(): void
    {
        $estacion = new EstacionServicio();
        $expectedFillable = [
            'id_contexto',
            'id_empresa_cliente',
            'nombre',
            'codigo_estacion_interno',
            'cod_repsol',
            'cod_cepsa',
            'concesion',
            'tipo',
            'direccion',
            'codigo_postal',
            'poblacion',
            'provincia',
            'pais',
            'latitud_wgs84',
            'longitud_wgs84',
            'delegacion',
            'delegado',
            'tecnico_gestion',
            'telefono_tecnico_gestion',
            'email_tecnico_gestion',
            'responsable_es_gestor',
            'telefono_movil',
            'telefono_oficina',
            'sede',
            'tipo_mantenimiento',
            'f_baja',
            'razon_modificacion',
            'observaciones',
            'activo',
        ];

        $this->assertEquals($expectedFillable, $estacion->getFillable());
    }

    /**
     * Test: EstacionServicio can be created
     */
    public function test_estacion_servicio_can_be_created(): void
    {
        $context = ContextoCliente::first();
        
        $empresa = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Test',
            'razon_social' => 'Empresa Test',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        $estacion = EstacionServicio::create([
            'id_contexto' => $context->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'nombre' => 'Estación Central',
            'codigo_estacion_interno' => 'EST-001',
            'tipo' => 'normal',
            'direccion' => 'Calle Principal 123',
            'codigo_postal' => '28001',
            'poblacion' => 'Madrid',
            'provincia' => 'Madrid',
            'pais' => 'España',
            'activo' => true,
        ]);

        $this->assertNotNull($estacion->id_estacion_servicio);
        $this->assertEquals('Estación Central', $estacion->nombre);
        $this->assertEquals('EST-001', $estacion->codigo_estacion_interno);
        $this->assertEquals($empresa->id_empresa, $estacion->id_empresa_cliente);
    }

    /**
     * Test: EstacionServicio table name is 'estaciones_servicio'
     */
    public function test_estacion_servicio_table_name(): void
    {
        $estacion = new EstacionServicio();
        $this->assertEquals('estaciones_servicio', $estacion->getTable());
    }

    /**
     * Test: EstacionServicio primary key is 'id_estacion_servicio'
     */
    public function test_estacion_servicio_primary_key(): void
    {
        $estacion = new EstacionServicio();
        $this->assertEquals('id_estacion_servicio', $estacion->getKeyName());
    }

    /**
     * Test: EstacionServicio activo cast to boolean
     */
    public function test_estacion_servicio_activo_cast_to_boolean(): void
    {
        $context = ContextoCliente::first();
        
        $empresa = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Test',
            'razon_social' => 'Empresa Test',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        $estacion = EstacionServicio::create([
            'id_contexto' => $context->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'nombre' => 'Estación Test',
            'tipo' => 'normal',
            'activo' => 1,
        ]);

        $this->assertIsBool($estacion->activo);
        $this->assertTrue($estacion->activo);
    }

    /**
     * Test: EstacionServicio latitud_wgs84 and longitud_wgs84 cast to decimal
     */
    public function test_estacion_servicio_coordinates_cast_to_decimal(): void
    {
        $context = ContextoCliente::first();
        
        $empresa = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Test',
            'razon_social' => 'Empresa Test',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        $estacion = EstacionServicio::create([
            'id_contexto' => $context->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'nombre' => 'Estación Test',
            'tipo' => 'normal',
            'latitud_wgs84' => 40.4168,
            'longitud_wgs84' => -3.7038,
            'activo' => true,
        ]);

        $this->assertIsString($estacion->latitud_wgs84);
        $this->assertIsString($estacion->longitud_wgs84);
        $this->assertEquals('40.41680000', $estacion->latitud_wgs84);
        $this->assertEquals('-3.70380000', $estacion->longitud_wgs84);
    }

    /**
     * Test: EstacionServicio f_baja cast to date
     */
    public function test_estacion_servicio_f_baja_cast_to_date(): void
    {
        $context = ContextoCliente::first();
        
        $empresa = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Test',
            'razon_social' => 'Empresa Test',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        $estacion = EstacionServicio::create([
            'id_contexto' => $context->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'nombre' => 'Estación Cerrada',
            'tipo' => 'normal',
            'f_baja' => '2026-03-15',
            'activo' => false,
        ]);

        $this->assertIsObject($estacion->f_baja);
        $this->assertEquals('2026-03-15', $estacion->f_baja->format('Y-m-d'));
    }

    /**
     * Test: EstacionServicio belongsTo empresa
     */
    public function test_estacion_servicio_belongs_to_empresa(): void
    {
        $context = ContextoCliente::first();
        
        $empresa = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Relacionada',
            'razon_social' => 'Empresa Relacionada',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        $estacion = EstacionServicio::create([
            'id_contexto' => $context->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'nombre' => 'Estación Test',
            'tipo' => 'normal',
            'activo' => true,
        ]);

        $this->assertInstanceOf(Empresa::class, $estacion->empresa);
        $this->assertEquals($empresa->id_empresa, $estacion->empresa->id_empresa);
        $this->assertEquals('Empresa Relacionada', $estacion->empresa->nombre);
    }

    /**
     * Test: EstacionServicio with complete address data
     */
    public function test_estacion_servicio_with_complete_address(): void
    {
        $context = ContextoCliente::first();
        
        $empresa = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Test',
            'razon_social' => 'Empresa Test',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        $estacion = EstacionServicio::create([
            'id_contexto' => $context->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'nombre' => 'Estación Madrid',
            'tipo' => 'normal',
            'direccion' => 'Paseo de la Castellana 123',
            'codigo_postal' => '28046',
            'poblacion' => 'Madrid',
            'provincia' => 'Madrid',
            'pais' => 'España',
            'activo' => true,
        ]);

        $this->assertEquals('Paseo de la Castellana 123', $estacion->direccion);
        $this->assertEquals('28046', $estacion->codigo_postal);
        $this->assertEquals('Madrid', $estacion->poblacion);
        $this->assertEquals('España', $estacion->pais);
    }

    /**
     * Test: EstacionServicio with technical contact information
     */
    public function test_estacion_servicio_with_technical_contact(): void
    {
        $context = ContextoCliente::first();
        
        $empresa = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Test',
            'razon_social' => 'Empresa Test',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        $estacion = EstacionServicio::create([
            'id_contexto' => $context->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'nombre' => 'Estación Test',
            'tipo' => 'normal',
            'tecnico_gestion' => 'Juan García',
            'telefono_tecnico_gestion' => '912345678',
            'email_tecnico_gestion' => 'juan@example.com',
            'activo' => true,
        ]);

        $this->assertEquals('Juan García', $estacion->tecnico_gestion);
        $this->assertEquals('912345678', $estacion->telefono_tecnico_gestion);
        $this->assertEquals('juan@example.com', $estacion->email_tecnico_gestion);
    }

    /**
     * Test: EstacionServicio with supplier codes
     */
    public function test_estacion_servicio_with_supplier_codes(): void
    {
        $context = ContextoCliente::first();
        
        $empresa = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Test',
            'razon_social' => 'Empresa Test',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        $estacion = EstacionServicio::create([
            'id_contexto' => $context->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'nombre' => 'Estación Test',
            'tipo' => 'normal',
            'cod_repsol' => 'REPS-123456',
            'cod_cepsa' => 'CEPS-789012',
            'activo' => true,
        ]);

        $this->assertEquals('REPS-123456', $estacion->cod_repsol);
        $this->assertEquals('CEPS-789012', $estacion->cod_cepsa);
    }

    /**
     * Test: EstacionServicio update
     */
    public function test_estacion_servicio_can_be_updated(): void
    {
        $context = ContextoCliente::first();
        
        $empresa = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Test',
            'razon_social' => 'Empresa Test',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        $estacion = EstacionServicio::create([
            'id_contexto' => $context->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'nombre' => 'Nombre Original',
            'tipo' => 'normal',
            'activo' => true,
        ]);

        $estacion->update([
            'nombre' => 'Nombre Actualizado',
            'activo' => false,
        ]);

        $this->assertEquals('Nombre Actualizado', $estacion->nombre);
        $this->assertFalse($estacion->activo);
    }

    /**
     * Test: EstacionServicio with observaciones
     */
    public function test_estacion_servicio_with_observaciones(): void
    {
        $context = ContextoCliente::first();
        
        $empresa = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Test',
            'razon_social' => 'Empresa Test',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        $estacion = EstacionServicio::create([
            'id_contexto' => $context->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'nombre' => 'Estación Test',
            'tipo' => 'normal',
            'observaciones' => 'Estación en reforma',
            'activo' => true,
        ]);

        $this->assertEquals('Estación en reforma', $estacion->observaciones);
    }
}
