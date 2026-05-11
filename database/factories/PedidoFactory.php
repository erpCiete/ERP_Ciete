<?php

namespace Database\Factories;

use App\Models\Pedido;
use App\Models\Trabajo;
use App\Models\Tarifario;
use Illuminate\Database\Eloquent\Factories\Factory;

class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    public function definition(): array
    {
        // Generamos importes coherentes
        $importePedido = $this->faker->randomFloat(2, 500, 5000);
        $unidades = $this->faker->randomFloat(2, 1, 50);

        return [
            // El contexto por defecto lo ponemos a 1 (Moeve), pero se sobreescribirá en los tests/seeders
            'id_contexto' => 1, 
            
            // Relaciones: asumimos que se crearán o se pasarán en la llamada a la factoría
            'id_trabajo' => Trabajo::factory(), 
            'id_tarifario' => null, // Opcional según la lógica de negocio
            
            // Datos base
            'numero_pedido' => $this->faker->unique()->bothify('PED-####-????'),
            'fecha_solicitud' => $this->faker->date(),
            'fecha_recepcion' => $this->faker->optional(0.7)->date(), // 70% de probabilidad de tener fecha de recepción
            
            // Control Económico
            'importe_pedido' => $importePedido,
            'importe_solicitado' => $this->faker->randomFloat(2, 0, $importePedido),
            'importe_facturado' => 0.00, // Por defecto un pedido nuevo no está facturado
            
            // Control de Unidades
            'unidades_pedido' => $unidades,
            'unidades_solicitadas' => $this->faker->randomFloat(2, 0, $unidades),
            
            // Estados y Flags
            'estado' => $this->faker->randomElement(['pendiente', 'solicitado', 'recibido', 'facturado_parcial', 'facturado', 'cancelado', 'anulado']),
            'pedido_completo' => $this->faker->boolean(30), // 30% de probabilidad de estar completo
            'tiene_mas_de_1_item' => $this->faker->boolean(80), // Lo habitual es que tengan más de un item
            'facturado_completo' => false, // Por defecto false al crearse
            
            // Extras
            'observaciones' => $this->faker->optional(0.5)->sentence(),
        ];
    }

    /**
     * Estado para pedidos de REPSOL (Contexto 2)
     */
    public function repsol(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_contexto' => 2,
        ]);
    }

    /**
     * Estado para pedidos ya facturados
     */
    public function completado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'facturado',
            'pedido_completo' => true,
            'facturado_completo' => true,
            'importe_facturado' => $attributes['importe_pedido'] ?? 1000,
            'importe_solicitado' => $attributes['importe_pedido'] ?? 1000,
        ]);
    }
}
