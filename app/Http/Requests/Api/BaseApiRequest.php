<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponse;

abstract class BaseApiRequest extends FormRequest
{
    use ApiResponse;

    /**
     * Determina si el usuario está autorizado para hacer esta petición.
     * Por defecto true. La seguridad fuerte la manejaremos vía Middlewares/Policies (RBAC).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Intercepta el fallo de validación nativo de Laravel y lanza 
     * una excepción HTTP formateada con nuestro contrato exacto.
     */
    protected function failedValidation(Validator $validator)
    {
        $response = $this->errorResponse(
            message: 'Los datos proporcionados no son válidos.',
            errorCode: 'VALIDATION_ERROR',
            errors: $validator->errors()->toArray(),
            code: 422
        );

        throw new HttpResponseException($response);
    }
}