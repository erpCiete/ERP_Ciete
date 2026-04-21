<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PedidoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->relationLoaded('items')
            ? $this->items->sortBy('orden')->values()->map(function ($item) {
                return [
                    'id' => $item->id_linea_pedido ?? $item->id ?? null,
                    'id_tarifario_servicio' => $item->id_tarifario_servicio ?? null,
                    'id_servicio' => $item->id_servicio ?? null,
                    'orden' => $item->orden ?? null,
                    'concepto_seleccionable' => $item->concepto_seleccionable ?? null,
                    'concepto_libre' => $item->concepto_libre ?? null,
                    'cantidad' => isset($item->cantidad) ? (float) $item->cantidad : null,
                    'precio_unitario' => isset($item->precio_unitario) ? (float) $item->precio_unitario : null,
                    'iva_porcentaje' => isset($item->iva_porcentaje) ? (float) $item->iva_porcentaje : null,
                    'total_linea' => isset($item->total_linea) ? (float) $item->total_linea : null,
                ];
            })->all()
            : null;

        return [
            'id' => $this->id_pedido,
            'id_presupuesto' => $this->id_presupuesto,
            'id_proyecto' => $this->id_proyecto,
            'id_empresa_cliente' => $this->id_empresa_cliente,
            'id_contacto_empresa_cliente' => $this->id_contacto_empresa_cliente,
            'id_estacion_servicio' => $this->id_estacion_servicio,
            'id_tarifario' => $this->id_tarifario,
            'id_usuario_responsable' => $this->id_usuario_responsable,
            'numero_pedido' => $this->numero_pedido,
            'numero_aviso' => $this->numero_aviso,
            'fecha_solicitud_pedido' => optional($this->fecha_solicitud_pedido)->format('Y-m-d') ?: $this->fecha_solicitud_pedido,
            'fecha_recepcion_pedido' => optional($this->fecha_recepcion_pedido)->format('Y-m-d') ?: $this->fecha_recepcion_pedido,
            'fecha_solicitud_factura' => optional($this->fecha_solicitud_factura)->format('Y-m-d') ?: $this->fecha_solicitud_factura,
            'estado' => $this->estado,
            'descripcion_seleccionable' => $this->descripcion_seleccionable,
            'descripcion_libre' => $this->descripcion_libre,
            'subtotal' => isset($this->subtotal) ? (float) $this->subtotal : null,
            'iva' => isset($this->iva) ? (float) $this->iva : null,
            'total' => isset($this->total) ? (float) $this->total : null,
            'observaciones' => $this->observaciones,
            'items' => $items,
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}