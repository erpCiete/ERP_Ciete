<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SolicitudSoporte;
use App\Models\User;
use Database\Seeders\ContextosClienteSeeder;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RolPermisosSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            ContextosClienteSeeder::class,
            RolesSeeder::class,
            PermisosSeeder::class,
            RolPermisosSeeder::class,
        ]);
    }

    public function test_any_authenticated_user_can_create_a_support_ticket(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('support.send'), [
            'topic' => 'error',
            'subject' => 'No puedo guardar cambios',
            'message' => 'La pantalla falla al guardar.',
            'priority' => 'alta',
        ])->assertSessionHasNoErrors();

        $ticket = SolicitudSoporte::query()->latest('id_solicitud_soporte')->firstOrFail();

        $this->assertDatabaseHas('solicitudes_soporte', [
            'id_solicitud_soporte' => $ticket->id_solicitud_soporte,
            'id_usuario_solicitante' => $user->id_usuario,
            'estado' => 'pending',
            'prioridad' => 'alta',
        ]);
    }

    public function test_support_manager_can_create_a_support_ticket(): void
    {
        $user = User::factory()->create();
        $this->assignRole($user, 'admin');

        $this->actingAs($user)->post(route('support.send'), [
            'topic' => 'error',
            'subject' => 'No puedo guardar cambios',
            'message' => 'La pantalla falla al guardar.',
            'priority' => 'alta',
        ])->assertSessionHasNoErrors();

        $ticket = SolicitudSoporte::query()->latest('id_solicitud_soporte')->firstOrFail();

        $this->assertDatabaseHas('solicitudes_soporte', [
            'id_solicitud_soporte' => $ticket->id_solicitud_soporte,
            'id_usuario_solicitante' => $user->id_usuario,
            'estado' => 'pending',
            'prioridad' => 'alta',
        ]);
    }

    public function test_non_manager_receives_403_when_updating_ticket_status(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $ticket = $this->createSupportTicket($owner);

        $this->actingAs($intruder)
            ->patch(route('admin.support.status', $ticket->id_solicitud_soporte), [
                'status' => 'resolved',
            ])->assertForbidden();
    }

    public function test_manager_can_change_ticket_status(): void
    {
        $owner = User::factory()->create();
        $manager = User::factory()->create();
        $this->assignRole($manager, 'admin');
        $ticket = $this->createSupportTicket($owner);

        $this->actingAs($manager)
            ->patch(route('admin.support.status', $ticket->id_solicitud_soporte), [
                'status' => 'resolved',
            ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('solicitudes_soporte', [
            'id_solicitud_soporte' => $ticket->id_solicitud_soporte,
            'estado' => 'resolved',
            'id_usuario_asignado' => $manager->id_usuario,
        ]);
    }

    public function test_requester_reply_is_saved_on_ticket(): void
    {
        $owner = User::factory()->create();
        $ticket = $this->createSupportTicket($owner);

        $this->actingAs($owner)
            ->post(route('support.reply', $ticket->id_solicitud_soporte), [
                'message' => 'Añado más detalle de la incidencia.',
            ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('comentarios_soporte', [
            'id_solicitud_soporte' => $ticket->id_solicitud_soporte,
            'id_usuario' => $owner->id_usuario,
            'mensaje' => 'Añado más detalle de la incidencia.',
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
            'topic' => 'error',
            'subject' => 'Ticket de prueba',
            'message' => 'Detalle inicial.',
            'priority' => 'normal',
        ])->assertSessionHasNoErrors();

        return SolicitudSoporte::query()->latest('id_solicitud_soporte')->firstOrFail();
    }
}
