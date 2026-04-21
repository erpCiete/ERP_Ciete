<?php

namespace App\Http\Requests\Api;

use App\Models\Factura;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateFacturaRequest extends BaseApiRequest
{
    /**
     * Prepara los datos para validación, sanitizando cadenas y arrays,
     * pero respetando la naturaleza parcial de las peticiones PATCH.
     */
    protected function prepareForValidation(): void
    {
        $normalized = [];

        // Casteo estricto de booleanos solo si vienen explícitamente en el request
        if ($this->has('autofactura')) {
            $normalized['autofactura'] = filter_var($this->input('autofactura'), FILTER_VALIDATE_BOOLEAN);
        }

        // Limpieza dinámica de cadenas de texto
        $stringFields = ['numero_factura', 'numero_factura_ccp', 'serie', 'sociedad', 'observaciones'];
        foreach ($stringFields as $field) {
            if ($this->has($field)) {
                $normalized[$field] = $this->normalizeNullableString($this->input($field));
            }
        }

        if (!empty($normalized)) {
            $this->merge($normalized);
        }

        // Limpieza y validación del array de la tabla pivote (factura_pedidos)
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
        
        // Obtener el ID de la factura que se está editando
        $factura = $this->route('factura');
        $facturaId = $factura instanceof Factura ? $factura->id_factura : $factura;

        // CRÍTICO: En un PATCH, el id_trabajo puede no venir en el payload. 
        // Si no viene, usamos el id_trabajo del modelo existente para las validaciones condicionales.
        $trabajoId = $this->input('id_trabajo', $factura instanceof Factura ? $factura->id_trabajo : null);

        $rules = [
            // Relaciones (Siempre aisladas por contexto)
            'id_trabajo' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('trabajos', 'id_trabajo')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'id_empresa_cliente' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('empresas', 'id_empresa')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],

            // Datos Base Generales
            'numero_factura' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('facturas', 'numero_factura')
                    ->ignore($facturaId, 'id_factura') // Ignora el registro actual
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ],
            'serie' => ['nullable', 'string', 'max:50'],
            'fecha_solicitud' => ['nullable', 'date'],
            'fecha_emision' => ['nullable', 'date'],
            'fecha_vencimiento' => ['nullable', 'date'],

            // Importes
            'importe' => ['sometimes', 'required', 'numeric', 'min:0'],
            'base_imponible' => ['sometimes', 'required', 'numeric', 'min:0'],
            'iva' => ['sometimes', 'required', 'numeric', 'min:0'],
            'retencion' => ['sometimes', 'required', 'numeric', 'min:0'],
            'total' => ['sometimes', 'required', 'numeric', 'min:0'],

            // Estados y Flags
            'estado' => ['sometimes', 'required', 'string', 'max:50'],
            'autofactura' => ['sometimes', 'boolean'],
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

        // ── REGLA DE NEGOCIO CRÍTICA: CONDICIONALES POR CLIENTE (Con Ignore) ──
        
        if ($contextId === 1) {
            // MOEVE
            $rules['numero_factura_ccp'] = [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('facturas', 'numero_factura_ccp')
                    ->ignore($facturaId, 'id_factura')
                    ->where(fn ($query) => $query->where('id_contexto', $contextId)),
            ];
            $rules['orden_factura'] = ['nullable', 'integer', 'min:1'];
            
        } elseif ($contextId === 2) {
            // REPSOL
            $rules['orden_factura'] = [
                'sometimes',
                'required',
                'integer',
                'min:1',
                Rule::unique('facturas', 'orden_factura')
                    ->ignore($facturaId, 'id_factura')
                    ->where(fn ($query) => $query->where('id_trabajo', $trabajoId) // Utiliza el ID recuperado inteligentemente
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