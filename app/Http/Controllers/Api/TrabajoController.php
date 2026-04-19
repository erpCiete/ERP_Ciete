<?php

namespace App\Http\Controllers\Api;

use App\Models\Trabajo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Api\StoreTrabajoRequest;
use App\Http\Requests\Api\UpdateTrabajoRequest;
use App\Http\Resources\Api\TrabajoResource;

class TrabajoController extends Controller
{
    /**
     * Muestra el listado principal de Obras (Trabajos).
     */
    public function index(Request $request): Response
    {
        // El trait HasContext filtra automáticamente según el usuario activo.
        $query = Trabajo::query()
            ->with(['empresa', 'estacion', 'contrato', 'tipoDocumento', 'tipoTrabajo']);

        // Implementación de búsqueda genérica básica (si se envía parámetro)
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('numero_trabajo', 'like', $searchTerm)
                ->orWhere('numero_aviso', 'like', $searchTerm)
                ->orWhere('descripcion_trabajo', 'like', $searchTerm);
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        $trabajos = $query->latest('fecha_encargo')->paginate(20)->withQueryString();

        return Inertia::render('Trabajos/Index', [
            // Pasamos por el Resource para que actúe el firewall de contexto (Tarea B03-03)
            'trabajos'    => TrabajoResource::collection($trabajos),
            'contextoIds' => $request->user()->getAccessibleContextIds(),
            'filtros'     => $request->only(['search', 'estado']),
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva Obra.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Trabajos/Form', [
            'trabajo'     => null,
            'contextoIds' => $request->user()->getAccessibleContextIds(),
            // @TODO: Aquí se inyectarían catálogos (empresas, contratos) si no se cargan vía API independiente.
        ]);
    }

    /**
     * Procesa y persiste una nueva Obra.
     */
    public function store(StoreTrabajoRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Si el Request no lo inyecta por defecto, asignamos el contexto del usuario creador
        if (!isset($validated['id_contexto'])) {
            $validated['id_contexto'] = $request->user()->id_contexto;
        }

        Trabajo::create($validated);

        return redirect()->route('trabajos.index')
            ->with('success', 'Obra registrada correctamente.');
    }

    /**
     * Muestra el formulario para editar una Obra existente.
     */
    public function edit(Request $request, Trabajo $trabajo): Response
    {
        // Eager loading para el recurso individual
        $trabajo->load(['empresa', 'estacion', 'contrato', 'tipoDocumento', 'tipoTrabajo']);

        return Inertia::render('Trabajos/Form', [
            'trabajo'     => new TrabajoResource($trabajo),
            'contextoIds' => $request->user()->getAccessibleContextIds(),
        ]);
    }

    /**
     * Actualiza la Obra en base de datos.
     */
    public function update(UpdateTrabajoRequest $request, Trabajo $trabajo): RedirectResponse
    {
        // Protección crítica de negocio: Bloqueo de Trabajos Cerrados
        if ($trabajo->cerrado && !$request->user()->hasRole('admin')) {
            abort(403, 'Acción denegada. Esta obra está cerrada y bloqueada para modificaciones.');
        }

        $trabajo->update($request->validated());

        return redirect()->route('trabajos.index')
            ->with('success', 'Obra actualizada correctamente.');
    }

    /**
     * Elimina una Obra (SoftDelete o HardDelete según el modelo).
     */
    public function destroy(Request $request, Trabajo $trabajo): RedirectResponse
    {
        // Protección crítica de negocio
        if ($trabajo->cerrado && !$request->user()->hasRole('admin')) {
            abort(403, 'Acción denegada. No se pueden eliminar obras que ya han sido cerradas.');
        }

        $trabajo->delete();

        return redirect()->route('trabajos.index')
            ->with('success', 'Obra eliminada del sistema.');
    }
}