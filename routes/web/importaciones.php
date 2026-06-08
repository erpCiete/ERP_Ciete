<?php

use App\Http\Controllers\ImportacionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'maintenance'])->group(function () {
    // ── Importaciones ─────────────────────────────────────────────────────────
    Route::middleware('permission:importaciones.ver')->group(function () {
        Route::get('/importaciones', [ImportacionController::class, 'index'])->name('importaciones.index');
    });
    Route::post('/importaciones/procesar', [ImportacionController::class, 'store'])
        ->middleware('permission:importaciones.ejecutar')
        ->name('importaciones.store');
    Route::post('/importaciones/confirmar/{id}', [ImportacionController::class, 'confirm'])
        ->middleware('permission:importaciones.confirmar,importaciones.ejecutar')
        ->name('importaciones.confirm');
});
