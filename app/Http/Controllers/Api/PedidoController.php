<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePedidoRequest;
use App\Http\Requests\Api\UpdatePedidoRequest;
use App\Http\Resources\Api\PedidoResource;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Services\AuditLogger;
use App\Services\Exports\MoevePedidoExportService;
use App\Services\TrabajoStateService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TrabajoStateService $trabajoStateService,
        private readonly MoevePedidoExportService $moevePedidoExportService,
    ) {}

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

    public function store(StorePedidoRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $pedido = new Pedido();
            $trabajo = \App\Models\Trabajo::findOrFail($request->input('id_trabajo'));
            $pedido->id_contexto = $trabajo->id_contexto;

            $this->mapRequestToModel($request, $pedido);
            if ($trabajo->id_tarifario) {
                $pedido->id_tarifario = $trabajo->id_tarifario;
            }
            $pedido->save();

            if ($request->has('items')) {
                $this->syncItems($pedido, $request->input('items'));
                $this->recalculatePedidoTotals($pedido);
            }

            $this->trabajoStateService->syncPedidosAndTrabajosByPedidoIds([$pedido->id_pedido]);
            $pedido->refresh();

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
            $previousTrabajoId = $pedido->id_trabajo;
            $trabajo = null;

            if ($request->filled('id_trabajo')) {
                $trabajo = \App\Models\Trabajo::findOrFail($request->input('id_trabajo'));
            } elseif ($pedido->id_trabajo) {
                $trabajo = \App\Models\Trabajo::findOrFail($pedido->id_trabajo);
            }

            if ($trabajo) {
                $pedido->id_contexto = $trabajo->id_contexto;
            }

            $this->mapRequestToModel($request, $pedido);
            if ($trabajo?->id_tarifario) {
                $pedido->id_tarifario = $trabajo->id_tarifario;
            }
            $pedido->save();

            $itemSyncSummary = null;

            if ($request->has('items')) {
                $itemSyncSummary = $this->syncItems($pedido, $request->input('items', []));
                $this->recalculatePedidoTotals($pedido);
            }

            $this->trabajoStateService->syncPedidosAndTrabajosByPedidoIds([$pedido->id_pedido]);
            $this->trabajoStateService->syncTrabajosByIds([$previousTrabajoId, $pedido->id_trabajo]);

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

            $this->trabajoStateService->syncPedidosAndTrabajosByPedidoIds([$pedido->id_pedido]);
        });

        return $this->successResponse(
            new PedidoResource($this->loadPedidoRelations($pedido->fresh())),
            'Pedido cancelado correctamente. No se ha borrado el historico.'
        );
    }

    public function exportMoeveCsv(Request $request, Pedido $pedido): StreamedResponse
    {
        $export = $this->buildMoeveExportPayload($request, $pedido);
        $this->logPedidoExport($request, $export['pedido'], 'CSV Moeve');

        return response()->streamDownload(function () use ($export): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $this->moeveCsvHeaders(), ';');

            foreach ($export['rows'] as $row) {
                fputcsv($handle, $this->moeveCsvRow($export['summary'], $row), ';');
            }

            fclose($handle);
        }, $this->moeveExportFilename($export['pedido'], 'csv'), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportMoevePdf(Request $request, Pedido $pedido)
    {
        $export = $this->buildMoeveExportPayload($request, $pedido);
        $this->logPedidoExport($request, $export['pedido'], 'HTML imprimible Moeve');

        return response()->view('pedidos.export.moeve_pdf', [
            'pedido' => $export['pedido'],
            'summary' => $export['summary'],
            'rows' => $export['rows'],
        ]);
    }

    public function exportMoeveAriba(Request $request, Pedido $pedido)
    {
        $export = $this->buildMoeveExportPayload($request, $pedido);
        $this->logPedidoExport($request, $export['pedido'], 'Cuadro ARIBA');

        return response()->view('pedidos.export.moeve_ariba', [
            'pedido' => $export['pedido'],
            'summary' => $export['summary'],
            'rows' => $export['rows'],
        ]);
    }

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

    private function recalculatePedidoTotals(Pedido $pedido): void
    {
        $items = $pedido->items()->get(['cantidad', 'total_linea']);
        $itemCount = $items->count();
        $importePedido = round((float) $items->sum(fn (PedidoItem $item) => (float) $item->total_linea), 2);
        $unidadesPedido = round((float) $items->sum(fn (PedidoItem $item) => (float) $item->cantidad), 3);
        $importeFacturado = (float) ($pedido->importe_facturado ?? 0);

        $pedido->setAttribute('importe_pedido', number_format($importePedido, 2, '.', ''));
        $pedido->setAttribute('unidades_pedido', number_format($unidadesPedido, 3, '.', ''));

        if ((float) ($pedido->importe_solicitado ?? 0) <= 0) {
            $pedido->setAttribute('importe_solicitado', number_format($importePedido, 2, '.', ''));
        }

        if ((float) ($pedido->unidades_solicitadas ?? 0) <= 0) {
            $pedido->setAttribute('unidades_solicitadas', number_format($unidadesPedido, 3, '.', ''));
        }

        $pedido->pedido_completo = $itemCount > 0 && $importePedido > 0;
        $pedido->tiene_mas_de_1_item = $itemCount > 1;
        $pedido->facturado_completo = $importePedido > 0 && abs($importePedido - $importeFacturado) < 0.01;
        $pedido->save();
    }

    /**
     * @return array{pedido: Pedido, summary: array<string, mixed>, rows: array<int, array<string, mixed>>}
     */
    private function buildMoeveExportPayload(Request $request, Pedido $pedido): array
    {
        $this->ensurePedidoAccess($request, $pedido);

        // El formato MOEVE (PDF, CSV, ARIBA) es exclusivo del contexto MOEVE.
        if (! \App\Support\ContextGuard::isMoeveContextId((int) $pedido->id_contexto)) {
            abort(422, 'Este pedido no pertenece al contexto MOEVE y no puede exportarse en formato Moeve.');
        }

        return $this->moevePedidoExportService->build($pedido);
    }

    /**
     * @return array<int, string>
     */
    private function moeveCsvHeaders(): array
    {
        return [
            'Producto',
            'Posición de la cesta',
            'Cantidad',
            'Fecha de entrega',
            'Mercado',
            'Características',
            'Nota interna',
            'Tipo de Multi-imputación',
            '% Cantidad, Valor de Multi-imputación',
            'Tipo de imputación',
            'Elemento de imputación',
            'Cuenta de mayor',
            'Indicador de IVA',
            'Documento presupuestario',
            'Contrato',
            'Tangible/intangible',
            'Aprobadores',
            'Almacen',
            'Texto Proveedor',
            'CLIENTE',
            'Descripción',
            'Centro /Concesión',
            'Precio Unidad',
            'Total Línea',
            'Total Pedido',
            'Sociedad',
            'Proveedor/ Contrato',
            'Confirmar',
        ];
    }

    /**
     * @param  array<string, mixed>  $summary
     * @param  array<string, mixed>  $row
     * @return array<int, string>
     */
    private function moeveCsvRow(array $summary, array $row): array
    {
        return [
            (string) $row['producto'],
            (string) $row['item'],
            $this->formatCsvNumber((float) $row['cantidad'], 3),
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            (string) $summary['cta_mayor'],
            '',
            '',
            (string) $summary['contrato_codigo'],
            '',
            (string) $summary['aprobado_por'],
            '',
            (string) $row['texto_proveedor'],
            (string) $summary['client_code'],
            (string) $summary['descripcion'],
            (string) $summary['nombre_estacion'],
            $this->formatCsvNumber((float) $row['precio_unitario'], 2),
            $this->formatCsvNumber((float) $row['total_linea'], 2),
            $this->formatCsvNumber((float) $summary['importe_pedido'], 2),
            (string) $summary['sociedad_ariba'],
            (string) $summary['proveedor_contrato'],
            (string) $summary['confirmar'],
        ];
    }

    private function moeveExportFilename(Pedido $pedido, string $extension): string
    {
        $safeNumber = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) ($pedido->numero_pedido ?: 'pedido_' . $pedido->id_pedido));

        return sprintf('moeve_pedido_%s.%s', $safeNumber, $extension);
    }

    private function formatCsvNumber(float $value, int $decimals): string
    {
        return number_format($value, $decimals, ',', '');
    }

    private function logPedidoExport(Request $request, Pedido $pedido, string $format): void
    {
        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => 'exportar',
            'modulo' => 'pedidos',
            'tabla' => 'pedidos',
            'entity_type' => Pedido::class,
            'entity_id' => $pedido->id_pedido,
            'registro_id' => $pedido->id_pedido,
            'descripcion' => sprintf('Exportacion %s del pedido %s.', $format, $pedido->numero_pedido),
            'datos_nuevos' => [
                'id_pedido' => $pedido->id_pedido,
                'numero_pedido' => $pedido->numero_pedido,
                'formato' => $format,
            ],
            'id_contexto' => $pedido->id_contexto,
        ], $request);
    }
}
