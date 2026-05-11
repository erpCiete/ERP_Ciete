<?php

namespace Database\Factories;

use App\Models\ContextoCliente;
use App\Models\Empresa;
use App\Models\Trabajo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trabajo>
 */
class TrabajoFactory extends Factory
{
    protected $model = Trabajo::class;

    public function definition(): array
    {
        return [
            'id_contexto'         => ContextoCliente::factory(),
            'id_empresa_cliente'  => Empresa::factory(),
            'numero_trabajo'      => fake()->unique()->randomNumber(5),
            'descripcion_trabajo' => fake()->sentence(),
            'estado'              => 'en_curso',
            'bloqueado_cierre'    => false,
            'fecha_encargo'       => now(),
        ];
    }

    public function closed(): static
    {
        return $this->state(fn() => [
            'estado'  => 'finalizado',
        ]);
    }
}
