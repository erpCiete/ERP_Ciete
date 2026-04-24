<?php

namespace App\Services;

use App\Models\ComentarioSoporte;
use App\Models\SolicitudSoporte;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SupportTicketService
{
    public function __construct(
        private readonly InternalMessageService $messageService,
    ) {
    }

    public function createTicket(User $requester, array $payload): SolicitudSoporte
    {
        return DB::transaction(function () use ($requester, $payload) {
            $ticket = SolicitudSoporte::create([
                'id_usuario_solicitante' => $requester->id_usuario,
                'tema' => $payload['topic'],
                'asunto' => $payload['subject'],
                'prioridad' => $payload['priority'] ?? 'normal',
                'estado' => 'pending',
                'ultimo_mensaje_at' => now(),
            ]);

            $comment = $ticket->comentarios()->create([
                'id_usuario' => $requester->id_usuario,
                'tipo_autor' => 'user',
                'mensaje' => $payload['message'],
            ]);

            $ticket->load('solicitante');
            $this->messageService->notifySupportManagers($ticket, $requester, $comment->mensaje);

            return $ticket->fresh(['solicitante', 'asignado', 'ultimoComentario.autor']);
        });
    }

    public function addRequesterReply(SolicitudSoporte $ticket, User $requester, string $message): ComentarioSoporte
    {
        return DB::transaction(function () use ($ticket, $requester, $message) {
            $comment = $ticket->comentarios()->create([
                'id_usuario' => $requester->id_usuario,
                'tipo_autor' => 'user',
                'mensaje' => $message,
            ]);

            $ticket->update([
                'estado' => $ticket->estado === 'archived' ? $ticket->estado : 'pending',
                'ultimo_mensaje_at' => $comment->created_at,
                'resuelta_at' => null,
            ]);

            $ticket->refresh()->load('solicitante');
            $this->messageService->notifySupportManagers($ticket, $requester, $message);

            return $comment;
        });
    }

    public function addManagerReply(SolicitudSoporte $ticket, User $manager, string $message, ?string $status = null): ComentarioSoporte
    {
        return DB::transaction(function () use ($ticket, $manager, $message, $status) {
            $comment = $ticket->comentarios()->create([
                'id_usuario' => $manager->id_usuario,
                'tipo_autor' => 'support',
                'mensaje' => $message,
            ]);

            $nextStatus = $status ?? ($ticket->estado === 'pending' ? 'in_review' : $ticket->estado);

            $ticket->update($this->buildStatusPayload($ticket, $manager, $nextStatus) + [
                'ultimo_mensaje_at' => $comment->created_at,
            ]);

            $ticket->refresh()->load('solicitante');
            $this->messageService->notifyRequesterAboutSupportReply($ticket, $manager, $message);

            return $comment;
        });
    }

    public function updateStatus(SolicitudSoporte $ticket, User $manager, string $status): SolicitudSoporte
    {
        $ticket->update($this->buildStatusPayload($ticket, $manager, $status));

        return $ticket->fresh(['solicitante', 'asignado', 'ultimoComentario.autor']);
    }

    private function buildStatusPayload(SolicitudSoporte $ticket, User $manager, string $status): array
    {
        return [
            'estado' => $status,
            'id_usuario_asignado' => $ticket->id_usuario_asignado ?? $manager->id_usuario,
            'resuelta_at' => $status === 'resolved' ? now() : null,
            'archivada_at' => $status === 'archived' ? now() : null,
        ];
    }
}
