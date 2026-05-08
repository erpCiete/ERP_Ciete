<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MuestraOperativaDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DatabaseSeeder::class,
            MuestraOperativa50Seeder::class,
        ]);
    }
}