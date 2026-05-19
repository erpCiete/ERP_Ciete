<?php

namespace App\Http\Requests\Api;

use App\Models\Factura;
use App\Models\PedidoItem;
use App\Models\Trabajo;
use App\Support\ContextGuard;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreFacturaRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return ContextGuard::canCreateInActiveContext($this->user());
    }

    protected function authorizationFailureMessage(): string
    {
        return ContextGuard::CREATE_FROM_ALL_MESSAGE;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        if ($this->has('factura_ccp')) {
            $normalized['numero_factura_ccp'] = $this->input('factura_ccp');
        }

        $items = $this->normalizeItemsInput();

        if ($items !== null) {
            $normalized['items'] = $items;
        }

        $trabajoId = $this->input('id_trabajo');
        $derivedTrabajoId = null;

        if ($items !== null && $items !== []) {
            $derivedTrabajoId = $this->inferTrabajoIdFromItems($items);

            if (($trabajoId === null || $trabajoId === '') && $derivedTrabajoId !== null) {
                $normalized['id_trabajo'] = $derivedTrabajoId;
            }
        }

        $idEmpresaCliente = $this->input('id_empresa_cliente');
        $headerTrabajoId = $derivedTrabajoId ?? $trabajoId;

        if (! $idEmpresaCliente && $headerTrabajoId) {
            $trabajo = Trabajo::query()->withoutGlobalScopes()->find($headerTrabajoId);

            if ($trabajo) {
                $idEmpresaCliente = $trabajo->id_empresa_cliente;
            }
        }

        $baseImponible = $this->input('base_imponible');
        $importe = $this->input('importe');
        $total = $this->input('total');

        if ($baseImponible === null || $baseImponible === '') {
            $baseImponible = $importe ?? $total ?? 0;
        }

        if ($importe === null || $importe === '') {
            $importe = $baseImponible;
        }

        if ($total === null || $total === '') {
            $total = $baseImponible;
        }

        $normalized = array_merge($normalized, [
            'id_empresa_cliente' => $idEmpresaCliente ?: null,
            'id_empresa_facturadora' => $this->has('id_empresa_facturadora')
                ? ($this->input('id_empresa_facturadora') ?: null)
                : $this->input('id_empresa_facturadora'),
            'estado' => $this->input('estado') ?: 'pendiente',
            'autofactura' => filter_var($this->input('autofactura', false), FILTER_VALIDATE_BOOLEAN),
            'importe' => $importe,
            'base_imponible' => $baseImponible,
            'iva' => $this->input('iva') ?? 0,
            'retencion' => $this->input('retencion') ?? 0,
            'total' => $total,
        ]);

        foreach (['numero_factura', 'numero_factura_ccp', 'serie', 'sociedad', 'observaciones'] as $field) {
            if ($this->has($field) || array_key_exists($field, $normalized)) {
                $normalized[$field] = $this->normalizeNullableString($normalized[$field] ?? $this->input($field));
            }
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        $user = $this->currentUser();
        $accessibleContextIds = $user?->getActiveContextIds() ?? [];
        $targetContextId = $this->resolveTargetContextId($accessibleContextIds);
        $items = $this->input('items', []);
        $trabajoId = is_array($items) && $items !== []
            ? ($this->inferTrabajoIdFromItems($items) ?? $this->input('id_trabajo'))
            : $this->input('id_trabajo');

        $rules = [
            'id_trabajo' => [
                'nullable',
                'required_without:items',
                'integer',
                Rule::exists('trabajos', 'id_trabajo')
                    ->where(fn($query) => $query->whereIn('id_contexto', $accessibleContextIds)),
            ],
            'id_empresa_cliente' => [
                'nullable',
                'integer',
                Rule::exists('empresas', 'id_empresa')
                    ->when($targetContextId !== null, fn($query) => $query->where('id_contexto', $targetContextId)),
            ],
            'id_contrato' => [
                'nullable',
                'integer',
                Rule::exists('contratos', 'id_contrato')
                    ->when($targetContextId !== null, fn($query) => $query->where('id_contexto', $targetContextId)),
            ],
            'id_empresa_facturadora' => [
                'nullable',
                'integer',
                Rule::exists('empresas', 'id_empresa')
                    ->when($targetContextId !== null, fn($query) => $query->where('id_contexto', $targetContextId)),
            ],
            'numero_factura' => [
                'nullable',
                'string',
                'max:100',
            ],
            'serie' => ['nullable', 'string', 'max:50'],
            'fecha_solicitud' => ['nullable', 'date'],
            'fecha_emision' => ['nullable', 'date'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'importe' => ['required', 'numeric', 'min:0'],
            'base_imponible' => ['required', 'numeric', 'min:0'],
            'iva' => ['required', 'numeric', 'min:0'],
            'retencion' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'estado' => ['required', Rule::in(Factura::ESTADOS_FUNCIONALES)],
            'autofactura' => ['required', 'boolean'],
            'sociedad' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_pedido_item' => [
                'required_with:items',
                'integer',
                'distinct',
                Rule::exists('pedido_items', 'id_pedido_item')
                    ->where(fn($query) => $query->whereIn('id_contexto', $accessibleContextIds)),
            ],
            'items.*.unidades_facturadas' => ['nullable', 'numeric', 'min:0'],
            'items.*.importe_facturado' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.observaciones' => ['nullable', 'string'],
        ];

        if (ContextGuard::isMoeveContextId($targetContextId ? (int) $targetContextId : null)) {
            $rules['numero_factura_ccp'] = [
                'required',
                'string',
                'max:100',
                Rule::unique('facturas', 'numero_factura_ccp')
                    ->where(fn($query) => $query->where('id_contexto', $targetContextId)),
            ];
            $rules['orden_factura'] = ['nullable', 'integer', 'min:1'];
        } elseif (ContextGuard::isRepsolContextId($targetContextId ? (int) $targetContextId : null)) {
            $rules['orden_factura'] = [
                'required',
                'integer',
                'min:1',
                Rule::unique('facturas', 'orden_factura')
                    ->where(fn($query) => $query->where('id_trabajo', $trabajoId)
                        ->where('id_contexto', $targetContextId)),
            ];
            $rules['numero_factura_ccp'] = ['nullable', 'string', 'max:100'];
        } else {
            $rules['orden_factura'] = ['nullable', 'integer', 'min:1'];
            $rules['numero_factura_ccp'] = ['nullable', 'string', 'max:100'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $estado = (string) $this->input('estado', 'pendiente');
            $numeroFactura = $this->normalizeNullableString($this->input('numero_factura'));

            if (in_array($estado, ['emitida', 'enviada'], true) && $numeroFactura === null) {
                $validator->errors()->add('numero_factura', 'El número de factura es obligatorio para facturas emitidas o enviadas.');
            }

            if ($numeroFactura === null) {
                return;
            }

            $accessibleContextIds = $this->currentUser()?->getActiveContextIds() ?? [];
            $targetContextId = $this->resolveTargetContextId($accessibleContextIds);
            $empresaFacturadoraId = $this->input('id_empresa_facturadora');

            if ($targetContextId === null || ! $empresaFacturadoraId) {
                return;
            }

            $exists = Factura::query()
                ->withoutGlobalScopes()
                ->where('id_contexto', $targetContextId)
                ->where('id_empresa_facturadora', (int) $empresaFacturadoraId)
                ->where('numero_factura', $numeroFactura)
                ->exists();

            if ($exists) {
                $validator->errors()->add('numero_factura', 'Ya existe una factura con ese número para el contexto y sociedad facturadora seleccionados.');
            }
        });
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function normalizeItemsInput(): ?array
    {
        if (! $this->has('items') || ! is_array($this->input('items'))) {
            return null;
        }

        return collect($this->input('items'))->map(function ($item) {
            return [
                'id_pedido_item' => $item['id_pedido_item'] ?? null,
                'unidades_facturadas' => isset($item['unidades_facturadas']) && $item['unidades_facturadas'] !== ''
                    ? (float) $item['unidades_facturadas']
                    : null,
                'importe_facturado' => isset($item['importe_facturado']) && $item['importe_facturado'] !== ''
                    ? (float) $item['importe_facturado']
                    : 0,
                'observaciones' => $this->normalizeNullableString($item['observaciones'] ?? null),
            ];
        })->values()->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function inferTrabajoIdFromItems(array $items): ?int
    {
        $firstItemId = collect($items)->pluck('id_pedido_item')->filter()->first();

        if (! $firstItemId) {
            return null;
        }

        $pedidoItem = PedidoItem::query()
            ->withoutGlobalScopes()
            ->with('pedido')
            ->find($firstItemId);

        return $pedidoItem?->pedido?->id_trabajo ? (int) $pedidoItem->pedido->id_trabajo : null;
    }

    private function resolveTargetContextId(array $accessibleContextIds): ?int
    {
        $firstItemId = collect($this->input('items', []))->pluck('id_pedido_item')->filter()->first();

        if ($firstItemId) {
            return PedidoItem::query()
                ->withoutGlobalScopes()
                ->whereIn('id_contexto', $accessibleContextIds)
                ->where('id_pedido_item', $firstItemId)
                ->value('id_contexto');
        }

        $trabajoId = $this->input('id_trabajo');

        if ($trabajoId) {
            return Trabajo::query()
                ->withoutGlobalScopes()
                ->whereIn('id_contexto', $accessibleContextIds)
                ->where('id_trabajo', $trabajoId)
                ->value('id_contexto');
        }

        return null;
    }
}
