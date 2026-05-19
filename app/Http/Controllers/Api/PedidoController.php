<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePedidoRequest;
use App\Http\Requests\Api\UpdatePedidoRequest;
use App\Http\Resources\Api\PedidoResource;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Services\AuditLogger;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Controlador para la gestión de Pedidos.
 * Refactorizado para el esquema del Sprint 04.
 */
class PedidoController extends Controller
{
    use ApiResponse;

    private const AUDIT_FIELDS = [
        'id_pedido',
        'id_contexto',
        'id_trabajo',
        'id_tarifario',
        'numero_pedido',
        'fecha_solicitud',
        'fecha_recepcion',
        'importe_pedido',
        'importe_solicitado',
        'importe_facturado',
        'unidades_pedido',
        'unidades_solicitadas',
        'estado',
        'pedido_completo',
        'tiene_mas_de_1_item',
        'facturado_completo',
        'observaciones',
    ];

    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * Listado paginado de pedidos con filtros por trabajo, estado y contexto.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 10), 1), 100);
        $search = trim((string) $request->input('search', ''));
        $estado = trim((string) $request->input('estado', ''));
        $trabajoId = $request->integer('id_trabajo');

        $pedidos = Pedido::query()
            ->with(['items' => function ($query) {
                $query->withCount('facturaItems')->orderBy('id_pedido_item');
            }, 'trabajo.estacion'])
            ->when($trabajoId > 0, function ($query) use ($trabajoId): void {
                $query->where('id_trabajo', $trabajoId);
            })
            ->when($estado !== '', function ($query) use ($estado): void {
                $query->where('estado', $estado);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('numero_pedido', 'like', "%{$search}%")
                        ->orWhere('observaciones', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return $this->successResponse(PedidoResource::collection($pedidos));
    }

    public function show(Request $request, Pedido $pedido): JsonResponse
    {
        $this->ensurePedidoAccess($request, $pedido);

        return $this->successResponse(new PedidoResource($this->loadPedidoRelations($pedido)));
    }

    /**
     * Almacena un nuevo pedido con sus líneas anidadas.
     */
    public function store(StorePedidoRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $pedido = new Pedido();
            $trabajo = \App\Models\Trabajo::findOrFail($request->input('id_trabajo'));
            $pedido->id_contexto = $trabajo->id_contexto;

            $this->mapRequestToModel($request, $pedido);
            $pedido->save();

            if ($request->has('items')) {
                $this->syncItems($pedido, $request->input('items'));
            }

            $after = $this->auditLogger->snapshotModel($pedido->fresh(), self::AUDIT_FIELDS);
            $this->auditLogger->log([
                'user' => $request->user(),
                'accion' => 'crear',
                'modulo' => 'pedidos',
                'tabla' => 'pedidos',
                'entity_type' => Pedido::class,
                'entity_id' => $pedido->id_pedido,
                'registro_id' => $pedido->id_pedido,
                'campo' => 'id_pedido',
                'valor_nuevo' => $pedido->id_pedido,
                'datos_nuevos' => $after,
                'descripcion' => 'Alta de pedido.',
                'id_contexto' => $pedido->id_contexto,
            ], $request);

            return (new PedidoResource($this->loadPedidoRelations($pedido)))->response()->setStatusCode(201);
        });
    }

    public function update(UpdatePedidoRequest $request, Pedido $pedido): JsonResponse
    {
        $this->ensurePedidoAccess($request, $pedido);

        return DB::transaction(function () use ($request, $pedido) {
            $before = $this->auditLogger->snapshotModel($pedido, self::AUDIT_FIELDS);

            if ($request->filled('id_trabajo')) {
                $trabajo = \App\Models\Trabajo::findOrFail($request->input('id_trabajo'));
                $pedido->id_contexto = $trabajo->id_contexto;
            }

            $this->mapRequestToModel($request, $pedido);
            $pedido->save();

            $itemSyncSummary = null;

            if ($request->has('items')) {
                $itemSyncSummary = $this->syncItems($pedido, $request->input('items', []));
            }

            $pedido->refresh();
            $after = $this->auditLogger->snapshotModel($pedido, self::AUDIT_FIELDS);
            $firstChange = $this->auditLogger->resolveFirstChange($before, $after);
            $statusChanged = ($before['estado'] ?? null) !== ($after['estado'] ?? null);
            $description = $this->auditLogger->buildChangedFieldsDescription($before, $after, 'Actualizacion de pedido');

            if ($itemSyncSummary !== null) {
                $description .= sprintf(
                    ' Items: %d actualizados, %d creados, %d eliminados, %d bloqueados por facturacion.',
                    $itemSyncSummary['updated'],
                    $itemSyncSummary['created'],
                    $itemSyncSummary['deleted'],
                    count($itemSyncSummary['blocked'])
                );
            }

            $this->auditLogger->log([
                'user' => $request->user(),
                'accion' => $statusChanged ? 'cambiar_estado' : 'actualizar',
                'modulo' => 'pedidos',
                'tabla' => 'pedidos',
                'entity_type' => Pedido::class,
                'entity_id' => $pedido->id_pedido,
                'registro_id' => $pedido->id_pedido,
                'campo' => $firstChange['campo'],
                'valor_anterior' => $firstChange['valor_anterior'],
                'valor_nuevo' => $firstChange['valor_nuevo'],
                'datos_anteriores' => $before,
                'datos_nuevos' => $after,
                'descripcion' => $description,
                'id_contexto' => $pedido->id_contexto,
            ], $request);

            $blockedItems = $itemSyncSummary['blocked'] ?? [];
            $message = $blockedItems === []
                ? 'Operación exitosa'
                : 'Pedido actualizado. No se eliminaron las lineas ya vinculadas a factura.';
            $meta = $blockedItems === [] ? [] : ['items_bloqueados' => $blockedItems];

            return $this->successResponse(new PedidoResource($this->loadPedidoRelations($pedido)), $message, 200, $meta);
        });
    }

    public function destroy(Request $request, Pedido $pedido): JsonResponse
    {
        $this->ensurePedidoAccess($request, $pedido);

        if ($pedido->items()->whereHas('facturaItems')->exists()) {
            return $this->errorResponse(
                'No se puede eliminar el pedido porque contiene lineas ya vinculadas a factura.',
                'PEDIDO_ITEMS_FACTURADOS',
                [],
                409
            );
        }

        DB::transaction(function () use ($request, $pedido): void {
            $before = $this->auditLogger->snapshotModel($pedido, self::AUDIT_FIELDS);

            $pedido->estado = 'cancelado';
            $pedido->save();

            $after = $this->auditLogger->snapshotModel($pedido->fresh(), self::AUDIT_FIELDS);

            $this->auditLogger->log([
                'user' => $request->user(),
                'accion' => 'cambiar_estado',
                'modulo' => 'pedidos',
                'tabla' => 'pedidos',
                'entity_type' => Pedido::class,
                'entity_id' => $pedido->id_pedido,
                'registro_id' => $pedido->id_pedido,
                'campo' => 'estado',
                'valor_anterior' => $before['estado'] ?? null,
                'valor_nuevo' => $after['estado'] ?? null,
                'datos_anteriores' => $before,
                'datos_nuevos' => $after,
                'descripcion' => 'Pedido cancelado desde accion de eliminacion restringida.',
                'id_contexto' => $pedido->id_contexto,
            ], $request);
        });

        return $this->successResponse(
            new PedidoResource($this->loadPedidoRelations($pedido->fresh())),
            'Pedido cancelado correctamente. No se ha borrado el historico.'
        );
    }

    /**
     * Mapeo de los campos del Request a las columnas REALES de la tabla pedidos.
     */
    private function mapRequestToModel(Request $request, Pedido $pedido): void
    {
        $fields = [
            'id_trabajo',
            'id_tarifario',
            'numero_pedido',
            'fecha_solicitud',
            'fecha_recepcion',
            'importe_pedido',
            'importe_solicitado',
            'importe_facturado',
            'unidades_pedido',
            'unidades_solicitadas',
            'estado',
            'pedido_completo',
            'tiene_mas_de_1_item',
            'facturado_completo',
            'observaciones',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $pedido->{$field} = $request->input($field);
            }
        }
    }

    /**
     * Sincroniza las líneas del pedido (pedido_items). (ACTUALIZADO)
     */
    private function syncItems(Pedido $pedido, array $items): array
    {
        $existingItems = $pedido->items()
            ->withCount('facturaItems')
            ->get()
            ->keyBy('id_pedido_item');

        $sentIds = collect($items)
            ->pluck('id_pedido_item')
            ->filter(fn($id) => $id !== null && $id !== '')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        $unknownIds = $sentIds
            ->reject(fn(int $id) => $existingItems->has($id))
            ->values();

        if ($unknownIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => [
                    'Hay lineas que no pertenecen a este pedido: ' . $unknownIds->implode(', ') . '.',
                ],
            ]);
        }

        $summary = [
            'updated' => 0,
            'created' => 0,
            'deleted' => 0,
            'blocked' => [],
        ];

        foreach (array_values($items) as $itemData) {
            $itemId = isset($itemData['id_pedido_item']) && $itemData['id_pedido_item'] !== ''
                ? (int) $itemData['id_pedido_item']
                : null;
            $item = $itemId !== null ? $existingItems->get($itemId) : new PedidoItem();

            $this->mapItemDataToModel($pedido, $item, $itemData);
            $item->save();

            if ($itemId !== null) {
                $summary['updated']++;
            } else {
                $summary['created']++;
            }
        }

        $itemsToRemove = $existingItems->except($sentIds->all());

        foreach ($itemsToRemove as $itemToRemove) {
            if ((int) ($itemToRemove->factura_items_count ?? 0) > 0) {
                $summary['blocked'][] = [
                    'id_pedido_item' => (int) $itemToRemove->id_pedido_item,
                    'descripcion_servicio' => $itemToRemove->descripcion_servicio,
                ];

                continue;
            }

            $itemToRemove->delete();
            $summary['deleted']++;
        }

        return $summary;
    }

    private function mapItemDataToModel(Pedido $pedido, PedidoItem $item, array $itemData): void
    {
        $cantidad = isset($itemData['cantidad']) ? (float) $itemData['cantidad'] : 1.0;
        $precioUnitario = isset($itemData['precio_unitario']) ? (float) $itemData['precio_unitario'] : 0.0;
        $totalLinea = isset($itemData['total_linea'])
            ? (float) $itemData['total_linea']
            : ($cantidad * $precioUnitario);

        $item->id_contexto = $pedido->id_contexto;
        $item->id_pedido = $pedido->id_pedido;
        $item->id_tarifario_linea = $itemData['id_tarifario_linea'] ?? null;
        $item->codigo_servicio = $itemData['codigo_servicio'] ?? null;
        $item->numero_tarifa = $itemData['numero_tarifa'] ?? null;
        $item->descripcion_servicio = $itemData['descripcion_servicio'] ?? null;
        $item->setAttribute('cantidad', number_format($cantidad, 3, '.', ''));
        $item->setAttribute('precio_unitario', number_format($precioUnitario, 2, '.', ''));
        $item->setAttribute('total_linea', number_format($totalLinea, 2, '.', ''));
    }

    private function loadPedidoRelations(Pedido $pedido): Pedido
    {
        return $pedido->load([
            'items' => function ($query): void {
                $query->withCount('facturaItems')->orderBy('id_pedido_item');
            },
            'trabajo.estacion',
        ]);
    }

    private function ensurePedidoAccess(Request $request, Pedido $pedido): void
    {
        $accessibleContextIds = array_map('intval', $request->user()->getActiveContextIds());

        if (! in_array((int) $pedido->id_contexto, $accessibleContextIds, true)) {
            abort(403, 'No autorizado. Violación de aislamiento de contexto.');
        }
    }
}
