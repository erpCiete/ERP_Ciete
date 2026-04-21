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
            'id_trabajo' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('trabajos', 'id_trabajo')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'id_tarifario' => [
                'nullable',
                'integer',
                Rule::exists('tarifarios', 'id_tarifario')
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
            'fecha_solicitud' => ['nullable', 'date'],
            'fecha_recepcion' => ['nullable', 'date'],
            'importe_pedido' => ['nullable', 'numeric', 'min:0'],
            'importe_solicitado' => ['nullable', 'numeric', 'min:0'],
            'importe_facturado' => ['nullable', 'numeric', 'min:0'],
            'unidades_pedido' => ['nullable', 'numeric', 'min:0'],
            'unidades_solicitadas' => ['nullable', 'numeric', 'min:0'],
            'estado' => [
                'sometimes',
                Rule::in(['pendiente', 'solicitado', 'recibido', 'en_ejecucion', 'cerrado', 'anulado']),
            ],
            'pedido_completo' => ['nullable', 'boolean'],
            'tiene_mas_de_1_item' => ['nullable', 'boolean'],
            'facturado_completo' => ['nullable', 'boolean'],
            'observaciones' => ['nullable', 'string'],

            'items' => ['sometimes', 'array'],
            'items.*.id_tarifario_linea' => [
                'nullable',
                'integer',
                Rule::exists('tarifario_lineas', 'id_tarifario_linea')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            
            // Validaciones para Líneas de Pedido (Items) - Esquema Real
            'items' => ['nullable', 'array'],
            'items.*.id_tarifario_linea' => ['nullable', 'integer'],
            'items.*.codigo_servicio' => ['nullable', 'string', 'max:30'],
            'items.*.numero_tarifa' => ['nullable', 'string', 'max:30'],
            'items.*.descripcion_servicio' => ['nullable', 'string', 'max:255'],
            'items.*.cantidad' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.precio_unitario' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.total_linea' => ['required_with:items', 'numeric'],
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