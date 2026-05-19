<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Support\StoreSupportCommentRequest;
use App\Http\Requests\Support\UpdateSupportTicketStatusRequest;
use App\Models\SolicitudSoporte;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class SupportTicketController extends Controller
{
    public function __construct(
        private readonly SupportTicketService $supportTicketService,
    ) {
    }

    public function index(Request $request): Response
    {
        $this->authorizeManager($request->user());

        if (! $this->supportModuleReady()) {
            return Inertia::render('Admin/Support/Index', [
                'tickets' => ['data' => []],
                'filters' => $request->only(['search', 'status', 'priority']),
                'supportReady' => false,
                'supportSchemaWarning' => 'Faltan migraciones del módulo de soporte. Ejecuta `php artisan migrate` antes de gestionar solicitudes.',
            ]);
        }

        $query = SolicitudSoporte::query()
            ->with([
                'solicitante:id_usuario,nombre,apellidos,email',
                'asignado:id_usuario,nombre,apellidos,email',
                'ultimoComentario.autor:id_usuario,nombre,apellidos',
            ])
            ->withCount('comentarios');

        if ($request->filled('search')) {
            $term = '%' . trim((string) $request->input('search')) . '%';
            $query->where(function ($builder) use ($term) {
                $builder->where('asunto', 'like', $term)
                    ->orWhere('tema', 'like', $term)
                    ->orWhereHas('solicitante', function ($userQuery) use ($term) {
                        $userQuery->where('nombre', 'like', $term)
                            ->orWhere('apellidos', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('estado', $request->string('status')->toString());
        }

        if ($request->filled('priority')) {
            $query->where('prioridad', $request->string('priority')->toString());
        }

        $tickets = $query
            ->orderByRaw("
                CASE estado
                    WHEN 'pending' THEN 0
                    WHEN 'in_review' THEN 1
                    WHEN 'resolved' THEN 2
                    WHEN 'archived' THEN 3
                    ELSE 4
                END
            ")
            ->orderByDesc('ultimo_mensaje_at')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (SolicitudSoporte $ticket) => $this->serializeTicketListItem($ticket));

        return Inertia::render('Admin/Support/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'status', 'priority']),
            'supportReady' => true,
            'supportSchemaWarning' => null,
        ]);
    }

    public function show(Request $request, SolicitudSoporte $solicitudSoporte): Response
    {
        $this->authorizeManager($request->user());
        abort_unless($this->supportModuleReady(), 409, 'El módulo de soporte aún no está disponible.');

        $solicitudSoporte->load([
            'solicitante:id_usuario,nombre,apellidos,email',
            'asignado:id_usuario,nombre,apellidos,email',
            'comentarios.autor:id_usuario,nombre,apellidos,email',
        ]);

        return Inertia::render('Admin/Support/Show', [
            'ticket' => $this->serializeTicketDetail($solicitudSoporte),
        ]);
    }

    public function updateStatus(UpdateSupportTicketStatusRequest $request, SolicitudSoporte $solicitudSoporte): RedirectResponse
    {
        $this->authorizeManager($request->user());

        if (! $this->supportModuleReady()) {
            return redirect()
                ->route('admin.support.index')
                ->with('error', 'Faltan migraciones del módulo de soporte.');
        }

        $this->supportTicketService->updateStatus(
            $solicitudSoporte,
            $request->user(),
            $request->validated('status'),
        );

        return back()->with('success', 'Estado de soporte actualizado correctamente.');
    }

    public function reply(StoreSupportCommentRequest $request, SolicitudSoporte $solicitudSoporte): RedirectResponse
    {
        $this->authorizeManager($request->user());

        if (! $this->supportModuleReady()) {
            return redirect()
                ->route('admin.support.index')
                ->with('error', 'Faltan migraciones del módulo de soporte.');
        }

        if ($solicitudSoporte->estado === 'archived') {
            return back()->withErrors(['message' => 'No se puede responder a una solicitud archivada.']);
        }

        $this->supportTicketService->addManagerReply(
            $solicitudSoporte,
            $request->user(),
            $request->validated('message'),
            $request->validated('status'),
        );

        return back()->with('success', 'Respuesta de soporte enviada correctamente.');
    }

    private function authorizeManager(?User $user): void
    {
        abort_unless($user?->canManageSupport(), 403, 'No tienes permisos para gestionar soporte.');
    }

    private function serializeTicketListItem(SolicitudSoporte $ticket): array
    {
        return [
            'id_solicitud_soporte' => $ticket->id_solicitud_soporte,
            'tema' => $ticket->tema,
            'asunto' => $ticket->asunto,
            'estado' => $ticket->estado,
            'prioridad' => $ticket->prioridad,
            'created_at' => $ticket->created_at?->toIso8601String(),
            'ultimo_mensaje_at' => $ticket->ultimo_mensaje_at?->toIso8601String(),
            'comentarios_count' => $ticket->comentarios_count,
            'solicitante' => $this->serializeUser($ticket->solicitante),
            'asignado' => $this->serializeUser($ticket->asignado),
            'ultimo_comentario' => $ticket->ultimoComentario ? [
                'mensaje' => Str::limit($ticket->ultimoComentario->mensaje, 120),
                'created_at' => $ticket->ultimoComentario->created_at?->toIso8601String(),
                'autor' => $this->serializeUser($ticket->ultimoComentario->autor),
            ] : null,
        ];
    }

    private function serializeTicketDetail(SolicitudSoporte $ticket): array
    {
        return [
            'id_solicitud_soporte' => $ticket->id_solicitud_soporte,
            'tema' => $ticket->tema,
            'asunto' => $ticket->asunto,
            'estado' => $ticket->estado,
            'prioridad' => $ticket->prioridad,
            'created_at' => $ticket->created_at?->toIso8601String(),
            'updated_at' => $ticket->updated_at?->toIso8601String(),
            'ultimo_mensaje_at' => $ticket->ultimo_mensaje_at?->toIso8601String(),
            'solicitante' => $this->serializeUser($ticket->solicitante),
            'asignado' => $this->serializeUser($ticket->asignado),
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
