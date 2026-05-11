<?php

namespace App\Http\Controllers;

use App\Models\Trabajo;
use App\Services\ClosureDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClosureDashboardController extends Controller
{
    public function __construct(
        private readonly ClosureDashboardService $closureDashboardService,
    ) {
    }

    public function index(Request $request): Response
    {
        return Inertia::render(
            'Cierre/Dashboard',
            $this->closureDashboardService->buildDashboardPayload($request->user())
        );
    }

    public function markReviewed(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $count = $this->closureDashboardService->markReviewed($request->user(), $validated['ids']);

        return back()->with('success', $count > 0
            ? 'Obras marcadas para revisión de finalización.'
            : 'No había obras pendientes de marcar para revisión.');
    }

    public function bulkClose(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $count = $this->closureDashboardService->closeMany($request->user(), $validated['ids']);

        return back()->with($count > 0 ? 'success' : 'error', $count > 0
            ? 'Obras finalizadas correctamente.'
            : 'Ninguna de las obras seleccionadas cumple todavia el checklist de finalizacion.');
    }

    public function close(Request $request, Trabajo $trabajo): RedirectResponse
    {
        $this->closureDashboardService->closeOne($trabajo, $request->user());

        return back()->with('success', 'Obra finalizada correctamente.');
    }

}
