<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Factura;
use App\Models\Trabajo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Factura>
 */
class FacturaFactory extends Factory
{
    protected $model = Factura::class;

    public function definition(): array
    {
        $importe       = $this->faker->randomFloat(2, 100, 10000);
        $baseImponible = $importe;
        $iva           = round($baseImponible * 0.21, 2);
        $total         = $baseImponible + $iva;

        return [
            'id_contexto'         => 1,
            'id_trabajo'          => Trabajo::factory(),
            'id_empresa_cliente'  => Empresa::factory(),
            'numero_factura'      => $this->faker->unique()->bothify('F-####-???'),
            'numero_factura_ccp'  => null,
            'serie'               => $this->faker->optional()->randomElement(['M', 'R', 'A', 'B']),
            'orden_factura'       => $this->faker->numberBetween(1, 9),
            'fecha_solicitud'     => $this->faker->optional()->date(),
            'fecha_emision'       => $this->faker->optional()->date(),
            'fecha_vencimiento'   => $this->faker->optional()->date(),
            'importe'             => $importe,
            'base_imponible'      => $baseImponible,
            'iva'                 => $iva,
            'retencion'           => null,
            'total'               => $total,
            'estado'              => $this->faker->randomElement([
                'pendiente', 'solicitada', 'emitida', 'enviada',
                'cobrada_parcial', 'cobrada', 'vencida', 'anulada',
            ]),
            'autofactura'    => false,
            'sociedad'       => null,
            'observaciones'  => $this->faker->optional()->sentence(),
        ];
    }

    /** Estado pendiente */
    public function pendiente(): static
    {
        return $this->state(fn () => ['estado' => 'pendiente']);
    }

    /** Estado emitida */
    public function emitida(): static
    {
        return $this->state(fn () => [
            'estado'        => 'emitida',
            'fecha_emision' => now()->toDateString(),
        ]);
    }

    /** Estado cobrada */
    public function cobrada(): static
    {
        return $this->state(fn () => [
            'estado'           => 'cobrada',
            'fecha_emision'    => now()->subDays(30)->toDateString(),
            'fecha_vencimiento'=> now()->subDays(1)->toDateString(),
        ]);
    }

    /** Contexto MOEVE (1) con numero_factura_ccp obligatorio */
    public function moeve(): static
    {
        return $this->state(fn () => [
            'id_contexto'        => 1,
            'numero_factura_ccp' => $this->faker->unique()->bothify('CCP-####-????'),
        ]);
    }

    /** Contexto REPSOL (2) con orden_factura único por trabajo */
    public function repsol(): static
    {
        return $this->state(fn () => [
            'id_contexto'  => 2,
            'orden_factura'=> $this->faker->numberBetween(1, 9),
        ]);
    }
}