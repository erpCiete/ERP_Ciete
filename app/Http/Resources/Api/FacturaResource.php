<?php

namespace App\Http\Resources\Api;

use App\Models\ContratoEmpresaFacturadora;
use App\Models\FacturaItem;
use App\Models\Pedido;
use App\Models\Trabajo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class FacturaResource extends JsonResource
{
    private const MONEY_TOLERANCE = 0.01;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->resource->loadMissing([
            'empresa',
            'empresaFacturadora',
            'contrato',
            'items.pedidoItem.pedido.trabajo.estacion',
            'items.pedidoItem.pedido.trabajo.contrato',
            'items.pedidoItem.pedido.tarifario',
            'items.pedidoItem.tarifarioLinea.tarifario.contrato',
        ]);

        /** @var Collection<int, FacturaItem> $items */
        $items = $this->items ?? collect();
        $importeAsignado = round($items->sum(fn (FacturaItem $item) => (float) $item->importe_facturado), 2);
        $importeFactura = $this->invoiceAmount();
        $diferencia = round($importeFactura - $importeAsignado, 2);
        $estadoCuadre = $this->resolveEstadoCuadre($items, $diferencia);

        return [
            'id_factura' => $this->id_factura,
            'id_contexto' => $this->id_contexto,
            'id_trabajo' => $this->derivedTrabajoId($items),
            'id_contrato' => $this->id_contrato,
            'id_empresa_facturadora' => $this->id_empresa_facturadora,
            'id_empresa_cliente' => $this->id_empresa_cliente,
            'numero_factura' => $this->numero_factura,
            'numero_factura_ccp' => $this->numero_factura_ccp,
            'factura_ccp' => $this->numero_factura_ccp,
            'serie' => $this->serie,
            'orden_factura' => $this->orden_factura ? (int) $this->orden_factura : null,
            'fecha_solicitud' => optional($this->fecha_solicitud)->format('Y-m-d') ?: $this->fecha_solicitud,
            'fecha_emision' => optional($this->fecha_emision)->format('Y-m-d') ?: $this->fecha_emision,
            'fecha_vencimiento' => optional($this->fecha_vencimiento)->format('Y-m-d') ?: $this->fecha_vencimiento,
            'importe' => isset($this->importe) ? (float) $this->importe : 0.0,
            'base_imponible' => isset($this->base_imponible) ? (float) $this->base_imponible : 0.0,
            'iva' => isset($this->iva) ? (float) $this->iva : 0.0,
            'retencion' => isset($this->retencion) ? (float) $this->retencion : 0.0,
            'total' => isset($this->total) ? (float) $this->total : 0.0,
            'importe_asignado' => $importeAsignado,
            'diferencia' => $diferencia,
            'estado_cuadre' => $estadoCuadre,
            'estado' => $this->estado,
            'autofactura' => (bool) $this->autofactura,
            'sociedad' => $this->sociedad,
            'observaciones' => $this->observaciones,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'trabajo' => $this->trabajosPayload($items)[0] ?? null,
            'empresa' => $this->empresaPayload($this->empresa),
            'empresa_facturadora' => $this->empresaPayload($this->empresaFacturadora),
            'sociedad_cif_validada' => $this->sociedadCifValidada(),
            'contrato' => $this->contratoPayload($this->contrato),
            'tarifarios' => $this->tarifariosPayload($items),
            'items' => $this->itemsPayload($items),
            'trabajos' => $this->trabajosPayload($items),
            'pedidos' => $this->pedidosPayload($items),
        ];
    }

    private function invoiceAmount(): float
    {
        if ($this->total !== null) {
            return (float) $this->total;
        }

        if ($this->importe !== null) {
            return (float) $this->importe;
        }

        return (float) ($this->base_imponible ?? 0);
    }

    /**
     * @param  Collection<int, FacturaItem>  $items
     */
    private function resolveEstadoCuadre(Collection $items, float $diferencia): string
    {
        if ($items->isEmpty()) {
            return 'sin_items';
        }

        return abs($diferencia) <= self::MONEY_TOLERANCE ? 'cuadrada' : 'descuadrada';
    }

    /**
     * @param  Collection<int, FacturaItem>  $items
     * @return array<int, array<string, mixed>>
     */
    private function itemsPayload(Collection $items): array
    {
        return $items
            ->sortBy('id_factura_item')
            ->values()
            ->map(function (FacturaItem $item): array {
                $pedidoItem = $item->pedidoItem;
                $pedido = $pedidoItem?->pedido;
                $trabajo = $pedido?->trabajo;
                $tarifario = $pedidoItem?->tarifarioLinea?->tarifario ?? $pedido?->tarifario;

                return [
                    'id_factura_item' => (int) $item->id_factura_item,
                    'id_factura' => (int) $item->id_factura,
                    'id_pedido_item' => $item->id_pedido_item ? (int) $item->id_pedido_item : null,
                    'unidades_facturadas' => $item->unidades_facturadas !== null ? (float) $item->unidades_facturadas : null,
                    'importe_facturado' => (float) $item->importe_facturado,
                    'observaciones' => $item->observaciones,
                    'pedido_item' => $pedidoItem ? [
                        'id_pedido_item' => (int) $pedidoItem->id_pedido_item,
                        'id_contexto' => (int) $pedidoItem->id_contexto,
                        'id_pedido' => (int) $pedidoItem->id_pedido,
                        'id_tarifario_linea' => $pedidoItem->id_tarifario_linea ? (int) $pedidoItem->id_tarifario_linea : null,
                        'codigo_servicio' => $pedidoItem->codigo_servicio,
                        'numero_tarifa' => $pedidoItem->numero_tarifa,
                        'descripcion_servicio' => $pedidoItem->descripcion_servicio,
                        'cantidad' => (float) $pedidoItem->cantidad,
                        'precio_unitario' => (float) $pedidoItem->precio_unitario,
                        'total_linea' => (float) $pedidoItem->total_linea,
                    ] : null,
                    'pedido' => $pedido ? $this->pedidoPayload($pedido) : null,
                    'trabajo' => $trabajo ? $this->trabajoPayload($trabajo) : null,
                    'tarifario' => $tarifario ? [
                        'id_tarifario' => (int) $tarifario->id_tarifario,
                        'id_contrato' => $tarifario->id_contrato ? (int) $tarifario->id_contrato : null,
                        'nombre' => $tarifario->nombre,
                        'version' => $tarifario->version,
                    ] : null,
                ];
            })
            ->all();
    }

    /**
     * @param  Collection<int, FacturaItem>  $items
     * @return array<int, array<string, mixed>>
     */
    private function trabajosPayload(Collection $items): array
    {
        $trabajos = $items
            ->map(fn (FacturaItem $item) => $item->pedidoItem?->pedido?->trabajo)
            ->filter()
            ->unique('id_trabajo')
            ->values();

        return $trabajos
            ->map(fn (Trabajo $trabajo) => $this->trabajoPayload($trabajo))
            ->all();
    }

    /**
     * @param  Collection<int, FacturaItem>  $items
     * @return array<int, array<string, mixed>>
     */
    private function pedidosPayload(Collection $items): array
    {
        $pedidos = $items
            ->map(fn (FacturaItem $item) => $item->pedidoItem?->pedido)
            ->filter()
            ->unique('id_pedido')
            ->values();

        if ($pedidos->isNotEmpty()) {
            return $pedidos
                ->map(fn (Pedido $pedido) => $this->pedidoPayload($pedido))
                ->all();
        }

        return [];
    }

    /**
     * @param  Collection<int, FacturaItem>  $items
     * @return array<int, array<string, mixed>>
     */
    private function tarifariosPayload(Collection $items): array
    {
        return $items
            ->map(fn (FacturaItem $item) => $item->pedidoItem?->tarifarioLinea?->tarifario ?? $item->pedidoItem?->pedido?->tarifario)
            ->filter()
            ->unique('id_tarifario')
            ->values()
            ->map(fn ($tarifario) => [
                'id_tarifario' => (int) $tarifario->id_tarifario,
                'id_contrato' => $tarifario->id_contrato ? (int) $tarifario->id_contrato : null,
                'nombre' => $tarifario->nombre,
                'version' => $tarifario->version,
            ])
            ->all();
    }

    /**
     * @param  Collection<int, FacturaItem>  $items
     */
    private function derivedTrabajoId(Collection $items): ?int
    {
        $trabajo = $items
            ->map(fn (FacturaItem $item) => $item->pedidoItem?->pedido?->trabajo)
            ->filter()
            ->first();

        return $trabajo?->id_trabajo ? (int) $trabajo->id_trabajo : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function trabajoPayload(Trabajo $trabajo): array
    {
        return [
            'id_trabajo' => (int) $trabajo->id_trabajo,
            'id_contexto' => (int) $trabajo->id_contexto,
            'id_empresa_cliente' => $trabajo->id_empresa_cliente ? (int) $trabajo->id_empresa_cliente : null,
            'id_contrato' => $trabajo->id_contrato ? (int) $trabajo->id_contrato : null,
            'id_tarifario' => $trabajo->id_tarifario ? (int) $trabajo->id_tarifario : null,
            'numero_trabajo' => $trabajo->numero_trabajo,
            'numero_trabajo_operativo' => $trabajo->numero_trabajo_operativo,
            'numero_trabajo_visible' => $trabajo->numeroTrabajoVisible(),
            'descripcion_trabajo' => $trabajo->descripcion_trabajo,
            'codigo_estacion' => $trabajo->estacion?->codigo_estacion,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pedidoPayload(Pedido $pedido): array
    {
        return [
            'id_pedido' => (int) $pedido->id_pedido,
            'id_contexto' => (int) $pedido->id_contexto,
            'id_trabajo' => $pedido->id_trabajo ? (int) $pedido->id_trabajo : null,
            'id_tarifario' => $pedido->id_tarifario ? (int) $pedido->id_tarifario : null,
            'numero_pedido' => $pedido->numero_pedido,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function empresaPayload(mixed $empresa): ?array
    {
        if (! $empresa) {
            return null;
        }

        return [
            'id_empresa' => (int) $empresa->id_empresa,
            'id_contexto' => (int) $empresa->id_contexto,
            'nombre' => $empresa->nombre,
            'nombre_comercial' => $empresa->nombre_comercial,
            'razon_social' => $empresa->razon_social,
            'cif' => $empresa->cif,
            'cif_label' => $empresa->cif ? $empresa->nombre . ' — ' . $empresa->cif : $empresa->nombre,
            'activo' => (bool) $empresa->activo,
        ];
    }

    private function sociedadCifValidada(): ?bool
    {
        if (! $this->id_empresa_facturadora || ! $this->id_contrato || ! $this->id_contexto) {
            return null;
        }

        if (array_key_exists('sociedad_cif_validada_preload', $this->resource->getAttributes())) {
            return (bool) $this->resource->getAttribute('sociedad_cif_validada_preload');
        }

        return ContratoEmpresaFacturadora::query()
            ->where('id_contrato', (int) $this->id_contrato)
            ->where('id_empresa', (int) $this->id_empresa_facturadora)
            ->where('id_contexto', (int) $this->id_contexto)
            ->where('activo', true)
            ->exists();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function contratoPayload(mixed $contrato): ?array
    {
        if (! $contrato) {
            return null;
        }

        return [
            'id_contrato' => (int) $contrato->id_contrato,
            'codigo_contrato' => $contrato->codigo_contrato,
            'nombre' => $contrato->nombre,
        ];
    }
}
