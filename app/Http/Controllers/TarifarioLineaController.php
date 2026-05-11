<?php

namespace App\Http\Controllers;

use App\Models\Tarifario;
use App\Models\TarifarioLinea;
use App\Models\Unidad;
use App\Services\AuditLogger;
use App\Support\ContextGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TarifarioLineaController extends Controller
{
    private const AUDIT_FIELDS = [
        'id_tarifario_linea',
        'id_contexto',
        'id_tarifario',
        'codigo_tarifa',
        'grupo',
        'actuacion',
        'descripcion',
        'tarifa_anterior',
        'tarifa_base',
        'tarifa_aplicada',
        'id_unidad',
        'activo',
    ];

    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $tarifarioId = $request->integer('tarifario') ?: null;
        $activo = trim((string) $request->input('activo', ''));

        $lineas = TarifarioLinea::query()
            ->with([
                'tarifario:id_tarifario,id_contexto,id_contrato,nombre,version,activo',
                'tarifario.contrato:id_contrato,id_contexto,codigo_contrato,nombre',
                'unidad:id_unidad,nombre,abreviatura',
            ])
            ->withCount('pedidoItems')
            ->when($tarifarioId, fn ($query) => $query->where('id_tarifario', $tarifarioId))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('codigo_tarifa', 'like', "%{$search}%")
                        ->orWhere('actuacion', 'like', "%{$search}%")
                        ->orWhere('grupo', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->when($activo !== '', fn ($query) => $query->where('activo', $activo === '1'))
            ->orderBy('id_contexto')
            ->orderBy('id_tarifario')
            ->orderBy('codigo_tarifa')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Tarifarios/Lineas', [
            'lineas' => $lineas,
            'filters' => [
                'search' => $search,
                'tarifario' => $tarifarioId,
                'activo' => $activo,
            ],
            'tarifarios' => $this->tarifarioOptions($request),
            'unidades' => $this->unidadOptions(),
            'canCreate' => ($request->user()?->hasPermission('tarifario_lineas.crear')
                || $request->user()?->hasAnyRole(['director', 'direccion']))
                && ContextGuard::canCreateInActiveContext($request->user()),
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        if (! ContextGuard::canCreateInActiveContext($request->user())) {
            return redirect()->route('maestros.tarifario-lineas.index')
                ->with('error', ContextGuard::CREATE_FROM_ALL_MESSAGE);
        }

        return Inertia::render('Tarifarios/LineaForm', [
            'linea' => null,
            'tarifarios' => $this->tarifarioOptions($request),
            'unidades' => $this->unidadOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $contextId = $this->contextIdForCreate($request);
        $validated = $this->validatedData($request, $contextId);

        $linea = DB::transaction(function () use ($request, $validated): TarifarioLinea {
            $linea = TarifarioLinea::create($validated);
            $this->auditCreate($request, $linea);

            return $linea;
        });

        return redirect()
            ->route('maestros.tarifario-lineas.edit', $linea)
            ->with('success', 'Linea de tarifario creada correctamente.');
    }

    public function edit(Request $request, TarifarioLinea $linea): Response
    {
        $this->ensureContextAccess($request, (int) $linea->id_contexto);

        return Inertia::render('Tarifarios/LineaForm', [
            'linea' => $this->lineaPayload($linea->load(['tarifario.contrato', 'unidad'])),
            'tarifarios' => $this->tarifarioOptions($request, (int) $linea->id_contexto),
            'unidades' => $this->unidadOptions(),
        ]);
    }

    public function update(Request $request, TarifarioLinea $linea): RedirectResponse
    {
        $this->ensureContextAccess($request, (int) $linea->id_contexto);
        $validated = $this->validatedData($request, (int) $linea->id_contexto, $linea);

        DB::transaction(function () use ($request, $linea, $validated): void {
            $before = $this->auditLogger->snapshotModel($linea, self::AUDIT_FIELDS);
            $linea->fill($validated);
            $linea->save();
            $this->auditUpdate($request, $linea, $before, 'Actualizacion de linea de tarifario maestro.');
        });

        return redirect()
            ->route('maestros.tarifario-lineas.edit', $linea)
            ->with('success', 'Linea de tarifario actualizada correctamente.');
    }

    public function destroy(Request $request, TarifarioLinea $linea): RedirectResponse
    {
        $this->ensureContextAccess($request, (int) $linea->id_contexto);

        DB::transaction(function () use ($request, $linea): void {
            $before = $this->auditLogger->snapshotModel($linea, self::AUDIT_FIELDS);
            $linea->forceFill(['activo' => false])->save();
            $this->auditUpdate($request, $linea, $before, 'Linea de tarifario desactivada. No se elimina historico relacionado.', 'desactivar');
        });

        return redirect()
            ->route('maestros.tarifario-lineas.index', ['tarifario' => $linea->id_tarifario])
            ->with('success', 'Linea de tarifario desactivada correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, int $contextId, ?TarifarioLinea $linea = null): array
    {
        $validated = $request->validate([
            'id_tarifario' => [
                'required',
                'integer',
                Rule::exists('tarifarios', 'id_tarifario')->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'codigo_tarifa' => [
                'required',
                'string',
                'max:30',
                Rule::unique('tarifario_lineas', 'codigo_tarifa')
                    ->ignore($linea?->id_tarifario_linea, 'id_tarifario_linea')
                    ->where(fn ($query) => $query
                        ->where('id_contexto', $contextId)
                        ->where('id_tarifario', $request->input('id_tarifario'))),
            ],
            'grupo' => ['nullable', 'string', 'max:120'],
            'actuacion' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tarifa_anterior' => ['nullable', 'numeric', 'min:0'],
            'tarifa_base' => ['required', 'numeric', 'min:0'],
            'tarifa_aplicada' => ['required', 'numeric', 'min:0'],
            'id_unidad' => ['nullable', 'integer', Rule::exists('unidades', 'id_unidad')],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $validated['id_contexto'] = $contextId;
        $validated['activo'] = $request->boolean('activo', true);

        return $validated;
    }

    private function contextIdForCreate(Request $request): int
    {
        $contextId = ContextGuard::activeContextIdForCreate($request->user());

        abort_if($contextId === null, 403, ContextGuard::CREATE_FROM_ALL_MESSAGE);

        return (int) $contextId;
    }

    private function ensureContextAccess(Request $request, int $contextId): void
    {
        abort_unless(ContextGuard::canOperateContext($request->user(), $contextId), 403);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function tarifarioOptions(Request $request, ?int $contextId = null): array
    {
        $contextIds = $contextId ? [$contextId] : $request->user()->getActiveContextIds();

        return Tarifario::query()
            ->with('contrato:id_contrato,id_contexto,codigo_contrato,nombre')
            ->whereIn('id_contexto', $contextIds)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id_tarifario', 'id_contexto', 'id_contrato', 'nombre', 'version'])
            ->map(fn (Tarifario $tarifario) => [
                'id_tarifario' => (int) $tarifario->id_tarifario,
                'id_contexto' => (int) $tarifario->id_contexto,
                'nombre' => $tarifario->nombre,
                'version' => $tarifario->version,
                'contrato' => $tarifario->contrato ? [
                    'codigo_contrato' => $tarifario->contrato->codigo_contrato,
                    'nombre' => $tarifario->contrato->nombre,
                ] : null,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function unidadOptions(): array
    {
        return Unidad::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id_unidad', 'nombre', 'abreviatura'])
            ->map(fn (Unidad $unidad) => [
                'id_unidad' => (int) $unidad->id_unidad,
                'nombre' => $unidad->nombre,
                'abreviatura' => $unidad->abreviatura,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function lineaPayload(TarifarioLinea $linea): array
    {
        return [
            'id_tarifario_linea' => (int) $linea->id_tarifario_linea,
            'id_contexto' => (int) $linea->id_contexto,
            'id_tarifario' => (int) $linea->id_tarifario,
            'codigo_tarifa' => $linea->codigo_tarifa,
            'grupo' => $linea->grupo,
            'actuacion' => $linea->actuacion,
            'descripcion' => $linea->descripcion,
            'tarifa_anterior' => $linea->tarifa_anterior,
            'tarifa_base' => $linea->tarifa_base,
            'tarifa_aplicada' => $linea->tarifa_aplicada,
            'id_unidad' => $linea->id_unidad,
            'activo' => (bool) $linea->activo,
            'tarifario' => $linea->tarifario ? [
                'nombre' => $linea->tarifario->nombre,
                'version' => $linea->tarifario->version,
                'contrato' => $linea->tarifario->contrato ? [
                    'codigo_contrato' => $linea->tarifario->contrato->codigo_contrato,
                    'nombre' => $linea->tarifario->contrato->nombre,
                ] : null,
            ] : null,
        ];
    }

    private function auditCreate(Request $request, TarifarioLinea $linea): void
    {
        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => 'crear',
            'modulo' => 'maestros.tarifario_lineas',
            'tabla' => 'tarifario_lineas',
            'entity_type' => TarifarioLinea::class,
            'entity_id' => $linea->id_tarifario_linea,
            'registro_id' => $linea->id_tarifario_linea,
            'campo' => 'id_tarifario_linea',
            'valor_nuevo' => $linea->id_tarifario_linea,
            'datos_nuevos' => $this->auditLogger->snapshotModel($linea, self::AUDIT_FIELDS),
            'descripcion' => 'Alta de linea de tarifario maestro.',
            'id_contexto' => $linea->id_contexto,
        ], $request);
    }

    private function auditUpdate(Request $request, TarifarioLinea $linea, array $before, string $description, string $action = 'actualizar'): void
    {
        $after = $this->auditLogger->snapshotModel($linea->fresh(), self::AUDIT_FIELDS);
        $firstChange = $this->auditLogger->resolveFirstChange($before, $after);

        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => $action,
            'modulo' => 'maestros.tarifario_lineas',
            'tabla' => 'tarifario_lineas',
            'entity_type' => TarifarioLinea::class,
            'entity_id' => $linea->id_tarifario_linea,
            'registro_id' => $linea->id_tarifario_linea,
            'campo' => $firstChange['campo'],
            'valor_anterior' => $firstChange['valor_anterior'],
            'valor_nuevo' => $firstChange['valor_nuevo'],
            'datos_anteriores' => $before,
            'datos_nuevos' => $after,
            'descripcion' => $this->auditLogger->buildChangedFieldsDescription($before, $after, $description),
            'id_contexto' => $linea->id_contexto,
        ], $request);
    }
}
