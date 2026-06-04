<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StoreTrabajoRequest;
use App\Http\Requests\Api\UpdateTrabajoRequest;
use App\Http\Resources\Api\TrabajoResource;
use App\Models\ContextoCliente;
use App\Models\AuditLog;
use App\Models\Contrato;
use App\Models\EstacionServicio;
use App\Models\Pedido;
use App\Models\Tarifario;
use App\Models\TipoDocumento;
use App\Models\TipoTrabajo;
use App\Models\Trabajo;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\TrabajoStateService;
use App\Support\ContextGuard;
use App\Support\TrabajoPermission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class TrabajoController extends Controller
{
    private const AUDIT_FIELDS = [
        'id_trabajo',
        'id_contexto',
        'id_estacion_servicio',
        'id_tipo_trabajo',
        'id_tipo_documento',
        'id_contrato',
        'id_tarifario',
        'numero_trabajo',
        'numero_trabajo_operativo',
        'numero_aviso',
        'descripcion_trabajo',
        'fecha_encargo',
        'fecha_inicio',
        'fecha_fin',
        'fecha_terminacion',
        'observaciones',
        'id_responsable_ciete',
        'estado',
    ];

    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TrabajoStateService $trabajoStateService,
    )
    {
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));

        // HasContext filtra automáticamente por el contexto activo del usuario (MOEVE/REPSOL/OTROS).
        $query = Trabajo::query()
            ->with([
                'empresa',
                'estacion',
                'contrato',
                'tarifario',
                'tipoDocumento',
                'tipoTrabajo',
                'responsableCiete',
                'primerPedido',
                'pedidos' => fn ($pedidos) => $pedidos
                    ->select([
                        'id_pedido',
                        'id_trabajo',
                        'id_contexto',
                        'numero_pedido',
                        'fecha_solicitud',
                        'estado',
                        'importe_pedido',
                        'importe_facturado',
                        'pedido_completo',
                        'facturado_completo',
                    ])
                    ->orderBy('id_pedido'),
            ])
            ->withSum('pedidos as importe_pedido_total', 'importe_pedido')
            ->withSum('pedidos as importe_solicitado_total', 'importe_solicitado')
            ->withSum('pedidos as importe_facturado_total', 'importe_facturado')
            ->withCount('pedidos');

        if ($search !== '') {
            $this->applyOperationalSearch($query, $search);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        $query->when($request->filled('fecha_desde'), fn ($q) => $q->where('fecha_encargo', '>=', $request->input('fecha_desde')));
        $query->when($request->filled('fecha_hasta'), fn ($q) => $q->where('fecha_encargo', '<=', $request->input('fecha_hasta')));
        $query->when($request->filled('id_responsable_ciete'), fn ($q) => $q->where('id_responsable_ciete', $request->input('id_responsable_ciete')));
        $query->when($request->filled('id_estacion_servicio'), fn ($q) => $q->where('id_estacion_servicio', $request->input('id_estacion_servicio')));
        $query->when($request->filled('id_tarifario'), fn ($q) => $q->where('id_tarifario', $request->input('id_tarifario')));
        $query->when($request->filled('id_contrato'), fn ($q) => $q->where('id_contrato', $request->input('id_contrato')));
        $query->when($request->filled('pedido_numero'), function (Builder $q) use ($request): void {
            $q->whereHas('pedidos', fn (Builder $pedidos) => $pedidos->where('numero_pedido', 'like', '%' . $request->input('pedido_numero') . '%'));
        });
        $query->when($request->filled('categoria'), function (Builder $q) use ($request): void {
            $term = '%' . $request->input('categoria') . '%';
            $q->where(function (Builder $nested) use ($term): void {
                $nested->where('categoria', 'like', $term)
                    ->orWhereHas('tipoTrabajo', fn (Builder $tipos) => $tipos
                        ->where('nombre', 'like', $term)
                        ->orWhere('codigo', 'like', $term))
                    ->orWhereHas('tipoDocumento', fn (Builder $tipos) => $tipos
                        ->where('nombre', 'like', $term)
                        ->orWhere('codigo', 'like', $term));
            });
        });

        // Filtros por municipio/provincia de la estación (texto parcial)
        $query->when($request->filled('municipio'), function ($q) use ($request) {
            $q->whereHas('estacion', fn ($es) => $es->where('poblacion', 'like', '%' . $request->input('municipio') . '%'));
        });
        $query->when($request->filled('provincia'), function ($q) use ($request) {
            $q->whereHas('estacion', fn ($es) => $es->where('provincia', 'like', '%' . $request->input('provincia') . '%'));
        });
        // Filtro por código de estación (texto parcial)
        $query->when($request->filled('codigo_estacion'), function ($q) use ($request) {
            $q->whereHas('estacion', fn ($es) => $es->where('codigo_estacion', 'like', '%' . $request->input('codigo_estacion') . '%'));
        });
        $query->when($request->filled('has_pedidos'), function (Builder $q) use ($request): void {
            $request->input('has_pedidos') === '1'
                ? $q->has('pedidos')
                : $q->doesntHave('pedidos');
        });
        $query->when($request->filled('multi_pedido'), function (Builder $q) use ($request): void {
            $request->input('multi_pedido') === '1'
                ? $q->has('pedidos', '>=', 2)
                : $q->has('pedidos', '<', 2);
        });
        $query->when($request->filled('pedido_importe'), function (Builder $q) use ($request): void {
            $this->applyPedidoAmountFilter($q, 'importe_pedido', $request->input('pedido_importe'));
        });
        $query->when($request->filled('facturado'), function (Builder $q) use ($request): void {
            $this->applyPedidoAmountFilter($q, 'importe_facturado', $request->input('facturado'));
        });
        $query->when($request->filled('solicitado'), function (Builder $q) use ($request): void {
            $this->applyPedidoAmountFilter($q, 'importe_solicitado', $request->input('solicitado'));
        });

        $sort = $this->normalizeTrabajoSort((string) $request->input('sort', ''));
        $direction = $this->normalizeSortDirection((string) $request->input('direction', ''));

        $this->applyTrabajoIndexOrdering($query, $sort, $direction);

        $trabajos = $query->paginate(10)->withQueryString();

        $responsables = User::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id_usuario', 'nombre', 'apellidos'])
            ->map(fn (User $u): array => [
                'id'     => $u->id_usuario,
                'nombre' => trim($u->nombre . ' ' . ($u->apellidos ?? '')),
            ])
            ->values()
            ->all();

        $canCreate = TrabajoPermission::canCreate($request->user());
        $canCreateInContext = $canCreate
            && ContextGuard::canCreateInActiveContext($request->user());
        $canUseExcelCatalogs = $canCreateInContext
            || ($request->user()?->hasPermission('trabajos.editar') ?? false);

        $filters = $request->only([
            'search',
            'estado',
            'fecha_desde',
            'fecha_hasta',
            'id_responsable_ciete',
            'id_estacion_servicio',
            'municipio',
            'provincia',
            'codigo_estacion',
            'id_tarifario',
            'id_contrato',
            'pedido_numero',
            'has_pedidos',
            'multi_pedido',
            'pedido_importe',
            'facturado',
            'solicitado',
            'categoria',
        ]);
        $filters['sort'] = $sort;
        $filters['direction'] = $sort ? $direction : null;

        return Inertia::render('Trabajos/Index', [
            // TrabajoResource aplica el firewall de contexto antes de serializar.
            'trabajos'     => TrabajoResource::collection($trabajos),
            'contextoIds'  => $request->user()->getActiveContextIds(),
            'filters'      => $filters,
            'canCreate'    => $canCreate,
            'responsables' => $responsables,
            'creationCatalogs' => $canUseExcelCatalogs
                ? $this->buildExcelCreationCatalogs($request->user()->getActiveContextIds())
                : [
                    'estaciones' => [],
                    'contratos' => [],
                    'tiposDocumento' => [],
                    'tiposTrabajo' => [],
                    'pedidos' => [],
                ],
        ]);
    }

    private function normalizeTrabajoSort(string $sort): ?string
    {
        $allowed = [
            'numero_trabajo',
            'codigo_estacion',
            'nombre_estacion',
            'municipio',
            'provincia',
            'categoria',
            'descripcion_trabajo',
            'tarifario',
            'importe_pedido_total',
            'importe_solicitado_total',
            'importe_facturado_total',
            'estado',
            'responsable',
            'fecha_encargo',
            'fecha_solicitud_pedido',
            'fecha_terminacion',
        ];

        return in_array($sort, $allowed, true) ? $sort : null;
    }

    private function normalizeSortDirection(string $direction): string
    {
        return strtolower($direction) === 'desc' ? 'desc' : 'asc';
    }

    private function applyTrabajoIndexOrdering(Builder $query, ?string $sort, string $direction): void
    {
        switch ($sort) {
            case 'numero_trabajo':
                $query->orderByRaw("CASE WHEN trabajos.numero_trabajo_operativo IS NULL OR trabajos.numero_trabajo_operativo = '' THEN 1 ELSE 0 END ASC")
                    ->orderBy('trabajos.numero_trabajo_operativo', $direction)
                    ->orderBy('trabajos.numero_trabajo', $direction)
                    ->orderBy('trabajos.id_trabajo', $direction);
                return;

            case 'codigo_estacion':
                $query->orderBy(
                    EstacionServicio::query()
                        ->select('codigo_estacion')
                        ->whereColumn('estaciones_servicio.id_estacion_servicio', 'trabajos.id_estacion_servicio')
                        ->limit(1),
                    $direction
                )->orderBy('trabajos.id_trabajo', $direction);
                return;

            case 'nombre_estacion':
                $query->orderBy(
                    EstacionServicio::query()
                        ->select('nombre')
                        ->whereColumn('estaciones_servicio.id_estacion_servicio', 'trabajos.id_estacion_servicio')
                        ->limit(1),
                    $direction
                )->orderBy('trabajos.id_trabajo', $direction);
                return;

            case 'municipio':
                $query->orderBy(
                    EstacionServicio::query()
                        ->select('poblacion')
                        ->whereColumn('estaciones_servicio.id_estacion_servicio', 'trabajos.id_estacion_servicio')
                        ->limit(1),
                    $direction
                )->orderBy('trabajos.id_trabajo', $direction);
                return;

            case 'provincia':
                $query->orderBy(
                    EstacionServicio::query()
                        ->select('provincia')
                        ->whereColumn('estaciones_servicio.id_estacion_servicio', 'trabajos.id_estacion_servicio')
                        ->limit(1),
                    $direction
                )->orderBy('trabajos.id_trabajo', $direction);
                return;

            case 'categoria':
                $query->orderByRaw(
                    "COALESCE(
                        (SELECT nombre FROM tipos_trabajo WHERE tipos_trabajo.id_tipo_trabajo = trabajos.id_tipo_trabajo LIMIT 1),
                        trabajos.categoria,
                        (SELECT nombre FROM tipos_documento WHERE tipos_documento.id_tipo_documento = trabajos.id_tipo_documento LIMIT 1)
                    ) {$direction}"
                )->orderBy('trabajos.id_trabajo', $direction);
                return;

            case 'descripcion_trabajo':
                $query->orderBy('trabajos.descripcion_trabajo', $direction)
                    ->orderBy('trabajos.id_trabajo', $direction);
                return;

            case 'tarifario':
                $query->orderByRaw(
                    "COALESCE(
                        (SELECT nombre FROM tarifarios WHERE tarifarios.id_tarifario = trabajos.id_tarifario LIMIT 1),
                        (SELECT nombre FROM contratos WHERE contratos.id_contrato = trabajos.id_contrato LIMIT 1)
                    ) {$direction}"
                )->orderBy('trabajos.id_trabajo', $direction);
                return;

            case 'importe_pedido_total':
                $query->orderBy('importe_pedido_total', $direction)
                    ->orderBy('trabajos.id_trabajo', $direction);
                return;

            case 'importe_solicitado_total':
                $query->orderBy('importe_solicitado_total', $direction)
                    ->orderBy('trabajos.id_trabajo', $direction);
                return;

            case 'importe_facturado_total':
                $query->orderBy('importe_facturado_total', $direction)
                    ->orderBy('trabajos.id_trabajo', $direction);
                return;

            case 'estado':
                $query->orderByRaw($this->trabajoEstadoOrderCase() . " {$direction}")
                    ->orderBy('trabajos.id_trabajo', $direction);
                return;

            case 'responsable':
                $query->orderBy(
                    User::query()
                        ->select('nombre')
                        ->whereColumn('usuarios.id_usuario', 'trabajos.id_responsable_ciete')
                        ->limit(1),
                    $direction
                )->orderBy(
                    User::query()
                        ->select('apellidos')
                        ->whereColumn('usuarios.id_usuario', 'trabajos.id_responsable_ciete')
                        ->limit(1),
                    $direction
                )->orderBy('trabajos.id_trabajo', $direction);
                return;

            case 'fecha_encargo':
                $query->orderBy('trabajos.fecha_encargo', $direction)
                    ->orderBy('trabajos.id_trabajo', $direction);
                return;

            case 'fecha_solicitud_pedido':
                $query->orderBy(
                    Pedido::query()
                        ->selectRaw('MIN(fecha_solicitud)')
                        ->whereColumn('pedidos.id_trabajo', 'trabajos.id_trabajo'),
                    $direction
                )->orderBy('trabajos.id_trabajo', $direction);
                return;

            case 'fecha_terminacion':
                $query->orderBy('trabajos.fecha_terminacion', $direction)
                    ->orderBy('trabajos.id_trabajo', $direction);
                return;

            default:
                $this->applyDefaultTrabajoOrder($query);
                return;
        }
    }

    private function applyDefaultTrabajoOrder(Builder $query): void
    {
        $query->orderByRaw($this->trabajoEstadoOrderCase() . ' ASC')
            ->orderBy('trabajos.fecha_encargo', 'desc')
            ->orderBy('trabajos.id_trabajo', 'desc');
    }

    private function trabajoEstadoOrderCase(): string
    {
        return "CASE trabajos.estado WHEN 'en_curso' THEN 1 WHEN 'terminado' THEN 2 WHEN 'pendiente_facturar' THEN 3 WHEN 'facturado' THEN 4 WHEN 'finalizado' THEN 5 WHEN 'cancelado' THEN 99 ELSE 90 END";
    }

    private function applyOperationalSearch(Builder $query, string $search): void
    {
        $like = '%' . $search . '%';

        $query->where(function (Builder $q) use ($like): void {
            $q->where('numero_trabajo', 'like', $like)
                ->orWhere('numero_trabajo_operativo', 'like', $like)
                ->orWhere('numero_aviso', 'like', $like)
                ->orWhere('descripcion_trabajo', 'like', $like)
                ->orWhere('observaciones', 'like', $like)
                ->orWhere('categoria', 'like', $like)
                ->orWhere('responsable_cliente', 'like', $like)
                ->orWhere('fecha_encargo', 'like', $like)
                ->orWhere('fecha_terminacion', 'like', $like)
                ->orWhereHas('empresa', fn (Builder $empresa) => $empresa
                    ->where('nombre', 'like', $like)
                    ->orWhere('nombre_comercial', 'like', $like)
                    ->orWhere('razon_social', 'like', $like)
                    ->orWhere('cif', 'like', $like))
                ->orWhereHas('estacion', fn (Builder $estacion) => $estacion
                    ->where('codigo_estacion', 'like', $like)
                    ->orWhere('nombre', 'like', $like)
                    ->orWhere('poblacion', 'like', $like)
                    ->orWhere('provincia', 'like', $like))
                ->orWhereHas('responsableCiete', fn (Builder $responsables) => $responsables
                    ->where('nombre', 'like', $like)
                    ->orWhere('apellidos', 'like', $like)
                    ->orWhere('email', 'like', $like))
                ->orWhereHas('tipoTrabajo', fn (Builder $tipos) => $tipos
                    ->where('nombre', 'like', $like)
                    ->orWhere('codigo', 'like', $like))
                ->orWhereHas('tipoDocumento', fn (Builder $tipos) => $tipos
                    ->where('nombre', 'like', $like)
                    ->orWhere('codigo', 'like', $like))
                ->orWhereHas('contrato', fn (Builder $contratos) => $contratos
                    ->where('codigo_contrato', 'like', $like)
                    ->orWhere('nombre', 'like', $like)
                    ->orWhereHas('empresa', fn (Builder $empresa) => $empresa
                        ->where('nombre', 'like', $like)
                        ->orWhere('nombre_comercial', 'like', $like)
                        ->orWhere('cif', 'like', $like)))
                ->orWhereHas('tarifario', fn (Builder $tarifarios) => $tarifarios
                    ->where('id_tarifario', 'like', $like)
                    ->orWhere('nombre', 'like', $like)
                    ->orWhere('version', 'like', $like)
                    ->orWhereHas('contrato', fn (Builder $contratos) => $contratos
                        ->where('codigo_contrato', 'like', $like)
                        ->orWhere('nombre', 'like', $like)))
                ->orWhereHas('pedidos', fn (Builder $pedidos) => $pedidos
                    ->where('numero_pedido', 'like', $like)
                    ->orWhere('estado', 'like', $like)
                    ->orWhere('fecha_solicitud', 'like', $like)
                    ->orWhere('importe_pedido', 'like', $like)
                    ->orWhere('importe_solicitado', 'like', $like)
                    ->orWhere('importe_facturado', 'like', $like));
        });
    }

    private function applyPedidoAmountFilter(Builder $query, string $column, string $value): void
    {
        $amountQuery = fn (Builder $pedidos) => $pedidos
            ->whereNotNull($column)
            ->where($column, '>', 0);

        if ($value === '1') {
            $query->whereHas('pedidos', $amountQuery);
            return;
        }

        if ($value === '0') {
            $query->whereDoesntHave('pedidos', $amountQuery);
        }
    }

    public function create(Request $request): Response|RedirectResponse
    {
        if (! TrabajoPermission::canCreate($request->user())) {
            abort(403, 'No tienes permiso para crear trabajos.');
        }

        if (! ContextGuard::canCreateInActiveContext($request->user())) {
            return redirect()
                ->route('trabajos.index')
                ->with('warning', ContextGuard::CREATE_FROM_ALL_MESSAGE);
        }

        $accessibleContextIds = $request->user()->getActiveContextIds();
        $clientContexts = ContextoCliente::query()
            ->whereIn('id_contexto', $accessibleContextIds)
            ->where('activo', true)
            ->orderBy('id_contexto')
            ->get(['id_contexto', 'nombre', 'codigo']);

        return Inertia::render('Trabajos/Form', [
            'trabajo' => null,
            'contextoIds' => $accessibleContextIds,
            'clientContexts' => $clientContexts,
            ...$this->buildFormCatalogs($accessibleContextIds),
        ]);
    }

    public function store(StoreTrabajoRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        if (!isset($validated['id_contexto'])) {
            $validated['id_contexto'] = ContextGuard::activeContextIdForCreate($request->user());
        }

        $trabajo = DB::transaction(function () use ($validated) {
            if (! isset($validated['id_empresa_cliente']) && isset($validated['id_estacion_servicio'])) {
                $estacion = EstacionServicio::withoutGlobalScopes()
                    ->findOrFail($validated['id_estacion_servicio']);

                $validated['id_empresa_cliente'] = $estacion->id_empresa_cliente;
            }

            if (! empty($validated['id_tarifario'])) {
                $tarifario = Tarifario::withoutGlobalScopes()
                    ->with('contrato:id_contrato,id_contexto,id_empresa_cliente,activo')
                    ->where('id_tarifario', $validated['id_tarifario'])
                    ->where('id_contexto', $validated['id_contexto'])
                    ->where('activo', true)
                    ->first();

                if (! $tarifario || ! $tarifario->contrato || ! $tarifario->contrato->activo) {
                    throw ValidationException::withMessages([
                        'id_tarifario' => 'El contrato/tarifa seleccionado no está disponible para este trabajo.',
                    ]);
                }

                $validated['id_contrato'] = $tarifario->id_contrato;
            }

            $validated = $this->prepareTrabajoStateData($validated);
            $validated = $this->prepareTrabajoNumberData($validated);

            $trabajo = Trabajo::create($validated);
            $this->trabajoStateService->syncTrabajo($trabajo);

            return $trabajo->fresh();
        });

        $snapshot = $this->auditLogger->snapshotModel($trabajo, self::AUDIT_FIELDS);
        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => 'crear',
            'modulo' => 'trabajos',
            'tabla' => 'trabajos',
            'entity_type' => Trabajo::class,
            'entity_id' => $trabajo->id_trabajo,
            'registro_id' => $trabajo->id_trabajo,
            'campo' => 'id_trabajo',
            'valor_nuevo' => $trabajo->id_trabajo,
            'datos_nuevos' => $snapshot,
            'descripcion' => 'Alta de trabajo.',
            'id_contexto' => $trabajo->id_contexto,
        ], $request);

        if ($request->expectsJson()) {
            $this->loadTrabajoForResponse($trabajo);

            return response()->json([
                'success' => true,
                'message' => 'Trabajo creado correctamente.',
                'trabajo' => (new TrabajoResource($trabajo))->resolve($request),
            ], 201);
        }

        return redirect()->route('trabajos.index')
            ->with('success', 'Obra registrada correctamente.');
    }

    public function show(Request $request, Trabajo $trabajo): JsonResponse
    {
        $activeContextIds = array_map('intval', $request->user()->getActiveContextIds());

        if (! in_array((int) $trabajo->id_contexto, $activeContextIds, true)) {
            return response()->json([
                'message' => 'Este trabajo no pertenece al contexto activo de tu usuario.',
            ], 403);
        }

        $this->loadTrabajoForResponse($trabajo);

        return response()->json([
            'success' => true,
            'message' => 'Detalle de trabajo obtenido.',
            'trabajo' => (new TrabajoResource($trabajo))->resolve($request),
        ]);
    }

    /**
     * Muestra el formulario para editar una Obra existente.
     */
    public function edit(Request $request, Trabajo $trabajo): Response
    {
        $trabajo->load(['empresa', 'estacion', 'contrato', 'tipoDocumento', 'tipoTrabajo']);
        $accessibleContextIds = $request->user()->getActiveContextIds();
        $clientContexts = ContextoCliente::query()
            ->whereIn('id_contexto', $accessibleContextIds)
            ->where('activo', true)
            ->orderBy('id_contexto')
            ->get(['id_contexto', 'nombre', 'codigo']);

        return Inertia::render('Trabajos/Form', [
            'trabajo' => new TrabajoResource($trabajo),
            'contextoIds' => $accessibleContextIds,
            'clientContexts' => $clientContexts,
            ...$this->buildFormCatalogs($accessibleContextIds),
        ]);
    }

    public function update(UpdateTrabajoRequest $request, Trabajo $trabajo): RedirectResponse
    {
        // Protección crítica de negocio: Bloqueo de Trabajos Cerrados
        if (TrabajoPermission::isClosed($trabajo) && ! TrabajoPermission::canEditClosed($request->user())) {
            abort(403, 'Acción denegada. Esta obra está finalizada y bloqueada para modificaciones.');
        }

        $validated = $request->validated();

        // Detección de conflicto para el formulario completo (Modo Moderno).
        // Si el cliente envía un updated_at y difiere del servidor, comprobamos
        // si otro usuario modificó el trabajo en los últimos 60 minutos.
        $clientTs = isset($validated['updated_at'])
            ? ($this->normalizePatchTimestamp($validated['updated_at']) ?? $validated['updated_at'])
            : null;
        $serverTs = $trabajo->updated_at?->format('Y-m-d H:i:s') ?? '';

        if ($clientTs !== null && $clientTs !== $serverTs) {
            $lastAudit = AuditLog::where('tabla', 'trabajos')
                ->where('registro_id', $trabajo->id_trabajo)
                ->orderByDesc('created_at')
                ->with('usuario')
                ->first();

            $fueModificadoPorOtro = $lastAudit?->id_usuario
                && (int) $lastAudit->id_usuario !== (int) $request->user()->id_usuario;

            $fueReciente = $fueModificadoPorOtro
                && $lastAudit?->created_at instanceof Carbon
                && $lastAudit->created_at->greaterThanOrEqualTo(now()->subHour());

            if ($fueReciente) {
                $nombreModificador = null;
                if ($lastAudit->usuario) {
                    $nombreModificador = trim(
                        ($lastAudit->usuario->nombre ?? '') . ' ' . ($lastAudit->usuario->apellidos ?? '')
                    ) ?: null;
                }

                return redirect()->back()
                    ->withInput()
                    ->with('conflict_warning', sprintf(
                        'Conflicto: este trabajo fue modificado recientemente por %s (%s). Recarga la página para ver los datos actualizados antes de guardar.',
                        $nombreModificador ?? 'otro usuario',
                        $serverTs
                    ));
            }
        }

        $requestedEstado = $validated['estado'] ?? null;
        if (is_string($requestedEstado) && $requestedEstado !== $trabajo->estado) {
            $stateError = $this->assertTrabajoStateMutationAllowed($request->user(), $requestedEstado);
            if ($stateError) {
                abort(403, 'No tienes permiso para cambiar manualmente el estado de este trabajo.');
            }
        }

        $before = $this->auditLogger->snapshotModel($trabajo, self::AUDIT_FIELDS);

        $trabajo->update($this->prepareTrabajoStateData($validated, $trabajo));
        $this->trabajoStateService->syncTrabajo($trabajo);
        $trabajo->refresh();

        $after = $this->auditLogger->snapshotModel($trabajo, self::AUDIT_FIELDS);
        $firstChange = $this->auditLogger->resolveFirstChange($before, $after);
        $statusChanged = ($before['estado'] ?? null) !== ($after['estado'] ?? null);

        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => $statusChanged ? 'cambiar_estado' : 'actualizar',
            'modulo' => 'trabajos',
            'tabla' => 'trabajos',
            'entity_type' => Trabajo::class,
            'entity_id' => $trabajo->id_trabajo,
            'registro_id' => $trabajo->id_trabajo,
            'campo' => $firstChange['campo'],
            'valor_anterior' => $firstChange['valor_anterior'],
            'valor_nuevo' => $firstChange['valor_nuevo'],
            'datos_anteriores' => $before,
            'datos_nuevos' => $after,
            'descripcion' => $this->auditLogger->buildChangedFieldsDescription($before, $after, 'Actualización de trabajo'),
            'id_contexto' => $trabajo->id_contexto,
        ], $request);

        return redirect()->route('trabajos.index')
            ->with('success', 'Obra actualizada correctamente.');
    }

    /**
     * Elimina una Obra (SoftDelete o HardDelete según el modelo).
     */
    public function destroy(Request $request, Trabajo $trabajo): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (! TrabajoPermission::canDelete($user, $trabajo)) {
            abort(403, 'No tienes permiso para cancelar este trabajo.');
        }

        $activeContextIds = array_map('intval', $user->getActiveContextIds());
        if (! in_array((int) $trabajo->id_contexto, $activeContextIds, true)) {
            abort(403, 'Este trabajo no pertenece al contexto activo de tu usuario.');
        }

        $before = $this->auditLogger->snapshotModel($trabajo, self::AUDIT_FIELDS);

        $trabajo->estado = 'cancelado';
        $trabajo->save();
        $trabajo->refresh();

        $after = $this->auditLogger->snapshotModel($trabajo, self::AUDIT_FIELDS);

        $this->auditLogger->log([
            'user' => $user,
            'accion' => 'cambiar_estado',
            'modulo' => 'trabajos',
            'tabla' => 'trabajos',
            'entity_type' => Trabajo::class,
            'entity_id' => $trabajo->id_trabajo,
            'registro_id' => $trabajo->id_trabajo,
            'campo' => 'estado',
            'valor_anterior' => $before['estado'] ?? null,
            'valor_nuevo' => $after['estado'] ?? null,
            'datos_anteriores' => $before,
            'datos_nuevos' => $after,
            'descripcion' => 'Trabajo cancelado desde accion de eliminacion restringida.',
            'id_contexto' => $trabajo->id_contexto,
        ], $request);

        if ($request->expectsJson()) {
            $this->loadTrabajoForResponse($trabajo);

            return response()->json([
                'success' => true,
                'message' => 'Trabajo cancelado correctamente. No se ha borrado el historico.',
                'trabajo' => (new TrabajoResource($trabajo))->resolve($request),
            ]);
        }

        return redirect()->route('trabajos.index')
            ->with('success', 'Trabajo cancelado correctamente. No se ha borrado el historico.');
    }

    /**
     * Guardado optimista de un campo único. Rechaza con 409 si otro usuario modificó
     * ese campo en los últimos 60 minutos, mostrando qué cambió y quién lo hizo.
     */
    public function patchField(Request $request, Trabajo $trabajo): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Usuario no autenticado.',
            ], 401);
        }

        if (! TrabajoPermission::canEdit($user, $trabajo)) {
            return response()->json([
                'message' => 'No tienes permiso para editar este trabajo.',
            ], 403);
        }

        $activeContextIds = array_map('intval', $user->getActiveContextIds());
        if (! in_array((int) $trabajo->id_contexto, $activeContextIds, true)) {
            return response()->json([
                'message' => 'Este trabajo no pertenece al contexto activo de tu usuario.',
            ], 403);
        }

        $fieldMap = [
            'estado' => 'estado',
            'observaciones' => 'observaciones',
            'fecha_terminacion' => 'fecha_terminacion',
            'id_responsable_ciete' => 'id_responsable_ciete',
            'descripcion' => 'descripcion_trabajo',
            'descripcion_trabajo' => 'descripcion_trabajo',
            'numero_trabajo' => 'numero_trabajo',
            'numero_trabajo_operativo' => 'numero_trabajo_operativo',
            'categoria' => 'categoria',
            'id_tipo_documento' => 'id_tipo_documento',
            'id_tipo_trabajo' => 'id_tipo_trabajo',
            'id_estacion_servicio' => 'id_estacion_servicio',
            'id_tarifario' => 'id_tarifario',
            'codigo_estacion' => 'codigo_estacion',
            'nombre_estacion' => 'nombre_estacion',
            'municipio' => 'municipio',
            'provincia' => 'provincia',
            'id_pedido_principal' => 'id_pedido_principal',
        ];

        $campo = $fieldMap[$request->input('campo')] ?? $request->input('campo');
        $stationFieldMap = [
            'codigo_estacion' => 'codigo_estacion',
            'nombre_estacion' => 'nombre',
            'municipio' => 'poblacion',
            'provincia' => 'provincia',
        ];
        $isStationField = array_key_exists($campo, $stationFieldMap);
        $isPedidoPrincipalField = $campo === 'id_pedido_principal';

        if ($campo === 'estado') {
            $stateError = $this->assertTrabajoStateMutationAllowed($user, (string) $request->input('valor'), true);
            if ($stateError) {
                return $stateError;
            }
        }

        if ($isStationField && ! $user->hasPermission('estaciones.editar')) {
            return response()->json([
                'message' => 'No tienes permiso para editar datos maestros de estación desde esta vista.',
            ], 403);
        }

        if ($isPedidoPrincipalField && ! $user->hasPermission('pedidos.editar')) {
            return response()->json([
                'message' => 'No tienes permiso para reasignar pedidos desde esta vista.',
            ], 403);
        }

        if (
            in_array($campo, ['observaciones', 'fecha_terminacion', 'id_responsable_ciete', 'descripcion_trabajo', 'numero_trabajo_operativo', 'categoria', 'municipio', 'provincia'], true)
            && $request->input('valor') === ''
        ) {
            $request->merge(['valor' => null]);
        }

        // Reglas de validación por campo
        $valorRules = match ($campo) {
            'estado'               => ['required', 'string', Rule::in(Trabajo::ESTADOS_MANUALES)],
            'observaciones'        => ['nullable', 'string', 'max:5000'],
            'fecha_terminacion'    => ['nullable', 'date'],
            'id_responsable_ciete' => [
                'nullable',
                'integer',
                Rule::exists('usuarios', 'id_usuario')->where(fn ($query) => $query->where('activo', true)),
            ],
            'descripcion_trabajo'   => ['nullable', 'string', 'max:1000'],
            'numero_trabajo'        => ['required', 'integer'],
            'numero_trabajo_operativo' => ['nullable', 'string', 'max:100'],
            'categoria'             => ['nullable', 'string', 'max:120'],
            'id_tipo_documento'      => [
                'required',
                'integer',
                Rule::exists('tipos_documento', 'id_tipo_documento')->where(fn ($query) => $query->whereIn('id_contexto', $activeContextIds)->where('activo', true)),
            ],
            'id_tipo_trabajo'        => [
                'required',
                'integer',
                Rule::exists('tipos_trabajo', 'id_tipo_trabajo')->where(fn ($query) => $query->whereIn('id_contexto', $activeContextIds)->where('activo', true)),
            ],
            'id_estacion_servicio'  => [
                'required',
                'integer',
                Rule::exists('estaciones_servicio', 'id_estacion_servicio')->where(fn ($query) => $query->whereIn('id_contexto', $activeContextIds)),
            ],
            'id_tarifario'         => [
                'required',
                'integer',
                Rule::exists('tarifarios', 'id_tarifario')->where(fn ($query) => $query->whereIn('id_contexto', $activeContextIds)->where('activo', true)),
            ],
            'codigo_estacion'       => ['required', 'string', 'max:80'],
            'nombre_estacion'       => ['required', 'string', 'max:180'],
            'municipio'             => ['nullable', 'string', 'max:120'],
            'provincia'             => ['nullable', 'string', 'max:120'],
            'id_pedido_principal'   => [
                'required',
                'integer',
                Rule::exists('pedidos', 'id_pedido')->where(fn ($query) => $query->whereIn('id_contexto', $activeContextIds)),
            ],
            default                => ['prohibited'],
        };

        $validated = $request->validate([
            'campo'      => ['required', 'string', Rule::in(array_keys($fieldMap))],
            'valor'      => $valorRules,
            'updated_at' => ['required', 'string'],
        ]);

        // El timestamp viaja en formato Y-m-d H:i:s para coincidir con lo que emite TrabajoResource.
        if ($isStationField && ! $trabajo->relationLoaded('estacion')) {
            $trabajo->load('estacion');
        }
        if ($isPedidoPrincipalField && ! $trabajo->relationLoaded('primerPedido')) {
            $trabajo->load('primerPedido');
        }

        $currentPatchValue = $isStationField
            ? $trabajo->estacion?->{$stationFieldMap[$campo]}
            : ($isPedidoPrincipalField ? $trabajo->primerPedido?->id_pedido : $trabajo->{$campo});

        $serverTs = $trabajo->updated_at?->format('Y-m-d H:i:s') ?? '';
        $clientTs = $this->normalizePatchTimestamp($validated['updated_at']) ?? $validated['updated_at'];

        if ($serverTs !== $clientTs) {
            $lastAudit = AuditLog::where('tabla', 'trabajos')
                ->where('registro_id', $trabajo->id_trabajo)
                ->where(function ($query) use ($campo): void {
                    $query->where('campo', $campo)
                        ->orWhereNull('campo');
                })
                ->orderByDesc('created_at')
                ->with('usuario')
                ->first();

            $usuarioMod = null;
            if ($lastAudit?->usuario) {
                $usuarioMod = trim(
                    ($lastAudit->usuario->nombre ?? '') . ' ' . ($lastAudit->usuario->apellidos ?? '')
                ) ?: null;
            }

            $fueModificadoPorOtroUsuario = $lastAudit?->id_usuario
                && (int) $lastAudit->id_usuario !== (int) $request->user()->id_usuario;

            // Bloqueo solo si otra persona modificó ese campo en los últimos 60 minutos.
            // Ediciones propias o antiguas pasan directamente sin aviso.
            $fueModificadoRecientemente = $fueModificadoPorOtroUsuario
                && $lastAudit?->created_at instanceof Carbon
                && $lastAudit->created_at->greaterThanOrEqualTo(now()->subHour());

            if ($fueModificadoRecientemente) {
                return response()->json([
                    'conflict'             => true,
                    'message'              => 'Este campo fue modificado recientemente por otro usuario.',
                    'campo'                => $campo,
                    'valor_actual'         => $this->formatPatchValue($currentPatchValue),
                    'valor_intentado'      => $this->formatPatchValue($request->input('valor')),
                    'updated_at_actual'    => $serverTs,
                    'usuario_modificacion' => $usuarioMod,
                    'modificado_recientemente' => true,
                    'current_value'        => $this->formatPatchValue($currentPatchValue),
                    'current_updated_at'   => $serverTs,
                ], 409);
            }
        }

        $valorAnterior = $this->formatPatchValue($currentPatchValue);
        $valorNuevo    = $validated['valor'];

        if (in_array($campo, ['id_tipo_documento', 'id_tipo_trabajo'], true)) {
            if ((int) $trabajo->id_contexto !== 2) {
                return response()->json([
                    'message' => 'El selector de tipo de trabajo solo aplica al contexto REPSOL.',
                ], 422);
            }

            if ($campo === 'id_tipo_documento') {
                $tipoDocumento = TipoDocumento::query()
                    ->where('id_tipo_documento', $valorNuevo)
                    ->where('id_contexto', $trabajo->id_contexto)
                    ->where('activo', true)
                    ->first();

                if (! $tipoDocumento) {
                    return response()->json([
                        'message' => 'El tipo documental seleccionado no pertenece al contexto del trabajo.',
                    ], 422);
                }

                $trabajo->id_tipo_documento = $tipoDocumento->id_tipo_documento;

                if (
                    $trabajo->id_tipo_trabajo
                    && ! TipoTrabajo::query()
                        ->where('id_tipo_trabajo', $trabajo->id_tipo_trabajo)
                        ->where('id_tipo_documento', $tipoDocumento->id_tipo_documento)
                        ->exists()
                ) {
                    $trabajo->id_tipo_trabajo = null;
                }
            } else {
                $tipoTrabajo = TipoTrabajo::query()
                    ->where('id_tipo_trabajo', $valorNuevo)
                    ->where('id_contexto', $trabajo->id_contexto)
                    ->where('activo', true)
                    ->first();

                if (! $tipoTrabajo) {
                    return response()->json([
                        'message' => 'El tipo de trabajo seleccionado no pertenece al contexto del trabajo.',
                    ], 422);
                }

                if ($trabajo->id_tipo_documento && (int) $tipoTrabajo->id_tipo_documento !== (int) $trabajo->id_tipo_documento) {
                    return response()->json([
                        'message' => 'El tipo de trabajo no pertenece al tipo documental actual.',
                    ], 422);
                }

                $trabajo->id_tipo_documento = $tipoTrabajo->id_tipo_documento;
                $trabajo->id_tipo_trabajo = $tipoTrabajo->id_tipo_trabajo;
            }

            if (! $trabajo->isDirty('id_tipo_documento') && ! $trabajo->isDirty('id_tipo_trabajo')) {
                $this->loadTrabajoForResponse($trabajo);

                return response()->json([
                    'success' => true,
                    'message' => 'El tipo seleccionado ya estaba asociado a este trabajo.',
                    'campo' => $campo,
                    'valor' => $valorNuevo,
                    'updated_at' => $serverTs,
                    'trabajo' => (new TrabajoResource($trabajo))->resolve($request),
                ]);
            }

            $trabajo->save();
            $trabajo->refresh();
            $this->loadTrabajoForResponse($trabajo);

            $this->auditLogger->log([
                'user'           => $user,
                'accion'         => 'actualizar',
                'modulo'         => 'trabajos',
                'tabla'          => 'trabajos',
                'entity_type'    => Trabajo::class,
                'entity_id'      => $trabajo->id_trabajo,
                'registro_id'    => $trabajo->id_trabajo,
                'campo'          => $campo,
                'valor_anterior' => $valorAnterior,
                'valor_nuevo'    => $valorNuevo,
                'id_contexto'    => $trabajo->id_contexto,
                'descripcion'    => sprintf('El usuario modifico la categorizacion REPSOL del trabajo %s.', $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
            ], $request);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de trabajo actualizado correctamente.',
                'campo' => $campo,
                'valor' => $valorNuevo,
                'updated_at' => $trabajo->updated_at?->format('Y-m-d H:i:s'),
                'trabajo' => (new TrabajoResource($trabajo))->resolve($request),
            ]);
        }

        if ($campo === 'id_estacion_servicio') {
            $estacion = EstacionServicio::query()
                ->where('id_estacion_servicio', $valorNuevo)
                ->where('id_contexto', $trabajo->id_contexto)
                ->first();

            if (! $estacion) {
                return response()->json([
                    'message' => 'La estacion seleccionada no pertenece al contexto del trabajo.',
                ], 422);
            }

            $trabajo->id_estacion_servicio = $estacion->id_estacion_servicio;
            $trabajo->id_empresa_cliente = $estacion->id_empresa_cliente;

            if (! $trabajo->isDirty('id_estacion_servicio') && ! $trabajo->isDirty('id_empresa_cliente')) {
                $this->loadTrabajoForResponse($trabajo);

                return response()->json([
                    'success' => true,
                    'message' => 'La estacion seleccionada ya estaba asociada a este trabajo.',
                    'campo' => $campo,
                    'valor' => $estacion->id_estacion_servicio,
                    'updated_at' => $serverTs,
                    'trabajo' => (new TrabajoResource($trabajo))->resolve($request),
                ]);
            }

            $trabajo->save();
            $trabajo->refresh();
            $this->loadTrabajoForResponse($trabajo);

            $this->auditLogger->log([
                'user'           => $user,
                'accion'         => 'actualizar',
                'modulo'         => 'trabajos',
                'tabla'          => 'trabajos',
                'entity_type'    => Trabajo::class,
                'entity_id'      => $trabajo->id_trabajo,
                'registro_id'    => $trabajo->id_trabajo,
                'campo'          => $campo,
                'valor_anterior' => $valorAnterior,
                'valor_nuevo'    => $estacion->id_estacion_servicio,
                'id_contexto'    => $trabajo->id_contexto,
                'descripcion'    => sprintf('El usuario modifico la estacion del trabajo %s.', $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
            ], $request);

            return response()->json([
                'success' => true,
                'message' => 'Estacion asociada correctamente.',
                'campo' => $campo,
                'valor' => $estacion->id_estacion_servicio,
                'updated_at' => $trabajo->updated_at?->format('Y-m-d H:i:s'),
                'trabajo' => (new TrabajoResource($trabajo))->resolve($request),
            ]);
        }

        if ($campo === 'id_tarifario') {
            if (Pedido::query()->where('id_trabajo', $trabajo->id_trabajo)->exists()) {
                return response()->json([
                    'message' => 'No se puede cambiar el contrato/tarifa porque este trabajo ya tiene pedidos.',
                ], 422);
            }

            $tarifario = Tarifario::query()
                ->with('contrato:id_contrato,id_contexto,id_empresa_cliente,activo')
                ->where('id_tarifario', $valorNuevo)
                ->where('id_contexto', $trabajo->id_contexto)
                ->where('activo', true)
                ->first();

            if (! $tarifario || ! $tarifario->contrato || ! $tarifario->contrato->activo) {
                return response()->json([
                    'message' => 'El contrato/tarifa seleccionado no está disponible para este trabajo.',
                ], 422);
            }

            if (
                $trabajo->id_empresa_cliente
                && (int) $tarifario->contrato->id_empresa_cliente !== (int) $trabajo->id_empresa_cliente
            ) {
                return response()->json([
                    'message' => 'El contrato/tarifa seleccionado no pertenece a la empresa del trabajo.',
                ], 422);
            }

            $trabajo->id_tarifario = $tarifario->id_tarifario;
            $trabajo->id_contrato = $tarifario->id_contrato;

            if (! $trabajo->isDirty('id_tarifario') && ! $trabajo->isDirty('id_contrato')) {
                $this->loadTrabajoForResponse($trabajo);

                return response()->json([
                    'success' => true,
                    'message' => 'El contrato/tarifa seleccionado ya estaba asociado a este trabajo.',
                    'campo' => $campo,
                    'valor' => $tarifario->id_tarifario,
                    'updated_at' => $serverTs,
                    'trabajo' => (new TrabajoResource($trabajo))->resolve($request),
                ]);
            }

            $trabajo->save();
            $trabajo->refresh();
            $this->loadTrabajoForResponse($trabajo);

            $this->auditLogger->log([
                'user'           => $user,
                'accion'         => 'actualizar',
                'modulo'         => 'trabajos',
                'tabla'          => 'trabajos',
                'entity_type'    => Trabajo::class,
                'entity_id'      => $trabajo->id_trabajo,
                'registro_id'    => $trabajo->id_trabajo,
                'campo'          => $campo,
                'valor_anterior' => $valorAnterior,
                'valor_nuevo'    => $tarifario->id_tarifario,
                'id_contexto'    => $trabajo->id_contexto,
                'descripcion'    => sprintf('El usuario modifico el contrato/tarifa del trabajo %s.', $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
            ], $request);

            return response()->json([
                'success' => true,
                'message' => 'Contrato/tarifa actualizado correctamente.',
                'campo' => $campo,
                'valor' => $tarifario->id_tarifario,
                'updated_at' => $trabajo->updated_at?->format('Y-m-d H:i:s'),
                'trabajo' => (new TrabajoResource($trabajo))->resolve($request),
            ]);
        }

        if ($campo === 'id_pedido_principal') {
            $pedidoActual = $trabajo->primerPedido;
            $valorAnterior = $pedidoActual?->id_pedido;

            $pedidoSeleccionado = Pedido::query()
                ->where('id_pedido', $valorNuevo)
                ->where('id_contexto', $trabajo->id_contexto)
                ->first();

            if (! $pedidoSeleccionado) {
                return response()->json([
                    'message' => 'El pedido seleccionado no pertenece al contexto del trabajo.',
                ], 422);
            }

            if ((int) $pedidoSeleccionado->id_trabajo === (int) $trabajo->id_trabajo) {
                return response()->json([
                    'success' => true,
                    'message' => 'El pedido seleccionado ya estaba asociado a este trabajo.',
                    'campo' => $campo,
                    'valor' => $pedidoSeleccionado->id_pedido,
                    'updated_at' => $serverTs,
                    'trabajo' => (new TrabajoResource($trabajo))->resolve($request),
                ]);
            }

            $trabajoOrigen = $pedidoSeleccionado->id_trabajo;

            DB::transaction(function () use ($pedidoSeleccionado, $trabajo): void {
                $pedidoSeleccionado->id_trabajo = $trabajo->id_trabajo;
                $pedidoSeleccionado->save();
                $trabajo->touch();
            });

            $this->trabajoStateService->syncPedidosAndTrabajosByPedidoIds([$pedidoSeleccionado->id_pedido]);
            $this->trabajoStateService->syncTrabajosByIds([$trabajoOrigen, $trabajo->id_trabajo]);

            $trabajo->refresh();
            $this->loadTrabajoForResponse($trabajo);

            $this->auditLogger->log([
                'user'           => $user,
                'accion'         => 'actualizar',
                'modulo'         => 'pedidos',
                'tabla'          => 'pedidos',
                'entity_type'    => Pedido::class,
                'entity_id'      => $pedidoSeleccionado->id_pedido,
                'registro_id'    => $pedidoSeleccionado->id_pedido,
                'campo'          => 'id_trabajo',
                'valor_anterior' => $trabajoOrigen,
                'valor_nuevo'    => $trabajo->id_trabajo,
                'id_contexto'    => $trabajo->id_contexto,
                'descripcion'    => sprintf('El usuario reasigno el pedido %s al trabajo %s desde la vista Excel.', $pedidoSeleccionado->numero_pedido ?? $pedidoSeleccionado->id_pedido, $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
            ], $request);

            return response()->json([
                'success' => true,
                'message' => 'Pedido asociado correctamente al trabajo.',
                'campo' => $campo,
                'valor' => $pedidoSeleccionado->id_pedido,
                'valor_anterior' => $valorAnterior,
                'updated_at' => $trabajo->updated_at?->format('Y-m-d H:i:s'),
                'trabajo' => (new TrabajoResource($trabajo))->resolve($request),
            ]);
        }

        if ($isStationField && ! $trabajo->estacion) {
            return response()->json([
                'message' => 'Este trabajo no tiene estacion asociada.',
            ], 422);
        }

        if ($isStationField) {
            $stationColumn = $stationFieldMap[$campo];
            $trabajo->estacion->{$stationColumn} = $valorNuevo;

            if (! $trabajo->estacion->isDirty($stationColumn)) {
                $this->loadTrabajoForResponse($trabajo);

                return response()->json([
                    'success' => true,
                    'message' => 'El campo no tenia cambios pendientes.',
                    'campo' => $campo,
                    'valor' => $this->formatPatchValue($trabajo->estacion->{$stationColumn}),
                    'updated_at' => $serverTs,
                    'trabajo' => (new TrabajoResource($trabajo))->resolve($request),
                ]);
            }

            $trabajo->estacion->save();
            $trabajo->touch();
            $trabajo->refresh();
            $this->loadTrabajoForResponse($trabajo);

            $auditDescriptions = [
                'codigo_estacion' => sprintf('El usuario modifico el codigo de estacion del trabajo %s.', $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
                'nombre_estacion' => sprintf('El usuario modifico el nombre de estacion del trabajo %s.', $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
                'municipio' => sprintf('El usuario modifico el municipio de la estacion del trabajo %s.', $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
                'provincia' => sprintf('El usuario modifico la provincia de la estacion del trabajo %s.', $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
            ];

            $this->auditLogger->log([
                'user'           => $user,
                'accion'         => 'actualizar',
                'modulo'         => 'trabajos',
                'tabla'          => 'estaciones_servicio',
                'entity_type'    => EstacionServicio::class,
                'entity_id'      => $trabajo->estacion->id_estacion_servicio,
                'registro_id'    => $trabajo->estacion->id_estacion_servicio,
                'campo'          => $campo,
                'valor_anterior' => $valorAnterior,
                'valor_nuevo'    => $this->formatPatchValue($trabajo->estacion->{$stationColumn}),
                'id_contexto'    => $trabajo->id_contexto,
                'descripcion'    => $auditDescriptions[$campo],
            ], $request);

            return response()->json([
                'success' => true,
                'message' => 'Campo actualizado correctamente.',
                'campo' => $campo,
                'valor' => $this->formatPatchValue($trabajo->estacion->{$stationColumn}),
                'updated_at' => $trabajo->updated_at?->format('Y-m-d H:i:s'),
                'trabajo' => (new TrabajoResource($trabajo))->resolve($request),
            ]);
        }

        $trabajo->{$campo} = $valorNuevo;

        if (
            $campo === 'estado'
            && $valorNuevo === 'terminado'
            && ! $trabajo->fecha_terminacion
        ) {
            $trabajo->fecha_terminacion = Carbon::today();
        }

        if ($campo === 'estado' && $valorNuevo === 'en_curso') {
            $trabajo->fecha_terminacion = null;
        }

        if (! $trabajo->isDirty($campo) && ! $trabajo->isDirty('fecha_terminacion')) {
            $this->loadTrabajoForResponse($trabajo);

            return response()->json([
                'success' => true,
                'message' => 'El campo no tenía cambios pendientes.',
                'campo' => $campo,
                'valor' => $this->formatPatchValue($trabajo->{$campo}),
                'updated_at' => $serverTs,
                'trabajo' => (new TrabajoResource($trabajo))->resolve($request),
            ]);
        }

        $trabajo->save();
        $this->trabajoStateService->syncTrabajo($trabajo);
        $trabajo->refresh();

        $auditDescriptions = [
            'estado' => sprintf('El usuario modifico el estado del trabajo %s.', $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
            'observaciones' => sprintf('El usuario modifico las observaciones del trabajo %s.', $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
            'fecha_terminacion' => sprintf('El usuario modifico la fecha de terminacion del trabajo %s.', $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
            'id_responsable_ciete' => sprintf('El usuario modifico el responsable CIETE del trabajo %s.', $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
            'descripcion_trabajo' => sprintf('El usuario modifico la descripcion del trabajo %s.', $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
            'numero_trabajo' => sprintf('El usuario modifico el numero del trabajo %s.', $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
            'numero_trabajo_operativo' => sprintf('El usuario modifico el numero operativo CIETE del trabajo %s.', $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
            'categoria' => sprintf('El usuario modifico la categoria del trabajo %s.', $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
            'id_tarifario' => sprintf('El usuario modifico el contrato/tarifa del trabajo %s.', $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
        ];

        $this->auditLogger->log([
            'user'           => $user,
            'accion'         => $campo === 'estado' ? 'cambiar_estado' : 'actualizar',
            'modulo'         => 'trabajos',
            'tabla'          => 'trabajos',
            'entity_type'    => Trabajo::class,
            'entity_id'      => $trabajo->id_trabajo,
            'registro_id'    => $trabajo->id_trabajo,
            'campo'          => $campo,
            'valor_anterior' => $valorAnterior,
            'valor_nuevo'    => $this->formatPatchValue($trabajo->{$campo}),
            'id_contexto'    => $trabajo->id_contexto,
            'descripcion'    => $auditDescriptions[$campo] ?? sprintf('El usuario modifico el campo %s del trabajo %s.', $campo, $trabajo->numero_trabajo ?? $trabajo->id_trabajo),
        ], $request);

        $this->loadTrabajoForResponse($trabajo);

        return response()->json([
            'success' => true,
            'message' => 'Campo actualizado correctamente.',
            'campo' => $campo,
            'valor' => $this->formatPatchValue($trabajo->{$campo}),
            'updated_at' => $trabajo->updated_at?->format('Y-m-d H:i:s'),
            'trabajo' => (new TrabajoResource($trabajo))->resolve($request),
        ]);
    }

    private function loadTrabajoForResponse(Trabajo $trabajo): void
    {
        $trabajo->load([
            'empresa',
            'estacion',
            'contrato',
            'tarifario',
            'tipoDocumento',
            'tipoTrabajo',
            'responsableCiete',
            'primerPedido',
            'pedidos' => fn ($pedidos) => $pedidos
                ->select([
                    'id_pedido',
                    'id_trabajo',
                    'id_contexto',
                    'numero_pedido',
                    'fecha_solicitud',
                    'estado',
                    'importe_pedido',
                    'importe_facturado',
                    'pedido_completo',
                    'facturado_completo',
                ])
                ->orderBy('id_pedido'),
        ]);

        // Compute aggregate sums so TrabajoResource can render importe totals without loading all pedidos.
        if (! isset($trabajo->importe_pedido_total)) {
            $sums = \App\Models\Pedido::query()
                ->where('id_trabajo', $trabajo->id_trabajo)
                ->selectRaw('SUM(importe_pedido) as importe_pedido_total, SUM(importe_solicitado) as importe_solicitado_total, SUM(importe_facturado) as importe_facturado_total')
                ->first();
            $trabajo->importe_pedido_total     = $sums?->importe_pedido_total;
            $trabajo->importe_solicitado_total  = $sums?->importe_solicitado_total;
            $trabajo->importe_facturado_total   = $sums?->importe_facturado_total;
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function prepareTrabajoStateData(array $data, ?Trabajo $trabajo = null): array
    {
        $estado = $data['estado'] ?? null;

        if (! $estado && $trabajo === null) {
            $data['estado'] = 'en_curso';
            $estado = 'en_curso';
        }

        if (
            $estado === 'terminado'
            && empty($data['fecha_terminacion'])
            && ! $trabajo?->fecha_terminacion
        ) {
            $data['fecha_terminacion'] = Carbon::today()->toDateString();
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function prepareTrabajoNumberData(array $data): array
    {
        $contextId = (int) ($data['id_contexto'] ?? 0);

        if ($contextId <= 0) {
            return $data;
        }

        $manualBaseNumber = $data['numero_trabajo'] ?? null;
        $manualOperationalNumber = trim((string) ($data['numero_trabajo_operativo'] ?? ''));

        ContextoCliente::query()
            ->whereKey($contextId)
            ->lockForUpdate()
            ->first();

        if ($manualBaseNumber === null || $manualBaseNumber === '') {
            [$generatedBaseNumber, $generatedOperationalNumber] = $this->nextGeneratedTrabajoNumbers($contextId);

            $data['numero_trabajo'] = $generatedBaseNumber;
            $data['numero_trabajo_operativo'] = $generatedOperationalNumber;

            return $data;
        }

        if ($manualOperationalNumber !== '') {
            $data['numero_trabajo_operativo'] = $manualOperationalNumber;

            return $data;
        }

        $candidateOperationalNumber = $this->formatOperationalTrabajoNumber($contextId, (int) $manualBaseNumber);
        $data['numero_trabajo_operativo'] = $this->operationalTrabajoNumberExists($candidateOperationalNumber)
            ? null
            : $candidateOperationalNumber;

        return $data;
    }

    /**
     * @return array{0:int,1:string}
     */
    private function nextGeneratedTrabajoNumbers(int $contextId): array
    {
        $nextBaseNumber = (int) Trabajo::query()
            ->where('id_contexto', $contextId)
            ->max('numero_trabajo');

        $nextBaseNumber = max(0, $nextBaseNumber) + 1;

        while (true) {
            $candidateOperationalNumber = $this->formatOperationalTrabajoNumber($contextId, $nextBaseNumber);

            $numberExistsInContext = Trabajo::query()
                ->where('id_contexto', $contextId)
                ->where('numero_trabajo', $nextBaseNumber)
                ->exists();

            if (! $numberExistsInContext && ! $this->operationalTrabajoNumberExists($candidateOperationalNumber)) {
                return [$nextBaseNumber, $candidateOperationalNumber];
            }

            $nextBaseNumber++;
        }
    }

    private function operationalTrabajoNumberExists(string $value): bool
    {
        return Trabajo::query()
            ->where('numero_trabajo_operativo', $value)
            ->exists();
    }

    private function formatOperationalTrabajoNumber(int $contextId, int $baseNumber): string
    {
        return sprintf('%s-%06d', $this->trabajoOperationalPrefix($contextId), $baseNumber);
    }

    private function trabajoOperationalPrefix(int $contextId): string
    {
        return match (ContextGuard::workspaceKeyForContextId($contextId)) {
            'moeve' => 'MOE',
            'repsol' => 'REP',
            default => 'OTR',
        };
    }

    private function normalizePatchTimestamp(?string $timestamp): ?string
    {
        if (! $timestamp) {
            return null;
        }

        try {
            return Carbon::parse($timestamp)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return trim($timestamp);
        }
    }

    private function formatPatchValue(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return $value;
    }

    private function assertTrabajoStateMutationAllowed(?User $user, string $nextState, bool $asJson = false): ?JsonResponse
    {
        $allowed = match ($nextState) {
            'terminado' => TrabajoPermission::canMarkFinished($user) || TrabajoPermission::canChangeState($user),
            'en_curso', 'cancelado' => TrabajoPermission::canChangeState($user),
            default => false,
        };

        if ($allowed) {
            return null;
        }

        if ($asJson) {
            return response()->json([
                'message' => 'No tienes permiso para cambiar manualmente el estado de este trabajo.',
            ], 403);
        }

        return response()->json([
            'message' => 'No tienes permiso para cambiar manualmente el estado de este trabajo.',
        ], 403);
    }

    /**
     * @param array<int, int|string> $accessibleContextIds
     * @return array<string, array<int, array<string, int|string|null>>>
     */
    private function buildFormCatalogs(array $accessibleContextIds): array
    {
        return [
            'contratos' => Contrato::query()
                ->whereIn('id_contexto', $accessibleContextIds)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(['id_contrato', 'id_contexto', 'nombre', 'codigo_contrato'])
                ->map(fn (Contrato $contrato): array => [
                    'id' => $contrato->id_contrato,
                    'id_contexto' => $contrato->id_contexto,
                    'nombre' => $contrato->nombre,
                    'codigo' => $contrato->codigo_contrato,
                ])
                ->values()
                ->all(),
            'tiposDocumento' => TipoDocumento::query()
                ->whereIn('id_contexto', $accessibleContextIds)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(['id_tipo_documento', 'id_contexto', 'nombre', 'codigo'])
                ->map(fn (TipoDocumento $tipo): array => [
                    'id' => $tipo->id_tipo_documento,
                    'id_contexto' => $tipo->id_contexto,
                    'nombre' => $tipo->nombre,
                    'codigo' => $tipo->codigo,
                ])
                ->values()
                ->all(),
            'tiposTrabajo' => TipoTrabajo::query()
                ->whereIn('id_contexto', $accessibleContextIds)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(['id_tipo_trabajo', 'id_contexto', 'id_tipo_documento', 'nombre', 'codigo'])
                ->map(fn (TipoTrabajo $tipo): array => [
                    'id' => $tipo->id_tipo_trabajo,
                    'id_contexto' => $tipo->id_contexto,
                    'id_tipo_documento' => $tipo->id_tipo_documento,
                    'nombre' => $tipo->nombre,
                    'codigo' => $tipo->codigo,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param array<int, int|string> $contextIds
     * @return array<string, array<int, array<string, int|string|null>>>
     */
    private function buildExcelCreationCatalogs(array $contextIds): array
    {
        $normalizedContextIds = array_map('intval', $contextIds);
        $hasTarifarioPredeterminadoColumn = Schema::hasColumn('tarifarios', 'es_predeterminado');

        $tarifariosQuery = Tarifario::query()
            ->join('contratos', 'tarifarios.id_contrato', '=', 'contratos.id_contrato')
            ->whereIn('tarifarios.id_contexto', $normalizedContextIds)
            ->where('tarifarios.activo', true)
            ->where('contratos.activo', true)
            ->orderBy('contratos.id_empresa_cliente')
            ->orderBy('contratos.nombre');

        if ($hasTarifarioPredeterminadoColumn) {
            $tarifariosQuery->orderByDesc('tarifarios.es_predeterminado');
        }

        $tarifariosQuery->orderBy('tarifarios.nombre');

        $tarifarioColumns = [
            'tarifarios.id_tarifario',
            'tarifarios.id_contexto',
            'tarifarios.id_contrato',
            'tarifarios.nombre as nombre_tarifario',
            'contratos.id_empresa_cliente',
            'contratos.nombre as nombre_contrato',
            'contratos.codigo_contrato',
        ];

        if ($hasTarifarioPredeterminadoColumn) {
            $tarifarioColumns[] = 'tarifarios.es_predeterminado';
        }

        return [
            'estaciones' => EstacionServicio::query()
                ->whereIn('id_contexto', $normalizedContextIds)
                ->orderBy('codigo_estacion')
                ->orderBy('nombre')
                ->limit(500)
                ->get(['id_estacion_servicio', 'id_contexto', 'id_empresa_cliente', 'codigo_estacion', 'nombre', 'poblacion', 'provincia'])
                ->map(fn (EstacionServicio $estacion): array => [
                    'id' => $estacion->id_estacion_servicio,
                    'id_contexto' => $estacion->id_contexto,
                    'id_empresa_cliente' => $estacion->id_empresa_cliente,
                    'codigo' => $estacion->codigo_estacion,
                    'nombre' => $estacion->nombre,
                    'municipio' => $estacion->poblacion,
                    'provincia' => $estacion->provincia,
                ])
                ->values()
                ->all(),
            'pedidos' => Pedido::query()
                ->whereIn('id_contexto', $normalizedContextIds)
                ->orderByDesc('fecha_solicitud')
                ->orderByDesc('id_pedido')
                ->limit(1000)
                ->get(['id_pedido', 'id_contexto', 'id_trabajo', 'numero_pedido', 'fecha_solicitud', 'importe_pedido'])
                ->map(fn (Pedido $pedido): array => [
                    'id' => $pedido->id_pedido,
                    'id_contexto' => $pedido->id_contexto,
                    'id_trabajo' => $pedido->id_trabajo,
                    'numero' => $pedido->numero_pedido,
                    'fecha_solicitud' => $pedido->fecha_solicitud?->format('Y-m-d'),
                    'importe_pedido' => $pedido->importe_pedido,
                ])
                ->values()
                ->all(),
            'tarifarios' => $tarifariosQuery
                ->get($tarifarioColumns)
                ->map(fn ($tarifario): array => [
                    'id' => (int) $tarifario->id_tarifario,
                    'id_contexto' => (int) $tarifario->id_contexto,
                    'id_contrato' => (int) $tarifario->id_contrato,
                    'id_empresa_cliente' => (int) $tarifario->id_empresa_cliente,
                    'nombre_tarifa' => $tarifario->nombre_tarifario,
                    'nombre_contrato' => $tarifario->nombre_contrato,
                    'codigo_contrato' => $tarifario->codigo_contrato,
                    'is_default' => $hasTarifarioPredeterminadoColumn
                        ? (bool) $tarifario->es_predeterminado
                        : false,
                ])
                ->values()
                ->all(),
            ...$this->buildFormCatalogs($normalizedContextIds),
        ];
    }
}
