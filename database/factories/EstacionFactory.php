<?php

namespace Database\Factories;

use App\Models\Estacion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class EstacionFactory extends Factory
{
    protected $model = Estacion::class;

    public function definition(): array
    {
        // Crea un cliente mínimo y usa su id
        $clienteId = DB::table('clientes')->insertGetId([
            'nombre' => $this->faker->company,
            'codigo' => 'CLI-' . $this->faker->unique()->numberBetween(100, 999),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'nombre'     => 'Estación de Prueba',
            'codigo'     => 'EST-' . $this->faker->unique()->numberBetween(100, 999),
            'direccion'  => $this->faker->address,
            'cliente_id' => $clienteId,
        ];
    }
}