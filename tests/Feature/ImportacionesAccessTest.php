<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ImportacionesAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_importaciones_index_even_if_excel_parser_is_not_available(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('importaciones.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Importaciones/Index'));
    }
}
