<?php

use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\NoticeController as AdminNoticeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ImportacionController;

// Sprint 03 — el TrabajoController web renderiza Inertia (namespace raíz)
// Vive en app/Http/Controllers/Api/TrabajoController.php
// con namespace App\Http\Controllers\Api
use App\Http\Controllers\Api\TrabajoController;
use App\Http\Controllers\Api\FacturaController;
use App\Http\Controllers\Api\PedidoController;

use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\Trabajo;
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

    Route::get('/', function () {
        return Inertia::render('Welcome', [
            'homeNotices' => AdminNoticeController::load(),
        ]);
    })->name('index');

    Route::get('/dashboard', function () {
        $todasLasObras = Trabajo::all();
        return Inertia::render('Dashboard', [
            'obras'           => $todasLasObras->take(5),
            'totalObrasCount' => $todasLasObras->count(),
            'pedidos'         => [],
            'legalizaciones'  => [],
        ]);
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // ── Clientes ─────────────────────────────────────────────────────────────
    Route::middleware('permission:empresas_contactos.gestionar')->group(function () {
        Route::get('/clientes', function () {
            return Inertia::render('Clientes/Index');
        })->name('clientes.index');
        Route::get('/clientes/crear', function () {
            return Inertia::render('Clientes/Form');
        })->name('clientes.create');
        Route::get('/clientes/{cliente}/editar', function (Empresa $cliente) {
            return Inertia::render('Clientes/Form', ['clienteId' => $cliente->id_empresa]);
        })->name('clientes.edit');
    });

    // ── Estaciones ────────────────────────────────────────────────────────────
    Route::middleware('permission:estaciones.ver,estaciones.gestionar')->group(function () {
        Route::get('/estaciones', function () {
            return Inertia::render('Estaciones/Index');
        })->name('estaciones.index');
    });
    Route::middleware('permission:estaciones.gestionar')->group(function () {
        Route::get('/estaciones/crear', function () {
            return Inertia::render('Estaciones/Form');
        })->name('estaciones.create');
        Route::get('/estaciones/{estacion}/editar', function (EstacionServicio $estacion) {
            return Inertia::render('Estaciones/Form', ['estacionId' => $estacion->id_estacion_servicio]);
        })->name('estaciones.edit');
    });

    // ── Páginas generales ─────────────────────────────────────────────────────
    Route::get('/ayuda',   function () { return Inertia::render('Help'); })->name('help');
    Route::get('/estado',  [StatusController::class, 'index'])->name('status');
    Route::get('/soporte', [SupportController::class, 'index'])->name('support');
    Route::post('/soporte',[SupportController::class, 'send'])->name('support.send');

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
        Route::get('/admin/usuarios',             [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/usuarios/crear',       [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/usuarios',            [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/usuarios/{user}/editar',[AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/usuarios/{user}',      [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::post('/admin/usuarios/{user}/toggle',[AdminUserController::class, 'toggle'])->name('admin.users.toggle');
        Route::get('/admin/auditoria',            [AdminUserController::class, 'audit'])->name('admin.audit');
    });

    // ── Cierre ────────────────────────────────────────────────────────────────
    Route::middleware('role:cierre')->group(function () {
        Route::get('/cierre', function () {
            return Inertia::render('Cierre/Dashboard');
        })->name('cierre.dashboard');
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
        Route::get('/trabajos/crear',        [TrabajoController::class, 'create'])->name('trabajos.create');
        Route::get('/trabajos/{trabajo}/editar', [TrabajoController::class, 'edit'])->name('trabajos.edit');
        Route::post('/trabajos',             [TrabajoController::class, 'store'])
            ->name('trabajos.store')->middleware('permission:trabajos.crear');
        Route::put('/trabajos/{trabajo}',    [TrabajoController::class, 'update'])
            ->name('trabajos.update')->middleware('permission:trabajos.editar');
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
            $isAdmin = $user->id_contexto === 3 || $user->hasRole('admin');

            $search = trim((string) $request->input('search', ''));
            $estado = trim((string) $request->input('estado', ''));

            $pedidos = \App\Models\Pedido::query()
                ->with(['trabajo', 'items'])
                ->when(!$isAdmin, function ($q) use ($user) {
                    $q->where('id_contexto', $user->id_contexto);
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
                'contextoIds' => $user->getAccessibleContextIds() ?? [],
                'canCreate'   => $user->hasPermission('pedidos.gestionar'),
            ]);
        })->name('pedidos.index');

        Route::get('/pedidos/crear', function () {
            return Inertia::render('Pedidos/Form', [
                'pedido'      => null,
                'trabajos'    => \App\Models\Trabajo::select('id_trabajo', 'numero_trabajo', 'descripcion_trabajo')
                                    ->orderBy('numero_trabajo')
                                    ->get(),
                'contextoIds' => auth()->user()->getAccessibleContextIds() ?? [],
            ]);
        })->name('pedidos.create');
        
        Route::get('/pedidos/{id}/editar', function ($id) {
            return Inertia::render('Pedidos/Form', [
                'pedido'      => null,
                'trabajos'    => \App\Models\Trabajo::select('id_trabajo', 'numero_trabajo', 'descripcion_trabajo')
                                    ->orderBy('numero_trabajo')
                                    ->get(),
                'contextoIds' => auth()->user()->getAccessibleContextIds() ?? [],
            ]);
        })->name('pedidos.edit');

        // Las rutas de mutación no son necesarias en web.php porque el Form usa axios
        // directamente contra /api/v1/pedidos. Se definen aquí solo para que
        // router.delete() de Inertia funcione en el modal de confirmación del Index.
        Route::delete('/pedidos/{pedido}', [PedidoController::class, 'destroy'])
                ->name('pedidos.destroy')
                ->middleware('permission:pedidos.gestionar');
    });

    // ── Sprint 04 · Facturas ──────────────────────────────────────────────────
    Route::middleware('permission:facturas.ver')->group(function () {
        Route::get('/facturas', function (\Illuminate\Http\Request $request) {
            $user = $request->user();
            $isAdmin = $user->id_contexto === 3 || $user->hasRole('admin');

            $search = trim((string) $request->input('search', ''));
            $estado = trim((string) $request->input('estado', ''));

            $facturas = \App\Models\Factura::query()
                ->with('trabajo')
                ->when(!$isAdmin, function ($q) use ($user) {
                    $q->where('id_contexto', $user->id_contexto);
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
                'contextoIds' => $user->getAccessibleContextIds() ?? [],
                'canCreate'   => $user->hasPermission('facturas.gestionar'),
            ]);
        })->name('facturas.index');

        Route::get('/facturas/crear', function () {
            return Inertia::render('Facturas/Form', [
                'factura'     => null,
                'trabajos'    => \App\Models\Trabajo::select('id_trabajo', 'numero_trabajo', 'descripcion_trabajo')
                                    ->orderBy('numero_trabajo')
                                    ->get(),
                'contextoIds' => auth()->user()->getAccessibleContextIds() ?? [],
            ]);
        })->name('facturas.create');
        
        Route::get('/facturas/{id}/editar', function ($id) {
            return Inertia::render('Facturas/Form', [
                'factura'     => null,
                'trabajos'    => \App\Models\Trabajo::select('id_trabajo', 'numero_trabajo', 'descripcion_trabajo')
                                    ->orderBy('numero_trabajo')
                                    ->get(),
                'contextoIds' => auth()->user()->getAccessibleContextIds() ?? [],
            ]);
        })->name('facturas.edit');

        Route::delete('/facturas/{factura}', [FacturaController::class, 'destroy'])
                ->name('facturas.destroy')
                ->middleware('permission:facturas.gestionar');
    });

    // ── Sprint 03 · Importaciones ─────────────────────────────────────────────
    Route::middleware('permission:importaciones.ejecutar')->group(function () {
        Route::get('/importaciones',              [ImportacionController::class, 'index'])->name('importaciones.index');
        Route::get('/importaciones/subir',        [ImportacionController::class, 'create'])->name('importaciones.create');
        Route::post('/importaciones/procesar',    [ImportacionController::class, 'store'])->name('importaciones.store');
        Route::get('/importaciones/preview/{id}', [ImportacionController::class, 'preview'])->name('importaciones.preview');
        Route::post('/importaciones/confirmar/{id}',[ImportacionController::class, 'confirm'])->name('importaciones.confirm');
    });
});

require __DIR__ . '/auth.php';

Route::fallback(function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }
    return Inertia::render('Error', ['status' => 404])
        ->toResponse(request())->setStatusCode(404);
});
