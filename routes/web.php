<?php

use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Models\Empresa;
use App\Models\EstacionServicio;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/', function () {
    return Inertia::render('Welcome');
})->middleware('auth')->name('index');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('permission:proyectos.ver')->group(function () {
        Route::get('/proyectos', function () {
            return Inertia::render('Proyectos/Index');
        })->name('proyectos.index');
    });

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

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('admin.dashboard');
    });

    Route::middleware('role:control_cierre')->group(function () {
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
