<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_factura,
            'id_trabajo' => $this->id_trabajo,
            'id_empresa_cliente' => $this->id_empresa_cliente,
            'numero_factura' => $this->numero_factura,
            'numero_factura_ccp' => $this->numero_factura_ccp,
            'serie' => $this->serie,
            'orden_factura' => $this->orden_factura,
            'fecha_solicitud' => optional($this->fecha_solicitud)->format('Y-m-d') ?: $this->fecha_solicitud,
            'fecha_emision' => optional($this->fecha_emision)->format('Y-m-d') ?: $this->fecha_emision,
            'fecha_vencimiento' => optional($this->fecha_vencimiento)->format('Y-m-d') ?: $this->fecha_vencimiento,
            'importe' => isset($this->importe) ? (float) $this->importe : null,
            'base_imponible' => isset($this->base_imponible) ? (float) $this->base_imponible : null,
            'iva' => isset($this->iva) ? (float) $this->iva : null,
            'retencion' => isset($this->retencion) ? (float) $this->retencion : null,
            'total' => isset($this->total) ? (float) $this->total : null,
            'estado' => $this->estado,
            'autofactura' => (bool) $this->autofactura,
            'sociedad' => $this->sociedad,
            'observaciones' => $this->observaciones,
            
            // Relaciones opcionales si se cargan
            'trabajo' => new TrabajoResource($this->whenLoaded('trabajo')),
            'pedidos' => $this->whenLoaded('pedidos', function () {
                return $this->pedidos->map(function ($pedido) {
                    return [
                        'id_pedido' => $pedido->id_pedido,
                        'numero_pedido' => $pedido->numero_pedido,
                        'importe_aplicado' => $pedido->pivot->importe_aplicado ?? null,
                    ];
                });
            }),
            
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}