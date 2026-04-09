<?php

namespace App\Http\Requests\Api;

class EstacionUpdateRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'                    => ['sometimes', 'required', 'string', 'max:255'],
            'id_empresa_cliente'        => ['sometimes', 'required', 'integer', 'exists:empresas,id'],
            'codigo_estacion_interno'   => ['nullable', 'string', 'max:50'],
            'cod_repsol'                => ['nullable', 'string', 'max:50'],
            'cod_cepsa'                 => ['nullable', 'string', 'max:50'],
            'tipo'                      => ['nullable', 'string', 'max:100'],
            'direccion'                 => ['nullable', 'string'],
            'poblacion'                 => ['nullable', 'string', 'max:100'],
            'provincia'                 => ['nullable', 'string', 'max:100'],
            'activo'                    => ['boolean'],
            'f_baja'                    => ['nullable', 'date'],
        ];
    }
}