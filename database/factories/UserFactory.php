<?php

namespace Database\Factories;

use App\Models\ContextoCliente;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $contexto = ContextoCliente::query()->firstOrCreate(
            ['codigo' => 'OTRO'],
            [
                'nombre' => 'OTRO',
                'descripcion' => 'Contexto de pruebas',
                'activo' => true,
            ]
        );

        return [
            'id_contexto' => $contexto->id_contexto,
            'id_contacto_empresa' => null,
            'nombre' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'nombre_usuario' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verificado_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'telefono' => fake()->numerify('6########'),
            'activo' => true,
            'ultimo_login_at' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Estado inactivo para pruebas.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }

    /**
     * Alias de compatibilidad con tests legacy.
     */
    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verificado_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->afterCreating(function ($user) {
            $role = Role::query()->firstOrCreate(
                ['slug' => 'admin'],
                [
                    'nombre' => 'admin',
                    'descripcion' => 'Acceso total',
                    'activo' => true,
                ]
            );

            DB::table('usuario_roles')->updateOrInsert(
                ['id_usuario' => $user->id_usuario, 'id_rol' => $role->id_rol],
                ['created_at' => now()]
            );
        });
    }
}
