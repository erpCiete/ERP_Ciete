<?php

namespace App\Http\Requests\Api;

use App\Models\Pedido;
use App\Support\ContextGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StorePedidoRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return ContextGuard::canCreateInActiveContext($this->user());
    }

    protected function authorizationFailureMessage(): string
    {
        return ContextGuard::CREATE_FROM_ALL_MESSAGE;
    }

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

    /**
     * Prepara los datos para validación, casteando booleanos
     * y recalculando los totales de las líneas si es necesario.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'estado' => $this->input('estado', 'pendiente'),
            'importe_pedido' => $this->input('importe_pedido', 0),
            'importe_solicitado' => $this->input('importe_solicitado', 0),
            'importe_facturado' => $this->input('importe_facturado', 0),
            'unidades_pedido' => $this->input('unidades_pedido', 0),
            'unidades_solicitadas' => $this->input('unidades_solicitadas', 0),
            'pedido_completo' => filter_var($this->input('pedido_completo', false), FILTER_VALIDATE_BOOLEAN),
            'tiene_mas_de_1_item' => filter_var($this->input('tiene_mas_de_1_item', false), FILTER_VALIDATE_BOOLEAN),
            'facturado_completo' => filter_var($this->input('facturado_completo', false), FILTER_VALIDATE_BOOLEAN),
        ]);

        if ($this->has('observaciones')) {
            $this->merge([
                'observaciones' => $this->normalizeNullableString($this->input('observaciones'))
            ]);
        }

        // Limpieza y estructuración de los items anidados (Esquema Real de abaco_ciete)
        if ($this->has('items') && is_array($this->input('items'))) {
            $items = collect($this->input('items'))->map(function ($item) {
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

            $this->merge(['items' => $items]);
        }
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        $user = Auth::user();
        $accessibleContextIds = $user?->getActiveContextIds() ?? [];
        $trabajo = $this->input('id_trabajo')
            ? \App\Models\Trabajo::query()->withoutGlobalScopes()->find($this->input('id_trabajo'))
            : null;
        $targetContextId = $trabajo?->id_contexto;
        $pedido = $this->route('pedido');
        $pedidoId = $pedido instanceof Pedido ? $pedido->id_pedido : $pedido;

        return [
            // Relaciones
            'id_trabajo' => [
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

            // Datos Base
            'numero_pedido' => [
                'required',
                'string',
                'max:100',
                Rule::unique('pedidos', 'numero_pedido')
                    ->ignore($pedidoId, 'id_pedido')
                    ->when($targetContextId !== null, fn ($query) => $query->where('id_contexto', $targetContextId)),
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
            'estado' => ['sometimes', 'required', Rule::in(self::ALLOWED_STATUSES)],
            'pedido_completo' => ['sometimes', 'boolean'],
            'tiene_mas_de_1_item' => ['sometimes', 'boolean'],
            'facturado_completo' => ['sometimes', 'boolean'],
            'observaciones' => ['nullable', 'string'],

            // Validaciones para Líneas de Pedido (Items)
            'items' => ['nullable', 'array'],
            'items.*.id_pedido_item' => ['nullable', 'integer', 'min:1'],
            'items.*.id_tarifario_linea' => ['nullable', 'integer'],
            'items.*.codigo_servicio' => ['nullable', 'string', 'max:30'],
            'items.*.numero_tarifa' => ['nullable', 'string', 'max:30'],
            'items.*.descripcion_servicio' => ['nullable', 'string', 'max:255'],
            'items.*.cantidad' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.precio_unitario' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.total_linea' => ['required_with:items', 'numeric'],
        ];
    }
}
