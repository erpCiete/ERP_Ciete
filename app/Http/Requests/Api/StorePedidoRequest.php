<?php

namespace App\Http\Requests\Api;

use App\Models\Pedido;
use App\Models\TarifarioLinea;
use App\Models\Trabajo;
use App\Support\ContextGuard;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

    public function rules(): array
    {
        $user = $this->currentUser();
        $accessibleContextIds = $user?->getActiveContextIds() ?? [];
        $trabajo = $this->input('id_trabajo')
            ? \App\Models\Trabajo::query()->withoutGlobalScopes()->find($this->input('id_trabajo'))
            : null;
        $targetContextId = $trabajo?->id_contexto;
        $pedido = $this->route('pedido');
        $pedidoId = $pedido instanceof Pedido ? $pedido->id_pedido : $pedido;

        return [
            'id_trabajo' => [
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
                'required',
                'string',
                'max:100',
                Rule::unique('pedidos', 'numero_pedido')
                    ->ignore($pedidoId, 'id_pedido')
                    ->when($targetContextId !== null, fn($query) => $query->where('id_contexto', $targetContextId)),
            ],
            'fecha_solicitud' => ['nullable', 'date'],
            'fecha_recepcion' => ['nullable', 'date'],

            'importe_pedido' => ['sometimes', 'required', 'numeric', 'min:0'],
            'importe_solicitado' => ['sometimes', 'required', 'numeric', 'min:0'],
            'importe_facturado' => ['sometimes', 'required', 'numeric', 'min:0'],
            'unidades_pedido' => ['sometimes', 'required', 'numeric', 'min:0'],
            'unidades_solicitadas' => ['sometimes', 'required', 'numeric', 'min:0'],

            'estado' => ['sometimes', 'required', Rule::in(self::ALLOWED_STATUSES)],
            'pedido_completo' => ['sometimes', 'boolean'],
            'tiene_mas_de_1_item' => ['sometimes', 'boolean'],
            'facturado_completo' => ['sometimes', 'boolean'],
            'observaciones' => ['nullable', 'string'],

            // Líneas opcionales: el Bloque 1/B permite crear la cabecera del pedido desde Trabajo.
            'items' => ['sometimes', 'array'],
            'items.*.id_pedido_item' => ['nullable', 'integer', 'min:1'],
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $trabajo = $this->input('id_trabajo')
                ? Trabajo::query()->withoutGlobalScopes()->find($this->input('id_trabajo'))
                : null;

            if (! $trabajo) {
                return;
            }

            if (! $trabajo->id_tarifario) {
                $validator->errors()->add('id_trabajo', 'El trabajo debe tener un tarifario valido antes de crear el pedido.');
            }

            if (
                $this->filled('id_tarifario')
                && $trabajo->id_tarifario
                && (int) $this->input('id_tarifario') !== (int) $trabajo->id_tarifario
            ) {
                $validator->errors()->add('id_tarifario', 'El pedido debe heredar el mismo tarifario del trabajo.');
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

            if ($lineasDisponibles && ! $hasMeaningfulItems) {
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
