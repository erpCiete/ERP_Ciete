<?php

namespace App\Http\Requests\Api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreFacturaRequest extends BaseApiRequest
{
    /**
     * Prepara los datos para validación, seteando valores por defecto,
     * deduciendo relaciones implícitas y casteando valores.
     */
    protected function prepareForValidation(): void
    {
        // 0. TRADUCTOR FRONTEND -> BACKEND
        // Si React nos manda 'factura_ccp', lo renombramos a 'numero_factura_ccp'
        if ($this->has('factura_ccp')) {
            $this->merge(['numero_factura_ccp' => $this->input('factura_ccp')]);
        }

        // 1. Deducir la Empresa Cliente a partir del Trabajo
        $idEmpresaCliente = $this->input('id_empresa_cliente');
        if (!$idEmpresaCliente && $this->input('id_trabajo')) {
            $trabajo = \App\Models\Trabajo::find($this->input('id_trabajo'));
            if ($trabajo) {
                $idEmpresaCliente = $trabajo->id_empresa_cliente;
            }
        }

        // 2. Saneamiento de valores por defecto
        // 'importe' es obligatorio. Lo igualamos a la base_imponible si no viene.
        $baseImponible = $this->input('base_imponible') ?? 0;

        $this->merge([
            'id_empresa_cliente' => $idEmpresaCliente,
            'estado' => $this->input('estado') ?: 'pendiente',
            'autofactura' => filter_var($this->input('autofactura', false), FILTER_VALIDATE_BOOLEAN),
            'importe' => $this->input('importe') ?? $baseImponible,
            'base_imponible' => $baseImponible,
            'iva' => $this->input('iva') ?? 0,
            'retencion' => $this->input('retencion') ?? 0,
            'total' => $this->input('total') ?? 0,
        ]);

        // 3. Limpieza dinámica de cadenas de texto
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

        // 4. Limpieza array pivote
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
        $user = Auth::user();
        $userContextId = $user?->id_contexto;
        $isAdmin = $userContextId === 3 || $user?->hasRole('admin');

        // 1. Determinar el contexto real del registro (heredado del Trabajo, crucial para el Admin)
        $trabajoId = $this->input('id_trabajo');
        $trabajo = \App\Models\Trabajo::find($trabajoId);
        $targetContextId = $trabajo ? $trabajo->id_contexto : $userContextId;

        $rules = [
            // Relaciones (Aisladas por contexto solo si NO es admin)
            'id_trabajo' => [
                'required',
                'integer',
                Rule::exists('trabajos', 'id_trabajo')
                    ->when(!$isAdmin, fn ($query) => $query->where('id_contexto', $userContextId)),
            ],
            'id_empresa_cliente' => [
                'required',
                'integer',
                Rule::exists('empresas', 'id_empresa')
                    ->when(!$isAdmin, fn ($query) => $query->where('id_contexto', $userContextId)),
            ],

            // Datos Base Generales
            'numero_factura' => [
                'required',
                'string',
                'max:100',
                Rule::unique('facturas', 'numero_factura')
                    ->when(!$isAdmin, fn ($query) => $query->where('id_contexto', $userContextId)),
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
                    ->when(!$isAdmin, fn ($query) => $query->where('id_contexto', $userContextId))
            ],
            'pedidos.*.importe_aplicado' => ['required_with:pedidos', 'numeric', 'min:0'],
        ];

        // ── REGLA DE NEGOCIO CRÍTICA: CONDICIONALES POR CLIENTE ──
        // Utilizamos el $targetContextId del Trabajo, así el Admin evalúa correctamente
        if ($targetContextId === 1) {
            // MOEVE: Obliga a registrar un 'numero_factura_ccp' y que no exista previamente.
            $rules['numero_factura_ccp'] = [
                'required',
                'string',
                'max:100',
                Rule::unique('facturas', 'numero_factura_ccp')
                    ->where(fn ($query) => $query->where('id_contexto', $targetContextId)),
            ];
            $rules['orden_factura'] = ['nullable', 'integer', 'min:1'];
            
        } elseif ($targetContextId === 2) {
            // REPSOL: Obliga a definir el 'orden_factura' (1 o 2) garantizando que no se repita en el MISMO trabajo.
            $rules['orden_factura'] = [
                'required',
                'integer',
                'min:1',
                Rule::unique('facturas', 'orden_factura')
                    ->where(fn ($query) => $query->where('id_trabajo', $trabajoId)
                                                 ->where('id_contexto', $targetContextId)),
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