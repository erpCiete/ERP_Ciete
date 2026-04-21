<?php

namespace App\Http\Requests\Api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreFacturaRequest extends BaseApiRequest
{
    /**
     * Prepara los datos para validación, seteando valores por defecto,
     * casteando booleanos y asegurando el formato de los arrays.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'estado' => $this->input('estado', 'pendiente'),
            'autofactura' => filter_var($this->input('autofactura', false), FILTER_VALIDATE_BOOLEAN),
            'importe' => $this->input('importe', 0),
            'base_imponible' => $this->input('base_imponible', 0),
            'iva' => $this->input('iva', 0),
            'retencion' => $this->input('retencion', 0),
            'total' => $this->input('total', 0),
        ]);

        // Limpieza dinámica de cadenas de texto
        $stringFields = ['numero_factura', 'numero_factura_ccp', 'serie', 'sociedad', 'observaciones'];
        $normalized = [];
        
        foreach ($stringFields as $field) {
            if ($this->has($field)) {
                $normalized[$field] = $this->normalizeNullableString($this->input($field));
            }
        }

        if (!empty($normalized)) {
            $this->merge($normalized);
        }

        // Limpieza y validación inicial del array de la tabla pivote (factura_pedidos)
        if ($this->has('pedidos') && is_array($this->input('pedidos'))) {
            $pedidos = collect($this->input('pedidos'))->map(function ($pedido) {
                return [
                    'id_pedido' => $pedido['id_pedido'] ?? null,
                    'importe_aplicado' => isset($pedido['importe_aplicado']) ? (float) $pedido['importe_aplicado'] : 0,
                ];
            })->values()->all();

            $this->merge(['pedidos' => $pedidos]);
        }
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        $contextId = Auth::user()?->id_contexto;

        $rules = [
            // Relaciones (Siempre aisladas por contexto)
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

            // Datos Base Generales
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

            // Importes
            'importe' => ['required', 'numeric', 'min:0'],
            'base_imponible' => ['required', 'numeric', 'min:0'],
            'iva' => ['required', 'numeric', 'min:0'],
            'retencion' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],

            // Estados y Flags
            'estado' => ['required', 'string', 'max:50'],
            'autofactura' => ['required', 'boolean'],
            'sociedad' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string'],

            // Array pivot para pedidos
            'pedidos' => ['nullable', 'array'],
            'pedidos.*.id_pedido' => [
                'required_with:pedidos', 
                'integer', 
                Rule::exists('pedidos', 'id_pedido')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId))
            ],
            'pedidos.*.importe_aplicado' => ['required_with:pedidos', 'numeric', 'min:0'],
        ];

        // ── REGLA DE NEGOCIO CRÍTICA: CONDICIONALES POR CLIENTE ──
        
        if ($contextId === 1) {
            // MOEVE: Obliga a registrar un 'numero_factura_ccp' y que no exista previamente.
            $rules['numero_factura_ccp'] = [
                'required',
                'string',
                'max:100',
                Rule::unique('facturas', 'numero_factura_ccp')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ];
            $rules['orden_factura'] = ['nullable', 'integer', 'min:1'];
            
        } elseif ($contextId === 2) {
            // REPSOL: Obliga a definir el 'orden_factura' (1 o 2) garantizando que no se repita en el MISMO trabajo.
            $rules['orden_factura'] = [
                'required',
                'integer',
                'min:1',
                Rule::unique('facturas', 'orden_factura')
                    ->where(fn ($query) => $query->where('id_trabajo', $this->input('id_trabajo'))
                                                 ->where('id_contexto', $contextId)),
            ];
            $rules['numero_factura_ccp'] = ['nullable', 'string', 'max:100'];
            
        } else {
            // Otros contextos (fallback)
            $rules['orden_factura'] = ['nullable', 'integer', 'min:1'];
            $rules['numero_factura_ccp'] = ['nullable', 'string', 'max:100'];
        }

        return $rules;
    }
}