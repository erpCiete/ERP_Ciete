<?php

namespace App\Http\Resources\Api;

use App\Support\TrabajoPermission;
use App\Services\TrabajoStateService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrabajoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // En el index se eager-carga primerPedido (hasOne). Si en su lugar llega
        // la colección completa (p. ej. tras store), se toma el primero por fecha.
        $primerPedido = $this->relationLoaded('primerPedido')
            ? $this->primerPedido
            : ($this->relationLoaded('pedidos')
                ? $this->pedidos->sortBy('fecha_solicitud')->first()
                : null);
        $pedidosResumen = $this->relationLoaded('pedidos')
            ? $this->pedidos
            : \App\Models\Pedido::query()
                ->where('id_trabajo', $this->id_trabajo)
                ->orderBy('id_pedido')
                ->get([
                    'id_pedido',
                    'id_trabajo',
                    'id_contexto',
                    'numero_pedido',
                    'fecha_solicitud',
                    'estado',
                    'importe_pedido',
                    'importe_facturado',
                    'pedido_completo',
                    'facturado_completo',
                ]);
        $billingSnapshot = app(TrabajoStateService::class)->billingSnapshotForTrabajo($this->resource);
        $closureSnapshot = app(TrabajoStateService::class)->closureSnapshotForTrabajo($this->resource, $billingSnapshot);

        $user = $request->user();
        return [
            'id_trabajo'           => $this->id_trabajo,
            'numero_trabajo'       => $this->numero_trabajo,
            'numero_trabajo_operativo' => $this->numero_trabajo_operativo,
            'numero_trabajo_visible' => $this->numeroTrabajoVisible(),
            'id_empresa_cliente'   => $this->id_empresa_cliente,
            'id_estacion_servicio' => $this->id_estacion_servicio,
            'id_tarifario'         => $this->id_tarifario,
            'estado'               => $this->estado,
            'estado_facturacion'   => $billingSnapshot['estado_facturacion'],
            'facturacion_completa' => $billingSnapshot['facturacion_completa'],
            'cierre_secundario'    => $closureSnapshot['estado_cierre'],
            'listo_para_cierre'    => $closureSnapshot['listo_para_cierre'],
            'fecha_encargo'        => $this->formatDateValue($this->fecha_encargo),
            'fecha_terminacion'    => $this->formatDateValue($this->fecha_terminacion),
            'fecha_terminado'      => $this->formatDateValue($this->fecha_terminacion),
            'id_contexto'          => $this->id_contexto,

            $this->mergeWhen($this->id_contexto === 1, [
                'id_contrato' => $this->id_contrato,
                'contrato'    => new ContratoResource($this->whenLoaded('contrato')),
            ]),

            $this->mergeWhen($this->id_contexto === 2, [
                'id_tipo_documento' => $this->id_tipo_documento,
                'id_tipo_trabajo'   => $this->id_tipo_trabajo,
                'numero_aviso'      => $this->numero_aviso,
                'tipo_documento'    => new TipoDocumentoResource($this->whenLoaded('tipoDocumento')),
                'tipo_trabajo'      => new TipoTrabajoResource($this->whenLoaded('tipoTrabajo')),
            ]),

            'empresa'  => new ClienteResource($this->whenLoaded('empresa')),
            'estacion' => new EstacionResource($this->whenLoaded('estacion')),

            'descripcion_trabajo'    => $this->descripcion_trabajo,
            'observaciones'          => $this->observaciones,
            'responsable_cliente'    => $this->responsable_cliente,
            'categoria'              => $this->categoria,
            'tipo_trabajo_nombre'    => $this->tipoTrabajo?->nombre ?? ($this->categoria ?? null),
            'codigo_estacion'        => $this->estacion?->codigo_estacion,
            'municipio'              => $this->estacion?->poblacion,
            'provincia'              => $this->estacion?->provincia,
            'nombre_estacion'        => $this->estacion?->nombre,
            'nombre_responsable'     => $this->responsableCiete
                ? trim(($this->responsableCiete->nombre ?? '') . ' ' . ($this->responsableCiete->apellidos ?? ''))
                : null,
            'id_responsable_ciete'   => $this->id_responsable_ciete,
            'responsable_ciete'      => $this->responsableCiete ? [
                'id_usuario' => $this->responsableCiete->id_usuario,
                'nombre' => trim(($this->responsableCiete->nombre ?? '') . ' ' . ($this->responsableCiete->apellidos ?? '')),
            ] : null,
            'nombre_contrato'        => $this->contrato?->nombre,
            'nombre_tarifa'          => $this->tarifario?->nombre,
            'fecha_solicitud_pedido' => $this->formatDateValue($primerPedido?->fecha_solicitud),
            'id_pedido_principal'    => $primerPedido?->id_pedido,
            'numero_pedido_principal' => $primerPedido?->numero_pedido,
            'pedidos_count'           => (int) ($this->pedidos_count
                ?? ($this->relationLoaded('pedidos') ? $this->pedidos->count() : 0)),
            'pedidos_resumen'         => $pedidosResumen
                ->sortBy('id_pedido')
                ->values()
                ->map(fn ($pedido): array => [
                    'id_pedido' => $pedido->id_pedido,
                    'id_trabajo' => $pedido->id_trabajo,
                    'id_contexto' => $pedido->id_contexto,
                    'numero_pedido' => $pedido->numero_pedido,
                    'fecha_solicitud' => $this->formatDateValue($pedido->fecha_solicitud),
                    'estado' => $pedido->estado,
                    'importe_pedido' => isset($pedido->importe_pedido) ? (float) $pedido->importe_pedido : 0.0,
                    'importe_facturado' => isset($pedido->importe_facturado) ? (float) $pedido->importe_facturado : 0.0,
                    'pedido_completo' => (bool) ($pedido->pedido_completo ?? false),
                    'facturado_completo' => (bool) ($pedido->facturado_completo ?? false),
                ])
                ->all(),
            // withSum añade el atributo directamente al modelo; si no está (post-store), se suma desde la colección.
            'importe_pedido_total'      => $this->importe_pedido_total
                ?? ($this->relationLoaded('pedidos') ? $this->pedidos->sum('importe_pedido') : null),
            'importe_solicitado_total'  => $this->importe_solicitado_total
                ?? ($this->relationLoaded('pedidos') ? $this->pedidos->sum('importe_solicitado') : null),
            'importe_facturado_total'   => $this->importe_facturado_total
                ?? ($this->relationLoaded('pedidos') ? $this->pedidos->sum('importe_facturado') : null),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            'can' => [
                'update'  => TrabajoPermission::canEdit($user, $this->resource),
                'delete'  => TrabajoPermission::canDelete($user, $this->resource),
                'restore' => false,
            ],
        ];
    }

    private function formatDateValue(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return $value ? (string) $value : null;
    }
}
