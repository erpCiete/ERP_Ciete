<?php

namespace App\Http\Requests\Api;

use App\Support\ContextGuard;
use Illuminate\Validation\Rule;

// Este request lo usa el API controller. El web controller valida directamente
// con $request->validate() para devolver errores Inertia en lugar de JSON.
class StoreImportacionRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return ContextGuard::canCreateInActiveContext($this->user());
    }

    protected function authorizationFailureMessage(): string
    {
        return ContextGuard::CREATE_FROM_ALL_MESSAGE;
    }

    public function rules(): array
    {
        return [
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'tipo' => ['required', 'string', Rule::in(['estaciones', 'trabajos', 'tarifario', 'facturas'])],
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.required' => 'Debe adjuntar un archivo para importar.',
            'archivo.mimes'    => 'El archivo debe ser un Excel válido (.xlsx, .xls) o un CSV.',
            'archivo.max'      => 'El archivo no puede pesar más de 10 MB.',
            'tipo.required'    => 'Debe especificar el tipo de importación.',
            'tipo.in'          => 'El tipo de importación no es válido.',
        ];
    }
}
