<?php

namespace Database\Seeders;

use App\Models\ContextoCliente;
use Illuminate\Database\Seeder;

class ContextosClienteSeeder extends Seeder
{
    /**
     * Seed contextos base del ERP.
     */
    public function run(): void
    {
        $rows = [
            [
                'id_contexto' => 1,
                'nombre' => 'MOEVE',
                'codigo' => 'MOEVE',
                'descripcion' => 'Contexto operativo MOEVE',
                'activo' => true,
            ],
            [
                'id_contexto' => 2,
                'nombre' => 'REPSOL',
                'codigo' => 'REPSOL',
                'descripcion' => 'Contexto operativo REPSOL',
                'activo' => true,
            ],
            [
                'id_contexto' => 3,
                'nombre' => 'OTRO',
                'codigo' => 'OTRO',
                'descripcion' => 'Contexto general u otros clientes',
                'activo' => true,
            ],
        ];

        foreach ($rows as $row) {
            ContextoCliente::query()->updateOrCreate(
                ['id_contexto' => $row['id_contexto']],
                $row
            );
        }
    }
}
