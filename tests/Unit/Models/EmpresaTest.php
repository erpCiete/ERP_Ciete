<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Empresa;
use App\Models\ContextoCliente;
use App\Models\EstacionServicio;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmpresaTest extends TestCase
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
     * Test: Empresa tiene los atributos fillables corretos
     */
    public function test_empresa_fillable_attributes(): void
    {
        $empresa = new Empresa();
        $expectedFillable = [
            'id_contexto',
            'empresa_padre_id',
            'nombre',
            'nombre_comercial',
            'razon_social',
            'cif',
            'tipo_empresa',
            'web',
            'observaciones',
            'activo',
        ];

        $this->assertEquals($expectedFillable, $empresa->getFillable());
    }

    /**
     * Test: Empresa puede ser creada con datos válidos
     */
    public function test_empresa_can_be_created(): void
    {
        $context = ContextoCliente::first();
        
        $empresa = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Test S.A.',
            'nombre_comercial' => 'Empresa Test',
            'razon_social' => 'Empresa Test S.A.',
            'cif' => 'A12345678',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        $this->assertNotNull($empresa->id_empresa);
        $this->assertEquals('Empresa Test S.A.', $empresa->nombre);
        $this->assertEquals('A12345678', $empresa->cif);
        $this->assertTrue($empresa->activo);
    }

    /**
     * Test: Empresa table name is 'empresas'
     */
    public function test_empresa_table_name(): void
    {
        $empresa = new Empresa();
        $this->assertEquals('empresas', $empresa->getTable());
    }

    /**
     * Test: Empresa primary key is 'id_empresa'
     */
    public function test_empresa_primary_key(): void
    {
        $empresa = new Empresa();
        $this->assertEquals('id_empresa', $empresa->getKeyName());
    }

    /**
     * Test: Empresa activo attribute is cast to boolean
     */
    public function test_empresa_activo_cast_to_boolean(): void
    {
        $context = ContextoCliente::first();
        
        $empresa = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Test',
            'razon_social' => 'Test',
            'tipo_empresa' => 'cliente',
            'activo' => 1,
        ]);

        $this->assertIsBool($empresa->activo);
        $this->assertTrue($empresa->activo);
    }

    /**
     * Test: Empresa activo as false
     */
    public function test_empresa_activo_false(): void
    {
        $context = ContextoCliente::first();
        
        $empresa = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Test Inactive',
            'razon_social' => 'Test Inactive',
            'tipo_empresa' => 'proveedor',
            'activo' => false,
        ]);

        $this->assertFalse($empresa->activo);
    }

    /**
     * Test: Empresa hasMany estaciones
     */
    public function test_empresa_has_many_estaciones(): void
    {
        $context = ContextoCliente::first();
        
        $empresa = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Con Estaciones',
            'razon_social' => 'Empresa Con Estaciones',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        EstacionServicio::create([
            'id_contexto' => $context->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'nombre' => 'Estación 1',
            'tipo' => 'normal',
            'activo' => true,
        ]);

        EstacionServicio::create([
            'id_contexto' => $context->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'nombre' => 'Estación 2',
            'tipo' => 'normal',
            'activo' => true,
        ]);

        $this->assertEquals(2, $empresa->estaciones()->count());
        $this->assertCount(2, $empresa->estaciones);
    }

    /**
     * Test: Scope clientes filters by type
     */
    public function test_scope_clientes(): void
    {
        $context = ContextoCliente::first();
        
        Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Cliente Normal',
            'razon_social' => 'Cliente Normal',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Cliente Proveedor',
            'razon_social' => 'Cliente Proveedor',
            'tipo_empresa' => 'cliente_proveedor',
            'activo' => true,
        ]);

        Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Solo Proveedor',
            'razon_social' => 'Solo Proveedor',
            'tipo_empresa' => 'proveedor',
            'activo' => true,
        ]);

        $clientes = Empresa::clientes()->get();
        
        $this->assertEquals(2, $clientes->count());
        $this->assertTrue($clientes->pluck('tipo_empresa')->every(fn($type) => 
            in_array($type, ['cliente', 'cliente_proveedor'])
        ));
    }

    /**
     * Test: Scope activas filters active companies
     */
    public function test_scope_activas(): void
    {
        $context = ContextoCliente::first();
        
        Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Activa 1',
            'razon_social' => 'Empresa Activa 1',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Activa 2',
            'razon_social' => 'Empresa Activa 2',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Inactiva',
            'razon_social' => 'Empresa Inactiva',
            'tipo_empresa' => 'cliente',
            'activo' => false,
        ]);

        $activas = Empresa::activas()->get();
        
        $this->assertEquals(2, $activas->count());
        $this->assertTrue($activas->every(fn($empresa) => $empresa->activo === true));
    }

    /**
     * Test: Scope clientes and activas together
     */
    public function test_scope_clientes_and_activas(): void
    {
        $context = ContextoCliente::first();
        
        Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Cliente Activo',
            'razon_social' => 'Cliente Activo',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Cliente Inactivo',
            'razon_social' => 'Cliente Inactivo',
            'tipo_empresa' => 'cliente',
            'activo' => false,
        ]);

        Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Proveedor Activo',
            'razon_social' => 'Proveedor Activo',
            'tipo_empresa' => 'proveedor',
            'activo' => true,
        ]);

        $result = Empresa::clientes()->activas()->get();
        
        $this->assertEquals(1, $result->count());
        $this->assertEquals('Cliente Activo', $result->first()->nombre);
    }

    /**
     * Test: Empresa can have parent empresa
     */
    public function test_empresa_can_have_parent(): void
    {
        $context = ContextoCliente::first();
        
        $empresaPadre = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Padre',
            'razon_social' => 'Empresa Padre',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        $empresaHija = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'empresa_padre_id' => $empresaPadre->id_empresa,
            'nombre' => 'Empresa Hija',
            'razon_social' => 'Empresa Hija',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        $this->assertEquals($empresaPadre->id_empresa, $empresaHija->empresa_padre_id);
    }

    /**
     * Test: Empresa with web and observaciones
     */
    public function test_empresa_with_web_and_observaciones(): void
    {
        $context = ContextoCliente::first();
        
        $empresa = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Empresa Web',
            'razon_social' => 'Empresa Web',
            'tipo_empresa' => 'cliente',
            'web' => 'https://example.com',
            'observaciones' => 'Cliente importante',
            'activo' => true,
        ]);

        $this->assertEquals('https://example.com', $empresa->web);
        $this->assertEquals('Cliente importante', $empresa->observaciones);
    }

    /**
     * Test: Empresa update
     */
    public function test_empresa_can_be_updated(): void
    {
        $context = ContextoCliente::first();
        
        $empresa = Empresa::create([
            'id_contexto' => $context->id_contexto,
            'nombre' => 'Original Name',
            'razon_social' => 'Original',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        $empresa->update([
            'nombre' => 'Updated Name',
            'activo' => false,
        ]);

        $this->assertEquals('Updated Name', $empresa->nombre);
        $this->assertFalse($empresa->activo);
    }
}
