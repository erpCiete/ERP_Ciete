<?php

namespace App\Http\Requests\Api;

use App\Rules\ValidSpanishPostalCode;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class EstacionStoreRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $contextId = Auth::user()?->id_contexto;

        return [
            'id_empresa_cliente' => [
                'required',
                'integer',
                Rule::exists('empresas', 'id_empresa')->where(
                    fn($query) => $query->where('id_contexto', $contextId)
                ),
            ],
            'nombre' => ['required', 'string', 'max:180'],
            'codigo_estacion' => [
                'required',
                'string',
                'max:80',
                Rule::unique('estaciones_servicio', 'codigo_estacion')->where(
                    fn($query) => $query->where('id_contexto', $contextId)
                ),
            ],
            'direccion' => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'size:5', new ValidSpanishPostalCode()],
            'poblacion' => ['nullable', 'string', 'max:120'],
            'provincia' => ['nullable', 'string', 'max:120'],
            'pais' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id_empresa_cliente' => $this->input('id_empresa_cliente'),
            'nombre' => $this->normalizeNullableString($this->input('nombre')),
            'codigo_estacion' => $this->normalizeNullableString($this->input('codigo_estacion')),
            'direccion' => $this->normalizeNullableString($this->input('direccion')),
            'codigo_postal' => ValidSpanishPostalCode::normalize($this->input('codigo_postal')),
            'poblacion' => $this->normalizeNullableString($this->input('poblacion')),
            'provincia' => $this->normalizeNullableString($this->input('provincia')),
            'pais' => $this->input('pais', 'Espana'),
            'observaciones' => $this->normalizeNullableString($this->input('observaciones')),
            'activo' => $this->has('activo') ? $this->boolean('activo') : true,
        ]);
    }
}
