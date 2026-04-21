<?php

namespace App\Http\Requests\Api;

use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdatePedidoRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $contextId = Auth::user()?->id_contexto;
        $pedido = $this->route('pedido');
        $pedidoId = $pedido instanceof Pedido ? $pedido->id_pedido : $pedido;

        return [
            'id_presupuesto' => [
                'nullable',
                'integer',
                Rule::exists('presupuestos', 'id_presupuesto')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'id_proyecto' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('proyectos', 'id_proyecto')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'id_empresa_cliente' => [
                'sometimes',
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
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('pedidos', 'numero_pedido')
                    ->ignore($pedidoId, 'id_pedido')
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
        $normalized = [];

        $simpleNullableFields = [
            'numero_pedido',
            'numero_aviso',
            'fecha_solicitud_pedido',
            'fecha_recepcion_pedido',
            'fecha_solicitud_factura',
            'descripcion_seleccionable',
            'descripcion_libre',
            'observaciones',
        ];

        foreach ($simpleNullableFields as $field) {
            if ($this->has($field)) {
                $normalized[$field] = $this->normalizeNullableString($this->input($field));
            }
        }

        if ($this->has('items')) {
            $normalized['items'] = collect($this->input('items', []))
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
                ->all();
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }
}