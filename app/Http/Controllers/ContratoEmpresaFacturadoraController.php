<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\ContratoEmpresaFacturadora;
use App\Models\Empresa;
use App\Services\AuditLogger;
use App\Support\ContextGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ContratoEmpresaFacturadoraController extends Controller
{
    private const AUDIT_FIELDS = [
        'id',
        'id_contrato',
        'id_empresa',
        'id_contexto',
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

        $relaciones = ContratoEmpresaFacturadora::query()
            ->with([
                'contrato:id_contrato,id_contexto,codigo_contrato,nombre,estado,activo',
                'empresa:id_empresa,id_contexto,nombre,cif,tipo_empresa,activo',
                'contexto:id_contexto,codigo,nombre',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->whereHas('contrato', fn ($contratoQuery) => $contratoQuery
                            ->where('codigo_contrato', 'like', "%{$search}%")
                            ->orWhere('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('empresa', fn ($empresaQuery) => $empresaQuery
                            ->where('nombre', 'like', "%{$search}%")
                            ->orWhere('cif', 'like', "%{$search}%"));
                });
            })
            ->when($activo !== '', fn ($query) => $query->where('activo', $activo === '1'))
            ->orderBy('id_contexto')
            ->orderByDesc('activo')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('SociedadesFacturadoras/Index', [
            'relaciones' => $relaciones,
            'filters' => [
                'search' => $search,
                'activo' => $activo,
            ],
            'contratos' => $this->contratoOptions($request),
            'empresas' => $this->empresaOptions($request),
            'canCreate' => ($request->user()?->hasPermission('sociedades_facturadoras.crear')
                || $request->user()?->hasAnyRole(['director', 'direccion']))
                && ContextGuard::canCreateInActiveContext($request->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $contextId = $this->contextIdForCreate($request);
        $validated = $this->validatedData($request, $contextId);

        $relacion = DB::transaction(function () use ($request, $validated): ContratoEmpresaFacturadora {
            $relacion = ContratoEmpresaFacturadora::create($validated);
            $this->auditCreate($request, $relacion);

            return $relacion;
        });

        return redirect()
            ->route('maestros.sociedades.index')
            ->with('success', 'Sociedad facturadora permitida asignada correctamente.');
    }

    public function update(Request $request, ContratoEmpresaFacturadora $sociedad): RedirectResponse
    {
        $this->ensureContextAccess($request, (int) $sociedad->id_contexto);
        $validated = $this->validatedData($request, (int) $sociedad->id_contexto, $sociedad);

        DB::transaction(function () use ($request, $sociedad, $validated): void {
            $before = $this->auditLogger->snapshotModel($sociedad, self::AUDIT_FIELDS);
            $sociedad->fill($validated);
            $sociedad->save();
            $this->auditUpdate($request, $sociedad, $before, 'Actualizacion de sociedad facturadora permitida.');
        });

        return redirect()
            ->route('maestros.sociedades.index')
            ->with('success', 'Sociedad facturadora permitida actualizada correctamente.');
    }

    public function destroy(Request $request, ContratoEmpresaFacturadora $sociedad): RedirectResponse
    {
        $this->ensureContextAccess($request, (int) $sociedad->id_contexto);

        DB::transaction(function () use ($request, $sociedad): void {
            $before = $this->auditLogger->snapshotModel($sociedad, self::AUDIT_FIELDS);
            $sociedad->forceFill(['activo' => false])->save();
            $this->auditUpdate($request, $sociedad, $before, 'Sociedad facturadora permitida desactivada. No se elimina historico relacionado.', 'desactivar');
        });

        return redirect()
            ->route('maestros.sociedades.index')
            ->with('success', 'Sociedad facturadora permitida desactivada correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, int $contextId, ?ContratoEmpresaFacturadora $sociedad = null): array
    {
        $validated = $request->validate([
            'id_contrato' => [
                'required',
                'integer',
                Rule::exists('contratos', 'id_contrato')->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'id_empresa' => [
                'required',
                'integer',
                Rule::exists('empresas', 'id_empresa')->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'activo' => ['sometimes', 'boolean'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $empresa = Empresa::query()
            ->where('id_contexto', $contextId)
            ->findOrFail($validated['id_empresa']);

        if (trim((string) $empresa->cif) === '') {
            throw ValidationException::withMessages([
                'id_empresa' => 'La empresa seleccionada debe tener CIF informado para actuar como sociedad facturadora.',
            ]);
        }

        $duplicated = ContratoEmpresaFacturadora::query()
            ->where('id_contexto', $contextId)
            ->where('id_contrato', $validated['id_contrato'])
            ->where('id_empresa', $validated['id_empresa'])
            ->when($sociedad, fn ($query) => $query->where('id', '!=', $sociedad->id))
            ->exists();

        if ($duplicated) {
            throw ValidationException::withMessages([
                'id_empresa' => 'Esta sociedad facturadora ya esta asignada al contrato seleccionado.',
            ]);
        }

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
    private function contratoOptions(Request $request): array
    {
        return Contrato::query()
            ->whereIn('id_contexto', $request->user()->getActiveContextIds())
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
     * @return array<int, array<string, mixed>>
     */
    private function empresaOptions(Request $request): array
    {
        return Empresa::query()
            ->whereIn('id_contexto', $request->user()->getActiveContextIds())
            ->where('activo', true)
            ->whereNotNull('cif')
            ->whereRaw("TRIM(cif) <> ''")
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

    private function auditCreate(Request $request, ContratoEmpresaFacturadora $relacion): void
    {
        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => 'crear',
            'modulo' => 'maestros.sociedades_facturadoras',
            'tabla' => 'contrato_empresas_facturadoras',
            'entity_type' => ContratoEmpresaFacturadora::class,
            'entity_id' => $relacion->id,
            'registro_id' => $relacion->id,
            'campo' => 'id',
            'valor_nuevo' => $relacion->id,
            'datos_nuevos' => $this->auditLogger->snapshotModel($relacion, self::AUDIT_FIELDS),
            'descripcion' => 'Asignacion de sociedad facturadora permitida a contrato.',
            'id_contexto' => $relacion->id_contexto,
        ], $request);
    }

    private function auditUpdate(Request $request, ContratoEmpresaFacturadora $relacion, array $before, string $description, string $action = 'actualizar'): void
    {
        $after = $this->auditLogger->snapshotModel($relacion->fresh(), self::AUDIT_FIELDS);
        $firstChange = $this->auditLogger->resolveFirstChange($before, $after);

        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => $action,
            'modulo' => 'maestros.sociedades_facturadoras',
            'tabla' => 'contrato_empresas_facturadoras',
            'entity_type' => ContratoEmpresaFacturadora::class,
            'entity_id' => $relacion->id,
            'registro_id' => $relacion->id,
            'campo' => $firstChange['campo'],
            'valor_anterior' => $firstChange['valor_anterior'],
            'valor_nuevo' => $firstChange['valor_nuevo'],
            'datos_anteriores' => $before,
            'datos_nuevos' => $after,
            'descripcion' => $this->auditLogger->buildChangedFieldsDescription($before, $after, $description),
            'id_contexto' => $relacion->id_contexto,
        ], $request);
    }
}
