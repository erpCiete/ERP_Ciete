<?php

namespace Tests\Unit\Models;

use App\Models\Empresa;
use App\Models\EstacionServicio;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstacionServicioTest extends TestCase
{
    use RefreshDatabase;

    public function test_estacion_servicio_uses_expected_table_key_and_fillable_attributes(): void
    {
        $estacion = new EstacionServicio;

        $this->assertSame('estaciones_servicio', $estacion->getTable());
        $this->assertSame('id_estacion_servicio', $estacion->getKeyName());
        $this->assertSame([
            'id_contexto',
            'id_empresa_cliente',
            'codigo_estacion',
            'nombre',
            'direccion',
            'codigo_postal',
            'poblacion',
            'provincia',
            'pais',
            'latitud_wgs84',
            'longitud_wgs84',
            'estado',
            'f_baja',
            'observaciones',
            'activo',
        ], $estacion->getFillable());
    }

    public function test_estacion_servicio_casts_decimal_date_and_boolean_attributes(): void
    {
        $estacion = EstacionServicio::factory()->create([
            'latitud_wgs84' => 40.4168,
            'longitud_wgs84' => -3.7038,
            'f_baja' => '2026-03-15',
            'activo' => 0,
        ]);

        $this->assertSame('40.41680000', $estacion->latitud_wgs84);
        $this->assertSame('-3.70380000', $estacion->longitud_wgs84);
        $this->assertInstanceOf(CarbonInterface::class, $estacion->f_baja);
        $this->assertFalse($estacion->activo);
    }

    public function test_estacion_servicio_belongs_to_empresa(): void
    {
        $empresa = Empresa::factory()->create();
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $empresa->id_contexto,
        ]);

        $this->assertTrue($estacion->empresa->is($empresa));
    }
}
