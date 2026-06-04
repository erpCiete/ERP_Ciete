<?php

use App\Http\Controllers\ClosureDashboardController;
use App\Models\Trabajo;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'maintenance'])->group(function () {
    Route::get('/dashboard', function () {
        abort(403);
    })->name('dashboard');
    // ── Cierre ────────────────────────────────────────────────────────────────
    Route::middleware(['role:director,direccion', 'permission:trabajos.ver'])->group(function () {
        Route::get('/cierre', [ClosureDashboardController::class, 'index'])->name('cierre.dashboard');
        Route::post('/cierre/revisar', [ClosureDashboardController::class, 'markReviewed'])
            ->middleware('permission:trabajos.cambiar_estado')
            ->name('cierre.review');
        Route::post('/cierre/cerrar', [ClosureDashboardController::class, 'bulkClose'])
            ->middleware('permission:trabajos.cambiar_estado')
            ->name('cierre.bulk-close');
        Route::post('/cierre/{trabajo}/cerrar', [ClosureDashboardController::class, 'close'])
            ->middleware('permission:trabajos.cambiar_estado')
            ->name('cierre.close');
    });
});
