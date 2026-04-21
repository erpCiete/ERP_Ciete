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

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);
        $search = trim((string) $request->input('search', ''));
        $estado = trim((string) $request->input('estado', ''));
        $trabajoId = $request->integer('id_trabajo');
        $empresaId = $request->integer('id_empresa_cliente');

        $facturas = Factura::query()
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
                    $nested
                        ->where('numero_factura', 'like', "%{$search}%")
                        ->orWhere('numero_factura_ccp', 'like', "%{$search}%")
                        ->orWhere('observaciones', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id_factura')
            ->paginate($perPage)
            ->withQueryString();

        return $this->paginatedResponse(
            $facturas,
            FacturaResource::collection($facturas->getCollection())->resolve(),
            'Listado de facturas obtenido'
        );
    }

    public function store(StoreFacturaRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $factura = DB::transaction(function () use ($payload): Factura {
            $factura = new Factura();
            $this->fillFactura($factura, $payload);
            $factura->id_contexto = Auth::user()?->id_contexto;
            $factura->save();

            if (array_key_exists('pedidos', $payload)) {
                $this->syncPedidos($factura, $payload['pedidos'] ?? []);
            }

            return $factura->load(['trabajo', 'pedidos']);
        });

        return $this->successResponse(
            (new FacturaResource($factura))->resolve(),
            'Factura creada correctamente',
            201
        );
    }

    public function show(Factura $factura): JsonResponse
    {
        $factura->load(['trabajo', 'pedidos']);

        return $this->successResponse(
            (new FacturaResource($factura))->resolve(),
            'Detalle de factura obtenido'
        );
    }

    public function update(UpdateFacturaRequest $request, Factura $factura): JsonResponse
    {
        $payload = $request->validated();

        DB::transaction(function () use ($factura, $payload): void {
            $this->fillFactura($factura, $payload);
            $factura->save();

            if (array_key_exists('pedidos', $payload)) {
                $this->syncPedidos($factura, $payload['pedidos'] ?? []);
            }
        });

        return $this->successResponse(
            (new FacturaResource($factura->fresh()->load(['trabajo', 'pedidos'])))->resolve(),
            'Factura actualizada correctamente'
        );
    }

    public function destroy(Factura $factura): JsonResponse
    {
        // El pivot table debería tener onDelete('cascade') en la migración,
        // pero por seguridad si no lo tiene, podemos hacer detach() aquí.
        DB::transaction(function () use ($factura): void {
            $factura->pedidos()->detach();
            $factura->delete();
        });

        return $this->successResponse(null, 'Factura eliminada correctamente');
    }

    private function fillFactura(Factura $factura, array $data): void
    {
        $fields = [
            'id_trabajo', 'id_empresa_cliente', 'numero_factura', 'numero_factura_ccp',
            'serie', 'orden_factura', 'fecha_solicitud', 'fecha_emision', 'fecha_vencimiento',
            'importe', 'base_imponible', 'iva', 'retencion', 'total', 'estado', 
            'autofactura', 'sociedad', 'observaciones'
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $factura->{$field} = $data[$field];
            }
        }
    }

    private function syncPedidos(Factura $factura, array $pedidosData): void
    {
        $syncData = [];
        foreach ($pedidosData as $pedido) {
            $syncData[$pedido['id_pedido']] = [
                'importe_aplicado' => $pedido['importe_aplicado'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        $factura->pedidos()->sync($syncData);
    }
}