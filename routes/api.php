<?php

use App\Http\Controllers\Api\AuthController;
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
        Route::apiResource('obras', ObraController::class);
        Route::patch('obras/{obra}/estado', [ObraController::class, 'updateEstado'])->name('obras.updateEstado');

        Route::get('obras/{obra}/legalizaciones', [LegalizacionController::class, 'index'])
            ->name('obras.legalizaciones.index');
        Route::post('obras/{obra}/legalizaciones', [LegalizacionController::class, 'store'])
            ->name('obras.legalizaciones.store');

        Route::apiResource('pedidos', PedidoController::class);

        Route::apiResource('estaciones', EstacionController::class); //
        Route::get('facturacion/pendientes', [FacturacionController::class, 'index'])->name('facturacion.pendientes');
        Route::get('informes/obras', [InformeController::class, 'index'])->name('informes.obras');
    });
});
