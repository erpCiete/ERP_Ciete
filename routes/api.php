<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\EstacionController;
use App\Http\Controllers\Api\TrabajoController as TrabajoApiController;
use App\Http\Controllers\Api\PedidoController; // <--- Importación añadida
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
        // API de Trabajos (Obras) - Acceso controlado por permiso de ver
        Route::apiResource('trabajos', TrabajoApiController::class)
            ->middleware('permission:trabajos.ver');

        /**
         * API de Pedidos - SPRINT ACTUAL
         * Se está aplicando el middleware permission:pedidos.ver a todo el apiResource. 
         * Esto significa que un usuario que solo tenga permiso para ver podrá también ejecutar POST (crear) o DELETE (eliminar).
        **/
//      Route::apiResource('pedidos', PedidoController::class)
//         ->middleware('permission:pedidos.ver'); // <--- Recurso API añadido

        // Corrección: separar los permisos
        Route::get('pedidos', [PedidoController::class, 'index'])->middleware('permission:pedidos.ver');
        Route::get('pedidos/{pedido}', [PedidoController::class, 'show'])->middleware('permission:pedidos.ver');
        Route::middleware('permission:pedidos.gestionar')->group(function () {
            Route::post('pedidos', [PedidoController::class, 'store']);
            Route::match(['put', 'patch'], 'pedidos/{pedido}', [PedidoController::class, 'update']);
            Route::delete('pedidos/{pedido}', [PedidoController::class, 'destroy']);
        });
    });
});