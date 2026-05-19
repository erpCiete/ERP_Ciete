<?php

use App\Http\Controllers\Api\FacturaController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\TrabajoController;
use App\Models\ContextoCliente;
use App\Models\ContratoEmpresaFacturadora;
use App\Models\Factura;
use App\Models\PedidoItem;
use App\Support\ContextGuard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Sprint 03 - el TrabajoController web renderiza Inertia (namespace raiz)
// Vive en app/Http/Controllers/Api/TrabajoController.php
// con namespace App\Http\Controllers\Api

// Nota: PedidoController y FacturaController existen en App\Http\Controllers\Api
// y devuelven JsonResponse; son controladores de API REST, no de Inertia.
// Las rutas web de pedidos y facturas usan closures con Inertia::render
// hasta que se creen controladores web dedicados (igual que TrabajoController).

Route::middleware(['auth', 'maintenance'])->group(function () {
    // ── Sprint 03 · Trabajos ──────────────────────────────────────────────────
    Route::middleware(['permission:trabajos.ver', 'forbid_role:contable'])->group(function () {
        Route::get('/trabajos',              [TrabajoController::class, 'index'])->name('trabajos.index');
        Route::get('/trabajos/crear',        [TrabajoController::class, 'create'])
            ->name('trabajos.create')->middleware('permission:trabajos.crear');
        Route::get('/trabajos/{trabajo}/editar', [TrabajoController::class, 'edit'])
            ->name('trabajos.edit')->middleware('permission:trabajos.editar');
        Route::post('/trabajos',             [TrabajoController::class, 'store'])
            ->name('trabajos.store')->middleware('permission:trabajos.crear');
        Route::put('/trabajos/{trabajo}',    [TrabajoController::class, 'update'])
            ->name('trabajos.update')->middleware('permission:trabajos.editar');
        Route::patch('/trabajos/{trabajo}/campo', [TrabajoController::class, 'patchField'])
            ->name('trabajos.patch-field')->middleware('permission:trabajos.editar');
        Route::delete('/trabajos/{trabajo}', [TrabajoController::class, 'destroy'])
            ->name('trabajos.destroy')->middleware('permission:trabajos.eliminar');
    });

    // ── Sprint 04 · Pedidos ───────────────────────────────────────────────────
    // Las mutaciones (store/update/destroy) van via API REST (/api/v1/pedidos)
    // directamente desde el Form.jsx con axios — no necesitan ruta web.
    // Solo necesitamos rutas web para las vistas Inertia.
    Route::middleware('permission:pedidos.ver')->group(function () {

        Route::get('/pedidos', function (\Illuminate\Http\Request $request) {
            $user = $request->user();
            $accessibleContextIds = $user->getActiveContextIds() ?? [];

            $search = trim((string) $request->input('search', ''));
            $estado = trim((string) $request->input('estado', ''));

            $pedidos = \App\Models\Pedido::query()
                ->with([
                    'trabajo.estacion',
                    'items' => fn($query) => $query->withCount('facturaItems')->orderBy('id_pedido_item'),
                ])
                ->when($accessibleContextIds !== [], function ($q) use ($accessibleContextIds) {
                    $q->whereIn('id_contexto', $accessibleContextIds);
                })
                ->when($estado !== '', function ($q) use ($estado) {
                    $q->where('estado', $estado);
                })
                ->when($search !== '', function ($q) use ($search) {
                    $q->where('numero_pedido', 'like', "%{$search}%");
                })
                ->orderByDesc('created_at')
                ->paginate(10)
                ->withQueryString();

            return Inertia::render('Pedidos/Index', [
                'pedidos'     => \App\Http\Resources\Api\PedidoResource::collection($pedidos),
                'filters'     => $request->only(['search', 'estado', 'fecha_desde', 'fecha_hasta']),
                'contextoIds' => $user->getActiveContextIds() ?? [],
                'canCreate'   => $user->hasPermission('pedidos.crear')
                    && ContextGuard::canCreateInActiveContext($user),
                'trabajos'    => \App\Models\Trabajo::query()
                    ->select('id_trabajo', 'id_contexto', 'numero_trabajo', 'numero_trabajo_operativo', 'descripcion_trabajo')
                    ->whereIn('id_contexto', $accessibleContextIds)
                    ->orderBy('numero_trabajo')
                    ->limit(200)
                    ->get(),
            ]);
        })->name('pedidos.index');

        Route::get('/pedidos/crear', function (\Illuminate\Http\Request $request) {
            if (! ContextGuard::canCreateInActiveContext($request->user())) {
                return redirect()
                    ->route('pedidos.index')
                    ->with('error', ContextGuard::CREATE_FROM_ALL_MESSAGE);
            }

            $accessibleContextIds = $request->user()->getActiveContextIds() ?? [];
            $clientContexts = ContextoCliente::query()
                ->whereIn('id_contexto', $accessibleContextIds)
                ->where('activo', true)
                ->orderBy('id_contexto')
                ->get(['id_contexto', 'nombre', 'codigo']);

            return Inertia::render('Pedidos/Form', [
                'pedido'         => null,
                'trabajos'       => \App\Models\Trabajo::query()
                    ->select('id_trabajo', 'id_contexto', 'id_contrato', 'id_tarifario', 'numero_trabajo', 'numero_trabajo_operativo', 'descripcion_trabajo')
                    ->whereIn('id_contexto', $accessibleContextIds)
                    ->orderBy('numero_trabajo')
                    ->get(),
                'tarifarioLineas' => \App\Models\TarifarioLinea::query()
                    ->join('tarifarios', 'tarifario_lineas.id_tarifario', '=', 'tarifarios.id_tarifario')
                    ->whereIn('tarifario_lineas.id_contexto', $accessibleContextIds)
                    ->where('tarifario_lineas.activo', true)
                    ->where('tarifarios.activo', true)
                    ->orderBy('tarifario_lineas.codigo_tarifa')
                    ->get([
                        'tarifario_lineas.id_tarifario_linea',
                        'tarifario_lineas.id_contexto',
                        'tarifario_lineas.id_tarifario',
                        'tarifarios.id_contrato',
                        'tarifario_lineas.codigo_tarifa',
                        'tarifario_lineas.actuacion',
                        'tarifario_lineas.descripcion',
                        'tarifario_lineas.tarifa_base',
                        'tarifario_lineas.tarifa_aplicada',
                    ]),
                'contextoIds'    => $accessibleContextIds,
                'clientContexts' => $clientContexts,
            ]);
        })->name('pedidos.create')->middleware('permission:pedidos.crear');

        Route::get('/pedidos/{id}/editar', function (\Illuminate\Http\Request $request, $id) {
            $accessibleContextIds = $request->user()->getActiveContextIds() ?? [];
            $clientContexts = ContextoCliente::query()
                ->whereIn('id_contexto', $accessibleContextIds)
                ->where('activo', true)
                ->orderBy('id_contexto')
                ->get(['id_contexto', 'nombre', 'codigo']);
            $pedido = \App\Models\Pedido::with([
                'items' => fn($query) => $query->withCount('facturaItems')->orderBy('id_pedido_item'),
            ])->findOrFail($id);

            return Inertia::render('Pedidos/Form', [
                'pedido'         => new \App\Http\Resources\Api\PedidoResource($pedido),
                'trabajos'       => \App\Models\Trabajo::query()
                    ->select('id_trabajo', 'id_contexto', 'id_contrato', 'id_tarifario', 'numero_trabajo', 'numero_trabajo_operativo', 'descripcion_trabajo')
                    ->whereIn('id_contexto', $accessibleContextIds)
                    ->orderBy('numero_trabajo')
                    ->get(),
                'tarifarioLineas' => \App\Models\TarifarioLinea::query()
                    ->join('tarifarios', 'tarifario_lineas.id_tarifario', '=', 'tarifarios.id_tarifario')
                    ->whereIn('tarifario_lineas.id_contexto', $accessibleContextIds)
                    ->where('tarifario_lineas.activo', true)
                    ->where('tarifarios.activo', true)
                    ->orderBy('tarifario_lineas.codigo_tarifa')
                    ->get([
                        'tarifario_lineas.id_tarifario_linea',
                        'tarifario_lineas.id_contexto',
                        'tarifario_lineas.id_tarifario',
                        'tarifarios.id_contrato',
                        'tarifario_lineas.codigo_tarifa',
                        'tarifario_lineas.actuacion',
                        'tarifario_lineas.descripcion',
                        'tarifario_lineas.tarifa_base',
                        'tarifario_lineas.tarifa_aplicada',
                    ]),
                'contextoIds'    => $accessibleContextIds,
                'clientContexts' => $clientContexts,
            ]);
        })->name('pedidos.edit')->middleware('permission:pedidos.editar');

        // Las rutas de mutación no son necesarias en web.php porque el Form usa axios
        // directamente contra /api/v1/pedidos. Se definen aquí solo para que
        // router.delete() de Inertia funcione en el modal de confirmación del Index.
        Route::delete('/pedidos/{pedido}', [PedidoController::class, 'destroy'])
            ->name('pedidos.destroy')
            ->middleware('permission:pedidos.eliminar');
    });

    // ── Sprint 04 · Facturas ──────────────────────────────────────────────────
    Route::middleware('permission:facturas.ver')->group(function () {
        $facturaRelations = [
            'empresa',
            'empresaFacturadora',
            'contrato',
            'items.pedidoItem.pedido.trabajo.estacion',
            'items.pedidoItem.pedido.trabajo.contrato',
            'items.pedidoItem.pedido.tarifario',
            'items.pedidoItem.tarifarioLinea.tarifario.contrato',
        ];

        $buildPedidoItemsFacturables = static function (array $accessibleContextIds, ?int $facturaId = null, ?int $limit = 500) {
            if ($accessibleContextIds === []) {
                return collect();
            }

            return PedidoItem::query()
                ->with([
                    'pedido.trabajo.estacion',
                    'pedido.trabajo.contrato',
                    'pedido.tarifario',
                    'pedido.tarifario.contrato',
                    'tarifarioLinea.tarifario.contrato',
                    'facturaItems' => fn($query) => $query
                        ->when($facturaId !== null, fn($nested) => $nested->where('id_factura', '!=', $facturaId)),
                ])
                ->whereIn('id_contexto', $accessibleContextIds)
                ->orderByDesc('id_pedido_item')
                ->when($limit ?? 500, fn($query, $resolvedLimit) => $query->limit($resolvedLimit))
                ->get()
                ->filter(fn(PedidoItem $item) => $item->pedido?->trabajo !== null)
                ->map(function (PedidoItem $item) {
                    $importeFacturado = (float) $item->facturaItems->sum(fn($linea) => (float) $linea->importe_facturado);
                    $unidadesFacturadas = (float) $item->facturaItems->sum(fn($linea) => (float) $linea->unidades_facturadas);
                    $importePendiente = max(0, (float) $item->total_linea - $importeFacturado);
                    $unidadesPendientes = max(0, (float) $item->cantidad - $unidadesFacturadas);
                    $pedido = $item->pedido;
                    $trabajo = $pedido->trabajo;
                    $tarifario = $item->tarifarioLinea?->tarifario ?? $pedido->tarifario;
                    $contrato = $trabajo->contrato ?? $tarifario?->contrato;

                    return [
                        'id_pedido_item' => (int) $item->id_pedido_item,
                        'id_contexto' => (int) $item->id_contexto,
                        'id_pedido' => (int) $item->id_pedido,
                        'numero_pedido' => $pedido->numero_pedido,
                        'id_trabajo' => (int) $trabajo->id_trabajo,
                        'numero_trabajo' => $trabajo->numero_trabajo,
                        'numero_trabajo_operativo' => $trabajo->numero_trabajo_operativo,
                        'numero_trabajo_visible' => $trabajo->numeroTrabajoVisible(),
                        'descripcion_trabajo' => $trabajo->descripcion_trabajo,
                        'codigo_estacion' => $trabajo->estacion?->codigo_estacion,
                        'id_contrato' => $trabajo->id_contrato ? (int) $trabajo->id_contrato : null,
                        'codigo_contrato' => $contrato?->codigo_contrato,
                        'nombre_contrato' => $contrato?->nombre,
                        'id_tarifario' => $tarifario?->id_tarifario ? (int) $tarifario->id_tarifario : null,
                        'codigo_servicio' => $item->codigo_servicio,
                        'numero_tarifa' => $item->numero_tarifa,
                        'descripcion_servicio' => $item->descripcion_servicio,
                        'cantidad' => (float) $item->cantidad,
                        'precio_unitario' => (float) $item->precio_unitario,
                        'total_linea' => (float) $item->total_linea,
                        'unidades_facturadas' => $unidadesFacturadas,
                        'importe_facturado' => $importeFacturado,
                        'unidades_pendientes' => $unidadesPendientes,
                        'importe_pendiente' => $importePendiente,
                    ];
                })
                ->filter(fn(array $item) => $item['importe_pendiente'] > 0.01 || $item['unidades_pendientes'] > 0.001)
                ->values();
        };

        $buildFacturacionCatalogos = static function ($pedidoItemsFacturables, $clientContexts): array {
            $items = collect($pedidoItemsFacturables)->values();

            return [
                'contextos' => collect($clientContexts)
                    ->map(fn($contexto) => [
                        'id_contexto' => (int) $contexto->id_contexto,
                        'nombre' => $contexto->nombre,
                        'codigo' => $contexto->codigo,
                    ])
                    ->values()
                    ->all(),
                'contratos' => $items
                    ->filter(fn(array $item) => ! empty($item['id_contrato']))
                    ->groupBy(fn(array $item) => $item['id_contexto'] . '-' . $item['id_contrato'])
                    ->map(function ($rows) {
                        $first = $rows->first();
                        $codigo = trim((string) ($first['codigo_contrato'] ?? ''));
                        $nombre = trim((string) ($first['nombre_contrato'] ?? ''));

                        return [
                            'id_contexto' => (int) $first['id_contexto'],
                            'id_contrato' => (int) $first['id_contrato'],
                            'codigo_contrato' => $codigo !== '' ? $codigo : null,
                            'nombre_contrato' => $nombre !== '' ? $nombre : null,
                            'label' => trim(($codigo !== '' ? $codigo : 'Contrato ' . $first['id_contrato']) . ($nombre !== '' ? ' · ' . $nombre : '')),
                        ];
                    })
                    ->sortBy('label')
                    ->values()
                    ->all(),
                'trabajos' => $items
                    ->filter(fn(array $item) => ! empty($item['id_contrato']) && ! empty($item['id_trabajo']))
                    ->groupBy(fn(array $item) => $item['id_contexto'] . '-' . $item['id_contrato'] . '-' . $item['id_trabajo'])
                    ->map(function ($rows) {
                        $first = $rows->first();
                        $numero = $first['numero_trabajo_visible'] ?? $first['numero_trabajo_operativo'] ?? $first['numero_trabajo'] ?? '-';
                        $descripcion = trim((string) ($first['descripcion_trabajo'] ?? ''));
                        $estacion = trim((string) ($first['codigo_estacion'] ?? ''));

                        return [
                            'id_contexto' => (int) $first['id_contexto'],
                            'id_contrato' => (int) $first['id_contrato'],
                            'id_trabajo' => (int) $first['id_trabajo'],
                            'numero_trabajo_visible' => $numero,
                            'descripcion_trabajo' => $descripcion !== '' ? $descripcion : null,
                            'codigo_estacion' => $estacion !== '' ? $estacion : null,
                            'label' => trim('Trabajo ' . $numero . ($descripcion !== '' ? ' · ' . $descripcion : ($estacion !== '' ? ' · ' . $estacion : ''))),
                        ];
                    })
                    ->sortBy('label')
                    ->values()
                    ->all(),
                'pedidos' => $items
                    ->filter(fn(array $item) => ! empty($item['id_contrato']) && ! empty($item['id_trabajo']) && ! empty($item['id_pedido']))
                    ->groupBy(fn(array $item) => $item['id_contexto'] . '-' . $item['id_contrato'] . '-' . $item['id_trabajo'] . '-' . $item['id_pedido'])
                    ->map(function ($rows) {
                        $first = $rows->first();
                        $numeroTrabajo = $first['numero_trabajo_visible'] ?? $first['numero_trabajo_operativo'] ?? $first['numero_trabajo'] ?? '-';

                        return [
                            'id_contexto' => (int) $first['id_contexto'],
                            'id_contrato' => (int) $first['id_contrato'],
                            'id_trabajo' => (int) $first['id_trabajo'],
                            'id_pedido' => (int) $first['id_pedido'],
                            'numero_pedido' => $first['numero_pedido'] ?? '-',
                            'numero_trabajo_visible' => $numeroTrabajo,
                            'label' => trim('Pedido ' . ($first['numero_pedido'] ?? '-') . ' · Trabajo ' . $numeroTrabajo),
                        ];
                    })
                    ->sortBy('label')
                    ->values()
                    ->all(),
            ];
        };

        /**
         * Devuelve empresas facturadoras permitidas, agrupadas por id_contexto.
         * Solo devuelve relaciones activas configuradas en contrato_empresas_facturadoras.
         *
         * Estructura: { "1": [{ id_empresa, nombre, cif, cif_label, id_contratos }], "2": [...] }
         */
        $buildEmpresasFacturadoras = static function (array $accessibleContextIds): array {
            if ($accessibleContextIds === []) {
                return [];
            }

            $permitidas = ContratoEmpresaFacturadora::query()
                ->join('empresas', function ($join) {
                    $join->on('contrato_empresas_facturadoras.id_empresa', '=', 'empresas.id_empresa')
                        ->on('contrato_empresas_facturadoras.id_contexto', '=', 'empresas.id_contexto');
                })
                ->join('contratos', 'contrato_empresas_facturadoras.id_contrato', '=', 'contratos.id_contrato')
                ->whereIn('contrato_empresas_facturadoras.id_contexto', $accessibleContextIds)
                ->where('contrato_empresas_facturadoras.activo', true)
                ->where('empresas.activo', true)
                ->whereNotNull('empresas.cif')
                ->where('empresas.cif', '!=', '')
                ->select([
                    'contrato_empresas_facturadoras.id_contexto',
                    'contrato_empresas_facturadoras.id_contrato',
                    'empresas.id_empresa',
                    'empresas.nombre',
                    'empresas.nombre_comercial',
                    'empresas.razon_social',
                    'empresas.cif',
                ])
                ->orderBy('empresas.nombre')
                ->get();

            $result = [];

            foreach ($accessibleContextIds as $ctxId) {
                $result[(string) $ctxId] = $permitidas
                    ->where('id_contexto', $ctxId)
                    ->groupBy('id_empresa')
                    ->map(function ($rows) {
                        $empresa = $rows->first();
                        $contractIds = $rows
                            ->pluck('id_contrato')
                            ->unique()
                            ->map(fn($id) => (int) $id)
                            ->values()
                            ->all();

                        return [
                            'id_empresa' => (int) $empresa->id_empresa,
                            'id_contrato' => $contractIds[0] ?? null,
                            'id_contratos' => $contractIds,
                            'nombre' => $empresa->nombre,
                            'nombre_comercial' => $empresa->nombre_comercial,
                            'razon_social' => $empresa->razon_social,
                            'cif' => $empresa->cif,
                            'cif_label' => $empresa->cif ? $empresa->nombre . ' - ' . $empresa->cif : $empresa->nombre,
                        ];
                    })
                    ->values()
                    ->all();
            }

            return $result;
        };

        Route::get('/facturas', function (\Illuminate\Http\Request $request) use ($facturaRelations, $buildPedidoItemsFacturables, $buildEmpresasFacturadoras, $buildFacturacionCatalogos) {
            $user = $request->user();
            $accessibleContextIds = $user->getActiveContextIds() ?? [];
            $canCreate = $user->hasPermission('facturas.crear')
                && ContextGuard::canCreateInActiveContext($user);
            $canEdit = $user->hasPermission('facturas.editar');
            $isCieteExcel = ($user->interface_mode ?? null) === 'ciete_excel';
            $clientContexts = ContextoCliente::query()
                ->whereIn('id_contexto', $accessibleContextIds)
                ->where('activo', true)
                ->orderBy('id_contexto')
                ->get(['id_contexto', 'nombre', 'codigo']);

            $search = trim((string) $request->input('search', ''));
            $estado = trim((string) $request->input('estado', ''));

            $facturas = \App\Models\Factura::query()
                ->withSociedadCifValidada()
                ->with($facturaRelations)
                ->when($accessibleContextIds !== [], function ($q) use ($accessibleContextIds) {
                    $q->whereIn('id_contexto', $accessibleContextIds);
                })
                ->when($estado !== '', function ($q) use ($estado) {
                    $q->where('estado', $estado);
                })
                ->when($search !== '', function ($q) use ($search) {
                    $q->where('numero_factura', 'like', "%{$search}%");
                })
                ->orderByDesc('created_at')
                ->paginate(10)
                ->withQueryString();

            $excelPedidoItemsFacturables = collect();
            $excelEmpresasFacturadoras = [];
            $facturacionCatalogos = $buildFacturacionCatalogos(collect(), $clientContexts);

            if (($canCreate || $canEdit) && $isCieteExcel) {
                $excelPedidoItemsFacturables = $buildPedidoItemsFacturables($accessibleContextIds, null, null);
                $excelEmpresasFacturadoras = $buildEmpresasFacturadoras($accessibleContextIds);
                $facturacionCatalogos = $buildFacturacionCatalogos($excelPedidoItemsFacturables, $clientContexts);
            }

            return Inertia::render('Facturas/Index', [
                'facturas'    => \App\Http\Resources\Api\FacturaResource::collection($facturas),
                'filters'     => $request->only(['search', 'estado', 'fecha_desde', 'fecha_hasta']),
                'contextoIds' => $user->getActiveContextIds() ?? [],
                'canCreate'   => $canCreate,
                'canExport'   => $user->hasPermission('facturas.exportar'),
                'pedidoItemsFacturables' => $excelPedidoItemsFacturables->values()->all(),
                'empresasFacturadoras' => $excelEmpresasFacturadoras,
                'facturacionCatalogos' => $facturacionCatalogos,
            ]);
        })->name('facturas.index');

        Route::get('/facturas/crear', function (\Illuminate\Http\Request $request) use ($buildPedidoItemsFacturables, $buildEmpresasFacturadoras) {
            if (! ContextGuard::canCreateInActiveContext($request->user())) {
                return redirect()
                    ->route('facturas.index')
                    ->with('error', ContextGuard::CREATE_FROM_ALL_MESSAGE);
            }

            $accessibleContextIds = $request->user()->getActiveContextIds() ?? [];
            $clientContexts = ContextoCliente::query()
                ->whereIn('id_contexto', $accessibleContextIds)
                ->where('activo', true)
                ->orderBy('id_contexto')
                ->get(['id_contexto', 'nombre', 'codigo']);
            $ordenesUsadas = DB::table('facturas')
                ->join('factura_items', 'facturas.id_factura', '=', 'factura_items.id_factura')
                ->join('pedido_items', 'factura_items.id_pedido_item', '=', 'pedido_items.id_pedido_item')
                ->join('pedidos', 'pedido_items.id_pedido', '=', 'pedidos.id_pedido')
                ->whereIn('facturas.id_contexto', $accessibleContextIds)
                ->whereNotNull('facturas.orden_factura')
                ->get(['pedidos.id_trabajo', 'facturas.orden_factura'])
                ->groupBy('id_trabajo')
                ->map(fn($rows) => $rows->pluck('orden_factura')->map(fn($orden) => (int) $orden)->values())
                ->all();

            return Inertia::render('Facturas/Form', [
                'factura'        => null,
                'trabajos'       => \App\Models\Trabajo::query()
                    ->select('id_trabajo', 'id_contexto', 'numero_trabajo', 'numero_trabajo_operativo', 'descripcion_trabajo')
                    ->whereIn('id_contexto', $accessibleContextIds)
                    ->orderBy('numero_trabajo')
                    ->get(),
                'contextoIds'    => $accessibleContextIds,
                'clientContexts' => $clientContexts,
                'ordenesUsadas'  => $ordenesUsadas,
                'pedidoItemsFacturables' => $buildPedidoItemsFacturables($accessibleContextIds),
                'empresasFacturadoras'   => $buildEmpresasFacturadoras($accessibleContextIds),
            ]);
        })->name('facturas.create')->middleware('permission:facturas.crear');

        Route::get('/facturas/{id}/editar', function (\Illuminate\Http\Request $request, $id) use ($buildPedidoItemsFacturables, $facturaRelations, $buildEmpresasFacturadoras) {
            $accessibleContextIds = $request->user()->getActiveContextIds() ?? [];
            $clientContexts = ContextoCliente::query()
                ->whereIn('id_contexto', $accessibleContextIds)
                ->where('activo', true)
                ->orderBy('id_contexto')
                ->get(['id_contexto', 'nombre', 'codigo']);
            $ordenesUsadas = DB::table('facturas')
                ->join('factura_items', 'facturas.id_factura', '=', 'factura_items.id_factura')
                ->join('pedido_items', 'factura_items.id_pedido_item', '=', 'pedido_items.id_pedido_item')
                ->join('pedidos', 'pedido_items.id_pedido', '=', 'pedidos.id_pedido')
                ->whereIn('facturas.id_contexto', $accessibleContextIds)
                ->whereNotNull('facturas.orden_factura')
                ->get(['pedidos.id_trabajo', 'facturas.orden_factura'])
                ->groupBy('id_trabajo')
                ->map(fn($rows) => $rows->pluck('orden_factura')->map(fn($orden) => (int) $orden)->values())
                ->all();
            $factura = Factura::with($facturaRelations)
                ->whereIn('id_contexto', $accessibleContextIds)
                ->findOrFail($id);

            return Inertia::render('Facturas/Form', [
                'factura'        => new \App\Http\Resources\Api\FacturaResource($factura),
                'trabajos'       => \App\Models\Trabajo::query()
                    ->select('id_trabajo', 'id_contexto', 'numero_trabajo', 'numero_trabajo_operativo', 'descripcion_trabajo')
                    ->whereIn('id_contexto', $accessibleContextIds)
                    ->orderBy('numero_trabajo')
                    ->get(),
                'contextoIds'    => $accessibleContextIds,
                'clientContexts' => $clientContexts,
                'ordenesUsadas'  => $ordenesUsadas,
                'pedidoItemsFacturables' => $buildPedidoItemsFacturables($accessibleContextIds, (int) $factura->id_factura),
                'empresasFacturadoras'   => $buildEmpresasFacturadoras($accessibleContextIds),
            ]);
        })->name('facturas.edit')->middleware('permission:facturas.editar');

        Route::delete('/facturas/{factura}', [FacturaController::class, 'destroy'])
            ->name('facturas.destroy')
            ->middleware('permission:facturas.eliminar');
    });
});
