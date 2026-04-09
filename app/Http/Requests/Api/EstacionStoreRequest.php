<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EstacionStoreRequest extends FormRequest
{
    public function authorize(): bool {
    return true;
}

public function rules(): bool {
    return [
        'nombre' => 'required|string|max:255',
        'ubicacion' => 'required|string',
        'codigo' => 'required|unique:estaciones,codigo',
    ];
}
}