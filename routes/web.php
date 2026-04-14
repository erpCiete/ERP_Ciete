<?php

use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TrabajoController;
use App\Http\Controllers\ImportacionController;
use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\MensajeInterno;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware(['auth', 'maintenance'])->group(function () {
    Route::get('/', function () {
        return Inertia::render('Welcome');
    })->name('index');
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('permission:empresas_contactos.gestionar')->group(function () {
        Route::get('/clientes', function () {
            return Inertia::render('Clientes/Index');
        })->name('clientes.index');

        Route::get('/clientes/crear', function () {
            return Inertia::render('Clientes/Form');
        })->name('clientes.create');

        Route::get('/clientes/{cliente}/editar', function (Empresa $cliente) {
            return Inertia::render('Clientes/Form', [
                'clienteId' => $cliente->id_empresa,
            ]);
        })->name('clientes.edit');
    });

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
            return Inertia::render('Estaciones/Form', [
                'estacionId' => $estacion->id_estacion_servicio,
            ]);
        })->name('estaciones.edit');
    });

    Route::get('/ayuda', function () {
        return Inertia::render('Help');
    })->name('help');

    Route::get('/estado', [StatusController::class, 'index'])->name('status');

    Route::get('/mensajes', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/mensajes', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/mensajes/{mensaje}', [MessageController::class, 'show'])
        ->where('mensaje', '[0-9]+')
        ->name('messages.show');
    Route::post('/mensajes/{mensaje}/leer', [MessageController::class, 'markRead'])
        ->where('mensaje', '[0-9]+')
        ->name('messages.read');
    Route::post('/mensajes/{mensaje}/archivar', [MessageController::class, 'archive'])
        ->where('mensaje', '[0-9]+')
        ->name('messages.archive');

    Route::get('/soporte', [SupportController::class, 'index'])->name('support');
    Route::post('/soporte', [SupportController::class, 'send'])->name('support.send');

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('admin.dashboard');

        Route::post('/admin/maintenance', [MaintenanceController::class, 'toggle'])
            ->name('admin.maintenance.toggle');

        Route::post('/mensajes/broadcast', [MessageController::class, 'broadcast'])
            ->name('messages.broadcast');
    });

    Route::middleware('role:cierre')->group(function () {
        Route::get('/cierre', function () {
            return Inertia::render('Cierre/Dashboard');
        })->name('cierre.dashboard');
    });

    if (app()->environment(['local', 'testing'])) {
        Route::get('/_preview/error/{status}', function (int $status) {
            abort_unless(in_array($status, [401, 403, 404, 419, 500, 503], true), 404);

            return Inertia::render('Error', [
                'status' => $status,
            ])->toResponse(request())->setStatusCode($status);
        })->name('preview.error');
    }

    //Modulo de Trabajos (Obras) - SPRINT 03
    Route::middleware('permission:trabajos.ver')->group(function () {
        // Rutas de visualización y formularios
        Route::get('/trabajos', [TrabajoController::class, 'index'])->name('trabajos.index');
        Route::get('/trabajos/crear', [TrabajoController::class, 'create'])->name('trabajos.create');
        Route::get('/trabajos/{trabajo}/editar', [TrabajoController::class, 'edit'])->name('trabajos.edit');
        
        // Rutas de acción (mutaciones) con permisos específicos
        Route::post('/trabajos', [TrabajoController::class, 'store'])
            ->name('trabajos.store')
            ->middleware('permission:trabajos.crear');
            
        Route::put('/trabajos/{trabajo}', [TrabajoController::class, 'update'])
            ->name('trabajos.update')
            ->middleware('permission:trabajos.editar');
            
        Route::delete('/trabajos/{trabajo}', [TrabajoController::class, 'destroy'])
            ->name('trabajos.destroy')
            ->middleware('permission:trabajos.eliminar');
    });

    //Modulo de Importaciones - SPRINT 03
    Route::middleware('permission:importaciones.gestionar')->group(function () {
        Route::get('/importaciones', [ImportacionController::class, 'index'])->name('importaciones.index');
        Route::get('/importaciones/subir', [ImportacionController::class, 'create'])->name('importaciones.create');
        Route::post('/importaciones/procesar', [ImportacionController::class, 'store'])->name('importaciones.store');
        Route::get('/importaciones/preview/{id}', [ImportacionController::class, 'preview'])->name('importaciones.preview');
        Route::post('/importaciones/confirmar/{id}', [ImportacionController::class, 'confirm'])->name('importaciones.confirm');
    });
});

require __DIR__ . '/auth.php';

Route::fallback(function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return Inertia::render('Error', [
        'status' => 404,
    ])->toResponse(request())->setStatusCode(404);
});
