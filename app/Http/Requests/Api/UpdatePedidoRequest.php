<?php

namespace App\Http\Requests\Api;

use App\Models\Pedido;
use App\Models\TarifarioLinea;
use App\Models\Trabajo;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
        $user = $this->currentUser();
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
                    ->where(fn($query) => $query->whereIn('id_contexto', $accessibleContextIds)),
            ],
            'id_tarifario' => [
                'nullable',
                'integer',
                Rule::exists('tarifarios', 'id_tarifario')
                    ->when($targetContextId !== null, fn($query) => $query->where('id_contexto', $targetContextId)),
            ],
            'numero_pedido' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('pedidos', 'numero_pedido')
                    ->ignore($pedidoId, 'id_pedido')
                    ->when($targetContextId !== null, fn($query) => $query->where('id_contexto', $targetContextId)),
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
                    ->where(fn($query) => $query->where('id_pedido', $pedidoId)),
            ],
            'items.*.id_tarifario_linea' => [
                'nullable',
                'integer',
                Rule::exists('tarifario_lineas', 'id_tarifario_linea')
                    ->when($targetContextId !== null, fn($query) => $query->where('id_contexto', $targetContextId)),
            ],
            'items.*.codigo_servicio' => ['nullable', 'string', 'max:30'],
            'items.*.numero_tarifa' => ['nullable', 'string', 'max:30'],
            'items.*.descripcion_servicio' => ['nullable', 'string', 'max:255'],
            'items.*.cantidad' => ['required_with:items', 'numeric', 'gt:0'],
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $pedido = $this->route('pedido');
            $trabajoId = $this->input('id_trabajo') ?: ($pedido instanceof Pedido ? $pedido->id_trabajo : null);
            $trabajo = $trabajoId
                ? Trabajo::query()->withoutGlobalScopes()->find($trabajoId)
                : null;

            if (! $trabajo) {
                return;
            }

            if ($this->filled('id_trabajo') && ! $trabajo->id_tarifario) {
                $validator->errors()->add('id_trabajo', 'El trabajo debe tener un tarifario valido antes de asignarlo al pedido.');
            }

            if (
                $this->filled('id_tarifario')
                && $trabajo->id_tarifario
                && (int) $this->input('id_tarifario') !== (int) $trabajo->id_tarifario
            ) {
                $validator->errors()->add('id_tarifario', 'El pedido debe mantener el mismo tarifario del trabajo.');
            }

            if (! is_array($this->input('items'))) {
                return;
            }

            $lineasDisponibles = TarifarioLinea::query()
                ->withoutGlobalScopes()
                ->where('id_contexto', $trabajo->id_contexto)
                ->where('activo', true)
                ->whereHas('tarifario', function ($query) use ($trabajo): void {
                    $query->where('activo', true);

                    if ($trabajo->id_tarifario) {
                        $query->where('id_tarifario', $trabajo->id_tarifario);
                        return;
                    }

                    if ($trabajo->id_contrato) {
                        $query->where('id_contrato', $trabajo->id_contrato);
                    }
                })
                ->exists();

            $items = $this->input('items', []);
            $hasMeaningfulItems = collect($items)->contains(function ($item): bool {
                return ($item['id_tarifario_linea'] ?? null)
                    || trim((string) ($item['codigo_servicio'] ?? '')) !== ''
                    || trim((string) ($item['descripcion_servicio'] ?? '')) !== ''
                    || (float) ($item['cantidad'] ?? 0) > 0
                    || (float) ($item['precio_unitario'] ?? 0) > 0;
            });

            if ($lineasDisponibles && $this->has('items') && ! $hasMeaningfulItems) {
                $validator->errors()->add('items', 'Añade al menos una línea del tarifario del trabajo.');
            }

            foreach ($items as $index => $item) {
                $lineaId = $item['id_tarifario_linea'] ?? null;

                if (! $lineaId) {
                    if ($lineasDisponibles) {
                        $validator->errors()->add("items.{$index}.id_tarifario_linea", 'Selecciona una línea de tarifa del trabajo.');
                    }

                    continue;
                }

                $linea = TarifarioLinea::query()
                    ->with('tarifario:id_tarifario,id_contexto,id_contrato,activo')
                    ->withoutGlobalScopes()
                    ->find($lineaId);

                if (! $linea || (int) $linea->id_contexto !== (int) $trabajo->id_contexto) {
                    $validator->errors()->add("items.{$index}.id_tarifario_linea", 'La línea de tarifa no pertenece al contexto del trabajo.');
                    continue;
                }

                if ($trabajo->id_tarifario && (int) $linea->id_tarifario !== (int) $trabajo->id_tarifario) {
                    $validator->errors()->add("items.{$index}.id_tarifario_linea", 'La línea de tarifa no pertenece al tarifario del trabajo.');
                    continue;
                }

                if ($trabajo->id_contrato && (int) ($linea->tarifario?->id_contrato ?? 0) !== (int) $trabajo->id_contrato) {
                    $validator->errors()->add("items.{$index}.id_tarifario_linea", 'La línea de tarifa no pertenece al contrato del trabajo.');
                }

                $cantidad = (float) ($item['cantidad'] ?? 0);
                $precioUnitario = (float) ($item['precio_unitario'] ?? 0);
                $totalLinea = round((float) ($item['total_linea'] ?? 0), 2);
                $totalEsperado = round($cantidad * $precioUnitario, 2);

                if (abs($totalLinea - $totalEsperado) > 0.01) {
                    $validator->errors()->add("items.{$index}.total_linea", 'El importe de la línea no coincide con cantidad por precio unitario.');
                }
            }
        });
    }
}
