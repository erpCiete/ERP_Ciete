<?php

namespace Tests\Feature;

use App\Models\Trabajo;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Vite;
use Illuminate\Support\HtmlString;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ClosureDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(Vite::class, new class extends Vite {
            public function __invoke($entrypoints, $buildDirectory = null): HtmlString
            {
                return new HtmlString('');
            }
        });

        $this->seed(DatabaseSeeder::class);
    }

    public function test_director_and_admin_can_view_dashboard_with_real_data(): void
    {
        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();
        $cesar = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();

        $this->actingAs($admin)
            ->get('/cierre')
            ->assertOk();

        $this->actingAs($cesar)
            ->get('/cierre')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cierre/Dashboard')
                ->has('works')
                ->has('contexts'));
    }

    public function test_director_can_mark_reviewed_and_close_eligible_work(): void
    {
        $cesar = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();
        $trabajo = Trabajo::query()->findOrFail(3);

        $this->actingAs($cesar)
            ->post(route('cierre.review'), ['ids' => [$trabajo->id_trabajo]])
            ->assertRedirect();

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'id_usuario_cierre' => $cesar->id_usuario,
        ]);

        $this->actingAs($cesar)
            ->post(route('cierre.close', $trabajo))
            ->assertRedirect();

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'estado' => 'cerrado',
            'cerrado' => true,
            'id_usuario_cierre' => $cesar->id_usuario,
        ]);
    }

    public function test_director_cannot_close_blocked_work(): void
    {
        $cesar = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();
        $trabajo = Trabajo::query()->findOrFail(5);

        $this->actingAs($cesar)
            ->post(route('cierre.close', $trabajo))
            ->assertSessionHasErrors(['message']);

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'estado' => 'terminado',
            'cerrado' => false,
        ]);
    }

    public function test_director_can_reopen_closed_work(): void
    {
        $cesar = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();
        $trabajo = Trabajo::query()->findOrFail(8);

        $this->actingAs($cesar)
            ->post(route('cierre.reopen', $trabajo), [
                'reason' => 'Falta revisar importes finales.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'estado' => 'terminado',
            'cerrado' => false,
            'id_usuario_cierre' => null,
        ]);

        $trabajo->refresh();

        $this->assertStringContainsString('Falta revisar importes finales.', (string) $trabajo->observaciones);
    }
}
