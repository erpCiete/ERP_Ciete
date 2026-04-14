<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\EstacionServicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EstacionServicio>
 */
class EstacionServicioFactory extends Factory
{
    protected $model = EstacionServicio::class;

    public function definition(): array
    {
        return [
            'id_empresa_cliente' => Empresa::factory(),
            'id_contexto' => fn(array $attributes) => Empresa::query()
                ->withoutGlobalScopes()
                ->findOrFail($attributes['id_empresa_cliente'])
                ->id_contexto,
            'codigo_estacion' => strtoupper(fake()->unique()->bothify('EST-####')),
            'nombre' => 'Estacion ' . fake()->unique()->company(),
            'direccion' => fake()->optional()->streetAddress(),
            'codigo_postal' => fake()->optional()->postcode(),
            'poblacion' => fake()->optional()->city(),
            'provincia' => fake()->optional()->state(),
            'pais' => 'Espana',
            'latitud_wgs84' => null,
            'longitud_wgs84' => null,
            'estado' => null,
            'f_baja' => null,
            'observaciones' => fake()->optional()->sentence(),
            'activo' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn() => [
            'activo' => false,
            'f_baja' => now()->toDateString(),
        ]);
    }
}
