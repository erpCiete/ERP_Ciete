<?php

use App\Http\Controllers\ClosureDashboardController;
use App\Models\Trabajo;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'maintenance'])->group(function () {
    Route::get('/dashboard', function () {
        $todasLasObras = Trabajo::all();
        $ultimosPedidos = \App\Models\Pedido::with('trabajo')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($pedido) {
                return [
                    // En el Dashboard usabas 'ref', 'fecha' y 'estado'
                    'ref'    => $pedido->numero_pedido ?? ('Pedido #' . $pedido->id_pedido),
                    'fecha'  => $pedido->fecha_solicitud ? \Carbon\Carbon::parse($pedido->fecha_solicitud)->format('d/m/Y') : '—',
                    'estado' => $pedido->estado ?? 'pendiente',
                ];
            });
        return Inertia::render('Dashboard', [
            'obras'           => $todasLasObras->take(5),
            'totalObrasCount' => $todasLasObras->count(),
            'pedidos'         => $ultimosPedidos,
            'legalizaciones'  => [],
        ]);
    })->name('dashboard')->middleware('role:director,direccion');
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
