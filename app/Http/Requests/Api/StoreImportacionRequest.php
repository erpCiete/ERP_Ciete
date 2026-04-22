<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class StoreImportacionRequest extends BaseApiRequest
{
    /**
     * Reglas de validación para la subida del Excel.
     */
    public function rules(): array
    {
        return [
            // Validamos que el archivo sea un Excel/CSV y no pase de 10MB
            'archivo' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240'
            ],
            // Validamos el tipo de importación basándonos en tu ENUM de BD
            'tipo' => [
                'required',
                'string',
                Rule::in(['estaciones', 'trabajos', 'tarifario', 'facturas'])
            ]
        ];
    }

    /**
     * Mensajes personalizados para el Frontend
     */
    public function messages(): array
    {
        return [
            'archivo.required' => 'Debe adjuntar un archivo para importar.',
            'archivo.mimes'    => 'El archivo debe ser un Excel válido (.xlsx, .xls) o un CSV.',
            'archivo.max'      => 'El archivo no puede pesar más de 10 MB.',
            'tipo.required'    => 'Debe especificar el tipo de importación.',
            'tipo.in'          => 'El tipo de importación no es válido.'
        ];
    }
}