<?php

use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\ActiveContextController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\NoticeController as AdminNoticeController;
use App\Http\Controllers\Admin\SupportTicketController as AdminSupportTicketController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ClosureDashboardController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\ContratoEmpresaFacturadoraController;
use App\Http\Controllers\ImportacionController;
use App\Http\Controllers\MaestroController;
use App\Http\Controllers\TarifarioController;
use App\Http\Controllers\TarifarioLineaController;

// Sprint 03 — el TrabajoController web renderiza Inertia (namespace raíz)
// Vive en app/Http/Controllers/Api/TrabajoController.php
// con namespace App\Http\Controllers\Api
use App\Http\Controllers\Api\TrabajoController;
use App\Http\Controllers\Api\FacturaController;
use App\Http\Controllers\Api\PedidoController;

use App\Models\ContextoCliente;
use App\Models\ContratoEmpresaFacturadora;
use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\Trabajo;
use App\Models\Pedido; // <--- ESTA ES LA QUE FALTA PARA EL DASHBOARD
use App\Models\Factura;
use App\Models\PedidoItem;
use App\Support\ContextGuard;
use App\Support\HomeNoticeCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ─── NOTA IMPORTANTE ─────────────────────────────────────────────────────────
// PedidoController y FacturaController existen en App\Http\Controllers\Api
// y devuelven JsonResponse — son controladores de API REST, NO de Inertia.
// Las rutas WEB de pedidos y facturas usan closures con Inertia::render
// hasta que se creen controladores web dedicados (igual que TrabajoController).
// ─────────────────────────────────────────────────────────────────────────────

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware(['auth', 'maintenance'])->group(function () {
    Route::post('/contexto/activo', [ActiveContextController::class, 'update'])->name('contexto.activo.update');

    Route::get('/', function () {
        $homeNotices = AdminNoticeController::load();

        return Inertia::render('Welcome', [
            'homeNotices' => $homeNotices,
            'featuredNotice' => HomeNoticeCatalog::featured($homeNotices),
        ]);
    })->name('index');

    Route::get('/dashboard', function () {
        $todasLasObras = Trabajo::all();
        $ultimosPedidos = \App\Models\Pedido::with('trabajo')
        ->latest()
        ->take(5)
        ->get()
        ->map(function ($pedido) {
            return [
                // En el Dashboard usabas 'ref', 'fecha' y 'estado'
                'ref'    => $pedido->numero_pedido ?? ('Pedido #' . $pedido->id_pedido),
                'fecha'  => $pedido->fecha_solicitud ? \Carbon\Carbon::parse($pedido->fecha_solicitud)->format('d/m/Y') : '—',
                'estado' => $pedido->estado ?? 'pendiente',
            ];
        });
        return Inertia::render('Dashboard', [
            'obras'           => $todasLasObras->take(5),
            'totalObrasCount' => $todasLasObras->count(),
            'pedidos'         => $ultimosPedidos,
            'legalizaciones'  => [],
        ]);
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/perfil/preferencias', [ProfileController::class, 'updatePreferences'])->name('profile.preferences');

    // ── Clientes ─────────────────────────────────────────────────────────────
    Route::middleware('permission:clientes.ver')->group(function () {
        Route::get('/clientes', function (\Illuminate\Http\Request $request) {
            $user = $request->user();

            return Inertia::render('Clientes/Index', [
                'canCreate' => $user->hasPermission('clientes.crear')
                    && ContextGuard::canCreateInActiveContext($user),
            ]);
        })->name('clientes.index');
    });
    Route::middleware('permission:clientes.crear')->group(function () {
        Route::get('/clientes/crear', function (\Illuminate\Http\Request $request) {
            if (! ContextGuard::canCreateInActiveContext($request->user())) {
                return redirect()
                    ->route('clientes.index')
                    ->with('error', ContextGuard::CREATE_FROM_ALL_MESSAGE);
            }

            return Inertia::render('Clientes/Form');
        })->name('clientes.create');
    });
    Route::middleware('permission:clientes.editar')->group(function () {
        Route::get('/clientes/{cliente}/editar', function (Empresa $cliente) {
            return Inertia::render('Clientes/Form', ['clienteId' => $cliente->id_empresa]);
        })->name('clientes.edit');
    });

    // ── Estaciones ────────────────────────────────────────────────────────────
    Route::middleware('permission:estaciones.ver')->group(function () {
        Route::get('/estaciones', function (\Illuminate\Http\Request $request) {
            $user = $request->user();

            return Inertia::render('Estaciones/Index', [
                'canCreate' => $user->hasPermission('estaciones.crear')
                    && ContextGuard::canCreateInActiveContext($user),
            ]);
        })->name('estaciones.index');
    });
    Route::middleware('permission:estaciones.crear')->group(function () {
        Route::get('/estaciones/crear', function (\Illuminate\Http\Request $request) {
            if (! ContextGuard::canCreateInActiveContext($request->user())) {
                return redirect()
                    ->route('estaciones.index')
                    ->with('error', ContextGuard::CREATE_FROM_ALL_MESSAGE);
            }

            return Inertia::render('Estaciones/Form');
        })->name('estaciones.create');
    });
    Route::middleware('permission:estaciones.editar')->group(function () {
        Route::get('/estaciones/{estacion}/editar', function (EstacionServicio $estacion) {
            return Inertia::render('Estaciones/Form', ['estacionId' => $estacion->id_estacion_servicio]);
        })->name('estaciones.edit');
    });

    // ── Páginas generales ─────────────────────────────────────────────────────
    Route::get('/ayuda',   function () { return Inertia::render('Help'); })->name('help');
    Route::get('/estado',  [StatusController::class, 'index'])->name('status');
    Route::get('/soporte', [SupportController::class, 'index'])->name('support');
    Route::post('/soporte',[SupportController::class, 'send'])->name('support.send');
    Route::get('/soporte/{solicitudSoporte}', [SupportController::class, 'show'])->name('support.show');
    Route::post('/soporte/{solicitudSoporte}/reply', [SupportController::class, 'reply'])->name('support.reply');

    Route::get('/mensajes', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/mensajes', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/mensajes/{mensaje}', [MessageController::class, 'show'])
        ->where('mensaje', '[0-9]+')->name('messages.show');
    Route::post('/mensajes/{mensaje}/leer', [MessageController::class, 'markRead'])
        ->where('mensaje', '[0-9]+')->name('messages.read');
    Route::post('/mensajes/{mensaje}/archivar', [MessageController::class, 'archive'])
        ->where('mensaje', '[0-9]+')->name('messages.archive');

    // ── Admin ─────────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin', AdminDashboardController::class)->name('admin.dashboard');
        Route::post('/admin/maintenance', [MaintenanceController::class, 'toggle'])
            ->name('admin.maintenance.toggle');
        Route::post('/admin/notices', [AdminNoticeController::class, 'update'])
            ->name('admin.notices.update');
        Route::post('/mensajes/broadcast', [MessageController::class, 'broadcast'])
            ->name('messages.broadcast');
        Route::get('/admin/soporte', [AdminSupportTicketController::class, 'index'])->name('admin.support.index');
        Route::get('/admin/soporte/{solicitudSoporte}', [AdminSupportTicketController::class, 'show'])->name('admin.support.show');
        Route::patch('/admin/soporte/{solicitudSoporte}/status', [AdminSupportTicketController::class, 'updateStatus'])->name('admin.support.status');
        Route::post('/admin/soporte/{solicitudSoporte}/reply', [AdminSupportTicketController::class, 'reply'])->name('admin.support.reply');
    });

    // ── Gestión de usuarios (accesible para admin y director) ────────────────
    Route::get('/admin/usuarios', [AdminUserController::class, 'index'])
        ->middleware('role:admin,director,direccion')
        ->name('admin.users.index');
    Route::get('/admin/usuarios/crear', [AdminUserController::class, 'create'])
        ->middleware('role:admin,director,direccion')
        ->name('admin.users.create');
    Route::post('/admin/usuarios', [AdminUserController::class, 'store'])
        ->middleware('role:admin,director,direccion')
        ->name('admin.users.store');
    Route::get('/admin/usuarios/{user}/editar', [AdminUserController::class, 'edit'])
        ->middleware('role:admin,director,direccion')
        ->name('admin.users.edit');
    Route::put('/admin/usuarios/{user}', [AdminUserController::class, 'update'])
        ->middleware('role:admin,director,direccion')
        ->name('admin.users.update');
    Route::post('/admin/usuarios/{user}/toggle', [AdminUserController::class, 'toggle'])
        ->middleware('role:admin,director,direccion')
        ->name('admin.users.toggle');

    Route::get('/admin/auditoria', [AdminUserController::class, 'audit'])
        ->middleware('audit.access')
        ->name('admin.audit');

    // ── Registro de Actividad (Bloque D) ─────────────────────────────────────
    Route::middleware('role:director,direccion')
        ->prefix('maestros')
        ->name('maestros.')
        ->group(function (): void {
            Route::get('/', MaestroController::class)->name('index');

            Route::get('/contratos', [ContratoController::class, 'index'])->name('contratos.index');
            Route::get('/contratos/crear', [ContratoController::class, 'create'])->name('contratos.create');
            Route::post('/contratos', [ContratoController::class, 'store'])->name('contratos.store');
            Route::get('/contratos/{contrato}/editar', [ContratoController::class, 'edit'])->name('contratos.edit');
            Route::put('/contratos/{contrato}', [ContratoController::class, 'update'])->name('contratos.update');
            Route::delete('/contratos/{contrato}', [ContratoController::class, 'destroy'])->name('contratos.destroy');

            Route::get('/sociedades-facturadoras', [ContratoEmpresaFacturadoraController::class, 'index'])->name('sociedades.index');
            Route::post('/sociedades-facturadoras', [ContratoEmpresaFacturadoraController::class, 'store'])->name('sociedades.store');
            Route::put('/sociedades-facturadoras/{sociedad}', [ContratoEmpresaFacturadoraController::class, 'update'])->name('sociedades.update');
            Route::delete('/sociedades-facturadoras/{sociedad}', [ContratoEmpresaFacturadoraController::class, 'destroy'])->name('sociedades.destroy');

            Route::get('/tarifarios', [TarifarioController::class, 'index'])->name('tarifarios.index');
            Route::get('/tarifarios/crear', [TarifarioController::class, 'create'])->name('tarifarios.create');
            Route::post('/tarifarios', [TarifarioController::class, 'store'])->name('tarifarios.store');
            Route::get('/tarifarios/{tarifario}/editar', [TarifarioController::class, 'edit'])->name('tarifarios.edit');
            Route::put('/tarifarios/{tarifario}', [TarifarioController::class, 'update'])->name('tarifarios.update');
            Route::delete('/tarifarios/{tarifario}', [TarifarioController::class, 'destroy'])->name('tarifarios.destroy');

            Route::get('/tarifario-lineas', [TarifarioLineaController::class, 'index'])->name('tarifario-lineas.index');
            Route::get('/tarifario-lineas/crear', [TarifarioLineaController::class, 'create'])->name('tarifario-lineas.create');
            Route::post('/tarifario-lineas', [TarifarioLineaController::class, 'store'])->name('tarifario-lineas.store');
            Route::get('/tarifario-lineas/{linea}/editar', [TarifarioLineaController::class, 'edit'])->name('tarifario-lineas.edit');
            Route::put('/tarifario-lineas/{linea}', [TarifarioLineaController::class, 'update'])->name('tarifario-lineas.update');
            Route::delete('/tarifario-lineas/{linea}', [TarifarioLineaController::class, 'destroy'])->name('tarifario-lineas.destroy');
        });

    Route::get('/registro-actividad', [AuditLogController::class, 'index'])
        ->middleware('permission:auditoria.ver')
        ->name('registro.actividad.index');
    Route::get('/registro-actividad/exportar', [AuditLogController::class, 'exportar'])
        ->middleware('permission:auditoria.exportar')
        ->name('registro.actividad.exportar');
    Route::delete('/registro-actividad/limpiar', [AuditLogController::class, 'limpiar'])
        ->middleware('permission:auditoria.limpiar')
        ->name('registro.actividad.limpiar');

    // ── Cierre ────────────────────────────────────────────────────────────────
    Route::middleware(['role:admin,director', 'permission:trabajos.ver'])->group(function () {
        Route::get('/cierre', [ClosureDashboardController::class, 'index'])->name('cierre.dashboard');
        Route::post('/cierre/revisar', [ClosureDashboardController::class, 'markReviewed'])
            ->middleware('permission:trabajos.cambiar_estado')
            ->name('cierre.review');
        Route::post('/cierre/cerrar', [ClosureDashboardController::class, 'bulkClose'])
            ->middleware('permission:trabajos.cambiar_estado')
            ->name('cierre.bulk-close');
        Route::post('/cierre/{trabajo}/cerrar', [ClosureDashboardController::class, 'close'])
            ->middleware('permission:trabajos.cambiar_estado')
            ->name('cierre.close');
    });

    if (app()->environment(['local', 'testing'])) {
        Route::get('/_preview/error/{status}', function (int $status) {
            abort_unless(in_array($status, [401, 403, 404, 419, 500, 503], true), 404);
            return Inertia::render('Error', ['status' => $status])
                ->toResponse(request())->setStatusCode($status);
        })->name('preview.error');
    }

    // ── Sprint 03 · Trabajos ──────────────────────────────────────────────────
    Route::middleware('permission:trabajos.ver')->group(function () {
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
                    'items' => fn ($query) => $query->withCount('facturaItems')->orderBy('id_pedido_item'),
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
                ->paginate(15)
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
                                    ->select('id_trabajo', 'id_contexto', 'numero_trabajo', 'numero_trabajo_operativo', 'descripcion_trabajo')
                                    ->whereIn('id_contexto', $accessibleContextIds)
                                    ->orderBy('numero_trabajo')
                                    ->get(),
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
                'items' => fn ($query) => $query->withCount('facturaItems')->orderBy('id_pedido_item'),
            ])->findOrFail($id);

            return Inertia::render('Pedidos/Form', [
                'pedido'         => new \App\Http\Resources\Api\PedidoResource($pedido),
                'trabajos'       => \App\Models\Trabajo::query()
                                    ->select('id_trabajo', 'id_contexto', 'numero_trabajo', 'numero_trabajo_operativo', 'descripcion_trabajo')
                                    ->whereIn('id_contexto', $accessibleContextIds)
                                    ->orderBy('numero_trabajo')
                                    ->get(),
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

        $buildPedidoItemsFacturables = static function (array $accessibleContextIds, ?int $facturaId = null) {
            if ($accessibleContextIds === []) {
                return collect();
            }

            return PedidoItem::query()
                ->with([
                    'pedido.trabajo.estacion',
                    'pedido.trabajo.contrato',
                    'pedido.tarifario',
                    'tarifarioLinea.tarifario.contrato',
                    'facturaItems' => fn ($query) => $query
                        ->when($facturaId !== null, fn ($nested) => $nested->where('id_factura', '!=', $facturaId)),
                ])
                ->whereIn('id_contexto', $accessibleContextIds)
                ->orderByDesc('id_pedido_item')
                ->limit(500)
                ->get()
                ->filter(fn (PedidoItem $item) => $item->pedido?->trabajo !== null)
                ->map(function (PedidoItem $item) {
                    $importeFacturado = (float) $item->facturaItems->sum(fn ($linea) => (float) $linea->importe_facturado);
                    $unidadesFacturadas = (float) $item->facturaItems->sum(fn ($linea) => (float) $linea->unidades_facturadas);
                    $importePendiente = max(0, (float) $item->total_linea - $importeFacturado);
                    $unidadesPendientes = max(0, (float) $item->cantidad - $unidadesFacturadas);
                    $pedido = $item->pedido;
                    $trabajo = $pedido->trabajo;
                    $tarifario = $item->tarifarioLinea?->tarifario ?? $pedido->tarifario;

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
                ->filter(fn (array $item) => $item['importe_pendiente'] > 0.01 || $item['unidades_pendientes'] > 0.001)
                ->values();
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
                            ->map(fn ($id) => (int) $id)
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

        Route::get('/facturas', function (\Illuminate\Http\Request $request) use ($facturaRelations) {
            $user = $request->user();
            $accessibleContextIds = $user->getActiveContextIds() ?? [];

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
                ->paginate(15)
                ->withQueryString();

            return Inertia::render('Facturas/Index', [
                'facturas'    => \App\Http\Resources\Api\FacturaResource::collection($facturas),
                'filters'     => $request->only(['search', 'estado', 'fecha_desde', 'fecha_hasta']),
                'contextoIds' => $user->getActiveContextIds() ?? [],
                'canCreate'   => $user->hasPermission('facturas.crear')
                    && ContextGuard::canCreateInActiveContext($user),
                'canExport'   => $user->hasPermission('facturas.exportar'),
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
                ->map(fn ($rows) => $rows->pluck('orden_factura')->map(fn ($orden) => (int) $orden)->values())
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
                ->map(fn ($rows) => $rows->pluck('orden_factura')->map(fn ($orden) => (int) $orden)->values())
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

    // ── Sprint 03 · Importaciones ─────────────────────────────────────────────
    Route::middleware('permission:importaciones.ver')->group(function () {
        Route::get('/importaciones',              [ImportacionController::class, 'index'])->name('importaciones.index');
        Route::get('/importaciones/preview/{id}', [ImportacionController::class, 'preview'])->name('importaciones.preview');
    });
    Route::get('/importaciones/subir', [ImportacionController::class, 'create'])
        ->middleware('permission:importaciones.ejecutar')
        ->name('importaciones.create');
    Route::post('/importaciones/procesar', [ImportacionController::class, 'store'])
        ->middleware('permission:importaciones.ejecutar')
        ->name('importaciones.store');
    Route::post('/importaciones/confirmar/{id}', [ImportacionController::class, 'confirm'])
        ->middleware('permission:importaciones.confirmar,importaciones.ejecutar')
        ->name('importaciones.confirm');
});

require __DIR__ . '/auth.php';

Route::fallback(function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }
    return Inertia::render('Error', ['status' => 404])
        ->toResponse(request())->setStatusCode(404);
});
