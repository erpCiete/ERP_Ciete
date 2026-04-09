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
            'nombre' => 'Estacion ' . fake()->unique()->company(),
            'codigo_estacion_interno' => null,
            'cod_repsol' => null,
            'cod_cepsa' => null,
            'concesion' => null,
            'tipo' => fake()->optional()->randomElement(['abanderada', 'libre', 'mixta']),
            'direccion' => fake()->optional()->streetAddress(),
            'codigo_postal' => fake()->optional()->postcode(),
            'poblacion' => fake()->optional()->city(),
            'provincia' => fake()->optional()->state(),
            'pais' => 'Espana',
            'latitud_wgs84' => null,
            'longitud_wgs84' => null,
            'delegacion' => null,
            'delegado' => null,
            'tecnico_gestion' => null,
            'telefono_tecnico_gestion' => null,
            'email_tecnico_gestion' => null,
            'responsable_es_gestor' => null,
            'telefono_movil' => null,
            'telefono_oficina' => null,
            'sede' => null,
            'tipo_mantenimiento' => null,
            'f_baja' => null,
            'razon_modificacion' => null,
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
