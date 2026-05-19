<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EstacionStoreRequest;
use App\Http\Requests\Api\EstacionUpdateRequest;
use App\Http\Resources\Api\EstacionResource;
use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\Trabajo;
use App\Services\AuditLogger;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstacionController extends Controller
{
    use ApiResponse;

    private const AUDIT_FIELDS = [
        'id_estacion_servicio',
        'id_contexto',
        'id_empresa_cliente',
        'codigo_estacion',
        'nombre',
        'direccion',
        'poblacion',
        'provincia',
        'estado',
        'f_baja',
        'activo',
    ];

    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 10), 1), 100);
        $search = trim((string) $request->input('search', ''));
        $clienteId = $request->integer('cliente_id');
        $operador = trim((string) $request->input('operador', ''));
        $municipio = trim((string) $request->input('municipio', ''));
        $provincia = trim((string) $request->input('provincia', ''));
        $codigo = trim((string) $request->input('codigo', ''));
        $activo = $request->query('activo');

        $estaciones = EstacionServicio::query()
            ->with(['empresa', 'contexto'])
            ->when($clienteId > 0, function ($query) use ($clienteId): void {
                $query->where('id_empresa_cliente', $clienteId);
            })
            ->when($operador !== '', function ($query) use ($operador): void {
                $query->whereHas('empresa', function ($empresaQuery) use ($operador): void {
                    $empresaQuery->where('nombre_comercial', 'like', "%{$operador}%");
                });
            })
            ->when($municipio !== '', function ($query) use ($municipio): void {
                $query->where('poblacion', 'like', "%{$municipio}%");
            })
            ->when($provincia !== '', function ($query) use ($provincia): void {
                $query->where('provincia', 'like', "%{$provincia}%");
            })
            ->when($codigo !== '', function ($query) use ($codigo): void {
                $query->where('codigo_estacion', 'like', "%{$codigo}%");
            })
            ->when($activo !== null && $activo !== '', function ($query) use ($activo): void {
                $query->where('activo', filter_var($activo, FILTER_VALIDATE_BOOL));
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhere('codigo_estacion', 'like', "%{$search}%")
                        ->orWhere('direccion', 'like', "%{$search}%")
                        ->orWhere('poblacion', 'like', "%{$search}%")
                        ->orWhere('provincia', 'like', "%{$search}%")
                        ->orWhereHas('empresa', function ($empresaQuery) use ($search): void {
                            $empresaQuery
                                ->where('nombre', 'like', "%{$search}%")
                                ->orWhere('nombre_comercial', 'like', "%{$search}%")
                                ->orWhere('razon_social', 'like', "%{$search}%");
                        })
                        ->orWhereHas('contexto', function ($contextoQuery) use ($search): void {
                            $contextoQuery
                                ->where('nombre', 'like', "%{$search}%")
                                ->orWhere('codigo', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('activo')
            ->orderBy('codigo_estacion')
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();

        return $this->paginatedResponse(
            $estaciones,
            EstacionResource::collection($estaciones->getCollection())->resolve(),
            'Listado de estaciones obtenido',
        );
    }

    public function store(EstacionStoreRequest $request): JsonResponse
    {
        $data = $this->normalizeLifecycleData($request->validated());
        $data['id_contexto'] = $this->resolveContextIdForCliente((int) $data['id_empresa_cliente']) ?? $request->user()?->id_contexto;

        $estacion = EstacionServicio::create($data);
        $estacion->load(['empresa', 'contexto']);

        $snapshot = $this->auditLogger->snapshotModel($estacion, self::AUDIT_FIELDS);
        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => 'crear',
            'modulo' => 'estaciones',
            'tabla' => 'estaciones_servicio',
            'entity_type' => EstacionServicio::class,
            'entity_id' => $estacion->id_estacion_servicio,
            'registro_id' => $estacion->id_estacion_servicio,
            'campo' => 'id_estacion_servicio',
            'valor_nuevo' => $estacion->id_estacion_servicio,
            'datos_nuevos' => $snapshot,
            'descripcion' => 'Alta de estacion.',
            'id_contexto' => $estacion->id_contexto,
        ], $request);

        return $this->successResponse(
            (new EstacionResource($estacion))->resolve(),
            'Estacion creada correctamente',
            201,
        );
    }

    public function show(EstacionServicio $estacion): JsonResponse
    {
        return $this->successResponse(
            (new EstacionResource($estacion->load(['empresa', 'contexto'])))->resolve(),
            'Detalle de estacion obtenido',
        );
    }

    public function update(EstacionUpdateRequest $request, EstacionServicio $estacion): JsonResponse
    {
        $before = $this->auditLogger->snapshotModel($estacion, self::AUDIT_FIELDS);
        $data = $this->normalizeLifecycleData($request->validated(), $estacion);

        if (array_key_exists('id_empresa_cliente', $data)) {
            $data['id_contexto'] = $this->resolveContextIdForCliente((int) $data['id_empresa_cliente']) ?? $estacion->id_contexto;
        }

        $estacion->update($data);
        $estacion->refresh()->load(['empresa', 'contexto']);

        $after = $this->auditLogger->snapshotModel($estacion, self::AUDIT_FIELDS);
        $firstChange = $this->auditLogger->resolveFirstChange($before, $after);
        $statusChanged = ($before['activo'] ?? null) !== ($after['activo'] ?? null)
            || ($before['estado'] ?? null) !== ($after['estado'] ?? null);

        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => $statusChanged ? 'cambiar_estado' : 'actualizar',
            'modulo' => 'estaciones',
            'tabla' => 'estaciones_servicio',
            'entity_type' => EstacionServicio::class,
            'entity_id' => $estacion->id_estacion_servicio,
            'registro_id' => $estacion->id_estacion_servicio,
            'campo' => $firstChange['campo'],
            'valor_anterior' => $firstChange['valor_anterior'],
            'valor_nuevo' => $firstChange['valor_nuevo'],
            'datos_anteriores' => $before,
            'datos_nuevos' => $after,
            'descripcion' => $this->auditLogger->buildChangedFieldsDescription($before, $after, 'Actualizacion de estacion'),
            'id_contexto' => $estacion->id_contexto,
        ], $request);

        return $this->successResponse(
            (new EstacionResource($estacion))->resolve(),
            'Estacion actualizada correctamente',
        );
    }

    public function destroy(Request $request, EstacionServicio $estacion): JsonResponse
    {
        $hasTrabajos = Trabajo::withoutGlobalScopes()
            ->where('id_estacion_servicio', $estacion->id_estacion_servicio)
            ->exists();

        $before = $this->auditLogger->snapshotModel($estacion, self::AUDIT_FIELDS);

        $estacion->forceFill([
            'activo' => false,
            'f_baja' => $estacion->f_baja ?? now()->toDateString(),
            'estado' => $estacion->estado ?: 'inactiva',
        ])->save();

        $after = $this->auditLogger->snapshotModel($estacion->fresh(), self::AUDIT_FIELDS);

        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => 'desactivar',
            'modulo' => 'estaciones',
            'tabla' => 'estaciones_servicio',
            'entity_type' => EstacionServicio::class,
            'entity_id' => $estacion->id_estacion_servicio,
            'registro_id' => $estacion->id_estacion_servicio,
            'campo' => 'activo',
            'valor_anterior' => $before['activo'] ?? null,
            'valor_nuevo' => $after['activo'] ?? null,
            'datos_anteriores' => $before,
            'datos_nuevos' => $after,
            'descripcion' => $hasTrabajos
                ? 'Estacion desactivada. Conserva trabajos y trazabilidad historica.'
                : 'Estacion desactivada sin borrado fisico.',
            'id_contexto' => $estacion->id_contexto,
        ], $request);

        $message = $hasTrabajos
            ? 'Estacion desactivada correctamente. No se borra porque tiene historial asociado.'
            : 'Estacion desactivada correctamente.';

        return $this->successResponse(
            (new EstacionResource($estacion->fresh()->load(['empresa', 'contexto'])))->resolve(),
            $message
        );
    }

    private function normalizeLifecycleData(array $data, ?EstacionServicio $estacion = null): array
    {
        $activo = array_key_exists('activo', $data)
            ? (bool) $data['activo']
            : (bool) ($estacion?->activo ?? true);

        if (! $activo) {
            $data['f_baja'] = $estacion?->f_baja?->format('Y-m-d') ?? now()->toDateString();
            $data['estado'] = $data['estado'] ?? $estacion?->estado ?? 'inactiva';
        }

        return $data;
    }

    private function resolveContextIdForCliente(int $clienteId): ?int
    {
        if ($clienteId <= 0) {
            return null;
        }

        return Empresa::withoutGlobalScopes()
            ->where('id_empresa', $clienteId)
            ->value('id_contexto');
    }
}
