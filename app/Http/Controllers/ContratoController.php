<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Empresa;
use App\Services\AuditLogger;
use App\Support\ContextGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ContratoController extends Controller
{
    private const AUDIT_FIELDS = [
        'id_contrato',
        'id_contexto',
        'id_empresa_cliente',
        'codigo_contrato',
        'nombre',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'activo',
        'observaciones',
    ];

    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $estado = trim((string) $request->input('estado', ''));
        $activo = trim((string) $request->input('activo', ''));

        $contratos = Contrato::query()
            ->with('empresa:id_empresa,id_contexto,nombre,cif,tipo_empresa,activo')
            ->withCount(['tarifarios', 'sociedadesFacturadoras', 'trabajos', 'facturas'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('codigo_contrato', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%")
                        ->orWhereHas('empresa', fn ($empresaQuery) => $empresaQuery->where('nombre', 'like', "%{$search}%"));
                });
            })
            ->when($estado !== '', fn ($query) => $query->where('estado', $estado))
            ->when($activo !== '', fn ($query) => $query->where('activo', $activo === '1'))
            ->orderBy('id_contexto')
            ->orderBy('codigo_contrato')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Contratos/Index', [
            'contratos' => $contratos,
            'filters' => [
                'search' => $search,
                'estado' => $estado,
                'activo' => $activo,
            ],
            'canCreate' => ($request->user()?->hasPermission('contratos.crear')
                || $request->user()?->hasAnyRole(['director', 'direccion']))
                && ContextGuard::canCreateInActiveContext($request->user()),
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        if (! ContextGuard::canCreateInActiveContext($request->user())) {
            return redirect()->route('maestros.contratos.index')
                ->with('error', ContextGuard::CREATE_FROM_ALL_MESSAGE);
        }

        return Inertia::render('Contratos/Form', [
            'contrato' => null,
            'empresas' => $this->empresaOptions($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $contextId = $this->contextIdForCreate($request);
        $validated = $this->validatedData($request, $contextId);

        $contrato = DB::transaction(function () use ($request, $validated): Contrato {
            $contrato = Contrato::create($validated);
            $this->auditCreate($request, $contrato);

            return $contrato;
        });

        return redirect()
            ->route('maestros.contratos.edit', $contrato)
            ->with('success', 'Contrato creado correctamente.');
    }

    public function edit(Request $request, Contrato $contrato): Response
    {
        $this->ensureContextAccess($request, (int) $contrato->id_contexto);

        return Inertia::render('Contratos/Form', [
            'contrato' => $this->contratoPayload($contrato->load('empresa')),
            'empresas' => $this->empresaOptions($request, (int) $contrato->id_contexto),
        ]);
    }

    public function update(Request $request, Contrato $contrato): RedirectResponse
    {
        $this->ensureContextAccess($request, (int) $contrato->id_contexto);
        $validated = $this->validatedData($request, (int) $contrato->id_contexto, $contrato);

        DB::transaction(function () use ($request, $contrato, $validated): void {
            $before = $this->auditLogger->snapshotModel($contrato, self::AUDIT_FIELDS);
            $contrato->fill($validated);
            $contrato->save();
            $this->auditUpdate($request, $contrato, $before, 'Actualizacion de contrato maestro');
        });

        return redirect()
            ->route('maestros.contratos.edit', $contrato)
            ->with('success', 'Contrato actualizado correctamente.');
    }

    public function destroy(Request $request, Contrato $contrato): RedirectResponse
    {
        $this->ensureContextAccess($request, (int) $contrato->id_contexto);
        $hasOperationalHistory = $contrato->trabajos()->exists() || $contrato->facturas()->exists();

        DB::transaction(function () use ($request, $contrato): void {
            $before = $this->auditLogger->snapshotModel($contrato, self::AUDIT_FIELDS);
            $contrato->forceFill([
                'activo' => false,
                'estado' => 'cancelado',
            ])->save();
            $this->auditUpdate($request, $contrato, $before, 'Contrato maestro desactivado. No se elimina historico relacionado.', 'desactivar');
        });

        return redirect()
            ->route('maestros.contratos.index')
            ->with('success', $hasOperationalHistory
                ? 'Contrato desactivado correctamente. Conserva trabajos o facturas históricas asociadas.'
                : 'Contrato desactivado correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, int $contextId, ?Contrato $contrato = null): array
    {
        $validated = $request->validate([
            'id_empresa_cliente' => [
                'required',
                'integer',
                Rule::exists('empresas', 'id_empresa')->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'codigo_contrato' => [
                'required',
                'string',
                'max:100',
                Rule::unique('contratos', 'codigo_contrato')
                    ->ignore($contrato?->id_contrato, 'id_contrato')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'nombre' => ['nullable', 'string', 'max:180'],
            'tipo' => ['required', Rule::in(['marco', 'directo', 'otro'])],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['required', Rule::in(['vigente', 'expirado', 'cancelado'])],
            'activo' => ['sometimes', 'boolean'],
            'observaciones' => ['nullable', 'string'],
            'ariba_cta_mayor' => ['nullable', 'string', 'max:120'],
            'ariba_propuesta_opex' => ['nullable', 'string', 'max:120'],
            'ariba_accion_gasto' => ['nullable', 'string', 'max:120'],
            'ariba_nombre_proveedor' => ['nullable', 'string', 'max:200'],
            'ariba_sociedad' => ['nullable', 'string', 'max:120'],
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
    private function empresaOptions(Request $request, ?int $contextId = null): array
    {
        $contextIds = $contextId ? [$contextId] : $request->user()->getActiveContextIds();

        return Empresa::query()
            ->whereIn('id_contexto', $contextIds)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id_empresa', 'id_contexto', 'nombre', 'cif', 'tipo_empresa'])
            ->map(fn (Empresa $empresa) => [
                'id_empresa' => (int) $empresa->id_empresa,
                'id_contexto' => (int) $empresa->id_contexto,
                'nombre' => $empresa->nombre,
                'cif' => $empresa->cif,
                'tipo_empresa' => $empresa->tipo_empresa,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function contratoPayload(Contrato $contrato): array
    {
        return [
            'id_contrato' => (int) $contrato->id_contrato,
            'id_contexto' => (int) $contrato->id_contexto,
            'id_empresa_cliente' => (int) $contrato->id_empresa_cliente,
            'codigo_contrato' => $contrato->codigo_contrato,
            'nombre' => $contrato->nombre,
            'tipo' => $contrato->tipo,
            'fecha_inicio' => $contrato->fecha_inicio?->format('Y-m-d'),
            'fecha_fin' => $contrato->fecha_fin?->format('Y-m-d'),
            'estado' => $contrato->estado,
            'activo' => (bool) $contrato->activo,
            'observaciones' => $contrato->observaciones,
            'ariba_cta_mayor' => $contrato->ariba_cta_mayor,
            'ariba_propuesta_opex' => $contrato->ariba_propuesta_opex,
            'ariba_accion_gasto' => $contrato->ariba_accion_gasto,
            'ariba_nombre_proveedor' => $contrato->ariba_nombre_proveedor,
            'ariba_sociedad' => $contrato->ariba_sociedad,
            'empresa' => $contrato->empresa ? [
                'id_empresa' => (int) $contrato->empresa->id_empresa,
                'nombre' => $contrato->empresa->nombre,
                'cif' => $contrato->empresa->cif,
            ] : null,
        ];
    }

    private function auditCreate(Request $request, Contrato $contrato): void
    {
        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => 'crear',
            'modulo' => 'maestros.contratos',
            'tabla' => 'contratos',
            'entity_type' => Contrato::class,
            'entity_id' => $contrato->id_contrato,
            'registro_id' => $contrato->id_contrato,
            'campo' => 'id_contrato',
            'valor_nuevo' => $contrato->id_contrato,
            'datos_nuevos' => $this->auditLogger->snapshotModel($contrato, self::AUDIT_FIELDS),
            'descripcion' => 'Alta de contrato maestro.',
            'id_contexto' => $contrato->id_contexto,
        ], $request);
    }

    private function auditUpdate(Request $request, Contrato $contrato, array $before, string $description, string $action = 'actualizar'): void
    {
        $after = $this->auditLogger->snapshotModel($contrato->fresh(), self::AUDIT_FIELDS);
        $firstChange = $this->auditLogger->resolveFirstChange($before, $after);

        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => $action,
            'modulo' => 'maestros.contratos',
            'tabla' => 'contratos',
            'entity_type' => Contrato::class,
            'entity_id' => $contrato->id_contrato,
            'registro_id' => $contrato->id_contrato,
            'campo' => $firstChange['campo'],
            'valor_anterior' => $firstChange['valor_anterior'],
            'valor_nuevo' => $firstChange['valor_nuevo'],
            'datos_anteriores' => $before,
            'datos_nuevos' => $after,
            'descripcion' => $this->auditLogger->buildChangedFieldsDescription($before, $after, $description),
            'id_contexto' => $contrato->id_contexto,
        ], $request);
    }
}
