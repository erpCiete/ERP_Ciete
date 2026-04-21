<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Factura;
use App\Models\Trabajo;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacturaFactory extends Factory
{
    protected $model = Factura::class;

    public function definition(): array
    {
        // Generación de un bloque económico coherente
        $importe       = $this->faker->randomFloat(2, 100, 10000);
        $baseImponible = $importe;
        $iva           = round($baseImponible * 0.21, 2);
        $retencion     = 0.00;
        $total         = $baseImponible + $iva - $retencion;

        return [
            // 1. Por defecto, generamos una factura válida para MOEVE (Contexto 1)
            'id_contexto'         => 1,
            
            // 2. Relaciones seguras: Forzamos a que el Trabajo se cree en el mismo contexto
            'id_trabajo'          => Trabajo::factory()->state(['id_contexto' => 1]),
            
            // 3. Heredamos la empresa del trabajo creado para evitar errores de Foreign Key en la DB
            'id_empresa_cliente'  => function (array $attributes) {
                return Trabajo::find($attributes['id_trabajo'])->id_empresa_cliente;
            },

            // 4. Datos Base
            'numero_factura'      => $this->faker->unique()->bothify('F-####-???'),
            'serie'               => $this->faker->optional()->randomElement(['M', 'A', 'B']),
            
            // 5. Lógica MOEVE: numero_ccp obligatorio, orden_factura anulado
            'numero_factura_ccp'  => $this->faker->unique()->bothify('CCP-####-????'),
            'orden_factura'       => null,
            'sociedad'            => 'MOEVE S.A.',
            'autofactura'         => false,
            
            // 6. Fechas
            'fecha_solicitud'     => $this->faker->date(),
            'fecha_emision'       => $this->faker->optional(0.7)->date(),
            'fecha_vencimiento'   => $this->faker->optional(0.5)->date(),
            
            // 7. Importes
            'importe'             => $importe,
            'base_imponible'      => $baseImponible,
            'iva'                 => $iva,
            'retencion'           => $retencion,
            'total'               => $total,
            
            // 8. Estados y Extras
            'estado'              => $this->faker->randomElement([
                'pendiente', 'emitida', 'cobrada_parcial', 'cobrada', 'vencida', 'anulada'
            ]),
            'observaciones'       => $this->faker->optional()->sentence(),
        ];
    }

    /*
     * Estado: Factura Pendiente 
     */
    public function pendiente(): static
    {
        return $this->state(fn () => [
            'estado' => 'pendiente', 
            'fecha_emision' => null, 
            'fecha_vencimiento' => null
        ]);
    }

    /** 
     * Estado: Factura Emitida 
     */
    public function emitida(): static
    {
        return $this->state(fn () => [
            'estado'            => 'emitida',
            'fecha_emision'     => now()->toDateString(),
            'fecha_vencimiento' => now()->addDays(60)->toDateString(),
        ]);
    }

    /** 
     * Estado: Factura Cobrada 
     */
    public function cobrada(): static
    {
        return $this->state(fn () => [
            'estado'            => 'cobrada',
            'fecha_emision'     => now()->subDays(30)->toDateString(),
            'fecha_vencimiento' => now()->subDays(1)->toDateString(),
        ]);
    }

    /** 
     * ── ESTADO CRÍTICO: Contexto REPSOL (2) ──
     * Intercambia la lógica: anula el CCP y hace obligatorio el orden_factura y autofactura.
     */
    public function repsol(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'id_contexto'        => 2,
                'id_trabajo'         => Trabajo::factory()->state(['id_contexto' => 2]),
                'numero_factura_ccp' => null,
                'sociedad'           => null,
                'orden_factura'      => $this->faker->randomElement([1, 2]),
                'autofactura'        => $this->faker->boolean(80), // Repsol usa mucha autofactura
            ];
        });
    }
}