<?php

use App\Http\Controllers\ContratoController;
use App\Http\Controllers\ContratoEmpresaFacturadoraController;
use App\Http\Controllers\ContratosTarifasController;
use App\Http\Controllers\MaestroController;
use App\Http\Controllers\TarifarioController;
use App\Http\Controllers\TarifarioLineaController;
use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Support\ContextGuard;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'maintenance'])->group(function () {
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
    // ── Registro de Actividad (Bloque D) ─────────────────────────────────────
    Route::prefix('maestros')
        ->name('maestros.')
        ->group(function (): void {
            Route::get('/', MaestroController::class)
                ->middleware('permission:maestros.ver')
                ->name('index');

            Route::get('/contratos-tarifas', ContratosTarifasController::class)
                ->middleware('permission:maestros.ver')
                ->name('contratos-tarifas');

            Route::get('/contratos', [ContratoController::class, 'index'])
                ->middleware('permission:contratos.ver')
                ->name('contratos.index');
            Route::get('/contratos/crear', [ContratoController::class, 'create'])
                ->middleware('permission:contratos.crear')
                ->name('contratos.create');
            Route::post('/contratos', [ContratoController::class, 'store'])
                ->middleware('permission:contratos.crear')
                ->name('contratos.store');
            Route::get('/contratos/{contrato}/editar', [ContratoController::class, 'edit'])
                ->middleware('permission:contratos.editar')
                ->name('contratos.edit');
            Route::put('/contratos/{contrato}', [ContratoController::class, 'update'])
                ->middleware('permission:contratos.editar')
                ->name('contratos.update');
            Route::delete('/contratos/{contrato}', [ContratoController::class, 'destroy'])
                ->middleware('permission:contratos.eliminar')
                ->name('contratos.destroy');

            Route::get('/sociedades-facturadoras', [ContratoEmpresaFacturadoraController::class, 'index'])
                ->middleware('permission:sociedades_facturadoras.ver')
                ->name('sociedades.index');
            Route::post('/sociedades-facturadoras', [ContratoEmpresaFacturadoraController::class, 'store'])
                ->middleware('permission:sociedades_facturadoras.crear')
                ->name('sociedades.store');
            Route::put('/sociedades-facturadoras/{sociedad}', [ContratoEmpresaFacturadoraController::class, 'update'])
                ->middleware('permission:sociedades_facturadoras.editar')
                ->name('sociedades.update');
            Route::delete('/sociedades-facturadoras/{sociedad}', [ContratoEmpresaFacturadoraController::class, 'destroy'])
                ->middleware('permission:sociedades_facturadoras.eliminar')
                ->name('sociedades.destroy');

            Route::get('/tarifarios', [TarifarioController::class, 'index'])
                ->middleware('permission:tarifarios.ver')
                ->name('tarifarios.index');
            Route::get('/tarifarios/crear', [TarifarioController::class, 'create'])
                ->middleware('permission:tarifarios.crear')
                ->name('tarifarios.create');
            Route::post('/tarifarios', [TarifarioController::class, 'store'])
                ->middleware('permission:tarifarios.crear')
                ->name('tarifarios.store');
            Route::get('/tarifarios/{tarifario}/editar', [TarifarioController::class, 'edit'])
                ->middleware('permission:tarifarios.editar')
                ->name('tarifarios.edit');
            Route::put('/tarifarios/{tarifario}', [TarifarioController::class, 'update'])
                ->middleware('permission:tarifarios.editar')
                ->name('tarifarios.update');
            Route::put('/tarifarios/{tarifario}/predeterminado', [TarifarioController::class, 'markAsDefault'])
                ->middleware('permission:tarifarios.editar')
                ->name('tarifarios.set-default');
            Route::delete('/tarifarios/{tarifario}', [TarifarioController::class, 'destroy'])
                ->middleware('permission:tarifarios.eliminar')
                ->name('tarifarios.destroy');

            Route::get('/tarifario-lineas', [TarifarioLineaController::class, 'index'])
                ->middleware('permission:tarifario_lineas.ver')
                ->name('tarifario-lineas.index');
            Route::get('/tarifario-lineas/crear', [TarifarioLineaController::class, 'create'])
                ->middleware('permission:tarifario_lineas.crear')
                ->name('tarifario-lineas.create');
            Route::post('/tarifario-lineas', [TarifarioLineaController::class, 'store'])
                ->middleware('permission:tarifario_lineas.crear')
                ->name('tarifario-lineas.store');
            Route::get('/tarifario-lineas/{linea}/editar', [TarifarioLineaController::class, 'edit'])
                ->middleware('permission:tarifario_lineas.editar')
                ->name('tarifario-lineas.edit');
            Route::put('/tarifario-lineas/{linea}', [TarifarioLineaController::class, 'update'])
                ->middleware('permission:tarifario_lineas.editar')
                ->name('tarifario-lineas.update');
            Route::delete('/tarifario-lineas/{linea}', [TarifarioLineaController::class, 'destroy'])
                ->middleware('permission:tarifario_lineas.eliminar')
                ->name('tarifario-lineas.destroy');
        });
});
