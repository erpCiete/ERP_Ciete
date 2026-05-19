<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\ContratoEmpresaFacturadora;
use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\Tarifario;
use App\Models\TarifarioLinea;
use App\Models\TipoDocumento;
use App\Models\TipoTrabajo;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaestroController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $contextIds = $user?->getActiveContextIds() ?? [];

        $unidades = Unidad::query()->count();
        $tiposDocumento = TipoDocumento::query()->whereIn('id_contexto', $contextIds)->count();
        $tiposTrabajo = TipoTrabajo::query()->whereIn('id_contexto', $contextIds)->count();
        $contratosActivos = Contrato::query()->whereIn('id_contexto', $contextIds)->where('activo', true)->count();
        $estacionesActivas = EstacionServicio::query()->whereIn('id_contexto', $contextIds)->where('activo', true)->count();
        $tarifariosActivos = Tarifario::query()->whereIn('id_contexto', $contextIds)->where('activo', true)->count();
        $lineasTarifarioActivas = TarifarioLinea::query()->whereIn('id_contexto', $contextIds)->where('activo', true)->count();
        $contratosSinSociedadFacturadora = Contrato::query()
            ->whereIn('id_contexto', $contextIds)
            ->where('activo', true)
            ->whereDoesntHave('sociedadesFacturadoras', function ($query): void {
                $query
                    ->where('activo', true)
                    ->whereHas('empresa', function ($empresaQuery): void {
                        $empresaQuery
                            ->where('activo', true)
                            ->whereNotNull('cif')
                            ->whereRaw("TRIM(cif) <> ''");
                    });
            })
            ->count();
        $empresasSinCif = Empresa::query()
            ->whereIn('id_contexto', $contextIds)
            ->where('activo', true)
            ->where(function ($query): void {
                $query
                    ->whereNull('cif')
                    ->orWhereRaw("TRIM(cif) = ''");
            })
            ->count();

        $diagnostics = [];

        if ($estacionesActivas === 0) {
            $diagnostics[] = [
                'key' => 'sin_estaciones',
                'severity' => 'critical',
                'count' => 0,
                'title' => 'Sin estaciones activas',
                'description' => 'Trabajos no puede operar si el contexto no tiene estaciones activas seleccionables.',
                'routeName' => 'estaciones.index',
                'permissionKey' => 'estaciones',
                'actionLabel' => 'Revisar estaciones',
            ];
        }

        if ($contratosActivos === 0) {
            $diagnostics[] = [
                'key' => 'sin_contratos',
                'severity' => 'critical',
                'count' => 0,
                'title' => 'Sin contratos activos',
                'description' => 'Trabajos, pedidos y facturas necesitan contrato/tarifa para mantener coherencia economica.',
                'routeName' => 'maestros.contratos.index',
                'permissionKey' => 'contratos',
                'actionLabel' => 'Revisar contratos',
            ];
        }

        if ($tarifariosActivos === 0 || $lineasTarifarioActivas === 0) {
            $diagnostics[] = [
                'key' => 'sin_tarifas_operativas',
                'severity' => 'critical',
                'count' => $lineasTarifarioActivas,
                'title' => 'Sin lineas de tarifa operativas',
                'description' => 'Pedidos puede quedarse sin lineas facturables si no hay tarifarios activos con lineas activas.',
                'routeName' => 'maestros.tarifario-lineas.index',
                'permissionKey' => 'lineas',
                'actionLabel' => 'Revisar lineas',
            ];
        }

        if ($tiposDocumento === 0 || $tiposTrabajo === 0) {
            $diagnostics[] = [
                'key' => 'catalogos_trabajo_incompletos',
                'severity' => 'warning',
                'count' => $tiposDocumento + $tiposTrabajo,
                'title' => 'Catalogos de trabajo incompletos',
                'description' => 'REPSOL necesita tipos de documento y tipos de trabajo activos para crear trabajos sin bloqueos.',
                'routeName' => null,
                'permissionKey' => null,
                'actionLabel' => 'Revisar catalogos',
            ];
        }

        if ($contratosSinSociedadFacturadora > 0) {
            $diagnostics[] = [
                'key' => 'contratos_sin_sociedad',
                'severity' => 'critical',
                'count' => $contratosSinSociedadFacturadora,
                'title' => 'Contratos sin sociedad facturadora operativa',
                'description' => 'Estos contratos activos no tienen ninguna relacion activa con empresa facturadora valida y con CIF. Facturas puede quedarse sin Sociedad/CIF seleccionable.',
                'routeName' => 'maestros.sociedades.index',
                'permissionKey' => 'sociedades',
                'actionLabel' => 'Revisar sociedades permitidas',
            ];
        }

        if ($empresasSinCif > 0) {
            $diagnostics[] = [
                'key' => 'empresas_sin_cif',
                'severity' => 'warning',
                'count' => $empresasSinCif,
                'title' => 'Empresas activas sin CIF',
                'description' => 'No podran utilizarse como sociedad facturadora hasta completar el CIF y revisar su rol dentro del contexto activo.',
                'routeName' => 'clientes.index',
                'permissionKey' => 'clientes',
                'actionLabel' => 'Revisar empresas',
            ];
        }

        return Inertia::render('Maestros/Index', [
            'summary' => [
                'empresas' => Empresa::query()->whereIn('id_contexto', $contextIds)->count(),
                'estaciones' => EstacionServicio::query()->whereIn('id_contexto', $contextIds)->count(),
                'contratos' => Contrato::query()->whereIn('id_contexto', $contextIds)->count(),
                'sociedades' => ContratoEmpresaFacturadora::query()->whereIn('id_contexto', $contextIds)->count(),
                'tarifarios' => Tarifario::query()->whereIn('id_contexto', $contextIds)->count(),
                'lineas_tarifario' => TarifarioLinea::query()->whereIn('id_contexto', $contextIds)->count(),
                'catalogos' => $unidades + $tiposDocumento + $tiposTrabajo,
                'unidades' => $unidades,
                'tipos_documento' => $tiposDocumento,
                'tipos_trabajo' => $tiposTrabajo,
                'usuarios' => User::query()->whereIn('id_contexto', $contextIds)->count(),
            ],
            'diagnostics' => $diagnostics,
            'can' => [
                'create_contextual' => \App\Support\ContextGuard::canCreateInActiveContext($user),
                'usuarios' => $this->permissions($user, 'usuarios'),
                'clientes' => $this->permissions($user, 'clientes'),
                'estaciones' => $this->permissions($user, 'estaciones'),
                'contratos' => $this->permissions($user, 'contratos'),
                'sociedades' => $this->permissions($user, 'sociedades_facturadoras'),
                'tarifarios' => $this->permissions($user, 'tarifarios'),
                'lineas' => $this->permissions($user, 'tarifario_lineas'),
            ],
        ]);
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
