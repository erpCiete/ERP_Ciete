<?php

namespace App\Services;

use App\Models\MensajeInterno;
use App\Models\SolicitudSoporte;
use App\Models\User;
use Illuminate\Support\Collection;

class InternalMessageService
{
    public function send(User $sender, User $recipient, array $payload, ?string $senderType = null): MensajeInterno
    {
        return MensajeInterno::create([
            'id_remitente' => $sender->id_usuario,
            'id_destinatario' => $recipient->id_usuario,
            'id_mensaje_padre' => $payload['id_mensaje_padre'] ?? null,
            'id_solicitud_soporte' => $payload['id_solicitud_soporte'] ?? null,
            'tipo_remitente' => $senderType ?? $this->resolveSenderType($sender),
            'asunto' => $payload['asunto'],
            'cuerpo' => $payload['cuerpo'],
            'prioridad' => $payload['prioridad'] ?? 'normal',
            'es_aviso_sistema' => (bool) ($payload['es_aviso_sistema'] ?? false),
        ]);
    }

    public function broadcast(User $sender, array $payload): void
    {
        $recipientIds = User::query()
            ->where('activo', true)
            ->where('id_usuario', '!=', $sender->id_usuario)
            ->pluck('id_usuario');

        if ($recipientIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $recipientIds->map(fn (int $id) => [
            'id_remitente' => $sender->id_usuario,
            'id_destinatario' => $id,
            'tipo_remitente' => 'system',
            'asunto' => $payload['asunto'],
            'cuerpo' => $payload['cuerpo'],
            'prioridad' => $payload['prioridad'] ?? 'normal',
            'es_aviso_sistema' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        MensajeInterno::insert($rows);
    }

    public function notifySupportManagers(SolicitudSoporte $ticket, User $actor, string $message): void
    {
        $managerIds = $this->supportManagerIds()
            ->reject(fn (int $id) => $id === $actor->id_usuario)
            ->values();

        if ($managerIds->isEmpty()) {
            return;
        }

        $body = implode("\n\n", [
            "Tema: {$ticket->tema}",
            "Asunto: {$ticket->asunto}",
            "Prioridad: {$ticket->prioridad}",
            "Estado: {$ticket->estado}",
            "Solicitud #{$ticket->id_solicitud_soporte}",
            '---',
            $message,
        ]);

        $now = now();
        $rows = $managerIds->map(fn (int $id) => [
            'id_remitente' => $actor->id_usuario,
            'id_destinatario' => $id,
            'id_solicitud_soporte' => $ticket->id_solicitud_soporte,
            'tipo_remitente' => $this->resolveSenderType($actor),
            'asunto' => "[Soporte] {$ticket->asunto}",
            'cuerpo' => $body,
            'prioridad' => $this->normalizePriorityForMessage($ticket->prioridad),
            'es_aviso_sistema' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        MensajeInterno::insert($rows);
    }

    public function notifyRequesterAboutSupportReply(SolicitudSoporte $ticket, User $manager, string $message): MensajeInterno
    {
        return $this->send($manager, $ticket->solicitante, [
            'id_solicitud_soporte' => $ticket->id_solicitud_soporte,
            'asunto' => "[Soporte] {$ticket->asunto}",
            'cuerpo' => $message,
            'prioridad' => $this->normalizePriorityForMessage($ticket->prioridad),
        ], 'support');
    }

    public function resolveSenderType(User $sender): string
    {
        return $sender->isAdmin() ? 'admin' : 'user';
    }

    private function normalizePriorityForMessage(string $priority): string
    {
        return match ($priority) {
            'baja' => 'normal',
            'alta' => 'alta',
            'urgente' => 'urgente',
            default => 'normal',
        };
    }

    private function supportManagerIds(): Collection
    {
        return User::query()
            ->where('activo', true)
            ->where(function ($query) {
                $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', 'admin'))
                    ->orWhereHas('roles.permissions', fn ($permissionQuery) => $permissionQuery->where('slug', 'soporte.gestionar'));
            })
            ->pluck('id_usuario')
            ->unique()
            ->values();
    }
}
