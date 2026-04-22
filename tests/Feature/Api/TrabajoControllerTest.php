<?php

namespace Tests\Feature\Api;

use App\Models\Trabajo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TrabajoControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** Crea un usuario normal autenticado. */
    private function actingAsUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        return $user;
    }

    /** Crea un usuario con rol 'admin' autenticado. */
    private function actingAsAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin'); // Requiere Spatie Permission o equivalente
        $this->actingAs($user);
        return $user;
    }

    public function index_renders_inertia_page_for_authenticated_user(): void
    {
        $this->actingAsUser();
        Trabajo::factory()->count(3)->create();

        $response = $this->get(route('trabajos.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Trabajos/Index')
                ->has('trabajos')
                ->has('filtros')
                ->has('contextoIds')
        );
    }

    public function index_redirects_unauthenticated_users(): void
    {
        $response = $this->get(route('trabajos.index'));

        $response->assertRedirect(route('login'));
    }

    public function index_filters_by_search_term(): void
    {
        $this->actingAsUser();

        Trabajo::factory()->create(['descripcion_trabajo' => 'Reparación tubería principal']);
        Trabajo::factory()->create(['descripcion_trabajo' => 'Instalación eléctrica']);

        $response = $this->get(route('trabajos.index', ['search' => 'tubería']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Trabajos/Index')
                ->where('filtros.search', 'tubería')
        );
    }

    public function index_filters_by_estado(): void
    {
        $this->actingAsUser();

        Trabajo::factory()->create(['estado' => 'abierto']);
        Trabajo::factory()->create(['estado' => 'cerrado']);

        $response = $this->get(route('trabajos.index', ['estado' => 'abierto']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Trabajos/Index')
                ->where('filtros.estado', 'abierto')
        );
    }
    public function index_paginates_results(): void
    {
        $this->actingAsUser();
        Trabajo::factory()->count(25)->create();

        $response = $this->get(route('trabajos.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Trabajos/Index')
                ->has('trabajos.data', 20) // Paginación de 20 elementos
        );
    }

    public function create_renders_form_for_authenticated_user(): void
    {
        $this->actingAsUser();

        $response = $this->get(route('trabajos.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Trabajos/Form')
                ->where('trabajo', null)
                ->has('contextoIds')
        );
    }

    /** @test */
    public function create_redirects_unauthenticated_users(): void
    {
        $response = $this->get(route('trabajos.create'));

        $response->assertRedirect(route('login'));
    }

    public function store_creates_trabajo_and_redirects(): void
    {
        $user = $this->actingAsUser();

        $payload = Trabajo::factory()->make()->toArray();

        $response = $this->post(route('trabajos.store'), $payload);

        $response->assertRedirect(route('trabajos.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('trabajos', [
            'numero_trabajo' => $payload['numero_trabajo'],
        ]);
    }

    public function store_assigns_user_context_if_not_provided(): void
    {
        $user = $this->actingAsUser();

        $payload = Trabajo::factory()->make(['id_contexto' => null])->toArray();
        unset($payload['id_contexto']);

        $this->post(route('trabajos.store'), $payload);

        $this->assertDatabaseHas('trabajos', [
            'id_contexto' => $user->id_contexto,
        ]);
    }
    public function store_fails_validation_with_missing_required_fields(): void
    {
        $this->actingAsUser();

        $response = $this->post(route('trabajos.store'), []);

        $response->assertSessionHasErrors(); // Los campos requeridos del StoreTrabajoRequest
    }

    public function store_redirects_unauthenticated_users(): void
    {
        $payload = Trabajo::factory()->make()->toArray();

        $response = $this->post(route('trabajos.store'), $payload);

        $response->assertRedirect(route('login'));
    }

    public function edit_renders_form_with_trabajo_data(): void
    {
        $this->actingAsUser();
        $trabajo = Trabajo::factory()->create();

        $response = $this->get(route('trabajos.edit', $trabajo));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Trabajos/Form')
                ->has('trabajo')
                ->has('contextoIds')
        );
    }

    public function edit_returns_404_for_nonexistent_trabajo(): void
    {
        $this->actingAsUser();

        $response = $this->get(route('trabajos.edit', ['trabajo' => 99999]));

        $response->assertStatus(404);
    }

    public function edit_redirects_unauthenticated_users(): void
    {
        $trabajo = Trabajo::factory()->create();

        $response = $this->get(route('trabajos.edit', $trabajo));

        $response->assertRedirect(route('login'));
    }

    public function update_modifies_trabajo_and_redirects(): void
    {
        $this->actingAsUser();
        $trabajo = Trabajo::factory()->create(['cerrado' => false]);

        $payload = array_merge($trabajo->toArray(), ['descripcion_trabajo' => 'Descripción actualizada']);

        $response = $this->put(route('trabajos.update', $trabajo), $payload);

        $response->assertRedirect(route('trabajos.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('trabajos', [
            'id'                  => $trabajo->id,
            'descripcion_trabajo' => 'Descripción actualizada',
        ]);
    }

    public function update_is_blocked_for_non_admin_on_closed_trabajo(): void
    {
        $this->actingAsUser(); // usuario sin rol admin
        $trabajo = Trabajo::factory()->create(['cerrado' => true]);

        $response = $this->put(route('trabajos.update', $trabajo), $trabajo->toArray());

        $response->assertForbidden();
    }

    public function update_allows_admin_to_modify_closed_trabajo(): void
    {
        $this->actingAsAdmin();
        $trabajo = Trabajo::factory()->create(['cerrado' => true]);

        $payload = array_merge($trabajo->toArray(), ['descripcion_trabajo' => 'Admin actualiza obra cerrada']);

        $response = $this->put(route('trabajos.update', $trabajo), $payload);

        $response->assertRedirect(route('trabajos.index'));
        $response->assertSessionHas('success');
    }

    public function update_fails_validation_with_invalid_data(): void
    {
        $this->actingAsUser();
        $trabajo = Trabajo::factory()->create(['cerrado' => false]);

        $response = $this->put(route('trabajos.update', $trabajo), [
            'numero_trabajo' => '', // campo requerido vacío
        ]);

        $response->assertSessionHasErrors();
    }

    public function update_redirects_unauthenticated_users(): void
    {
        $trabajo = Trabajo::factory()->create();

        $response = $this->put(route('trabajos.update', $trabajo), []);

        $response->assertRedirect(route('login'));
    }

    public function destroy_deletes_open_trabajo_and_redirects(): void
    {
        $this->actingAsUser();
        $trabajo = Trabajo::factory()->create(['cerrado' => false]);

        $response = $this->delete(route('trabajos.destroy', $trabajo));

        $response->assertRedirect(route('trabajos.index'));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('trabajos', ['id' => $trabajo->id]);
        // Si el modelo usa HardDelete: $this->assertDatabaseMissing('trabajos', ['id' => $trabajo->id]);
    }

    public function destroy_is_blocked_for_non_admin_on_closed_trabajo(): void
    {
        $this->actingAsUser();
        $trabajo = Trabajo::factory()->create(['cerrado' => true]);

        $response = $this->delete(route('trabajos.destroy', $trabajo));

        $response->assertForbidden();
        $this->assertDatabaseHas('trabajos', ['id' => $trabajo->id]);
    }

    public function destroy_allows_admin_to_delete_closed_trabajo(): void
    {
        $this->actingAsAdmin();
        $trabajo = Trabajo::factory()->create(['cerrado' => true]);

        $response = $this->delete(route('trabajos.destroy', $trabajo));

        $response->assertRedirect(route('trabajos.index'));
        $response->assertSessionHas('success');
    }

    public function destroy_returns_404_for_nonexistent_trabajo(): void
    {
        $this->actingAsUser();

        $response = $this->delete(route('trabajos.destroy', ['trabajo' => 99999]));

        $response->assertStatus(404);
    }

    public function destroy_redirects_unauthenticated_users(): void
    {
        $trabajo = Trabajo::factory()->create();

        $response = $this->delete(route('trabajos.destroy', $trabajo));

        $response->assertRedirect(route('login'));
    }
}
