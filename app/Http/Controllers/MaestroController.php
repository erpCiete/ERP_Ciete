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
