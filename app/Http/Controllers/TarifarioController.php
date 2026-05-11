<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Tarifario;
use App\Services\AuditLogger;
use App\Support\ContextGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TarifarioController extends Controller
{
    private const AUDIT_FIELDS = [
        'id_tarifario',
        'id_contexto',
        'id_contrato',
        'nombre',
        'version',
        'fecha_inicio_vigencia',
        'fecha_fin_vigencia',
        'factor_multiplicador',
        'moneda',
        'activo',
        'observaciones',
    ];

    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $activo = trim((string) $request->input('activo', ''));

        $tarifarios = Tarifario::query()
            ->with('contrato:id_contrato,id_contexto,codigo_contrato,nombre')
            ->withCount(['lineas', 'trabajos', 'pedidos'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('nombre', 'like', "%{$search}%")
                        ->orWhere('version', 'like', "%{$search}%")
                        ->orWhereHas('contrato', fn ($contratoQuery) => $contratoQuery
                            ->where('codigo_contrato', 'like', "%{$search}%")
                            ->orWhere('nombre', 'like', "%{$search}%"));
                });
            })
            ->when($activo !== '', fn ($query) => $query->where('activo', $activo === '1'))
            ->orderBy('id_contexto')
            ->orderBy('nombre')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Tarifarios/Index', [
            'tarifarios' => $tarifarios,
            'filters' => [
                'search' => $search,
                'activo' => $activo,
            ],
            'canCreate' => ($request->user()?->hasPermission('tarifarios.crear')
                || $request->user()?->hasAnyRole(['director', 'direccion']))
                && ContextGuard::canCreateInActiveContext($request->user()),
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        if (! ContextGuard::canCreateInActiveContext($request->user())) {
            return redirect()->route('maestros.tarifarios.index')
                ->with('error', ContextGuard::CREATE_FROM_ALL_MESSAGE);
        }

        return Inertia::render('Tarifarios/Form', [
            'tarifario' => null,
            'contratos' => $this->contratoOptions($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $contextId = $this->contextIdForCreate($request);
        $validated = $this->validatedData($request, $contextId);

        $tarifario = DB::transaction(function () use ($request, $validated): Tarifario {
            $tarifario = Tarifario::create($validated);
            $this->auditCreate($request, $tarifario);

            return $tarifario;
        });

        return redirect()
            ->route('maestros.tarifarios.edit', $tarifario)
            ->with('success', 'Tarifario creado correctamente.');
    }

    public function edit(Request $request, Tarifario $tarifario): Response
    {
        $this->ensureContextAccess($request, (int) $tarifario->id_contexto);

        return Inertia::render('Tarifarios/Form', [
            'tarifario' => $this->tarifarioPayload($tarifario->load('contrato')),
            'contratos' => $this->contratoOptions($request, (int) $tarifario->id_contexto),
        ]);
    }

    public function update(Request $request, Tarifario $tarifario): RedirectResponse
    {
        $this->ensureContextAccess($request, (int) $tarifario->id_contexto);
        $validated = $this->validatedData($request, (int) $tarifario->id_contexto, $tarifario);

        DB::transaction(function () use ($request, $tarifario, $validated): void {
            $before = $this->auditLogger->snapshotModel($tarifario, self::AUDIT_FIELDS);
            $tarifario->fill($validated);
            $tarifario->save();
            $this->auditUpdate($request, $tarifario, $before, 'Actualizacion de tarifario maestro.');
        });

        return redirect()
            ->route('maestros.tarifarios.edit', $tarifario)
            ->with('success', 'Tarifario actualizado correctamente.');
    }

    public function destroy(Request $request, Tarifario $tarifario): RedirectResponse
    {
        $this->ensureContextAccess($request, (int) $tarifario->id_contexto);

        DB::transaction(function () use ($request, $tarifario): void {
            $before = $this->auditLogger->snapshotModel($tarifario, self::AUDIT_FIELDS);
            $tarifario->forceFill(['activo' => false])->save();
            $this->auditUpdate($request, $tarifario, $before, 'Tarifario maestro desactivado. No se elimina historico relacionado.', 'desactivar');
        });

        return redirect()
            ->route('maestros.tarifarios.index')
            ->with('success', 'Tarifario desactivado correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, int $contextId, ?Tarifario $tarifario = null): array
    {
        $validated = $request->validate([
            'id_contrato' => [
                'required',
                'integer',
                Rule::exists('contratos', 'id_contrato')->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'nombre' => [
                'required',
                'string',
                'max:160',
                Rule::unique('tarifarios', 'nombre')
                    ->ignore($tarifario?->id_tarifario, 'id_tarifario')
                    ->where(fn ($query) => $query
                        ->where('id_contexto', $contextId)
                        ->where('version', $request->input('version'))),
            ],
            'version' => ['nullable', 'string', 'max:40'],
            'fecha_inicio_vigencia' => ['nullable', 'date'],
            'fecha_fin_vigencia' => ['nullable', 'date', 'after_or_equal:fecha_inicio_vigencia'],
            'factor_multiplicador' => ['required', 'numeric', 'min:0'],
            'moneda' => ['nullable', 'string', 'size:3'],
            'activo' => ['sometimes', 'boolean'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $validated['id_contexto'] = $contextId;
        $validated['moneda'] = strtoupper((string) ($validated['moneda'] ?? 'EUR'));
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
    private function contratoOptions(Request $request, ?int $contextId = null): array
    {
        $contextIds = $contextId ? [$contextId] : $request->user()->getActiveContextIds();

        return Contrato::query()
            ->whereIn('id_contexto', $contextIds)
            ->where('activo', true)
            ->orderBy('codigo_contrato')
            ->get(['id_contrato', 'id_contexto', 'codigo_contrato', 'nombre'])
            ->map(fn (Contrato $contrato) => [
                'id_contrato' => (int) $contrato->id_contrato,
                'id_contexto' => (int) $contrato->id_contexto,
                'codigo_contrato' => $contrato->codigo_contrato,
                'nombre' => $contrato->nombre,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function tarifarioPayload(Tarifario $tarifario): array
    {
        return [
            'id_tarifario' => (int) $tarifario->id_tarifario,
            'id_contexto' => (int) $tarifario->id_contexto,
            'id_contrato' => (int) $tarifario->id_contrato,
            'nombre' => $tarifario->nombre,
            'version' => $tarifario->version,
            'fecha_inicio_vigencia' => $tarifario->fecha_inicio_vigencia?->format('Y-m-d'),
            'fecha_fin_vigencia' => $tarifario->fecha_fin_vigencia?->format('Y-m-d'),
            'factor_multiplicador' => $tarifario->factor_multiplicador,
            'moneda' => $tarifario->moneda ?: 'EUR',
            'activo' => (bool) $tarifario->activo,
            'observaciones' => $tarifario->observaciones,
            'contrato' => $tarifario->contrato ? [
                'codigo_contrato' => $tarifario->contrato->codigo_contrato,
                'nombre' => $tarifario->contrato->nombre,
            ] : null,
        ];
    }

    private function auditCreate(Request $request, Tarifario $tarifario): void
    {
        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => 'crear',
            'modulo' => 'maestros.tarifarios',
            'tabla' => 'tarifarios',
            'entity_type' => Tarifario::class,
            'entity_id' => $tarifario->id_tarifario,
            'registro_id' => $tarifario->id_tarifario,
            'campo' => 'id_tarifario',
            'valor_nuevo' => $tarifario->id_tarifario,
            'datos_nuevos' => $this->auditLogger->snapshotModel($tarifario, self::AUDIT_FIELDS),
            'descripcion' => 'Alta de tarifario maestro.',
            'id_contexto' => $tarifario->id_contexto,
        ], $request);
    }

    private function auditUpdate(Request $request, Tarifario $tarifario, array $before, string $description, string $action = 'actualizar'): void
    {
        $after = $this->auditLogger->snapshotModel($tarifario->fresh(), self::AUDIT_FIELDS);
        $firstChange = $this->auditLogger->resolveFirstChange($before, $after);

        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => $action,
            'modulo' => 'maestros.tarifarios',
            'tabla' => 'tarifarios',
            'entity_type' => Tarifario::class,
            'entity_id' => $tarifario->id_tarifario,
            'registro_id' => $tarifario->id_tarifario,
            'campo' => $firstChange['campo'],
            'valor_anterior' => $firstChange['valor_anterior'],
            'valor_nuevo' => $firstChange['valor_nuevo'],
            'datos_anteriores' => $before,
            'datos_nuevos' => $after,
            'descripcion' => $this->auditLogger->buildChangedFieldsDescription($before, $after, $description),
            'id_contexto' => $tarifario->id_contexto,
        ], $request);
    }
}
