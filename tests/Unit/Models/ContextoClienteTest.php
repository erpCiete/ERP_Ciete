<?php

namespace Tests\Unit\Models;

use App\Models\ContextoCliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContextoClienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_contexto_cliente_uses_expected_table_key_and_fillable_attributes(): void
    {
        $contexto = new ContextoCliente;

        $this->assertSame('contextos_cliente', $contexto->getTable());
        $this->assertSame('id_contexto', $contexto->getKeyName());
        $this->assertSame([
            'nombre',
            'codigo',
            'descripcion',
            'activo',
        ], $contexto->getFillable());
    }

    public function test_contexto_cliente_casts_activo_to_boolean(): void
    {
        $contexto = ContextoCliente::factory()->create([
            'activo' => 0,
        ]);

        $this->assertIsBool($contexto->activo);
        $this->assertFalse($contexto->activo);
    }
}
