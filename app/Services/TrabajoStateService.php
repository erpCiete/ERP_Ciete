<?php

namespace App\Services;

use App\Models\FacturaItem;
use App\Models\Pedido;
use App\Models\Trabajo;
use Illuminate\Support\Collection;

class TrabajoStateService
{
    private const MONEY_TOLERANCE = 0.01;

    /**
     * @param  iterable<int, int|string|null>  $pedidoIds
     */
    public function syncPedidosAndTrabajosByPedidoIds(iterable $pedidoIds): void
    {
        $normalizedPedidoIds = collect($pedidoIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($normalizedPedidoIds->isEmpty()) {
            return;
        }

        $pedidos = Pedido::query()
            ->with([
                'items.facturaItems.factura:id_factura,estado',
                'trabajo:id_trabajo,estado,fecha_terminacion,bloqueado_cierre',
            ])
            ->whereIn('id_pedido', $normalizedPedidoIds)
            ->get();

        foreach ($pedidos as $pedido) {
            $this->syncPedidoState($pedido);
        }

        $this->syncTrabajosByIds($pedidos->pluck('id_trabajo'));
    }

    /**
     * @param  iterable<int, int|string|null>  $trabajoIds
     */
    public function syncTrabajosByIds(iterable $trabajoIds): void
    {
        $normalizedTrabajoIds = collect($trabajoIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($normalizedTrabajoIds->isEmpty()) {
            return;
        }

        $trabajos = Trabajo::query()
            ->with([
                'pedidos' => fn ($query) => $query->orderBy('id_pedido'),
            ])
            ->whereIn('id_trabajo', $normalizedTrabajoIds)
            ->get();

        foreach ($trabajos as $trabajo) {
            $this->syncTrabajoState($trabajo);
        }
    }

    public function syncTrabajo(Trabajo $trabajo): void
    {
        $trabajo->loadMissing([
            'pedidos' => fn ($query) => $query
                ->with(['items.facturaItems.factura:id_factura,estado'])
                ->orderBy('id_pedido'),
        ]);

        foreach ($trabajo->pedidos as $pedido) {
            $this->syncPedidoState($pedido);
        }

        $trabajo->unsetRelation('pedidos');
        $trabajo->load([
            'pedidos' => fn ($query) => $query->orderBy('id_pedido'),
        ]);

        $this->syncTrabajoState($trabajo);
    }

    public function syncPedidoState(Pedido $pedido): void
    {
        $pedido->loadMissing([
            'items.facturaItems.factura:id_factura,estado',
        ]);

        $facturado = $this->pedidoFacturadoAmount($pedido);
        $importePedido = round((float) ($pedido->importe_pedido ?? 0), 2);
        $currentStatus = (string) ($pedido->estado ?? '');

        $nextStatus = $currentStatus;

        // Los pedidos cancelados/anulados no cambian de estado por facturación.
        if (! in_array($currentStatus, ['cancelado', 'anulado'], true)) {
            if ($importePedido > 0 && $facturado >= ($importePedido - self::MONEY_TOLERANCE)) {
                $nextStatus = 'facturado';
            } elseif ($facturado > self::MONEY_TOLERANCE) {
                $nextStatus = 'facturado_parcial';
            } elseif (in_array($currentStatus, ['facturado', 'facturado_parcial'], true)) {
                $nextStatus = (bool) $pedido->pedido_completo ? 'recibido' : 'pendiente';
            }
        }

        $pedido->setAttribute('importe_facturado', number_format($facturado, 2, '.', ''));
        $pedido->facturado_completo = $importePedido > 0 && $facturado >= ($importePedido - self::MONEY_TOLERANCE);
        $pedido->estado = $nextStatus;

        if ($pedido->isDirty(['importe_facturado', 'facturado_completo', 'estado'])) {
            $pedido->save();
        }
    }

    public function syncTrabajoState(Trabajo $trabajo): void
    {
        $trabajo->loadMissing([
            'pedidos' => fn ($query) => $query->orderBy('id_pedido'),
        ]);

        $derivedState = $this->deriveTrabajoState($trabajo);

        if ($trabajo->estado !== $derivedState) {
            $trabajo->estado = $derivedState;
            $trabajo->save();
        }
    }

    /**
     * @return array{
     *   tiene_pedidos:bool,
     *   tiene_pedido_facturable:bool,
     *   importe_pedido_total:float,
     *   importe_facturado_total:float,
     *   estado_facturacion:string,
     *   facturacion_completa:bool
     * }
     */
    public function billingSnapshotForTrabajo(Trabajo $trabajo): array
    {
        $pedidos = $this->resolveTrabajoPedidos($trabajo);
        $pedidosActivos = $pedidos
            ->filter(fn (Pedido $pedido) => ! in_array((string) $pedido->estado, ['cancelado', 'anulado'], true))
            ->values();

        $hasAggregatePedidoTotal = isset($trabajo->importe_pedido_total);
        $hasAggregateFacturadoTotal = isset($trabajo->importe_facturado_total);
        $pedidosWithoutItemsLoaded = $pedidosActivos->isNotEmpty()
            && $pedidosActivos->every(fn (Pedido $pedido) => ! $pedido->relationLoaded('items'));

        $importePedidoTotal = $hasAggregatePedidoTotal && $pedidosWithoutItemsLoaded
            ? round((float) $trabajo->importe_pedido_total, 2)
            : round((float) $pedidosActivos->sum(fn (Pedido $pedido) => (float) ($pedido->importe_pedido ?? 0)), 2);
        $importeFacturadoTotal = $hasAggregateFacturadoTotal && $pedidosWithoutItemsLoaded
            ? round((float) $trabajo->importe_facturado_total, 2)
            : round((float) $pedidosActivos->sum(fn (Pedido $pedido) => $this->pedidoFacturadoAmount($pedido)), 2);
        $tienePedidoFacturable = $importePedidoTotal > self::MONEY_TOLERANCE;

        $estadoFacturacion = match (true) {
            $pedidosActivos->isEmpty() => 'sin_pedido',
            ! $tienePedidoFacturable => 'sin_facturar',
            $importeFacturadoTotal >= ($importePedidoTotal - self::MONEY_TOLERANCE) => 'facturado_completo',
            $importeFacturadoTotal > self::MONEY_TOLERANCE => 'facturado_parcial',
            default => 'sin_facturar',
        };

        return [
            'tiene_pedidos' => $pedidosActivos->isNotEmpty(),
            'tiene_pedido_facturable' => $tienePedidoFacturable,
            'importe_pedido_total' => $importePedidoTotal,
            'importe_facturado_total' => $importeFacturadoTotal,
            'estado_facturacion' => $estadoFacturacion,
            'facturacion_completa' => $estadoFacturacion === 'facturado_completo',
        ];
    }

    /**
     * @param  array{
     *   tiene_pedidos:bool,
     *   tiene_pedido_facturable:bool,
     *   importe_pedido_total:float,
     *   importe_facturado_total:float,
     *   estado_facturacion:string,
     *   facturacion_completa:bool
     * }|null  $billingSnapshot
     * @return array{estado_cierre:string,listo_para_cierre:bool}
     */
    public function closureSnapshotForTrabajo(Trabajo $trabajo, ?array $billingSnapshot = null): array
    {
        $billingSnapshot ??= $this->billingSnapshotForTrabajo($trabajo);

        if ($trabajo->estado === 'cancelado') {
            return ['estado_cierre' => 'no_aplica', 'listo_para_cierre' => false];
        }

        if ($trabajo->estado === 'finalizado') {
            return ['estado_cierre' => 'cerrado', 'listo_para_cierre' => false];
        }

        if (! $this->isTrabajoFinished($trabajo)) {
            return ['estado_cierre' => 'no_aplica', 'listo_para_cierre' => false];
        }

        if (! $billingSnapshot['facturacion_completa']) {
            return ['estado_cierre' => 'pendiente_facturacion', 'listo_para_cierre' => false];
        }

        if ($trabajo->bloqueado_cierre) {
            return ['estado_cierre' => 'bloqueado', 'listo_para_cierre' => false];
        }

        return ['estado_cierre' => 'listo_para_cierre', 'listo_para_cierre' => true];
    }

    // El estado visible del trabajo se deriva de pedidos y facturas.
    // cancelado y finalizado son estados terminales que nunca se recalculan.
    public function deriveTrabajoState(Trabajo $trabajo): string
    {
        $currentState = (string) ($trabajo->estado ?? '');

        if ($currentState === 'cancelado') {
            return 'cancelado';
        }

        if ($currentState === 'finalizado') {
            return 'finalizado';
        }

        if (! $this->isTrabajoFinished($trabajo)) {
            return 'en_curso';
        }

        $billingSnapshot = $this->billingSnapshotForTrabajo($trabajo);

        if (! $billingSnapshot['tiene_pedidos'] || ! $billingSnapshot['tiene_pedido_facturable']) {
            return 'terminado';
        }

        if ($billingSnapshot['facturacion_completa']) {
            return 'facturado';
        }

        return 'pendiente_facturar';
    }

    private function resolveTrabajoPedidos(Trabajo $trabajo): Collection
    {
        if ($trabajo->relationLoaded('pedidos')) {
            return $trabajo->pedidos;
        }

        return $trabajo->pedidos()->orderBy('id_pedido')->get();
    }

    private function pedidoFacturadoAmount(Pedido $pedido): float
    {
        if ($pedido->relationLoaded('items')) {
            return round((float) $pedido->items->sum(function ($item): float {
                if (! $item->relationLoaded('facturaItems')) {
                    return (float) FacturaItem::query()
                        ->join('facturas', 'facturas.id_factura', '=', 'factura_items.id_factura')
                        ->where('factura_items.id_pedido_item', $item->id_pedido_item)
                        ->where('facturas.estado', '!=', 'anulada')
                        ->sum('factura_items.importe_facturado');
                }

                if ($item->facturaItems->contains(fn ($facturaItem) => ! $facturaItem->relationLoaded('factura'))) {
                    return (float) FacturaItem::query()
                        ->join('facturas', 'facturas.id_factura', '=', 'factura_items.id_factura')
                        ->where('factura_items.id_pedido_item', $item->id_pedido_item)
                        ->where('facturas.estado', '!=', 'anulada')
                        ->sum('factura_items.importe_facturado');
                }

                return (float) $item->facturaItems
                    ->filter(fn ($facturaItem) => ($facturaItem->factura?->estado ?? null) !== 'anulada')
                    ->sum(fn ($facturaItem) => (float) ($facturaItem->importe_facturado ?? 0));
            }), 2);
        }

        return round((float) ($pedido->importe_facturado ?? 0), 2);
    }

    public function isTrabajoFinished(Trabajo $trabajo): bool
    {
        return filled($trabajo->fecha_terminacion)
            || in_array((string) $trabajo->estado, ['terminado', 'pendiente_facturar', 'facturado', 'finalizado'], true);
    }
}
