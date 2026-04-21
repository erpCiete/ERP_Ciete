<?php

namespace App\Http\Requests\Api;

use App\Models\Factura;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateFacturaRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $contextId = Auth::user()?->id_contexto;
        $factura = $this->route('factura');
        $facturaId = $factura instanceof Factura ? $factura->id_factura : $factura;
        $trabajoId = $this->input('id_trabajo', $factura instanceof Factura ? $factura->id_trabajo : null);

        $rules = [
            'id_trabajo' => [
                'sometimes', 'required', 'integer',
                Rule::exists('trabajos', 'id_trabajo')->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'id_empresa_cliente' => [
                'sometimes', 'required', 'integer',
                Rule::exists('empresas', 'id_empresa')->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'numero_factura' => [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('facturas', 'numero_factura')
                    ->ignore($facturaId, 'id_factura')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'serie' => ['nullable', 'string', 'max:50'],
            'fecha_solicitud' => ['nullable', 'date'],
            'fecha_emision' => ['nullable', 'date'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'importe' => ['nullable', 'numeric', 'min:0'],
            'base_imponible' => ['nullable', 'numeric', 'min:0'],
            'iva' => ['nullable', 'numeric', 'min:0'],
            'retencion' => ['nullable', 'numeric', 'min:0'],
            'total' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['sometimes', Rule::in(['pendiente', 'emitida', 'cobrada', 'anulada'])],
            'autofactura' => ['boolean'],
            'sociedad' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string'],
            
            'pedidos' => ['sometimes', 'array'],
            'pedidos.*.id_pedido' => ['required_with:pedidos', 'integer', Rule::exists('pedidos', 'id_pedido')],
            'pedidos.*.importe_aplicado' => ['nullable', 'numeric', 'min:0'],
        ];

        // Lógica condicional: REPSOL
        if ($contextId === 2) {
            $rules['orden_factura'] = [
                'sometimes', 'required', 'integer', 'min:1',
                Rule::unique('facturas', 'orden_factura')
                    ->ignore($facturaId, 'id_factura')
                    ->where('id_trabajo', $trabajoId)
                    ->where('id_contexto', $contextId)
            ];
            $rules['numero_factura_ccp'] = ['nullable', 'string', 'max:100'];
        } 
        // Lógica condicional: MOEVE
        elseif ($contextId === 1) {
            $rules['orden_factura'] = ['nullable', 'integer', 'min:1'];
            $rules['numero_factura_ccp'] = [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('facturas', 'numero_factura_ccp')
                    ->ignore($facturaId, 'id_factura')
                    ->where('id_contexto', $contextId)
            ];
        } else {
            $rules['orden_factura'] = ['nullable', 'integer'];
            $rules['numero_factura_ccp'] = ['nullable', 'string'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];
        $simpleNullableFields = ['numero_factura', 'numero_factura_ccp', 'serie', 'observaciones'];

        foreach ($simpleNullableFields as $field) {
            if ($this->has($field)) {
                $normalized[$field] = $this->normalizeNullableString($this->input($field));
            }
        }

        if ($this->has('autofactura')) {
            $normalized['autofactura'] = $this->boolean('autofactura');
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }
}