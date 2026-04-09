<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class EstacionStoreRequest extends FormRequest
{
    // ESTO DEBE DEVOLVER UN BOOLEANO (true)
    public function authorize(): bool
    {
        return true; 
    }

    // ESTO DEBE DEVOLVER EL ARRAY DE REGLAS
    public function rules(): array
    {
        return [
            'nombre'     => 'required|string|max:255',
            'codigo'     => 'required|string|unique:estaciones,codigo',
            'cliente_id' => 'required|exists:clientes,id', // O la regla que tengas
            'direccion'  => 'nullable|string',
        ];
    }
}