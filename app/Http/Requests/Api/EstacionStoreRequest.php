<?php

namespace App\Http\Requests\Api;

class EstacionStoreRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true; // La seguridad real la da el HasContext y Middlewares RBAC
    }

    public function rules(): array
    {
        return [
            'nombre'                    => ['required', 'string', 'max:255'],
            'id_empresa_cliente'        => ['required', 'integer', 'exists:empresas,id'], // Ajustar nombre tabla según tu BBDD
            'codigo_estacion_interno'   => ['nullable', 'string', 'max:50'],
            'cod_repsol'                => ['nullable', 'string', 'max:50'],
            'cod_cepsa'                 => ['nullable', 'string', 'max:50'],
            'tipo'                      => ['nullable', 'string', 'max:100'],
            'direccion'                 => ['nullable', 'string'],
            'poblacion'                 => ['nullable', 'string', 'max:100'],
            'provincia'                 => ['nullable', 'string', 'max:100'],
            'activo'                    => ['boolean'],
        ];
    }
}