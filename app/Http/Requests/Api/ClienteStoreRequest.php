<?php

namespace App\Http\Requests\Api;

use App\Rules\ValidSpanishTaxId;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ClienteStoreRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $contextId = Auth::user()?->id_contexto;

        return [
            'nombre' => [
                'required',
                'string',
                'max:180',
                Rule::unique('empresas', 'nombre')->where(
                    fn($query) => $query->where('id_contexto', $contextId)
                ),
            ],
            'nombre_comercial' => ['nullable', 'string', 'max:180'],
            'razon_social' => ['nullable', 'string', 'max:220'],
            'cif' => [
                'nullable',
                'string',
                'size:9',
                new ValidSpanishTaxId(),
                Rule::unique('empresas', 'cif')->where(
                    fn($query) => $query->where('id_contexto', $contextId)
                ),
            ],
            'tipo_empresa' => ['sometimes', Rule::in(['cliente', 'proveedor', 'cliente_proveedor', 'interna', 'otra'])],
            'web' => ['nullable', 'url', 'max:255'],
            'observaciones' => ['nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre' => $this->normalizeNullableString($this->input('nombre')),
            'nombre_comercial' => $this->normalizeNullableString($this->input('nombre_comercial')),
            'razon_social' => $this->normalizeNullableString($this->input('razon_social')),
            'cif' => ValidSpanishTaxId::normalize($this->input('cif')),
            'web' => $this->normalizeNullableString($this->input('web')),
            'observaciones' => $this->normalizeNullableString($this->input('observaciones')),
            'tipo_empresa' => $this->input('tipo_empresa', 'cliente'),
            'activo' => $this->has('activo') ? $this->boolean('activo') : true,
        ]);
    }
}
