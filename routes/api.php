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
        Route::middleware('permission:empresas_contactos.gestionar')->group(function () {
            Route::get('clientes', [ClienteController::class, 'index']);
            Route::post('clientes', [ClienteController::class, 'store']);
            Route::get('clientes/{cliente}', [ClienteController::class, 'show']);
            Route::match(['put', 'patch'], 'clientes/{cliente}', [ClienteController::class, 'update']);
            Route::delete('clientes/{cliente}', [ClienteController::class, 'destroy']);
        });

        Route::middleware('permission:estaciones.ver,estaciones.gestionar')->group(function () {
            Route::get('estaciones', [EstacionController::class, 'index']);
            Route::get('estaciones/{estacion}', [EstacionController::class, 'show']);
        });

        Route::middleware('permission:estaciones.gestionar')->group(function () {
            Route::post('estaciones', [EstacionController::class, 'store']);
            Route::match(['put', 'patch'], 'estaciones/{estacion}', [EstacionController::class, 'update']);
            Route::delete('estaciones/{estacion}', [EstacionController::class, 'destroy']);
        });
    });

    Route::middleware('auth')->group(function () {
        // API de Trabajos (Obras)
        Route::apiResource('trabajos', TrabajoApiController::class)
            ->middleware('permission:trabajos.ver');

        // API de Pedidos
        Route::get('pedidos', [PedidoController::class, 'index'])->middleware('permission:pedidos.ver');
        Route::get('pedidos/{pedido}', [PedidoController::class, 'show'])->middleware('permission:pedidos.ver');
        Route::middleware('permission:pedidos.gestionar')->group(function () {
            Route::post('pedidos', [PedidoController::class, 'store']);
            Route::match(['put', 'patch'], 'pedidos/{pedido}', [PedidoController::class, 'update']);
            Route::delete('pedidos/{pedido}', [PedidoController::class, 'destroy']);
        });

        // API de Facturas — B04-02
        Route::get('facturas', [FacturaController::class, 'index'])->middleware('permission:facturas.ver');
        Route::get('facturas/{factura}', [FacturaController::class, 'show'])->middleware('permission:facturas.ver');
        Route::middleware('permission:facturas.gestionar')->group(function () {
            Route::post('facturas', [FacturaController::class, 'store']);
            Route::match(['put', 'patch'], 'facturas/{factura}', [FacturaController::class, 'update']);
            Route::delete('facturas/{factura}', [FacturaController::class, 'destroy']);
        });
    });
});