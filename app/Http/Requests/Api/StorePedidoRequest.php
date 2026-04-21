<?php

namespace App\Http\Requests\Api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StorePedidoRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $contextId = Auth::user()?->id_contexto;

        return [
            'id_presupuesto' => [
                'nullable',
                'integer',
                Rule::exists('presupuestos', 'id_presupuesto')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'id_proyecto' => [
                'required',
                'integer',
                Rule::exists('proyectos', 'id_proyecto')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'id_empresa_cliente' => [
                'required',
                'integer',
                Rule::exists('empresas', 'id_empresa')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'id_contacto_empresa_cliente' => [
                'nullable',
                'integer',
                Rule::exists('contactos_empresas', 'id_contacto_empresa')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'id_estacion_servicio' => [
                'nullable',
                'integer',
                Rule::exists('estaciones_servicio', 'id_estacion_servicio')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'id_tarifario' => [
                'nullable',
                'integer',
                Rule::exists('tarifarios', 'id_tarifario')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'id_usuario_responsable' => [
                'nullable',
                'integer',
                Rule::exists('usuarios', 'id_usuario')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'numero_pedido' => [
                'required',
                'string',
                'max:100',
                Rule::unique('pedidos', 'numero_pedido')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'numero_aviso' => ['nullable', 'string', 'max:100'],
            'fecha_solicitud_pedido' => ['nullable', 'date'],
            'fecha_recepcion_pedido' => ['nullable', 'date'],
            'fecha_solicitud_factura' => ['nullable', 'date'],
            'estado' => [
                'sometimes',
                Rule::in(['pendiente', 'solicitado', 'recibido', 'en_ejecucion', 'cerrado', 'anulado']),
            ],
            'descripcion_seleccionable' => ['nullable', 'string', 'max:255'],
            'descripcion_libre' => ['nullable', 'string'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'iva' => ['nullable', 'numeric', 'min:0'],
            'total' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string'],

            'items' => ['sometimes', 'array'],
            'items.*.id_tarifario_servicio' => [
                'nullable',
                'integer',
                Rule::exists('tarifario_servicios', 'id_tarifario_servicio')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'items.*.id_servicio' => [
                'nullable',
                'integer',
                Rule::exists('servicios', 'id_servicio'),
            ],
            'items.*.orden' => ['nullable', 'integer', 'min:1'],
            'items.*.concepto_seleccionable' => ['nullable', 'string', 'max:255'],
            'items.*.concepto_libre' => ['nullable', 'string'],
            'items.*.cantidad' => ['required_with:items', 'numeric', 'gt:0'],
            'items.*.precio_unitario' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.iva_porcentaje' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.total_linea' => ['required_with:items', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'numero_pedido' => $this->normalizeNullableString($this->input('numero_pedido')),
            'numero_aviso' => $this->normalizeNullableString($this->input('numero_aviso')),
            'fecha_solicitud_pedido' => $this->normalizeNullableString($this->input('fecha_solicitud_pedido')),
            'fecha_recepcion_pedido' => $this->normalizeNullableString($this->input('fecha_recepcion_pedido')),
            'fecha_solicitud_factura' => $this->normalizeNullableString($this->input('fecha_solicitud_factura')),
            'descripcion_seleccionable' => $this->normalizeNullableString($this->input('descripcion_seleccionable')),
            'descripcion_libre' => $this->normalizeNullableString($this->input('descripcion_libre')),
            'observaciones' => $this->normalizeNullableString($this->input('observaciones')),
            'estado' => $this->input('estado', 'pendiente'),
            'subtotal' => $this->input('subtotal', 0),
            'iva' => $this->input('iva', 0),
            'total' => $this->input('total', 0),
            'items' => collect($this->input('items', []))
                ->map(function ($item, $index) {
                    return [
                        'id_tarifario_servicio' => $item['id_tarifario_servicio'] ?? null,
                        'id_servicio' => $item['id_servicio'] ?? null,
                        'orden' => $item['orden'] ?? ($index + 1),
                        'concepto_seleccionable' => $this->normalizeNullableString($item['concepto_seleccionable'] ?? null),
                        'concepto_libre' => $this->normalizeNullableString($item['concepto_libre'] ?? null),
                        'cantidad' => $item['cantidad'] ?? null,
                        'precio_unitario' => $item['precio_unitario'] ?? null,
                        'iva_porcentaje' => $item['iva_porcentaje'] ?? 21,
                        'total_linea' => $item['total_linea'] ?? null,
                    ];
                })
                ->values()
                ->all(),
        ]);
    }
}