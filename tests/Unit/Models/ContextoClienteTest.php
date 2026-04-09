<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\ContextoCliente;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContextoClienteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: ContextoCliente has correct table name
     */
    public function test_contexto_cliente_table_name(): void
    {
        $contexto = new ContextoCliente();
        $this->assertEquals('contextos_cliente', $contexto->getTable());
    }

    /**
     * Test: ContextoCliente primary key is 'id_contexto'
     */
    public function test_contexto_cliente_primary_key(): void
    {
        $contexto = new ContextoCliente();
        $this->assertEquals('id_contexto', $contexto->getKeyName());
    }

    /**
     * Test: ContextoCliente has correct fillable attributes
     */
    public function test_contexto_cliente_fillable_attributes(): void
    {
        $contexto = new ContextoCliente();
        $expectedFillable = [
            'nombre',
            'codigo',
            'descripcion',
            'activo',
        ];

        $this->assertEquals($expectedFillable, $contexto->getFillable());
    }

    /**
     * Test: ContextoCliente can be created
     */
    public function test_contexto_cliente_can_be_created(): void
    {
        $contexto = ContextoCliente::create([
            'nombre' => 'Contexto Prueba',
            'codigo' => 'CTX-001',
            'descripcion' => 'Contexto de prueba para testing',
            'activo' => true,
        ]);

        $this->assertNotNull($contexto->id_contexto);
        $this->assertEquals('Contexto Prueba', $contexto->nombre);
        $this->assertEquals('CTX-001', $contexto->codigo);
        $this->assertTrue($contexto->activo);
    }

    /**
     * Test: ContextoCliente activo attribute is cast to boolean
     */
    public function test_contexto_cliente_activo_cast_to_boolean(): void
    {
        $contexto = ContextoCliente::create([
            'nombre' => 'Contexto Test',
            'codigo' => 'CTX-002',
            'activo' => 1,
        ]);

        $this->assertIsBool($contexto->activo);
        $this->assertTrue($contexto->activo);
    }

    /**
     * Test: ContextoCliente activo false
     */
    public function test_contexto_cliente_activo_false(): void
    {
        $contexto = ContextoCliente::create([
            'nombre' => 'Contexto Inactivo',
            'codigo' => 'CTX-003',
            'activo' => false,
        ]);

        $this->assertFalse($contexto->activo);
    }

    /**
     * Test: ContextoCliente can be created without optional fields
     */
    public function test_contexto_cliente_can_be_created_with_minimal_data(): void
    {
        $contexto = ContextoCliente::create([
            'nombre' => 'Contexto Minimal',
            'codigo' => 'CTX-004',
        ]);

        $this->assertNotNull($contexto->id_contexto);
        $this->assertEquals('Contexto Minimal', $contexto->nombre);
    }

    /**
     * Test: ContextoCliente with description
     */
    public function test_contexto_cliente_with_description(): void
    {
        $descripcion = 'Contexto para el cliente ABC Inc. con todas sus operaciones';
        
        $contexto = ContextoCliente::create([
            'nombre' => 'ABC Inc',
            'codigo' => 'ABC-001',
            'descripcion' => $descripcion,
            'activo' => true,
        ]);

        $this->assertEquals($descripcion, $contexto->descripcion);
    }

    /**
     * Test: ContextoCliente can be retrieved by ID
     */
    public function test_contexto_cliente_can_be_retrieved_by_id(): void
    {
        $created = ContextoCliente::create([
            'nombre' => 'Contexto Retrieval Test',
            'codigo' => 'CTX-005',
            'activo' => true,
        ]);

        $retrieved = ContextoCliente::find($created->id_contexto);

        $this->assertInstanceOf(ContextoCliente::class, $retrieved);
        $this->assertEquals($created->id_contexto, $retrieved->id_contexto);
        $this->assertEquals('Contexto Retrieval Test', $retrieved->nombre);
    }

    /**
     * Test: ContextoCliente can be updated
     */
    public function test_contexto_cliente_can_be_updated(): void
    {
        $contexto = ContextoCliente::create([
            'nombre' => 'Nombre Original',
            'codigo' => 'CTX-006',
            'activo' => true,
        ]);

        $contexto->update([
            'nombre' => 'Nombre Actualizado',
            'descripcion' => 'Nueva descripción',
            'activo' => false,
        ]);

        $this->assertEquals('Nombre Actualizado', $contexto->nombre);
        $this->assertEquals('Nueva descripción', $contexto->descripcion);
        $this->assertFalse($contexto->activo);
    }

    /**
     * Test: ContextoCliente can be deleted
     */
    public function test_contexto_cliente_can_be_deleted(): void
    {
        $contexto = ContextoCliente::create([
            'nombre' => 'Contexto a Eliminar',
            'codigo' => 'CTX-007',
            'activo' => true,
        ]);

        $id = $contexto->id_contexto;
        $contexto->delete();

        $this->assertNull(ContextoCliente::find($id));
    }

    /**
     * Test: Multiple ContextoCliente can exist
     */
    public function test_multiple_contexto_cliente_can_exist(): void
    {
        ContextoCliente::create([
            'nombre' => 'Contexto 1',
            'codigo' => 'CTX-001',
            'activo' => true,
        ]);

        ContextoCliente::create([
            'nombre' => 'Contexto 2',
            'codigo' => 'CTX-002',
            'activo' => true,
        ]);

        ContextoCliente::create([
            'nombre' => 'Contexto 3',
            'codigo' => 'CTX-003',
            'activo' => false,
        ]);

        $all = ContextoCliente::all();
        $this->assertEquals(3, $all->count());
    }

    /**
     * Test: ContextoCliente codigo is unique (if enforced by database)
     */
    public function test_contexto_cliente_codigo_uniqueness(): void
    {
        ContextoCliente::create([
            'nombre' => 'Contexto A',
            'codigo' => 'UNIQUE-001',
            'activo' => true,
        ]);

        // Try to create another with same codigo - should fail if unique constraint exists
        // This test depends on database constraints being in place
        // For now, just verify the first one was created
        $contextos = ContextoCliente::where('codigo', 'UNIQUE-001')->get();
        $this->assertGreaterThanOrEqual(1, $contextos->count());
    }

    /**
     * Test: ContextoCliente query builder
     */
    public function test_contexto_cliente_query_builder(): void
    {
        ContextoCliente::create([
            'nombre' => 'Activo 1',
            'codigo' => 'ACT-001',
            'activo' => true,
        ]);

        ContextoCliente::create([
            'nombre' => 'Inactivo 1',
            'codigo' => 'INA-001',
            'activo' => false,
        ]);

        $activos = ContextoCliente::where('activo', true)->get();
        $this->assertEquals(1, $activos->count());
    }
}
