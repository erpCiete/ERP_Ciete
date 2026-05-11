<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\EstacionController;
use App\Http\Controllers\Api\FacturaController;
use App\Http\Controllers\Api\TrabajoController as TrabajoApiController;
use App\Http\Controllers\Api\PedidoController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('web')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    Route::middleware('auth')->group(function () {
        Route::get('clientes', [ClienteController::class, 'index'])->middleware('permission:clientes.ver');
        Route::get('clientes/{cliente}', [ClienteController::class, 'show'])->middleware('permission:clientes.ver');
        Route::post('clientes', [ClienteController::class, 'store'])->middleware('permission:clientes.crear');
        Route::match(['put', 'patch'], 'clientes/{cliente}', [ClienteController::class, 'update'])->middleware('permission:clientes.editar');
        Route::delete('clientes/{cliente}', [ClienteController::class, 'destroy'])->middleware('permission:clientes.eliminar');

        Route::middleware('permission:estaciones.ver')->group(function () {
            Route::get('estaciones', [EstacionController::class, 'index']);
            Route::get('estaciones/{estacion}', [EstacionController::class, 'show']);
        });

        Route::post('estaciones', [EstacionController::class, 'store'])->middleware('permission:estaciones.crear');
        Route::match(['put', 'patch'], 'estaciones/{estacion}', [EstacionController::class, 'update'])->middleware('permission:estaciones.editar');
        Route::delete('estaciones/{estacion}', [EstacionController::class, 'destroy'])->middleware('permission:estaciones.eliminar');
    });

    Route::middleware('auth')->group(function () {
        // API de Trabajos (Obras)
        Route::get('trabajos', [TrabajoApiController::class, 'index'])->name('api.trabajos.index')->middleware('permission:trabajos.ver');
        Route::get('trabajos/{trabajo}', [TrabajoApiController::class, 'show'])->name('api.trabajos.show')->middleware('permission:trabajos.ver');
        Route::post('trabajos', [TrabajoApiController::class, 'store'])->name('api.trabajos.store')->middleware('permission:trabajos.crear');
        Route::match(['put', 'patch'], 'trabajos/{trabajo}', [TrabajoApiController::class, 'update'])->name('api.trabajos.update')->middleware('permission:trabajos.editar');
        Route::delete('trabajos/{trabajo}', [TrabajoApiController::class, 'destroy'])->name('api.trabajos.destroy')->middleware('permission:trabajos.eliminar');

        // API de Pedidos
        Route::get('pedidos', [PedidoController::class, 'index'])->middleware('permission:pedidos.ver');
        Route::get('pedidos/{pedido}', [PedidoController::class, 'show'])->middleware('permission:pedidos.ver');
        Route::post('pedidos', [PedidoController::class, 'store'])->middleware('permission:pedidos.crear');
        Route::match(['put', 'patch'], 'pedidos/{pedido}', [PedidoController::class, 'update'])->middleware('permission:pedidos.editar');
        Route::delete('pedidos/{pedido}', [PedidoController::class, 'destroy'])->middleware('permission:pedidos.eliminar');

        // API de Facturas — B04-02
        Route::get('facturas', [FacturaController::class, 'index'])->middleware('permission:facturas.ver');
        Route::get('facturas/export', [FacturaController::class, 'exportIndex'])
            ->middleware('permission:facturas.exportar')
            ->name('api.facturas.export');
        Route::get('facturas/{factura}/export', [FacturaController::class, 'exportDetail'])
            ->middleware('permission:facturas.exportar')
            ->name('api.facturas.export-detail');
        Route::get('facturas/{factura}', [FacturaController::class, 'show'])->middleware('permission:facturas.ver');
        Route::post('facturas', [FacturaController::class, 'store'])->middleware('permission:facturas.crear');
        Route::match(['put', 'patch'], 'facturas/{factura}', [FacturaController::class, 'update'])->middleware('permission:facturas.editar');
        Route::delete('facturas/{factura}', [FacturaController::class, 'destroy'])->middleware('permission:facturas.eliminar');
    });
});
