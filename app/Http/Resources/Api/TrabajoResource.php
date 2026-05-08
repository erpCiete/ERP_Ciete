<?php

namespace App\Http\Resources\Api;

use App\Support\TrabajoPermission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrabajoResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Prefer the hasOne primerPedido relation (eager-loaded in index).
        // Fall back to sorting the full pedidos collection if that was loaded instead.
        $primerPedido = $this->relationLoaded('primerPedido')
            ? $this->primerPedido
            : ($this->relationLoaded('pedidos')
                ? $this->pedidos->sortBy('fecha_solicitud')->first()
                : null);

        $user = $request->user();
        return [
            'id_trabajo'           => $this->id_trabajo,
            'numero_trabajo'       => $this->numero_trabajo,
            'numero_trabajo_operativo' => $this->numero_trabajo_operativo,
            'numero_trabajo_visible' => $this->numeroTrabajoVisible(),
            'id_empresa_cliente'   => $this->id_empresa_cliente,
            'id_estacion_servicio' => $this->id_estacion_servicio,
            'estado'               => $this->estado,
            'fecha_encargo'        => $this->formatDateValue($this->fecha_encargo),
            'fecha_terminacion'    => $this->formatDateValue($this->fecha_terminacion),
            'fecha_terminado'      => $this->formatDateValue($this->fecha_terminacion),
            'id_contexto'          => $this->id_contexto,

            $this->mergeWhen($this->id_contexto === 1, [
                'id_contrato' => $this->id_contrato,
                'categoria'   => $this->categoria,
                // Serializar relación solo si está cargada
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

            // ── Campos planos para vista Excel ──────────────────────────────
            'descripcion_trabajo'    => $this->descripcion_trabajo,
            'observaciones'          => $this->observaciones,
            'responsable_cliente'    => $this->responsable_cliente,
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
            'numero_pedido_principal' => $primerPedido?->numero_pedido,
            // withSum attributes are set directly on the model when using ->withSum() in the query builder.
            // When pedidos collection is loaded instead (e.g. after store/patchField), sum from collection.
            'importe_pedido_total'      => $this->importe_pedido_total
                ?? ($this->relationLoaded('pedidos') ? $this->pedidos->sum('importe_pedido') : null),
            'importe_solicitado_total'  => $this->importe_solicitado_total
                ?? ($this->relationLoaded('pedidos') ? $this->pedidos->sum('importe_solicitado') : null),
            'importe_facturado_total'   => $this->importe_facturado_total
                ?? ($this->relationLoaded('pedidos') ? $this->pedidos->sum('importe_facturado') : null),
            // ────────────────────────────────────────────────────────────────

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
