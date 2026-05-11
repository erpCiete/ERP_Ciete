<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatosBaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogoBaseSeeder::class,
        ]);
    }
}