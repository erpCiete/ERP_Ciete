<?php

namespace Tests\Unit\Models;

use App\Models\Empresa;
use App\Models\EstacionServicio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaTest extends TestCase
{
    use RefreshDatabase;

    public function test_empresa_uses_expected_table_key_and_fillable_attributes(): void
    {
        $empresa = new Empresa;

        $this->assertSame('empresas', $empresa->getTable());
        $this->assertSame('id_empresa', $empresa->getKeyName());
        $this->assertSame([
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
        ], $empresa->getFillable());
    }

    public function test_empresa_clientes_scope_only_returns_client_types(): void
    {
        $cliente = Empresa::factory()->create([
            'tipo_empresa' => 'cliente',
        ]);
        $clienteProveedor = Empresa::factory()->clienteProveedor()->create();
        Empresa::factory()->proveedor()->create();

        $ids = Empresa::query()->clientes()->pluck('id_empresa')->all();

        $this->assertEqualsCanonicalizing([
            $cliente->id_empresa,
            $clienteProveedor->id_empresa,
        ], $ids);
    }

    public function test_empresa_activas_scope_only_returns_active_companies(): void
    {
        $activa = Empresa::factory()->create();
        Empresa::factory()->inactive()->create();

        $ids = Empresa::query()->activas()->pluck('id_empresa')->all();

        $this->assertSame([$activa->id_empresa], $ids);
    }

    public function test_empresa_has_many_estaciones(): void
    {
        $empresa = Empresa::factory()->create();

        EstacionServicio::factory()->count(2)->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $empresa->id_contexto,
        ]);

        $this->assertCount(2, $empresa->fresh()->estaciones);
    }
}
