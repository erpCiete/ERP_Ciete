<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePedidoRequest;
use App\Http\Requests\Api\UpdatePedidoRequest;
use App\Http\Resources\Api\PedidoResource;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);
        $search = trim((string) $request->input('search', ''));
        $estado = trim((string) $request->input('estado', ''));
        $proyectoId = $request->integer('id_proyecto');
        $empresaId = $request->integer('id_empresa_cliente');

        $pedidos = Pedido::query()
            ->with(['items' => function ($query) {
                $query->orderBy('orden');
            }])
            ->when($proyectoId > 0, function ($query) use ($proyectoId): void {
                $query->where('id_proyecto', $proyectoId);
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
                        ->where('numero_pedido', 'like', "%{$search}%")
                        ->orWhere('numero_aviso', 'like', "%{$search}%")
                        ->orWhere('descripcion_seleccionable', 'like', "%{$search}%")
                        ->orWhere('descripcion_libre', 'like', "%{$search}%")
                        ->orWhere('observaciones', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id_pedido')
            ->paginate($perPage)
            ->withQueryString();

        return $this->paginatedResponse(
            $pedidos,
            PedidoResource::collection($pedidos->getCollection())->resolve(),
            'Listado de pedidos obtenido',
        );
    }

    public function store(StorePedidoRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $pedido = DB::transaction(function () use ($payload): Pedido {
            $pedido = new Pedido();

            $this->fillPedido($pedido, $payload);
            $pedido->id_contexto = Auth::user()?->id_contexto;
            $pedido->save();

            $this->syncItems($pedido, $payload['items'] ?? []);

            return $pedido->load(['items' => function ($query) {
                $query->orderBy('orden');
            }]);
        });

        return $this->successResponse(
            (new PedidoResource($pedido))->resolve(),
            'Pedido creado correctamente',
            201,
        );
    }

    public function show(Pedido $pedido): JsonResponse
    {
        $pedido->load(['items' => function ($query) {
            $query->orderBy('orden');
        }]);

        return $this->successResponse(
            (new PedidoResource($pedido))->resolve(),
            'Detalle de pedido obtenido',
        );
    }

    public function update(UpdatePedidoRequest $request, Pedido $pedido): JsonResponse
    {
        $payload = $request->validated();

        DB::transaction(function () use ($pedido, $payload): void {
            $this->fillPedido($pedido, $payload);
            $pedido->save();

            if (array_key_exists('items', $payload)) {
                $this->syncItems($pedido, $payload['items'] ?? []);
            }
        });

        $pedido->load(['items' => function ($query) {
            $query->orderBy('orden');
        }]);

        return $this->successResponse(
            (new PedidoResource($pedido->fresh()->load(['items' => function ($query) {
                $query->orderBy('orden');
            }])))->resolve(),
            'Pedido actualizado correctamente',
        );
    }

    public function destroy(Pedido $pedido): JsonResponse
    {
        $pedido->delete();

        return $this->successResponse(null, 'Pedido eliminado correctamente');
    }

    private function fillPedido(Pedido $pedido, array $data): void
    {
        $fields = [
            'id_presupuesto',
            'id_proyecto',
            'id_empresa_cliente',
            'id_contacto_empresa_cliente',
            'id_estacion_servicio',
            'id_tarifario',
            'id_usuario_responsable',
            'numero_pedido',
            'numero_aviso',
            'fecha_solicitud_pedido',
            'fecha_recepcion_pedido',
            'fecha_solicitud_factura',
            'estado',
            'descripcion_seleccionable',
            'descripcion_libre',
            'subtotal',
            'iva',
            'total',
            'observaciones',
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $pedido->{$field} = $data[$field];
            }
        }
    }

    private function syncItems(Pedido $pedido, array $items): void
    {
        PedidoItem::query()
            ->where('id_pedido', $pedido->id_pedido)
            ->delete();

        $contextId = $pedido->id_contexto;

        foreach (array_values($items) as $index => $itemData) {
            $item = new PedidoItem();

            $item->id_contexto = $contextId;
            $item->id_pedido = $pedido->id_pedido;
            $item->id_tarifario_servicio = $itemData['id_tarifario_servicio'] ?? null;
            $item->id_servicio = $itemData['id_servicio'] ?? null;
            $item->orden = $itemData['orden'] ?? ($index + 1);
            $item->concepto_seleccionable = $itemData['concepto_seleccionable'] ?? null;
            $item->concepto_libre = $itemData['concepto_libre'] ?? null;
            $item->cantidad = $itemData['cantidad'];
            $item->precio_unitario = $itemData['precio_unitario'];
            $item->iva_porcentaje = $itemData['iva_porcentaje'];
            $item->total_linea = $itemData['total_linea'];

            $item->save();
        }
    }
}