<?php
namespace Tests\Feature\Seeders;

use Database\Seeders\ContextosClienteSeeder;
use Database\Seeders\DatosBaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatosBaseSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ContextosClienteSeeder::class);
        $this->seed(DatosBaseSeeder::class);
    }

    public function test_datos_base_seeder_creates_minimum_system_catalogs(): void
    {
        $this->assertDatabaseHas('unidades', ['id_unidad' => 1, 'abreviatura' => 'ud']);
        $this->assertDatabaseHas('tipos_documento', ['id_tipo_documento' => 1, 'id_contexto' => 1]);
        $this->assertDatabaseHas('tipos_documento', ['id_tipo_documento' => 2, 'id_contexto' => 2]);
        $this->assertDatabaseHas('tipos_trabajo', ['id_tipo_trabajo' => 1, 'codigo' => 'NPV']);
        $this->assertDatabaseHas('tipos_trabajo', ['id_tipo_trabajo' => 4, 'id_contexto' => 2]);
        $this->assertSame(4, DB::table('unidades')->count());
        $this->assertSame(10, DB::table('tipos_documento')->count());
        $this->assertSame(7, DB::table('tipos_trabajo')->count());
    }

    public function test_datos_base_seeder_is_idempotent(): void
    {
        $this->seed(DatosBaseSeeder::class);

        $this->assertSame(4, DB::table('unidades')->count());
        $this->assertSame(10, DB::table('tipos_documento')->count());
        $this->assertSame(7, DB::table('tipos_trabajo')->count());
    }
}