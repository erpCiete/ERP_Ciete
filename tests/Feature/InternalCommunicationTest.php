<?php

namespace Tests\Feature;

use App\Models\MensajeInterno;
use App\Models\Role;
use App\Models\SolicitudSoporte;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RolPermisosSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalCommunicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            RolesSeeder::class,
            PermisosSeeder::class,
            RolPermisosSeeder::class,
        ]);
    }

    public function test_user_cannot_view_message_that_does_not_belong_to_them(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $intruder = User::factory()->create();

        $message = MensajeInterno::create([
            'id_remitente' => $sender->id_usuario,
            'id_destinatario' => $recipient->id_usuario,
            'asunto' => 'Privado',
            'cuerpo' => 'Contenido restringido',
            'prioridad' => 'normal',
        ]);

        $response = $this->actingAs($intruder)->get(route('messages.show', $message));

        $response->assertForbidden();
    }

    public function test_recipient_can_mark_message_as_read_and_archive_it(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $message = MensajeInterno::create([
            'id_remitente' => $sender->id_usuario,
            'id_destinatario' => $recipient->id_usuario,
            'asunto' => 'Seguimiento',
            'cuerpo' => 'Mensaje de prueba',
            'prioridad' => 'alta',
        ]);

        $this->actingAs($recipient)
            ->post(route('messages.read', $message))
            ->assertSessionHasNoErrors();

        $this->assertNotNull($message->fresh()->leido_at);

        $this->actingAs($recipient)
            ->post(route('messages.archive', $message))
            ->assertSessionHasNoErrors();

        $this->assertTrue($message->fresh()->archivado);
    }

    public function test_support_request_is_persisted_and_notifies_admin_support(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();
        $this->assignRole($admin, 'admin');

        $response = $this->actingAs($user)->post(route('support.send'), [
            'topic' => 'error',
            'subject' => 'No puedo guardar una factura',
            'message' => 'La pantalla devuelve un error al guardar.',
            'priority' => 'alta',
        ]);

        $response->assertSessionHasNoErrors();

        $ticket = SolicitudSoporte::query()->first();

        $response->assertRedirect(route('support.show', ['solicitudSoporte' => $ticket->id_solicitud_soporte]));

        $this->assertDatabaseHas('solicitudes_soporte', [
            'id_solicitud_soporte' => $ticket->id_solicitud_soporte,
            'id_usuario_solicitante' => $user->id_usuario,
            'tema' => 'error',
            'asunto' => 'No puedo guardar una factura',
            'estado' => 'pending',
            'prioridad' => 'alta',
        ]);

        $this->assertDatabaseHas('comentarios_soporte', [
            'id_solicitud_soporte' => $ticket->id_solicitud_soporte,
            'id_usuario' => $user->id_usuario,
            'tipo_autor' => 'user',
        ]);

        $this->assertDatabaseHas('mensajes_internos', [
            'id_destinatario' => $admin->id_usuario,
            'id_solicitud_soporte' => $ticket->id_solicitud_soporte,
            'asunto' => '[Soporte] No puedo guardar una factura',
        ]);
    }

    public function test_support_root_redirects_admin_to_ticket_management(): void
    {
        $admin = User::factory()->create();
        $this->assignRole($admin, 'admin');

        $this->actingAs($admin)
            ->get(route('support'))
            ->assertRedirect(route('admin.support.index'));
    }

    public function test_only_owner_or_manager_can_view_support_ticket(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $admin = User::factory()->create();
        $this->assignRole($admin, 'admin');

        $ticket = $this->createSupportTicket($owner);

        $this->actingAs($other)
            ->get(route('support.show', ['solicitudSoporte' => $ticket->id_solicitud_soporte]))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.support.show', ['solicitudSoporte' => $ticket->id_solicitud_soporte]))
            ->assertOk();
    }

    public function test_manager_reply_updates_ticket_status_and_notifies_requester(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $this->assignRole($admin, 'admin');

        $ticket = $this->createSupportTicket($owner);

        $this->actingAs($admin)->post(route('admin.support.reply', ['solicitudSoporte' => $ticket->id_solicitud_soporte]), [
            'message' => 'Estamos revisando la incidencia.',
            'status' => 'in_review',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('comentarios_soporte', [
            'id_solicitud_soporte' => $ticket->id_solicitud_soporte,
            'id_usuario' => $admin->id_usuario,
            'tipo_autor' => 'support',
            'mensaje' => 'Estamos revisando la incidencia.',
        ]);

        $this->assertDatabaseHas('solicitudes_soporte', [
            'id_solicitud_soporte' => $ticket->id_solicitud_soporte,
            'estado' => 'in_review',
            'id_usuario_asignado' => $admin->id_usuario,
        ]);

        $this->assertDatabaseHas('mensajes_internos', [
            'id_destinatario' => $owner->id_usuario,
            'id_solicitud_soporte' => $ticket->id_solicitud_soporte,
            'tipo_remitente' => 'support',
            'asunto' => '[Soporte] Error de acceso',
        ]);
    }

    private function assignRole(User $user, string $roleSlug): void
    {
        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $user->roles()->syncWithoutDetaching([$role->id_rol]);
    }

    private function createSupportTicket(User $owner): SolicitudSoporte
    {
        $this->actingAs($owner)->post(route('support.send'), [
            'topic' => 'access',
            'subject' => 'Error de acceso',
            'message' => 'No puedo entrar al módulo.',
            'priority' => 'normal',
        ])->assertSessionHasNoErrors();

        return SolicitudSoporte::query()->latest('id_solicitud_soporte')->firstOrFail();
    }
}
