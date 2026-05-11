<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Vite;
use Illuminate\Support\HtmlString;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Acceso al panel de Auditoría (registro de actividad) por rol.
 *
 * Reglas de negocio:
 *   - admin      → puede ver, exportar y limpiar
 *   - director   → puede ver, exportar y limpiar (role:admin,director en ruta limpiar)
 *   - execution  → prohibido (ver y limpiar)
 *   - accounting → prohibido
 *   - invitado   → redirigido al login
 */
class AuditLogTest extends TestCase
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

    public function test_guest_redirected_to_login(): void
    {
        $this->get('/registro-actividad')->assertRedirect('/login');
    }

    public function test_admin_can_view_audit_log(): void
    {
        $admin = User::where('email', 'admin@ciete.es')->firstOrFail();

        $this->actingAs($admin)->get('/registro-actividad')->assertOk();
    }

    public function test_director_can_view_audit_log(): void
    {
        $director = User::where('email', 'cesar@ciete.es')->firstOrFail();

        $this->actingAs($director)->get('/registro-actividad')->assertOk();
    }

    public function test_execution_user_cannot_view_audit_log(): void
    {
        $execution = User::where('email', 'usuario@ciete.es')->firstOrFail();

        $this->actingAs($execution)->get('/registro-actividad')->assertForbidden();
    }

    public function test_admin_can_delete_audit_log(): void
    {
        $admin = User::where('email', 'admin@ciete.es')->firstOrFail();

        // DELETE requires the form fields; without them it returns 302 (validation redirect).
        // We verify it is NOT forbidden (403/401).
        $response = $this->actingAs($admin)->delete('/registro-actividad/limpiar');

        $this->assertNotEquals(403, $response->getStatusCode());
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    public function test_director_can_also_delete_audit_log(): void
    {
        // Route middleware is role:admin,director — both roles may limpiar.
        $director = User::where('email', 'cesar@ciete.es')->firstOrFail();

        $response = $this->actingAs($director)->delete('/registro-actividad/limpiar');

        $this->assertNotEquals(403, $response->getStatusCode());
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    public function test_execution_user_cannot_delete_audit_log(): void
    {
        $execution = User::where('email', 'usuario@ciete.es')->firstOrFail();

        $this->actingAs($execution)->delete('/registro-actividad/limpiar')->assertForbidden();
    }

    public function test_interface_mode_changes_do_not_create_new_audit_entries(): void
    {
        $admin = User::where('email', 'admin@ciete.es')->firstOrFail();
        $initialCount = AuditLog::query()->count();

        $this->actingAs($admin)
            ->patch(route('profile.preferences'), [
                'interface_mode' => 'ciete_excel',
            ])
            ->assertRedirect();

        $this->assertSame($initialCount, AuditLog::query()->count());
        $this->assertDatabaseMissing('audit_log', [
            'modulo' => 'perfil',
            'campo' => 'interface_mode',
            'registro_id' => $admin->id_usuario,
        ]);
    }

    public function test_operational_audit_filter_hides_legacy_visual_preference_logs_by_default(): void
    {
        $director = User::where('email', 'cesar@ciete.es')->firstOrFail();

        AuditLog::query()->create([
            'id_usuario' => $director->id_usuario,
            'id_contexto' => $director->id_contexto,
            'accion' => 'actualizar',
            'tabla' => 'usuarios',
            'modulo' => 'perfil',
            'registro_id' => $director->id_usuario,
            'entity_id' => $director->id_usuario,
            'campo' => 'interface_mode',
            'valor_anterior' => 'ciete_moderno',
            'valor_nuevo' => 'ciete_excel',
            'descripcion' => 'Cambio legacy de modo visual.',
            'created_at' => now(),
        ]);

        $this->actingAs($director)
            ->get('/registro-actividad?modulo=perfil')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AuditLog/Index')
                ->where('filtros.solo_datos', true)
                ->where('filtros.modulo', 'perfil')
                ->where('logs.data', []));
    }

    public function test_cleanup_creates_a_trace_record_that_is_not_deleted_in_same_operation(): void
    {
        $director = User::where('email', 'cesar@ciete.es')->firstOrFail();

        AuditLog::query()->create([
            'id_usuario' => $director->id_usuario,
            'id_contexto' => $director->id_contexto,
            'accion' => 'actualizar',
            'tabla' => 'trabajos',
            'modulo' => 'trabajos',
            'registro_id' => 999,
            'entity_id' => 999,
            'descripcion' => 'Log antiguo para limpieza.',
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($director)
            ->delete('/registro-actividad/limpiar', [
                'fecha_hasta' => now()->toDateString(),
                'confirmar' => '1',
                'con_exportacion' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('audit_log', [
            'descripcion' => 'Log antiguo para limpieza.',
        ]);
        $this->assertDatabaseHas('audit_log', [
            'accion' => 'limpiar_logs',
            'tabla' => 'audit_log',
        ]);
    }
}
