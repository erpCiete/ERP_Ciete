<?php

namespace App\Http\Requests\Api;

use App\Models\Empresa;
use App\Rules\ValidSpanishTaxId;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ClienteUpdateRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $contextId = Auth::user()?->id_contexto;
        $cliente = $this->route('cliente');
        $clienteId = $cliente instanceof Empresa ? $cliente->id_empresa : $cliente;

        return [
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:180',
                Rule::unique('empresas', 'nombre')
                    ->ignore($clienteId, 'id_empresa')
                    ->where(fn($query) => $query->where('id_contexto', $contextId)),
            ],
            'nombre_comercial' => ['nullable', 'string', 'max:180'],
            'razon_social' => ['nullable', 'string', 'max:220'],
            'cif' => [
                'nullable',
                'string',
                'size:9',
                new ValidSpanishTaxId(),
                Rule::unique('empresas', 'cif')
                    ->ignore($clienteId, 'id_empresa')
                    ->where(fn($query) => $query->where('id_contexto', $contextId)),
            ],
            'tipo_empresa' => ['sometimes', Rule::in(['cliente', 'proveedor', 'cliente_proveedor', 'interna', 'otra'])],
            'web' => ['nullable', 'url', 'max:255'],
            'observaciones' => ['nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        if ($this->has('nombre')) {
            $normalized['nombre'] = $this->normalizeNullableString($this->input('nombre'));
        }

        if ($this->has('nombre_comercial')) {
            $normalized['nombre_comercial'] = $this->normalizeNullableString($this->input('nombre_comercial'));
        }

        if ($this->has('razon_social')) {
            $normalized['razon_social'] = $this->normalizeNullableString($this->input('razon_social'));
        }

        if ($this->has('cif')) {
            $normalized['cif'] = ValidSpanishTaxId::normalize($this->input('cif'));
        }

        if ($this->has('web')) {
            $normalized['web'] = $this->normalizeNullableString($this->input('web'));
        }

        if ($this->has('observaciones')) {
            $normalized['observaciones'] = $this->normalizeNullableString($this->input('observaciones'));
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }

        if ($this->has('activo')) {
            $this->merge([
                'activo' => $this->boolean('activo'),
            ]);
        }
    }
}
