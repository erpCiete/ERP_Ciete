<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\EstacionController;
use App\Http\Controllers\Api\FacturacionController;
use App\Http\Controllers\Api\InformeController;
use App\Http\Controllers\Api\LegalizacionController;
use App\Http\Controllers\Api\ObraController;
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

        Route::apiResource('obras', ObraController::class);
        Route::patch('obras/{obra}/estado', [ObraController::class, 'updateEstado'])->name('obras.updateEstado');

        Route::get('obras/{obra}/legalizaciones', [LegalizacionController::class, 'index'])
            ->name('obras.legalizaciones.index');
        Route::post('obras/{obra}/legalizaciones', [LegalizacionController::class, 'store'])
            ->name('obras.legalizaciones.store');

        Route::apiResource('pedidos', PedidoController::class);
        Route::get('facturacion/pendientes', [FacturacionController::class, 'index'])->name('facturacion.pendientes');
        Route::get('informes/obras', [InformeController::class, 'index'])->name('informes.obras');
    });
});
