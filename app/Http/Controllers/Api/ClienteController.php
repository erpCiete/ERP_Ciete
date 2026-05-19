<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ClienteStoreRequest;
use App\Http\Requests\Api\ClienteUpdateRequest;
use App\Http\Resources\Api\ClienteResource;
use App\Models\Contrato;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\Trabajo;
use App\Services\AuditLogger;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    use ApiResponse;

    private const AUDIT_FIELDS = [
        'id_empresa',
        'id_contexto',
        'nombre',
        'nombre_comercial',
        'razon_social',
        'cif',
        'tipo_empresa',
        'activo',
    ];

    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 10), 1), 100);
        $search = trim((string) $request->input('search', ''));
        $contexto = trim((string) $request->input('contexto', ''));

        $clientes = Empresa::query()
            ->clientes()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhere('nombre_comercial', 'like', "%{$search}%")
                        ->orWhere('razon_social', 'like', "%{$search}%")
                        ->orWhere('cif', 'like', "%{$search}%");
                });
            })
            ->when($contexto !== '', function ($query) use ($contexto): void {
                $query->where('nombre_comercial', 'like', "%{$contexto}%");
            })
            ->orderBy('nombre_comercial')
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();

        return $this->paginatedResponse(
            $clientes,
            ClienteResource::collection($clientes->getCollection())->resolve(),
            'Listado de clientes obtenido',
        );
    }

    public function store(ClienteStoreRequest $request): JsonResponse
    {
        $cliente = Empresa::create($request->validated());

        $snapshot = $this->auditLogger->snapshotModel($cliente, self::AUDIT_FIELDS);
        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => 'crear',
            'modulo' => 'clientes',
            'tabla' => 'empresas',
            'entity_type' => Empresa::class,
            'entity_id' => $cliente->id_empresa,
            'registro_id' => $cliente->id_empresa,
            'campo' => 'id_empresa',
            'valor_nuevo' => $cliente->id_empresa,
            'datos_nuevos' => $snapshot,
            'descripcion' => 'Alta de cliente.',
            'id_contexto' => $cliente->id_contexto,
        ], $request);

        return $this->successResponse(
            (new ClienteResource($cliente))->resolve(),
            'Cliente creado correctamente',
            201,
        );
    }

    public function show(Empresa $cliente): JsonResponse
    {
        return $this->successResponse(
            (new ClienteResource($cliente))->resolve(),
            'Detalle de cliente obtenido',
        );
    }

    public function update(ClienteUpdateRequest $request, Empresa $cliente): JsonResponse
    {
        $before = $this->auditLogger->snapshotModel($cliente, self::AUDIT_FIELDS);

        $cliente->update($request->validated());
        $cliente->refresh();

        $after = $this->auditLogger->snapshotModel($cliente, self::AUDIT_FIELDS);
        $firstChange = $this->auditLogger->resolveFirstChange($before, $after);
        $statusChanged = ($before['activo'] ?? null) !== ($after['activo'] ?? null);

        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => $statusChanged ? 'cambiar_estado' : 'actualizar',
            'modulo' => 'clientes',
            'tabla' => 'empresas',
            'entity_type' => Empresa::class,
            'entity_id' => $cliente->id_empresa,
            'registro_id' => $cliente->id_empresa,
            'campo' => $firstChange['campo'],
            'valor_anterior' => $firstChange['valor_anterior'],
            'valor_nuevo' => $firstChange['valor_nuevo'],
            'datos_anteriores' => $before,
            'datos_nuevos' => $after,
            'descripcion' => $this->auditLogger->buildChangedFieldsDescription($before, $after, 'Actualizacion de cliente'),
            'id_contexto' => $cliente->id_contexto,
        ], $request);

        return $this->successResponse(
            (new ClienteResource($cliente))->resolve(),
            'Cliente actualizado correctamente',
        );
    }

    public function destroy(Request $request, Empresa $cliente): JsonResponse
    {
        $hasHistorial = $cliente->estaciones()->exists()
            || Trabajo::withoutGlobalScopes()->where('id_empresa_cliente', $cliente->id_empresa)->exists()
            || Contrato::withoutGlobalScopes()->where('id_empresa_cliente', $cliente->id_empresa)->exists()
            || Factura::withoutGlobalScopes()
                ->where(function ($query) use ($cliente): void {
                    $query->where('id_empresa_cliente', $cliente->id_empresa)
                        ->orWhere('id_empresa_facturadora', $cliente->id_empresa);
                })
                ->exists();

        $before = $this->auditLogger->snapshotModel($cliente, self::AUDIT_FIELDS);

        $cliente->forceFill(['activo' => false])->save();

        $after = $this->auditLogger->snapshotModel($cliente->fresh(), self::AUDIT_FIELDS);

        $this->auditLogger->log([
            'user' => $request->user(),
            'accion' => 'desactivar',
            'modulo' => 'clientes',
            'tabla' => 'empresas',
            'entity_type' => Empresa::class,
            'entity_id' => $cliente->id_empresa,
            'registro_id' => $cliente->id_empresa,
            'campo' => 'activo',
            'valor_anterior' => $before['activo'] ?? null,
            'valor_nuevo' => $after['activo'] ?? null,
            'datos_anteriores' => $before,
            'datos_nuevos' => $after,
            'descripcion' => $hasHistorial
                ? 'Cliente desactivado. Conserva estaciones, contratos, trabajos o facturas historicas.'
                : 'Cliente desactivado sin borrado fisico.',
            'id_contexto' => $cliente->id_contexto,
        ], $request);

        $message = $hasHistorial
            ? 'Cliente desactivado correctamente. No se borra porque tiene historial asociado.'
            : 'Cliente desactivado correctamente.';

        return $this->successResponse((new ClienteResource($cliente->fresh()))->resolve(), $message);
    }
}
