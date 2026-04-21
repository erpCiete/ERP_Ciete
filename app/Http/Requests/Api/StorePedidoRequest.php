<?php

namespace App\Http\Requests\Api;

use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdatePedidoRequest extends BaseApiRequest
{
    /**
     * Prepara los datos para validación, casteando booleanos
     * y recalculando los totales de las líneas si es necesario.
     */
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

        // Limpieza de cadenas
        if ($this->has('observaciones')) {
            $normalized['observaciones'] = $this->normalizeNullableString($this->input('observaciones'));
        }

        // Limpieza y estructuración de los items anidados
        if ($this->has('items') && is_array($this->input('items'))) {
            $normalized['items'] = collect($this->input('items'))->map(function ($item, $index) {
                $cantidad = $item['cantidad'] ?? 0;
                $precioUnitario = $item['precio_unitario'] ?? 0;
                
                return [
                    'orden' => $item['orden'] ?? ($index + 1),
                    'concepto_libre' => $this->normalizeNullableString($item['concepto_libre'] ?? null),
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'iva_porcentaje' => $item['iva_porcentaje'] ?? 21,
                    'total_linea' => $item['total_linea'] ?? ($cantidad * $precioUnitario),
                ];
            })->values()->all();
        }

        if (!empty($normalized)) {
            $this->merge($normalized);
        }
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        $contextId = Auth::user()?->id_contexto;
        $pedido = $this->route('pedido');
        $pedidoId = $pedido instanceof Pedido ? $pedido->id_pedido : $pedido;

        return [
            // Relaciones
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
                // Aseguramos que si envían un tarifario, pertenezca al mismo contexto
                Rule::exists('tarifarios', 'id_tarifario')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],

            // Datos Base
            'numero_pedido' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                // Ignorar el pedido actual en la validación unique y respetar el contexto
                Rule::unique('pedidos', 'numero_pedido')
                    ->ignore($pedidoId, 'id_pedido')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'fecha_solicitud' => ['nullable', 'date'],
            'fecha_recepcion' => ['nullable', 'date'],

            // Control Económico y de Cantidades
            'importe_pedido' => ['sometimes', 'required', 'numeric', 'min:0'],
            'importe_solicitado' => ['sometimes', 'required', 'numeric', 'min:0'],
            'importe_facturado' => ['sometimes', 'required', 'numeric', 'min:0'],
            'unidades_pedido' => ['sometimes', 'required', 'numeric', 'min:0'],
            'unidades_solicitadas' => ['sometimes', 'required', 'numeric', 'min:0'],

            // Flags y Estado
            'estado' => ['sometimes', 'required', 'string', 'max:50'],
            'pedido_completo' => ['sometimes', 'boolean'],
            'tiene_mas_de_1_item' => ['sometimes', 'boolean'],
            'facturado_completo' => ['sometimes', 'boolean'],
            'observaciones' => ['nullable', 'string'],

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

    /**
     * Helper para limpiar cadenas vacías
     */
    protected function normalizeNullableString(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}