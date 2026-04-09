<?php

namespace Database\Factories;

use App\Models\ContextoCliente;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empresa>
 */
class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    public function definition(): array
    {
        return [
            'id_contexto' => ContextoCliente::factory(),
            'empresa_padre_id' => null,
            'nombre' => fake()->unique()->company(),
            'nombre_comercial' => fake()->optional()->company(),
            'razon_social' => fake()->company() . ' SL',
            'cif' => null,
            'tipo_empresa' => 'cliente',
            'web' => fake()->optional()->url(),
            'observaciones' => fake()->optional()->sentence(),
            'activo' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn() => [
            'activo' => false,
        ]);
    }

    public function proveedor(): static
    {
        return $this->state(fn() => [
            'tipo_empresa' => 'proveedor',
        ]);
    }

    public function clienteProveedor(): static
    {
        return $this->state(fn() => [
            'tipo_empresa' => 'cliente_proveedor',
        ]);
    }
}
