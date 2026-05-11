<?php

namespace App\Http\Requests\Api;

use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdatePedidoRequest extends BaseApiRequest
{
    private const ALLOWED_STATUSES = [
        'pendiente',
        'solicitado',
        'recibido',
        'en_ejecucion',
        'facturado_parcial',
        'facturado',
        'cancelado',
        'anulado',
    ];

    public function rules(): array
    {
        $user = Auth::user();
        $accessibleContextIds = $user?->getActiveContextIds() ?? [];
        $pedido = $this->route('pedido');
        $pedidoId = $pedido instanceof Pedido ? $pedido->id_pedido : $pedido;
        $trabajo = $this->input('id_trabajo')
            ? \App\Models\Trabajo::query()->withoutGlobalScopes()->find($this->input('id_trabajo'))
            : null;
        $targetContextId = $trabajo?->id_contexto ?? ($pedido instanceof Pedido ? $pedido->id_contexto : null);

        return [
            'id_trabajo' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('trabajos', 'id_trabajo')
                    ->where(fn ($query) => $query->whereIn('id_contexto', $accessibleContextIds)),
            ],
            'id_tarifario' => [
                'nullable',
                'integer',
                Rule::exists('tarifarios', 'id_tarifario')
                    ->when($targetContextId !== null, fn ($query) => $query->where('id_contexto', $targetContextId)),
            ],
            'numero_pedido' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('pedidos', 'numero_pedido')
                    ->ignore($pedidoId, 'id_pedido')
                    ->when($targetContextId !== null, fn ($query) => $query->where('id_contexto', $targetContextId)),
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
                Rule::in(self::ALLOWED_STATUSES),
            ],
            'pedido_completo' => ['nullable', 'boolean'],
            'tiene_mas_de_1_item' => ['nullable', 'boolean'],
            'facturado_completo' => ['nullable', 'boolean'],
            'observaciones' => ['nullable', 'string'],

            'items' => ['sometimes', 'array'],
            'items.*.id_pedido_item' => [
                'nullable',
                'integer',
                'distinct',
                'min:1',
                Rule::exists('pedido_items', 'id_pedido_item')
                    ->where(fn ($query) => $query->where('id_pedido', $pedidoId)),
            ],
            'items.*.id_tarifario_linea' => [
                'nullable',
                'integer',
                Rule::exists('tarifario_lineas', 'id_tarifario_linea')
                    ->when($targetContextId !== null, fn ($query) => $query->where('id_contexto', $targetContextId)),
            ],
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

        // Casteo estricto de booleanos solo si vienen en el request (para soportar PATCH)
        $booleans = ['pedido_completo', 'tiene_mas_de_1_item', 'facturado_completo'];
        foreach ($booleans as $field) {
            if ($this->has($field)) {
                $normalized[$field] = filter_var($this->input($field), FILTER_VALIDATE_BOOLEAN);
            }
        }

        if ($this->has('observaciones')) {
            $normalized['observaciones'] = $this->normalizeNullableString($this->input('observaciones'));
        }

        // Limpieza y estructuración de los items anidados (Esquema Real de abaco_ciete)
        if ($this->has('items') && is_array($this->input('items'))) {
            $normalized['items'] = collect($this->input('items'))->map(function ($item) {
                $idPedidoItem = $item['id_pedido_item'] ?? $item['id'] ?? null;
                $cantidad = isset($item['cantidad']) ? (float) $item['cantidad'] : 1;
                $precioUnitario = isset($item['precio_unitario']) ? (float) $item['precio_unitario'] : 0;
                
                return [
                    'id_pedido_item' => $idPedidoItem !== null && $idPedidoItem !== '' ? (int) $idPedidoItem : null,
                    'id_tarifario_linea' => $item['id_tarifario_linea'] ?? null,
                    'codigo_servicio' => $this->normalizeNullableString($item['codigo_servicio'] ?? null),
                    'numero_tarifa' => $this->normalizeNullableString($item['numero_tarifa'] ?? null),
                    'descripcion_servicio' => $this->normalizeNullableString($item['descripcion_servicio'] ?? null),
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'total_linea' => $item['total_linea'] ?? ($cantidad * $precioUnitario),
                ];
            })->values()->all();
        }

        if (!empty($normalized)) {
            $this->merge($normalized);
        }
    }
}
