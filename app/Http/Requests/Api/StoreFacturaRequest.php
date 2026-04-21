<?php

namespace App\Http\Requests\Api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreFacturaRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $contextId = Auth::user()?->id_contexto;

        $rules = [
            'id_trabajo' => [
                'required',
                'integer',
                Rule::exists('trabajos', 'id_trabajo')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'id_empresa_cliente' => [
                'required',
                'integer',
                Rule::exists('empresas', 'id_empresa')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'numero_factura' => [
                'required',
                'string',
                'max:100',
                Rule::unique('facturas', 'numero_factura')
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
            
            // Validación de pedidos pivot
            'pedidos' => ['sometimes', 'array'],
            'pedidos.*.id_pedido' => ['required_with:pedidos', 'integer', Rule::exists('pedidos', 'id_pedido')],
            'pedidos.*.importe_aplicado' => ['nullable', 'numeric', 'min:0'],
        ];

        // Lógica condicional: Contexto 2 (REPSOL) -> Unique id_trabajo + orden_factura
        if ($contextId === 2) {
            $rules['orden_factura'] = [
                'required',
                'integer',
                'min:1',
                Rule::unique('facturas', 'orden_factura')
                    ->where('id_trabajo', $this->input('id_trabajo'))
                    ->where('id_contexto', $contextId)
            ];
            $rules['numero_factura_ccp'] = ['nullable', 'string', 'max:100'];
        } 
        // Lógica condicional: Contexto 1 (MOEVE) -> Unique numero_factura_ccp
        elseif ($contextId === 1) {
            $rules['orden_factura'] = ['nullable', 'integer', 'min:1'];
            $rules['numero_factura_ccp'] = [
                'required',
                'string',
                'max:100',
                Rule::unique('facturas', 'numero_factura_ccp')
                    ->where('id_contexto', $contextId)
            ];
        } else {
            // Reglas por defecto para otros contextos
            $rules['orden_factura'] = ['nullable', 'integer'];
            $rules['numero_factura_ccp'] = ['nullable', 'string'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'numero_factura' => $this->normalizeNullableString($this->input('numero_factura')),
            'numero_factura_ccp' => $this->normalizeNullableString($this->input('numero_factura_ccp')),
            'serie' => $this->normalizeNullableString($this->input('serie')),
            'observaciones' => $this->normalizeNullableString($this->input('observaciones')),
            'estado' => $this->input('estado', 'pendiente'),
            'autofactura' => $this->boolean('autofactura'),
        ]);
    }
}