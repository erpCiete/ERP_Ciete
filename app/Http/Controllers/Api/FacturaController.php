<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFacturaRequest;
use App\Http\Requests\Api\UpdateFacturaRequest;
use App\Http\Resources\Api\FacturaResource;
use App\Models\ContratoEmpresaFacturadora;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\PedidoItem;
use App\Models\Trabajo;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\TrabajoStateService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacturaController extends Controller
{
    use ApiResponse;

    private const MONEY_TOLERANCE = 0.01;
    private const UNITS_TOLERANCE = 0.001;

    private const AUDIT_FIELDS = [
        'id_factura',
        'id_contexto',
        'id_trabajo',
        'id_contrato',
        'id_empresa_facturadora',
        'id_empresa_cliente',
        'numero_factura',
        'numero_factura_ccp',
        'serie',
        'orden_factura',
        'fecha_solicitud',
        'fecha_emision',
        'fecha_vencimiento',
        'importe',
        'base_imponible',
        'iva',
        'retencion',
        'total',
        'estado',
        'autofactura',
        'sociedad',
        'observaciones',
    ];

    private const ITEM_AUDIT_FIELDS = [
        'id_factura_item',
        'id_factura',
        'id_pedido_item',
        'unidades_facturadas',
        'importe_facturado',
        'observaciones',
    ];

    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TrabajoStateService $trabajoStateService,
    ) {}

    // id_trabajo en facturas es una cabecera auxiliar derivada del primer ítem,
    // nunca el origen del detalle. El detalle real vive en factura_items.
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 10), 1), 100);
        $search = trim((string) $request->input('search', ''));
        $estado = trim((string) $request->input('estado', ''));
        $trabajoId = $request->integer('id_trabajo');
        $empresaId = $request->integer('id_empresa_cliente');

        $facturas = Factura::query()
            ->withSociedadCifValidada()
            ->with($this->facturaRelations())
            ->when($trabajoId > 0, function ($query) use ($trabajoId): void {
                $query->whereHas('items.pedidoItem.pedido', fn($pedidoQuery) => $pedidoQuery->where('id_trabajo', $trabajoId));
            })
            ->when($empresaId > 0, function ($query) use ($empresaId): void {
                $query->where('id_empresa_cliente', $empresaId);
            })
            ->when($estado !== '', function ($query) use ($estado): void {
                $query->where('estado', $estado);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('numero_factura', 'like', "%{$search}%")
                        ->orWhere('numero_factura_ccp', 'like', "%{$search}%")
                        ->orWhere('observaciones', 'like', "%{$search}%")
                        ->orWhereHas('items.pedidoItem.pedido', fn($pedidoQuery) => $pedidoQuery->where('numero_pedido', 'like', "%{$search}%"))
                        ->orWhereHas('items.pedidoItem.pedido.trabajo', function ($trabajoQuery) use ($search): void {
                            $trabajoQuery
                                ->where('numero_trabajo', 'like', "%{$search}%")
                                ->orWhere('numero_trabajo_operativo', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return $this->successResponse(FacturaResource::collection($facturas));
    }

    public function store(StoreFacturaRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $factura = new Factura();
            $data = $request->validated();
            $itemsData = $request->input('items', []);
            $pedidoItems = $this->loadPedidoItemsForPayload($itemsData, $request);

            $this->fillFactura($factura, $data);
            $this->applyDerivedHeaderData($factura, $data, $pedidoItems);
            $this->validateEmpresaFacturadora($factura, $pedidoItems, $data, $pedidoItems->isNotEmpty());
            $factura->save();

            $itemSyncSummary = null;

            if ($request->has('items')) {
                $itemSyncSummary = $this->syncFacturaItems($request, $factura, $itemsData);
            }

            $this->trabajoStateService->syncPedidosAndTrabajosByPedidoIds($this->pedidoIdsForFactura($factura));

            $after = $this->auditLogger->snapshotModel($factura->fresh(), self::AUDIT_FIELDS);
            $description = 'Alta de factura.';

            if ($itemSyncSummary !== null) {
                $description .= sprintf(
                    ' Items: %d creados. Importe asignado: %.2f.',
                    $itemSyncSummary['created'],
                    $itemSyncSummary['assigned_after']
                );
            }

            $this->auditLogger->log([
                'user' => $request->user(),
                'accion' => 'crear',
                'modulo' => 'facturas',
                'tabla' => 'facturas',
                'entity_type' => Factura::class,
                'entity_id' => $factura->id_factura,
                'registro_id' => $factura->id_factura,
                'campo' => 'id_factura',
                'valor_nuevo' => $factura->id_factura,
                'datos_nuevos' => $after,
                'descripcion' => $description,
                'id_contexto' => $factura->id_contexto,
            ], $request);

            return (new FacturaResource($this->loadFacturaRelations($factura)))
                ->response()
                ->setStatusCode(201);
        });
    }

    public function show(Request $request, Factura $factura): JsonResponse
    {
        $accessibleContextIds = $this->accessibleContextIds($request);

        if (! in_array((int) $factura->id_contexto, $accessibleContextIds, true)) {
            return $this->errorResponse('No autorizado. Violación de aislamiento de contexto.', 'CONTEXT_FORBIDDEN', [], 403);
        }

        return $this->successResponse(new FacturaResource($this->loadFacturaRelations($factura)));
    }

    public function exportIndex(Request $request): StreamedResponse
    {
        $facturasQuery = $this->buildExportQuery($request);
        $facturasCount = (clone $facturasQuery)->count();
        $selectedIds = $this->parseExportIds($request);

        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => 'exportar',
            'modulo' => 'facturas',
            'tabla' => 'facturas',
            'descripcion' => sprintf(
                'Exportacion CSV de %d factura(s)%s.',
                $facturasCount,
                $selectedIds === [] ? ' filtradas' : ' seleccionadas'
            ),
            'datos_nuevos' => [
                'ids' => $selectedIds,
                'filtros' => $request->only(['search', 'estado', 'id_trabajo', 'id_empresa_cliente', 'fecha_desde', 'fecha_hasta']),
            ],
            'id_contexto' => is_int($request->user()->getActiveContextSelection())
                ? $request->user()->getActiveContextSelection()
                : null,
        ], $request);

        return response()->streamDownload(function () use ($facturasQuery): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $this->facturaListExportHeaders(), ';');

            (clone $facturasQuery)->chunk(200, function (Collection $facturas) use ($handle): void {
                foreach ($facturas as $factura) {
                    fputcsv($handle, $this->facturaListExportRow($factura), ';');
                }
            });

            fclose($handle);
        }, 'facturas_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportDetail(Request $request, Factura $factura): StreamedResponse
    {
        $accessibleContextIds = $this->accessibleContextIds($request);

        if (! in_array((int) $factura->id_contexto, $accessibleContextIds, true)) {
            abort(403);
        }

        $factura = $this->loadFacturaRelations($factura);

        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => 'exportar',
            'modulo' => 'facturas',
            'tabla' => 'facturas',
            'entity_type' => Factura::class,
            'entity_id' => $factura->id_factura,
            'registro_id' => $factura->id_factura,
            'descripcion' => sprintf('Exportacion CSV detallada de factura %s.', $this->facturaNumber($factura)),
            'datos_nuevos' => [
                'id_factura' => $factura->id_factura,
                'numero_factura' => $this->facturaNumber($factura),
            ],
            'id_contexto' => $factura->id_contexto,
        ], $request);

        $filename = sprintf(
            'factura_%s_%s.csv',
            preg_replace('/[^A-Za-z0-9_-]+/', '_', $this->facturaNumber($factura)),
            now()->format('Ymd_His')
        );

        return response()->streamDownload(function () use ($factura): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Campo', 'Valor'], ';');
            foreach ($this->facturaDetailHeaderRows($factura) as $row) {
                fputcsv($handle, $row, ';');
            }

            fputcsv($handle, [], ';');
            fputcsv($handle, ['Lineas'], ';');
            fputcsv($handle, $this->facturaDetailLineHeaders(), ';');

            foreach ($this->facturaDetailLineRows($factura) as $row) {
                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function update(UpdateFacturaRequest $request, Factura $factura): JsonResponse
    {
        $accessibleContextIds = $this->accessibleContextIds($request);

        if (! in_array((int) $factura->id_contexto, $accessibleContextIds, true)) {
            return $this->errorResponse('No autorizado. Violación de aislamiento de contexto.', 'CONTEXT_FORBIDDEN', [], 403);
        }

        return DB::transaction(function () use ($request, $factura) {
            $before = $this->auditLogger->snapshotModel($factura, self::AUDIT_FIELDS);
            $assignedBefore = $this->assignedAmount($factura);
            $pedidoIdsBefore = $this->pedidoIdsForFactura($factura);
            $data = $request->validated();
            $itemsData = $request->input('items', []);
            $pedidoItems = $this->loadPedidoItemsForPayload($itemsData, $request);

            $this->fillFactura($factura, $data);

            if ($request->has('items')) {
                $this->applyDerivedHeaderData($factura, $data, $pedidoItems);
            }

            if (array_key_exists('id_empresa_facturadora', $data) || $request->has('items')) {
                $validationItems = $pedidoItems->isNotEmpty()
                    ? $pedidoItems
                    : $this->loadPedidoItemsForExistingFactura($factura);

                $this->validateEmpresaFacturadora($factura, $validationItems, $data, $validationItems->isNotEmpty());
            }

            $factura->save();

            $itemSyncSummary = null;

            if ($request->has('items')) {
                $itemSyncSummary = $this->syncFacturaItems($request, $factura, $itemsData);
            }

            $this->trabajoStateService->syncPedidosAndTrabajosByPedidoIds(
                $pedidoIdsBefore->merge($this->pedidoIdsForFactura($factura))->all()
            );

            $factura->refresh();
            $after = $this->auditLogger->snapshotModel($factura, self::AUDIT_FIELDS);
            $firstChange = $this->auditLogger->resolveFirstChange($before, $after);
            $statusChanged = ($before['estado'] ?? null) !== ($after['estado'] ?? null);
            $description = $this->auditLogger->buildChangedFieldsDescription($before, $after, 'Actualizacion de factura');

            if ($itemSyncSummary !== null) {
                $description .= sprintf(
                    ' Items: %d actualizados, %d creados, %d eliminados. Importe asignado: %.2f -> %.2f.',
                    $itemSyncSummary['updated'],
                    $itemSyncSummary['created'],
                    $itemSyncSummary['deleted'],
                    $assignedBefore,
                    $itemSyncSummary['assigned_after']
                );
            }

            $this->auditLogger->log([
                'user' => $request->user(),
                'accion' => $statusChanged ? 'cambiar_estado' : 'actualizar',
                'modulo' => 'facturas',
                'tabla' => 'facturas',
                'entity_type' => Factura::class,
                'entity_id' => $factura->id_factura,
                'registro_id' => $factura->id_factura,
                'campo' => $firstChange['campo'],
                'valor_anterior' => $firstChange['valor_anterior'],
                'valor_nuevo' => $firstChange['valor_nuevo'],
                'datos_anteriores' => $before,
                'datos_nuevos' => $after,
                'descripcion' => $description,
                'id_contexto' => $factura->id_contexto,
            ], $request);

            return $this->successResponse(new FacturaResource($this->loadFacturaRelations($factura)));
        });
    }

    public function destroy(Request $request, Factura $factura): JsonResponse
    {
        $accessibleContextIds = $this->accessibleContextIds($request);

        if (! in_array((int) $factura->id_contexto, $accessibleContextIds, true)) {
            return $this->errorResponse('No autorizado. Violación de aislamiento de contexto.', 'CONTEXT_FORBIDDEN', [], 403);
        }

        DB::transaction(function () use ($request, $factura): void {
            $before = $this->auditLogger->snapshotModel($factura, self::AUDIT_FIELDS);
            $pedidoIds = $this->pedidoIdsForFactura($factura);

            $factura->estado = 'anulada';
            $factura->save();

            $after = $this->auditLogger->snapshotModel($factura->fresh(), self::AUDIT_FIELDS);

            $this->auditLogger->log([
                'user' => $request->user(),
                'accion' => 'cambiar_estado',
                'modulo' => 'facturas',
                'tabla' => 'facturas',
                'entity_type' => Factura::class,
                'entity_id' => $factura->id_factura,
                'registro_id' => $factura->id_factura,
                'campo' => 'estado',
                'valor_anterior' => $before['estado'] ?? null,
                'valor_nuevo' => $after['estado'] ?? null,
                'datos_anteriores' => $before,
                'datos_nuevos' => $after,
                'descripcion' => 'Factura anulada desde accion de eliminacion restringida.',
                'id_contexto' => $factura->id_contexto,
            ], $request);

            $this->trabajoStateService->syncPedidosAndTrabajosByPedidoIds($pedidoIds);
        });

        return $this->successResponse(
            new FacturaResource($this->loadFacturaRelations($factura->fresh())),
            'Factura anulada correctamente. No se ha borrado la trazabilidad.'
        );
    }

    /**
     * Valida que la empresa facturadora está activa, tiene CIF y está
     * autorizada para el contrato derivado de los items.
     *
     * En el flujo funcional por factura_items la sociedad/CIF es obligatoria.
     *
     * @param  Collection<int, PedidoItem>  $pedidoItems
     * @param  array<string, mixed>         $data
     */
    private function validateEmpresaFacturadora(
        Factura $factura,
        Collection $pedidoItems,
        array $data,
        bool $requiresFacturadora
    ): void {
        $empresaId = $factura->id_empresa_facturadora;

        if (! $empresaId) {
            if ($requiresFacturadora) {
                throw ValidationException::withMessages([
                    'id_empresa_facturadora' => ['Selecciona la sociedad/CIF que emite la factura.'],
                ]);
            }

            return;
        }

        /** @var Empresa|null $empresa */
        $empresa = Empresa::query()
            ->where('id_empresa', $empresaId)
            ->first();

        if (! $empresa) {
            throw ValidationException::withMessages([
                'id_empresa_facturadora' => ['La sociedad/empresa facturadora no existe.'],
            ]);
        }

        if (! $empresa->activo) {
            throw ValidationException::withMessages([
                'id_empresa_facturadora' => ['La sociedad/empresa facturadora está inactiva y no puede usarse para facturar.'],
            ]);
        }

        if (trim((string) $empresa->cif) === '') {
            throw ValidationException::withMessages([
                'id_empresa_facturadora' => [
                    'La sociedad/empresa facturadora no tiene CIF registrado. '
                        . 'Registra el CIF antes de usarla para facturar.',
                ],
            ]);
        }

        // Validar que la empresa pertenece al contexto de la factura
        if ($factura->id_contexto && (int) $empresa->id_contexto !== (int) $factura->id_contexto) {
            throw ValidationException::withMessages([
                'id_empresa_facturadora' => [
                    'La sociedad/CIF seleccionada no pertenece al contexto (grupo operativo) de la factura.',
                ],
            ]);
        }

        // Validar que la empresa está permitida para el contrato/tarifa derivado de los items
        $contractId = $factura->id_contrato
            ?? ($pedidoItems->isNotEmpty() ? $this->singleContractIdFromItems($pedidoItems) : null);

        if (! $contractId) {
            if ($requiresFacturadora) {
                throw ValidationException::withMessages([
                    'items' => ['No se puede validar sociedad/CIF porque los items no tienen contrato o tarifa asociado.'],
                ]);
            }

            return;
        }

        $configuredQuery = ContratoEmpresaFacturadora::query()
            ->where('id_contrato', $contractId)
            ->where('id_contexto', (int) $factura->id_contexto);

        if (! (clone $configuredQuery)->exists()) {
            throw ValidationException::withMessages([
                'id_empresa_facturadora' => [
                    'No hay sociedades/CIF configuradas para el contrato o tarifa de los items facturados.',
                ],
            ]);
        }

        $isPermitted = (clone $configuredQuery)
            ->where('id_empresa', $empresaId)
            ->where('activo', true)
            ->exists();

        if (! $isPermitted) {
            throw ValidationException::withMessages([
                'id_empresa_facturadora' => [
                    'La sociedad/CIF seleccionada no está permitida para el contrato o tarifa de los ítems facturados.',
                ],
            ]);
        }
    }

    private function fillFactura(Factura $factura, array $data): void
    {
        $fields = [
            'id_contrato',
            'id_empresa_facturadora',
            'id_empresa_cliente',
            'numero_factura',
            'numero_factura_ccp',
            'serie',
            'orden_factura',
            'fecha_solicitud',
            'fecha_emision',
            'fecha_vencimiento',
            'importe',
            'base_imponible',
            'iva',
            'retencion',
            'total',
            'estado',
            'autofactura',
            'sociedad',
            'observaciones',
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $factura->{$field} = $data[$field];
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemsData
     * @return Collection<int, PedidoItem>
     */
    private function loadPedidoItemsForPayload(array $itemsData, Request $request): Collection
    {
        if ($itemsData === []) {
            return collect();
        }

        $accessibleContextIds = $this->accessibleContextIds($request);
        $ids = collect($itemsData)
            ->pluck('id_pedido_item')
            ->filter(fn($id) => $id !== null && $id !== '')
            ->map(fn($id) => (int) $id)
            ->values();

        $duplicatedIds = $ids->duplicates()->unique()->values();

        if ($duplicatedIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => ['No se puede incluir dos veces el mismo item de pedido: ' . $duplicatedIds->implode(', ') . '.'],
            ]);
        }

        $pedidoItems = PedidoItem::query()
            ->with([
                'pedido.trabajo.estacion',
                'pedido.trabajo.contrato',
                'pedido.tarifario',
                'tarifarioLinea.tarifario.contrato',
            ])
            ->whereIn('id_contexto', $accessibleContextIds)
            ->whereIn('id_pedido_item', $ids->all())
            ->get()
            ->keyBy('id_pedido_item');

        $missingIds = $ids
            ->unique()
            ->reject(fn(int $id) => $pedidoItems->has($id))
            ->values();

        if ($missingIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => ['Hay items de pedido inexistentes o fuera del contexto permitido: ' . $missingIds->implode(', ') . '.'],
            ]);
        }

        $invalidItems = $pedidoItems->filter(fn(PedidoItem $item) => $item->pedido === null || $item->pedido->trabajo === null);

        if ($invalidItems->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => ['Todos los items deben pertenecer a pedidos y trabajos existentes.'],
            ]);
        }

        $this->validateItemCompatibility($pedidoItems);

        return $pedidoItems;
    }

    /**
     * @param  Collection<int, PedidoItem>  $pedidoItems
     */
    private function validateItemCompatibility(Collection $pedidoItems): void
    {
        if ($pedidoItems->isEmpty()) {
            return;
        }

        $contextIds = $pedidoItems
            ->map(fn(PedidoItem $item) => (int) $item->id_contexto)
            ->unique()
            ->values();

        if ($contextIds->count() > 1) {
            throw ValidationException::withMessages([
                'items' => ['No se pueden mezclar items de contextos distintos en una misma factura.'],
            ]);
        }

        $contractIds = $pedidoItems
            ->map(function (PedidoItem $item): ?int {
                return $item->pedido?->trabajo?->id_contrato
                    ?? $item->pedido?->tarifario?->id_contrato
                    ?? $item->tarifarioLinea?->tarifario?->id_contrato;
            })
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($contractIds->count() > 1) {
            throw ValidationException::withMessages([
                'items' => ['Los items seleccionados pertenecen a contratos distintos o incompatibles.'],
            ]);
        }

        $tarifarioIds = $pedidoItems
            ->map(function (PedidoItem $item): ?int {
                return $item->pedido?->id_tarifario
                    ?? $item->pedido?->trabajo?->id_tarifario
                    ?? $item->tarifarioLinea?->id_tarifario;
            })
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($tarifarioIds->count() > 1) {
            throw ValidationException::withMessages([
                'items' => ['Los items seleccionados pertenecen a tarifarios distintos.'],
            ]);
        }
    }

    /**
     * @param  Collection<int, PedidoItem>  $pedidoItems
     */
    private function applyDerivedHeaderData(Factura $factura, array $data, Collection $pedidoItems): void
    {
        if ($pedidoItems->isEmpty()) {
            return;
        }

        /** @var PedidoItem $firstItem */
        $firstItem = $pedidoItems->first();
        $firstTrabajo = $firstItem->pedido?->trabajo;

        $factura->id_contexto = (int) $firstItem->id_contexto;
        // Cabecera auxiliar: siempre se deriva del primer pedido_item seleccionado.
        $factura->id_trabajo = $firstTrabajo?->id_trabajo;
        $factura->id_empresa_cliente = $firstTrabajo?->id_empresa_cliente ?? $data['id_empresa_cliente'] ?? $factura->id_empresa_cliente;
        $factura->id_contrato = $this->singleContractIdFromItems($pedidoItems) ?? $data['id_contrato'] ?? $factura->id_contrato;
    }

    /**
     * @param  Collection<int, PedidoItem>  $pedidoItems
     */
    private function singleContractIdFromItems(Collection $pedidoItems): ?int
    {
        $contractIds = $pedidoItems
            ->map(fn(PedidoItem $item) => $item->pedido?->trabajo?->id_contrato
                ?? $item->pedido?->tarifario?->id_contrato
                ?? $item->tarifarioLinea?->tarifario?->id_contrato)
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        return $contractIds->count() === 1 ? (int) $contractIds->first() : null;
    }

    /**
     * @return Collection<int, PedidoItem>
     */
    private function loadPedidoItemsForExistingFactura(Factura $factura): Collection
    {
        $factura->loadMissing([
            'items.pedidoItem.pedido.trabajo.estacion',
            'items.pedidoItem.pedido.trabajo.contrato',
            'items.pedidoItem.pedido.tarifario',
            'items.pedidoItem.tarifarioLinea.tarifario.contrato',
        ]);

        return $factura->items
            ->map(fn(FacturaItem $item) => $item->pedidoItem)
            ->filter()
            ->values();
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemsData
     * @return array{updated:int,created:int,deleted:int,assigned_after:float}
     */
    private function syncFacturaItems(Request $request, Factura $factura, array $itemsData): array
    {
        $existingItems = $factura->items()
            ->with('pedidoItem')
            ->get()
            ->keyBy('id_factura_item');

        $sentFacturaItemIds = collect($itemsData)
            ->pluck('id_factura_item')
            ->filter(fn($id) => $id !== null && $id !== '')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        $unknownFacturaItemIds = $sentFacturaItemIds
            ->reject(fn(int $id) => $existingItems->has($id))
            ->values();

        if ($unknownFacturaItemIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => ['Hay lineas que no pertenecen a esta factura: ' . $unknownFacturaItemIds->implode(', ') . '.'],
            ]);
        }

        $pedidoItems = $this->loadPedidoItemsForPayload($itemsData, $request);
        $this->validatePendingAmounts($itemsData, $pedidoItems);

        $summary = [
            'updated' => 0,
            'created' => 0,
            'deleted' => 0,
            'assigned_after' => 0.0,
        ];

        foreach (array_values($itemsData) as $itemData) {
            $facturaItemId = isset($itemData['id_factura_item']) && $itemData['id_factura_item'] !== ''
                ? (int) $itemData['id_factura_item']
                : null;
            $facturaItem = $facturaItemId !== null ? $existingItems->get($facturaItemId) : new FacturaItem();
            $before = $facturaItemId !== null
                ? $this->auditLogger->snapshotModel($facturaItem, self::ITEM_AUDIT_FIELDS)
                : null;

            $facturaItem->id_factura = $factura->id_factura;
            $facturaItem->id_pedido_item = (int) $itemData['id_pedido_item'];
            $facturaItem->unidades_facturadas = $this->nullableFloat($itemData['unidades_facturadas'] ?? null);
            $facturaItem->importe_facturado = (float) ($itemData['importe_facturado'] ?? 0);
            $facturaItem->observaciones = $itemData['observaciones'] ?? null;
            $facturaItem->save();

            $after = $this->auditLogger->snapshotModel($facturaItem->fresh(), self::ITEM_AUDIT_FIELDS);
            $this->logFacturaItemChange(
                $request,
                $facturaItemId === null ? 'crear' : 'actualizar',
                $factura,
                $before,
                $after,
                $facturaItem->id_factura_item
            );

            if ($facturaItemId === null) {
                $summary['created']++;
            } else {
                $summary['updated']++;
            }
        }

        $itemsToDelete = $existingItems->except($sentFacturaItemIds->all());

        foreach ($itemsToDelete as $itemToDelete) {
            $before = $this->auditLogger->snapshotModel($itemToDelete, self::ITEM_AUDIT_FIELDS);
            $deletedId = (int) $itemToDelete->id_factura_item;
            $itemToDelete->delete();
            $summary['deleted']++;

            $this->logFacturaItemChange($request, 'eliminar', $factura, $before, null, $deletedId);
        }

        $summary['assigned_after'] = $this->assignedAmount($factura->fresh());

        return $summary;
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemsData
     * @param  Collection<int, PedidoItem>  $pedidoItems
     */
    private function validatePendingAmounts(array $itemsData, Collection $pedidoItems): void
    {
        foreach (array_values($itemsData) as $index => $itemData) {
            $pedidoItemId = (int) ($itemData['id_pedido_item'] ?? 0);
            /** @var PedidoItem|null $pedidoItem */
            $pedidoItem = $pedidoItems->get($pedidoItemId);

            if ($pedidoItem === null) {
                continue;
            }

            $facturaItemId = isset($itemData['id_factura_item']) && $itemData['id_factura_item'] !== ''
                ? (int) $itemData['id_factura_item']
                : null;
            $requestedAmount = (float) ($itemData['importe_facturado'] ?? 0);
            $requestedUnits = $this->nullableFloat($itemData['unidades_facturadas'] ?? null);
            $alreadyBilledAmount = (float) FacturaItem::query()
                ->join('facturas', 'facturas.id_factura', '=', 'factura_items.id_factura')
                ->where('factura_items.id_pedido_item', $pedidoItemId)
                ->where('facturas.estado', '!=', 'anulada')
                ->when($facturaItemId !== null, fn($query) => $query->where('factura_items.id_factura_item', '!=', $facturaItemId))
                ->sum('factura_items.importe_facturado');
            $alreadyBilledUnits = (float) FacturaItem::query()
                ->join('facturas', 'facturas.id_factura', '=', 'factura_items.id_factura')
                ->where('factura_items.id_pedido_item', $pedidoItemId)
                ->where('facturas.estado', '!=', 'anulada')
                ->when($facturaItemId !== null, fn($query) => $query->where('factura_items.id_factura_item', '!=', $facturaItemId))
                ->sum('factura_items.unidades_facturadas');
            $pendingAmount = max(0.0, (float) $pedidoItem->total_linea - $alreadyBilledAmount);
            $pendingUnits = max(0.0, (float) $pedidoItem->cantidad - $alreadyBilledUnits);
            $fieldPrefix = 'items.' . $index;

            if ($requestedAmount > $pendingAmount + self::MONEY_TOLERANCE) {
                throw ValidationException::withMessages([
                    "{$fieldPrefix}.importe_facturado" => [
                        sprintf(
                            'El item %d solo tiene %.2f euros pendientes de facturar.',
                            $pedidoItemId,
                            $pendingAmount
                        ),
                    ],
                ]);
            }

            if ($requestedUnits !== null && $requestedUnits > $pendingUnits + self::UNITS_TOLERANCE) {
                throw ValidationException::withMessages([
                    "{$fieldPrefix}.unidades_facturadas" => [
                        sprintf(
                            'El item %d solo tiene %.3f unidades pendientes de facturar.',
                            $pedidoItemId,
                            $pendingUnits
                        ),
                    ],
                ]);
            }
        }
    }

    private function assignedAmount(Factura $factura): float
    {
        return (float) $factura->items()->sum('importe_facturado');
    }

    /**
     * @return Collection<int, int>
     */
    private function pedidoIdsForFactura(Factura $factura): Collection
    {
        return $this->loadPedidoItemsForExistingFactura($factura)
            ->pluck('id_pedido')
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function logFacturaItemChange(
        Request $request,
        string $action,
        Factura $factura,
        ?array $before,
        ?array $after,
        int $facturaItemId
    ): void {
        $change = $after !== null && $before !== null
            ? $this->auditLogger->resolveFirstChange($before, $after)
            : [
                'campo' => 'id_factura_item',
                'valor_anterior' => $before['id_factura_item'] ?? null,
                'valor_nuevo' => $after['id_factura_item'] ?? null,
            ];

        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => $action,
            'modulo' => 'facturas',
            'tabla' => 'factura_items',
            'entity_type' => FacturaItem::class,
            'entity_id' => $facturaItemId,
            'registro_id' => $facturaItemId,
            'campo' => $change['campo'],
            'valor_anterior' => $change['valor_anterior'],
            'valor_nuevo' => $change['valor_nuevo'],
            'datos_anteriores' => $before,
            'datos_nuevos' => $after,
            'descripcion' => sprintf(
                '%s de linea de factura %d en factura %d.',
                ucfirst($action),
                $facturaItemId,
                $factura->id_factura
            ),
            'id_contexto' => $factura->id_contexto,
        ], $request);
    }

    private function buildExportQuery(Request $request): Builder
    {
        $search = trim((string) $request->input('search', ''));
        $estado = trim((string) $request->input('estado', ''));
        $trabajoId = $request->integer('id_trabajo');
        $empresaId = $request->integer('id_empresa_cliente');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $selectedIds = $this->parseExportIds($request);

        /** @var Builder $query */
        $query = Factura::query();

        $query->withSociedadCifValidada()
            ->with($this->facturaRelations())
            ->whereIn('id_contexto', $this->accessibleContextIds($request))
            ->when($selectedIds !== [], fn(Builder $query) => $query->whereIn('id_factura', $selectedIds))
            ->when($selectedIds === [] && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('numero_factura', 'like', "%{$search}%")
                        ->orWhere('sociedad', 'like', "%{$search}%")
                        ->orWhereHas('empresa', fn(Builder $empresaQuery) => $empresaQuery->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('empresaFacturadora', fn(Builder $empresaQuery) => $empresaQuery->where('nombre', 'like', "%{$search}%"));
                });
            })
            ->when($selectedIds === [] && $estado !== '', fn(Builder $query) => $query->where('estado', $estado))
            ->when($selectedIds === [] && $trabajoId, function (Builder $query) use ($trabajoId): void {
                $query->whereHas('items.pedidoItem.pedido', fn(Builder $pedidoQuery) => $pedidoQuery->where('id_trabajo', $trabajoId));
            })
            ->when($selectedIds === [] && $empresaId, fn(Builder $query) => $query->where('id_empresa_cliente', $empresaId))
            ->when($selectedIds === [] && $fechaDesde, fn(Builder $query) => $query->whereDate('fecha_emision', '>=', $fechaDesde))
            ->when($selectedIds === [] && $fechaHasta, fn(Builder $query) => $query->whereDate('fecha_emision', '<=', $fechaHasta))
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id_factura');

        return $query;
    }

    /**
     * @return array<int, int>
     */
    private function parseExportIds(Request $request): array
    {
        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? $ids : explode(',', (string) $ids);

        return collect($ids)
            ->map(fn(mixed $id): int => (int) $id)
            ->filter(fn(int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function facturaListExportHeaders(): array
    {
        return [
            'Número factura',
            'Contexto',
            'Cliente',
            'Sociedad facturadora',
            'CIF',
            'Fecha emisión',
            'Fecha vencimiento',
            'Estado',
            'Total factura',
            'Importe asignado',
            'Diferencia',
            'Estado cuadre',
            'Pedidos derivados',
            'Trabajos derivados',
            'Contrato',
            'Tarifarios',
        ];
    }

    /**
     * @return array<int, string|float|null>
     */
    private function facturaListExportRow(Factura $factura): array
    {
        $assignedAmount = $this->assignedAmountForExport($factura);
        $total = (float) ($factura->total ?? $factura->importe ?? 0);

        return [
            $this->facturaNumber($factura),
            $this->contextLabel($factura),
            $this->companyLabel($factura->empresa),
            $this->companyLabel($factura->empresaFacturadora) ?: (string) $factura->sociedad,
            $factura->empresaFacturadora?->cif ?: $factura->empresa?->cif,
            $this->formatDateValue($factura->fecha_emision),
            $this->formatDateValue($factura->fecha_vencimiento),
            $factura->estado,
            $this->formatMoneyValue($total),
            $this->formatMoneyValue($assignedAmount),
            $this->formatMoneyValue($total - $assignedAmount),
            $this->balanceStatusForExport($factura, $assignedAmount, $total),
            $this->pedidosLabels($factura)->implode(' | '),
            $this->trabajosLabels($factura)->implode(' | '),
            $this->contractLabel($factura),
            $this->tarifarioLabels($factura)->implode(' | '),
        ];
    }

    /**
     * @return array<int, array{0:string,1:string|float|null}>
     */
    private function facturaDetailHeaderRows(Factura $factura): array
    {
        $assignedAmount = $this->assignedAmountForExport($factura);
        $total = (float) ($factura->total ?? $factura->importe ?? 0);

        return [
            ['Número factura', $this->facturaNumber($factura)],
            ['Contexto', $this->contextLabel($factura)],
            ['Sociedad facturadora', $this->companyLabel($factura->empresaFacturadora) ?: (string) $factura->sociedad],
            ['CIF', $factura->empresaFacturadora?->cif ?: $factura->empresa?->cif],
            ['Cliente', $this->companyLabel($factura->empresa)],
            ['Fecha emisión', $this->formatDateValue($factura->fecha_emision)],
            ['Fecha vencimiento', $this->formatDateValue($factura->fecha_vencimiento)],
            ['Estado', $factura->estado],
            ['Total factura', $this->formatMoneyValue($total)],
            ['Importe asignado', $this->formatMoneyValue($assignedAmount)],
            ['Diferencia', $this->formatMoneyValue($total - $assignedAmount)],
            ['Estado cuadre', $this->balanceStatusForExport($factura, $assignedAmount, $total)],
            ['Pedidos', $this->pedidosLabels($factura)->implode(' | ')],
            ['Trabajos', $this->trabajosLabels($factura)->implode(' | ')],
            ['Contrato', $this->contractLabel($factura)],
            ['Tarifarios', $this->tarifarioLabels($factura)->implode(' | ')],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function facturaDetailLineHeaders(): array
    {
        return [
            'id_factura_item',
            'id_pedido_item',
            'codigo_servicio',
            'numero_tarifa',
            'descripcion_servicio',
            'unidades_facturadas',
            'importe_facturado',
            'observaciones',
            'pedido_asociado',
            'trabajo_asociado',
            'estacion',
            'contrato',
            'tarifa',
            'origen',
        ];
    }

    /**
     * @return array<int, array<int, string|float|int|null>>
     */
    private function facturaDetailLineRows(Factura $factura): array
    {
        if ($factura->items->isNotEmpty()) {
            return $factura->items
                ->map(function (FacturaItem $item): array {
                    $pedidoItem = $item->pedidoItem;
                    $pedido = $pedidoItem?->pedido;
                    $trabajo = $pedido?->trabajo;
                    $tarifaLinea = $pedidoItem?->tarifarioLinea;
                    $tarifario = $tarifaLinea?->tarifario ?? $pedido?->tarifario;
                    $contrato = $tarifario?->contrato ?? $trabajo?->contrato;

                    return [
                        $item->id_factura_item,
                        $item->id_pedido_item,
                        $pedidoItem?->codigo_servicio ?? $tarifaLinea?->codigo_tarifa,
                        $pedidoItem?->numero_tarifa ?? $tarifaLinea?->codigo_tarifa,
                        $pedidoItem?->descripcion_servicio ?? $tarifaLinea?->descripcion,
                        $this->formatMoneyValue((float) $item->unidades_facturadas),
                        $this->formatMoneyValue((float) $item->importe_facturado),
                        $item->observaciones,
                        $pedido?->numero_pedido ?? $pedido?->id_pedido,
                        $trabajo?->numeroTrabajoVisible() ?? $trabajo?->id_trabajo,
                        $trabajo?->estacion?->nombre,
                        $this->modelLabel($contrato, ['codigo', 'nombre']),
                        $this->modelLabel($tarifario, ['nombre', 'version']),
                        'factura_items',
                    ];
                })
                ->values()
                ->all();
        }

        return [];
    }

    private function assignedAmountForExport(Factura $factura): float
    {
        if ($factura->items->isNotEmpty()) {
            return (float) $factura->items->sum(fn(FacturaItem $item) => (float) $item->importe_facturado);
        }

        return 0.0;
    }

    private function balanceStatusForExport(Factura $factura, float $assignedAmount, float $total): string
    {
        if ($factura->items->isEmpty()) {
            return 'sin_items';
        }

        $difference = round($total - $assignedAmount, 2);

        if (abs($difference) <= self::MONEY_TOLERANCE) {
            return 'cuadrada';
        }

        return $difference > 0 ? 'parcial' : 'excedida';
    }

    private function facturaNumber(Factura $factura): string
    {
        return (string) ($factura->numero_factura ?: $factura->numero_factura_ccp ?: $factura->id_factura);
    }

    private function contextLabel(Factura $factura): string
    {
        return (string) ($factura->contexto?->nombre ?? $factura->contexto?->codigo ?? $factura->id_contexto);
    }

    private function companyLabel(?Empresa $empresa): string
    {
        return $this->modelLabel($empresa, ['nombre_comercial', 'nombre', 'razon_social']);
    }

    private function contractLabel(Factura $factura): string
    {
        $contract = $factura->contrato
            ?? $factura->items->first()?->pedidoItem?->tarifarioLinea?->tarifario?->contrato
            ?? $factura->items->first()?->pedidoItem?->pedido?->trabajo?->contrato;

        return $this->modelLabel($contract, ['codigo', 'nombre']);
    }

    private function pedidosLabels(Factura $factura): Collection
    {
        $fromItems = $factura->items
            ->map(fn(FacturaItem $item) => $item->pedidoItem?->pedido)
            ->filter()
            ->map(fn($pedido) => $pedido->numero_pedido ?? $pedido->id_pedido);

        return collect($fromItems->all())->filter()->unique()->values();
    }

    private function trabajosLabels(Factura $factura): Collection
    {
        $fromItems = $factura->items
            ->map(fn(FacturaItem $item) => $item->pedidoItem?->pedido?->trabajo)
            ->filter()
            ->map(fn($trabajo) => $trabajo->numeroTrabajoVisible() ?? $trabajo->id_trabajo);

        return collect($fromItems->all())
            ->filter()
            ->unique()
            ->values();
    }

    private function tarifarioLabels(Factura $factura): Collection
    {
        $fromItems = $factura->items
            ->flatMap(function (FacturaItem $item): array {
                return [
                    $item->pedidoItem?->tarifarioLinea?->tarifario,
                    $item->pedidoItem?->pedido?->tarifario,
                ];
            })
            ->filter()
            ->map(fn($tarifario) => $this->modelLabel($tarifario, ['nombre', 'version']));

        return collect($fromItems->all())->filter()->unique()->values();
    }

    /**
     * @param  array<int, string>  $fields
     */
    private function modelLabel(mixed $model, array $fields): string
    {
        if (! $model) {
            return '';
        }

        if (! is_object($model)) {
            return (string) $model;
        }

        foreach ($fields as $field) {
            if (filled($model->{$field} ?? null)) {
                return (string) $model->{$field};
            }
        }

        return (string) ($model->getKey() ?? '');
    }

    private function formatDateValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return method_exists($value, 'format') ? $value->format('Y-m-d') : (string) $value;
    }

    private function formatMoneyValue(float $value): string
    {
        return number_format($value, 2, ',', '');
    }

    private function loadFacturaRelations(Factura $factura): Factura
    {
        return $factura->load($this->facturaRelations());
    }

    private function facturaRelations(): array
    {
        return [
            'contexto',
            'empresa',
            'empresaFacturadora',
            'contrato',
            'items.pedidoItem.pedido.trabajo.estacion',
            'items.pedidoItem.pedido.trabajo.contrato',
            'items.pedidoItem.pedido.tarifario',
            'items.pedidoItem.tarifarioLinea.tarifario.contrato',
        ];
    }

    private function currentUser(Request $request): ?User
    {
        $user = $request->user();

        return $user instanceof User ? $user : null;
    }

    /**
     * @return array<int, int>
     */
    private function accessibleContextIds(Request $request): array
    {
        return array_map('intval', $this->currentUser($request)?->getActiveContextIds() ?? []);
    }
}
