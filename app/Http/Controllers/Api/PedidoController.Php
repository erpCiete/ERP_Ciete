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

/**
 * Controlador para la gestión de Pedidos.
 * Refactorizado para el esquema del Sprint 04.
 */
class PedidoController extends Controller
{
    use ApiResponse;

    /**
     * Listado paginado de pedidos con filtros por trabajo, estado y contexto.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);
        $search = trim((string) $request->input('search', ''));
        $estado = trim((string) $request->input('estado', ''));
        $trabajoId = $request->integer('id_trabajo');

        // 1. Identificar si el usuario es Administrador (Contexto 3 o Rol 'admin')
        $user = $request->user();
        $isAdmin = $user->id_contexto === 3 || $user->hasRole('admin');

        $pedidos = Pedido::query()
            // 2. Eager loading (Añadido 'trabajo' para optimizar si la tabla del frontend muestra el nombre de la obra)
            ->with(['items' => function ($query) {
                $query->orderBy('id_pedido_item');
            }, 'trabajo'])
            // 3. AISLAMIENTO CONDICIONAL: Si NO es admin, filtramos por su contexto. Si es admin, no filtramos (ve todos).
            ->when(!$isAdmin, function ($query) use ($user): void {
                $query->where('id_contexto', $user->id_contexto);
            })
            ->when($trabajoId > 0, function ($query) use ($trabajoId): void {
                $query->where('id_trabajo', $trabajoId);
            })
            ->when($estado !== '', function ($query) use ($estado): void {
                $query->where('estado', $estado);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('numero_pedido', 'like', "%{$search}%")
                        ->orWhere('observaciones', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return $this->successResponse(PedidoResource::collection($pedidos));
    }

    /**
     * Almacena un nuevo pedido con sus líneas anidadas.
     */
    public function store(StorePedidoRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $pedido = new Pedido();
            
            // BORRAR ESTO: $pedido->id_contexto = Auth::user()->id_contexto;
            
            // NUEVO: Heredar el contexto directamente del Trabajo seleccionado
            $trabajo = \App\Models\Trabajo::findOrFail($request->input('id_trabajo'));
            $pedido->id_contexto = $trabajo->id_contexto;
            
            $this->mapRequestToModel($request, $pedido);
            $pedido->save();

            if ($request->has('items')) {
                $this->syncItems($pedido, $request->input('items'));
            }

            return (new PedidoResource($pedido->load('items')))->response()->setStatusCode(201);
        });
    }

    /**
     * Mapeo de los campos del Request a las columnas REALES de la tabla pedidos.
     */
    private function mapRequestToModel(Request $request, Pedido $pedido): void
    {
        $fields = [
            'id_trabajo',
            'id_tarifario',
            'numero_pedido',
            'fecha_solicitud',
            'fecha_recepcion',
            'importe_pedido',
            'importe_solicitado',
            'importe_facturado',
            'unidades_pedido',
            'unidades_solicitadas',
            'estado',
            'pedido_completo',
            'tiene_mas_de_1_item',
            'facturado_completo',
            'observaciones',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $pedido->{$field} = $request->input($field);
            }
        }
    }

    /**
     * Sincroniza las líneas del pedido (pedido_items). (ACTUALIZADO)
     */
    private function syncItems(Pedido $pedido, array $items): void
    {
        PedidoItem::query()
            ->where('id_pedido', $pedido->id_pedido)
            ->delete();

        foreach (array_values($items) as $index => $itemData) {
            $item = new PedidoItem();
            $item->id_contexto = $pedido->id_contexto;
            $item->id_pedido = $pedido->id_pedido;
            
            $item->id_tarifario_linea = $itemData['id_tarifario_linea'] ?? null;
            $item->codigo_servicio = $itemData['codigo_servicio'] ?? null;
            $item->numero_tarifa = $itemData['numero_tarifa'] ?? null;
            $item->descripcion_servicio = $itemData['descripcion_servicio'] ?? null;
            
            $item->cantidad = $itemData['cantidad'] ?? 1;
            $item->precio_unitario = $itemData['precio_unitario'] ?? 0;
            $item->total_linea = $itemData['total_linea'] ?? ($item->cantidad * $item->precio_unitario);
            
            $item->save();
        }
    }
}