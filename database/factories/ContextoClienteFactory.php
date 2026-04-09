<?php

namespace Database\Factories;

use App\Models\ContextoCliente;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContextoCliente>
 */
class ContextoClienteFactory extends Factory
{
    protected $model = ContextoCliente::class;

    public function definition(): array
    {
        return [
            'nombre' => 'Contexto ' . fake()->unique()->company(),
            'codigo' => Str::upper(fake()->unique()->bothify('CTX-###??')),
            'descripcion' => fake()->sentence(),
            'activo' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn() => [
            'activo' => false,
        ]);
    }
}
