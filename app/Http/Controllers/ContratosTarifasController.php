<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\ContextoCliente;
use App\Models\TarifarioLinea;
use App\Models\User;
use App\Support\ContextGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class ContratosTarifasController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        abort_unless($this->canViewPricing($user), 403);

        $contextIds = $user?->getActiveContextIds() ?? [];
        $activeContextSelection = $user?->getActiveContextSelection();
        $activeContextId = $activeContextSelection === User::ACTIVE_CONTEXT_ALL
            ? $user?->getDefaultContextId()
            : (is_int($activeContextSelection) ? $activeContextSelection : null);
        $activeContext = $activeContextId
            ? ContextoCliente::query()->find($activeContextId, ['id_contexto', 'codigo', 'nombre'])
            : null;
        $hasPredeterminadoColumn = Schema::hasColumn('tarifarios', 'es_predeterminado');

        $contratos = Contrato::query()
            ->with([
                'empresa:id_empresa,id_contexto,nombre,cif,activo',
                'sociedadesFacturadoras' => function ($query): void {
                    $query
                        ->with('empresa:id_empresa,id_contexto,nombre,cif,activo')
                        ->orderByDesc('activo')
                        ->orderBy('id_empresa');
                },
                'tarifarios' => function ($query) use ($hasPredeterminadoColumn): void {
                    $query
                        ->withCount(['lineas', 'trabajos', 'pedidos'])
                        ->when($hasPredeterminadoColumn, fn ($builder) => $builder->orderByDesc('es_predeterminado'))
                        ->orderBy('nombre');
                },
            ])
            ->withCount(['sociedadesFacturadoras', 'tarifarios', 'trabajos', 'facturas'])
            ->whereIn('id_contexto', $contextIds)
            ->orderBy('id_contexto')
            ->orderBy('codigo_contrato')
            ->get();

        $contratosPayload = $contratos->map(function (Contrato $contrato) use ($hasPredeterminadoColumn): array {
            $sociedades = $contrato->sociedadesFacturadoras->map(function ($relacion): array {
                $empresa = $relacion->empresa;
                $cif = trim((string) ($empresa?->cif ?? ''));

                return [
                    'id' => (int) $relacion->id,
                    'activa' => (bool) $relacion->activo,
                    'empresa' => $empresa ? [
                        'id' => (int) $empresa->id_empresa,
                        'nombre' => $empresa->nombre,
                        'cif' => $empresa->cif,
                        'activa' => (bool) $empresa->activo,
                    ] : null,
                    'has_valid_cif' => $cif !== '',
                ];
            })->values();

            $tarifarios = $contrato->tarifarios->map(fn ($tarifario): array => [
                'id' => (int) $tarifario->id_tarifario,
                'nombre' => $tarifario->nombre,
                'version' => $tarifario->version,
                'activo' => (bool) $tarifario->activo,
                'predeterminado' => $hasPredeterminadoColumn ? (bool) $tarifario->es_predeterminado : false,
                'lineas_count' => (int) $tarifario->lineas_count,
                'trabajos_count' => (int) $tarifario->trabajos_count,
                'pedidos_count' => (int) $tarifario->pedidos_count,
            ])->values();

            $sociedadesValidas = $sociedades->filter(fn (array $sociedad): bool => $sociedad['activa']
                && $sociedad['has_valid_cif']
                && (bool) ($sociedad['empresa']['activa'] ?? false))->count();
            $tarifarioPredeterminado = $tarifarios->first(fn (array $tarifario): bool => $tarifario['predeterminado']);

            return [
                'id' => (int) $contrato->id_contrato,
                'codigo' => $contrato->codigo_contrato,
                'nombre' => $contrato->nombre,
                'activo' => (bool) $contrato->activo,
                'estado' => $contrato->estado,
                'empresa' => $contrato->empresa ? [
                    'id' => (int) $contrato->empresa->id_empresa,
                    'nombre' => $contrato->empresa->nombre,
                    'cif' => $contrato->empresa->cif,
                    'activa' => (bool) $contrato->empresa->activo,
                ] : null,
                'sociedades_count' => (int) $contrato->sociedades_facturadoras_count,
                'sociedades_validas_count' => $sociedadesValidas,
                'has_valid_sociedad' => $sociedadesValidas > 0,
                'tarifarios_count' => (int) $contrato->tarifarios_count,
                'lineas_count' => (int) $tarifarios->sum('lineas_count'),
                'trabajos_count' => (int) $contrato->trabajos_count,
                'facturas_count' => (int) $contrato->facturas_count,
                'pedidos_count' => (int) $tarifarios->sum('pedidos_count'),
                'tarifario_predeterminado' => $tarifarioPredeterminado,
                'sociedades' => $sociedades->all(),
                'tarifarios' => $tarifarios->all(),
            ];
        })->values();

        $selectedContract = $contratosPayload->firstWhere('id', $request->integer('contrato'))
            ?? $contratosPayload->first();

        $selectedTarifario = null;

        if ($selectedContract) {
            $selectedTarifario = collect($selectedContract['tarifarios'])->firstWhere('id', $request->integer('tarifario'))
                ?? collect($selectedContract['tarifarios'])->firstWhere('predeterminado', true)
                ?? collect($selectedContract['tarifarios'])->first();
        }

        $lineasLimit = 200;
        $lineasTotal = 0;
        $lineasPayload = [];

        if ($selectedTarifario) {
            $lineasQuery = TarifarioLinea::query()
                ->with('unidad:id_unidad,nombre,abreviatura')
                ->where('id_tarifario', $selectedTarifario['id']);

            $lineasTotal = (clone $lineasQuery)->count();

            $lineasPayload = $lineasQuery
                ->orderBy('codigo_tarifa')
                ->limit($lineasLimit)
                ->get()
                ->map(fn (TarifarioLinea $linea): array => [
                    'id' => (int) $linea->id_tarifario_linea,
                    'codigo' => $linea->codigo_tarifa,
                    'descripcion' => $linea->descripcion ?: $linea->actuacion,
                    'actuacion' => $linea->actuacion,
                    'unidad' => $linea->unidad?->abreviatura ?: $linea->unidad?->nombre,
                    'precio' => (string) $linea->tarifa_aplicada,
                    'activo' => (bool) $linea->activo,
                ])
                ->all();
        }

        $contratosSinSociedadValida = $contratosPayload
            ->filter(fn (array $contrato): bool => ! $contrato['has_valid_sociedad'])
            ->count();

        return Inertia::render('Maestros/ContratosTarifas', [
            'overview' => [
                'context' => $activeContext ? [
                    'id' => (int) $activeContext->id_contexto,
                    'codigo' => $activeContext->codigo,
                    'nombre' => ContextGuard::displayName($activeContext->codigo, $activeContext->nombre),
                    'is_all' => $activeContextSelection === User::ACTIVE_CONTEXT_ALL,
                ] : null,
                'empresa' => $selectedContract['empresa'] ?? null,
                'counts' => [
                    'contratos' => $contratosPayload->count(),
                    'tarifarios' => $contratosPayload->sum('tarifarios_count'),
                    'lineas' => $contratosPayload->sum('lineas_count'),
                ],
                'alerts' => [
                    'contratos_sin_sociedad_valida' => $contratosSinSociedadValida,
                ],
            ],
            'contracts' => $contratosPayload,
            'selected' => [
                'contrato_id' => $selectedContract['id'] ?? null,
                'tarifario_id' => $selectedTarifario['id'] ?? null,
                'tab' => (string) $request->input('tab', 'resumen'),
                'lineas' => $lineasPayload,
                'lineas_total' => $lineasTotal,
                'lineas_limit' => $lineasLimit,
                'lineas_truncated' => $lineasTotal > $lineasLimit,
            ],
            'can' => [
                'contratos' => $this->permissions($user, 'contratos'),
                'sociedades' => $this->permissions($user, 'sociedades_facturadoras'),
                'tarifarios' => $this->permissions($user, 'tarifarios'),
                'lineas' => $this->permissions($user, 'tarifario_lineas'),
            ],
            'supportsPredeterminado' => $hasPredeterminadoColumn,
        ]);
    }

    private function canViewPricing(mixed $user): bool
    {
        if ($user?->hasAnyRole(['director', 'direccion'])) {
            return true;
        }

        foreach (['contratos.ver', 'sociedades_facturadoras.ver', 'tarifarios.ver', 'tarifario_lineas.ver'] as $permission) {
            if ($user?->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{view: bool, create: bool, edit: bool, delete: bool}
     */
    private function permissions(mixed $user, string $prefix): array
    {
        if ($user?->hasAnyRole(['director', 'direccion'])) {
            return [
                'view' => true,
                'create' => true,
                'edit' => true,
                'delete' => true,
            ];
        }

        return [
            'view' => (bool) $user?->hasPermission("{$prefix}.ver"),
            'create' => (bool) $user?->hasPermission("{$prefix}.crear"),
            'edit' => (bool) $user?->hasPermission("{$prefix}.editar"),
            'delete' => (bool) $user?->hasPermission("{$prefix}.eliminar"),
        ];
    }
}
