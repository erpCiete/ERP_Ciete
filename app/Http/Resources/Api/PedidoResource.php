<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PedidoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Mapeo seguro de las líneas de pedido (solo si se incluyeron en la consulta con eager loading)
        $items = $this->whenLoaded('items', function () {
            return $this->items->sortBy('id_pedido_item')->values()->map(function ($item) {
                $facturaItemsCount = $item->factura_items_count;

                if ($facturaItemsCount === null) {
                    $facturaItemsCount = $item->relationLoaded('facturaItems')
                        ? $item->facturaItems->count()
                        : (int) $item->facturaItems()->count();
                }

                return [
                    'id_pedido_item' => $item->id_pedido_item,
                    'id_contexto' => $item->id_contexto,
                    'id_pedido' => $item->id_pedido,
                    'id_tarifario_linea' => $item->id_tarifario_linea,
                    'codigo_servicio' => $item->codigo_servicio,
                    'numero_tarifa' => $item->numero_tarifa,
                    'descripcion_servicio' => $item->descripcion_servicio,
                    'cantidad' => isset($item->cantidad) ? (float) $item->cantidad : 0.0,
                    'precio_unitario' => isset($item->precio_unitario) ? (float) $item->precio_unitario : 0.0,
                    'total_linea' => isset($item->total_linea) ? (float) $item->total_linea : 0.0,
                    'factura_items_count' => (int) $facturaItemsCount,
                    'esta_facturado' => (int) $facturaItemsCount > 0,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });
        });
        $trabajo = $this->whenLoaded('trabajo', function () {
            return [
                'id_trabajo' => $this->trabajo?->id_trabajo,
                'numero_trabajo' => $this->trabajo?->numero_trabajo,
                'descripcion_trabajo' => $this->trabajo?->descripcion_trabajo,
                'codigo_estacion' => $this->trabajo?->estacion?->codigo_estacion,
            ];
        });

        return [
            // Identificadores y Relaciones
            'id_pedido' => $this->id_pedido,
            'id_contexto' => $this->id_contexto,
            'id_trabajo' => $this->id_trabajo,
            'id_tarifario' => $this->id_tarifario,
            
            // Datos Base
            'numero_pedido' => $this->numero_pedido,
            
            // Formateo seguro de fechas (Y-m-d) previniendo errores si llegan null
            'fecha_solicitud' => optional($this->fecha_solicitud)->format('Y-m-d') ?: $this->fecha_solicitud,
            'fecha_recepcion' => optional($this->fecha_recepcion)->format('Y-m-d') ?: $this->fecha_recepcion,
            
            // Control Económico (Casteado a float estricto para React)
            'importe_pedido' => isset($this->importe_pedido) ? (float) $this->importe_pedido : 0.0,
            'importe_solicitado' => isset($this->importe_solicitado) ? (float) $this->importe_solicitado : 0.0,
            'importe_facturado' => isset($this->importe_facturado) ? (float) $this->importe_facturado : 0.0,
            
            // Control de Unidades (Casteado a float estricto para React)
            'unidades_pedido' => isset($this->unidades_pedido) ? (float) $this->unidades_pedido : 0.0,
            'unidades_solicitadas' => isset($this->unidades_solicitadas) ? (float) $this->unidades_solicitadas : 0.0,
            
            // Estados y Flags Operativos
            'estado' => $this->estado,
            'pedido_completo' => (bool) $this->pedido_completo,
            'tiene_mas_de_1_item' => (bool) $this->tiene_mas_de_1_item,
            'facturado_completo' => (bool) $this->facturado_completo,
            
            // Extras y Timestamps
            'observaciones' => $this->observaciones,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relaciones anidadas
            'items' => $items,
            'trabajo' => $trabajo,
        ];
    }
}
