<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreImportacionRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta petición.
     */
    public function authorize(): bool
    {
        return $this->user()->can('importaciones.gestionar');
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            // Debe ser un archivo válido, tipo excel/csv, máximo 10MB (10240 KB)
            'archivo' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240', 
            ],
        ];
    }

    /**
     * Mensajes personalizados para el frontend.
     */
    public function messages(): array
    {
        return [
            'archivo.required' => 'Debe seleccionar un archivo para importar.',
            'archivo.mimes'    => 'El archivo debe ser un Excel válido (.xlsx, .xls) o CSV.',
            'archivo.max'      => 'El archivo es demasiado grande. El máximo permitido son 10MB.',
        ];
    }
}