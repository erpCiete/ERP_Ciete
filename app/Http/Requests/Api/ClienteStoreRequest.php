<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class ClienteStoreRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $idContexto = auth()->user()?->id_contexto;

        return [
            'nombre' => [
                'required',
                'string',
                'max:180',
                Rule::unique('empresas', 'nombre')
                    ->where(fn ($q) => $q->where('id_contexto', $idContexto)),
            ],
            'nombre_comercial' => ['nullable', 'string', 'max:180'],
            'razon_social' => ['nullable', 'string', 'max:220'],
            'cif' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('empresas', 'cif')
                    ->where(fn ($q) => $q->where('id_contexto', $idContexto)),
            ],
            'tipo_empresa' => [
                'sometimes',
                Rule::in(['cliente', 'proveedor', 'cliente_proveedor', 'interna', 'otra']),
            ],
            'web' => ['nullable', 'url', 'max:255'],
            'observaciones' => ['nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tipo_empresa' => $this->input('tipo_empresa', 'cliente'),
            'activo' => $this->has('activo') ? $this->boolean('activo') : true,
        ]);
    }
}