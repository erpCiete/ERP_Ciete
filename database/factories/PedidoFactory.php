<?php

namespace Database\Factories;

use App\Models\Pedido;
use App\Models\Proyecto; // Asegúrate de ajustar según tu estructura real (Trabajo, Presupuesto, etc.)
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 5000);
        $iva = $subtotal * 0.21;
        
        return [
            'id_contexto' => 1, 
            'id_empresa_cliente' => Empresa::factory(),
            'numero_pedido' => $this->faker->unique()->bothify('PED-####-????'),
            'numero_aviso' => $this->faker->optional()->bothify('AV-####-????'),
            'fecha_solicitud_pedido' => $this->faker->date(),
            'fecha_recepcion_pedido' => $this->faker->date(),
            'fecha_solicitud_factura' => $this->faker->optional()->date(),
            'estado' => $this->faker->randomElement(['pendiente', 'solicitado', 'recibido', 'en_ejecucion', 'cerrado', 'anulado']),
            'descripcion_seleccionable' => $this->faker->sentence(3),
            'descripcion_libre' => $this->faker->paragraph(),
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $subtotal + $iva,
            'observaciones' => $this->faker->optional()->sentence(),
        ];
    }
}