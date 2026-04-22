<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFacturaRequest;
use App\Http\Requests\Api\UpdateFacturaRequest;
use App\Http\Resources\Api\FacturaResource;
use App\Models\Factura;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    use ApiResponse;

    /**
     * Listado de Facturas.
     * Aislamiento estricto por id_contexto inyectado en la base de la query.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);
        $search = trim((string) $request->input('search', ''));
        $estado = trim((string) $request->input('estado', ''));
        $trabajoId = $request->integer('id_trabajo');
        $empresaId = $request->integer('id_empresa_cliente');

        $facturas = Factura::query()
            ->where('id_contexto', Auth::user()->id_contexto) // <-- BLOQUEO MULTI-TENANT CRÍTICO
            ->with(['trabajo', 'pedidos'])
            ->when($trabajoId > 0, function ($query) use ($trabajoId): void {
                $query->where('id_trabajo', $trabajoId);
            })
            ->when($empresaId > 0, function ($query) use ($empresaId): void {
                $query->where('id_empresa_cliente', $empresaId);
            })
            ->when($estado !== '', function ($query) use ($estado): void {
                $query->where('estado', $estado);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('numero_factura', 'like', "%{$search}%")
                           ->orWhere('numero_factura_ccp', 'like', "%{$search}%") // Soporte Búsqueda MOEVE
                           ->orWhere('observaciones', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return $this->successResponse(FacturaResource::collection($facturas));
    }

    /**
     * Creación de una nueva Factura.
     */
    public function store(StoreFacturaRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $factura = new Factura();
            
            // Asignación inmutable del contexto
            $factura->id_contexto = Auth::user()->id_contexto;
            
            $this->fillFactura($factura, $request->validated());
            $factura->save();

            if ($request->has('pedidos')) {
                $this->syncPedidos($factura, $request->input('pedidos'));
            }

            return (new FacturaResource($factura->load(['trabajo', 'pedidos'])))
                    ->response()
                    ->setStatusCode(201);
        });
    }

    /**
     * Visualización detallada de una Factura.
     */
    public function show(Factura $factura): JsonResponse
    {
        if ($factura->id_contexto !== Auth::user()->id_contexto) {
            return $this->errorResponse('No autorizado. Violación de aislamiento de contexto.', 403);
        }

        $factura->load(['trabajo', 'pedidos']);
        return $this->successResponse(new FacturaResource($factura));
    }

    /**
     * Actualización de Factura.
     */
    public function update(UpdateFacturaRequest $request, Factura $factura): JsonResponse
    {
        if ($factura->id_contexto !== Auth::user()->id_contexto) {
            return $this->errorResponse('No autorizado. Violación de aislamiento de contexto.', 403);
        }

        return DB::transaction(function () use ($request, $factura) {
            $this->fillFactura($factura, $request->validated());
            $factura->save();

            if ($request->has('pedidos')) {
                $this->syncPedidos($factura, $request->input('pedidos'));
            }

            return $this->successResponse(new FacturaResource($factura->load(['trabajo', 'pedidos'])));
        });
    }

    /**
     * Eliminación de Factura.
     */
    public function destroy(Factura $factura): JsonResponse
    {
        if ($factura->id_contexto !== Auth::user()->id_contexto) {
            return $this->errorResponse('No autorizado. Violación de aislamiento de contexto.', 403);
        }

        DB::transaction(function () use ($factura): void {
            // Limpieza de relaciones en tabla pivote (factura_pedidos)
            $factura->pedidos()->detach();
            $factura->delete();
        });

        return $this->successResponse(null, 'Factura eliminada correctamente');
    }

    /**
     * Mapeo de columnas validadas al modelo.
     */
    private function fillFactura(Factura $factura, array $data): void
    {
        $fields = [
            'id_trabajo', 
            'id_empresa_cliente', 
            'numero_factura', 
            'numero_factura_ccp',
            'serie', 
            'orden_factura', 
            'fecha_solicitud', 
            'fecha_emision', 
            'fecha_vencimiento',
            'importe', 
            'base_imponible', 
            'iva', 
            'retencion', 
            'total', 
            'estado', 
            'autofactura', 
            'sociedad', 
            'observaciones'
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $factura->{$field} = $data[$field];
            }
        }
    }

    /**
     * Sincronización de la tabla pivote factura_pedidos.
     */
    private function syncPedidos(Factura $factura, array $pedidosData): void
    {
        $syncData = [];
        
        foreach ($pedidosData as $pedido) {
            $syncData[$pedido['id_pedido']] = [
                'importe_aplicado' => $pedido['importe_aplicado'] ?? null,
            ];
        }

        // El método sync inserta, actualiza y elimina automáticamente los registros huérfanos.
        // Si la relación ->pedidos() en el modelo Factura tiene ->withTimestamps(), Laravel 
        // inyecta el created_at y updated_at automáticamente.
        $factura->pedidos()->sync($syncData);
    }
}