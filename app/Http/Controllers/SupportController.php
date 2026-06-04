<?php

namespace App\Http\Controllers;

use App\Http\Requests\Support\StoreSupportCommentRequest;
use App\Http\Requests\Support\StoreSupportTicketRequest;
use App\Models\SolicitudSoporte;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class SupportController extends Controller
{
    public function __construct(
        private readonly SupportTicketService $supportTicketService,
    ) {
    }

    public function index(Request $request): Response|RedirectResponse
    {
        if ($request->user()?->canManageSupport()) {
            return redirect()->route('admin.support.index');
        }

        $supportReady = $this->supportModuleReady();

        return Inertia::render('Support', [
            'supportReady' => $supportReady,
            'supportSchemaWarning' => $supportReady ? null : 'El módulo de soporte aún no está listo en este entorno.',
            'tickets' => $supportReady
                ? SolicitudSoporte::query()
                    ->visibleFor($request->user())
                    ->with(['asignado:id_usuario,nombre,apellidos'])
                    ->latest('ultimo_mensaje_at')
                    ->latest('created_at')
                    ->limit(10)
                    ->get()
                    ->map(fn (SolicitudSoporte $ticket) => [
                        'id_solicitud_soporte' => $ticket->id_solicitud_soporte,
                        'tema' => $ticket->tema,
                        'asunto' => $ticket->asunto,
                        'estado' => $ticket->estado,
                        'prioridad' => $ticket->prioridad,
                        'created_at' => $ticket->created_at?->toIso8601String(),
                        'ultimo_mensaje_at' => $ticket->ultimo_mensaje_at?->toIso8601String(),
                        'asignado' => $this->serializeUser($ticket->asignado),
                    ])
                    ->values()
                    ->all()
                : [],
        ]);
    }

    public function send(StoreSupportTicketRequest $request): RedirectResponse
    {
        abort_unless($this->supportModuleReady(), 409, 'El módulo de soporte aún no está disponible.');

        $ticket = $this->supportTicketService->createTicket($request->user(), $request->validated());

        return redirect()
            ->route('support.show', ['solicitudSoporte' => $ticket->id_solicitud_soporte])
            ->with('status', 'support-sent');
    }

    public function show(Request $request, SolicitudSoporte $solicitudSoporte): Response
    {
        abort_unless($this->supportModuleReady(), 409, 'El módulo de soporte aún no está disponible.');
        abort_unless($solicitudSoporte->puedeSerVistaPor($request->user()), 403, 'No tienes acceso a esta solicitud.');

        $solicitudSoporte->load([
            'solicitante:id_usuario,nombre,apellidos,email',
            'asignado:id_usuario,nombre,apellidos,email',
            'comentarios.autor:id_usuario,nombre,apellidos,email',
        ]);

        return Inertia::render('Support/Show', [
            'ticket' => $this->serializeTicket($solicitudSoporte, $request->user()),
        ]);
    }

    public function reply(StoreSupportCommentRequest $request, SolicitudSoporte $solicitudSoporte): RedirectResponse
    {
        abort_unless($this->supportModuleReady(), 409, 'El módulo de soporte aún no está disponible.');
        abort_unless($solicitudSoporte->id_usuario_solicitante === $request->user()->id_usuario, 403, 'No tienes acceso a esta solicitud.');

        if ($solicitudSoporte->estado === 'archived') {
            return back()->withErrors(['message' => 'No se puede responder a una solicitud archivada.']);
        }

        $this->supportTicketService->addRequesterReply(
            $solicitudSoporte,
            $request->user(),
            $request->validated('message'),
        );

        return back()->with('success', 'Tu respuesta se ha añadido correctamente.');
    }

    private function serializeTicket(SolicitudSoporte $ticket, User $user): array
    {
        return [
            'id_solicitud_soporte' => $ticket->id_solicitud_soporte,
            'tema' => $ticket->tema,
            'asunto' => $ticket->asunto,
            'estado' => $ticket->estado,
            'prioridad' => $ticket->prioridad,
            'created_at' => $ticket->created_at?->toIso8601String(),
            'updated_at' => $ticket->updated_at?->toIso8601String(),
            'solicitante' => $this->serializeUser($ticket->solicitante),
            'asignado' => $this->serializeUser($ticket->asignado),
            'can_reply' => $ticket->estado !== 'archived' && $ticket->id_usuario_solicitante === $user->id_usuario,
            'comentarios' => $ticket->comentarios->map(fn ($comment) => [
                'id_comentario_soporte' => $comment->id_comentario_soporte,
                'mensaje' => $comment->mensaje,
                'tipo_autor' => $comment->tipo_autor,
                'created_at' => $comment->created_at?->toIso8601String(),
                'autor' => $this->serializeUser($comment->autor),
            ])->values()->all(),
        ];
    }

    private function serializeUser(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id_usuario' => $user->id_usuario,
            'nombre' => $user->nombre,
            'apellidos' => $user->apellidos,
            'email' => $user->email,
        ];
    }

    private function supportModuleReady(): bool
    {
        return Schema::hasTable('solicitudes_soporte')
            && Schema::hasTable('comentarios_soporte')
            && Schema::hasColumns('mensajes_internos', ['tipo_remitente', 'id_mensaje_padre', 'id_solicitud_soporte']);
    }
}
