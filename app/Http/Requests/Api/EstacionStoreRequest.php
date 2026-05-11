<?php

namespace App\Http\Requests\Api;

use App\Models\Empresa;
use App\Rules\ValidSpanishPostalCode;
use App\Support\ContextGuard;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class EstacionStoreRequest extends BaseApiRequest
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
        $user = Auth::user();
        $activeContextIds = $user?->getActiveContextIds() ?? [];

        return [
            'id_empresa_cliente' => [
                'required',
                'integer',
                Rule::exists('empresas', 'id_empresa')->where(
                    fn($query) => $query->whereIn('id_contexto', $activeContextIds)
                ),
            ],
            'nombre' => ['required', 'string', 'max:180'],
            'codigo_estacion' => [
                'required',
                'string',
                'max:80',
                Rule::unique('estaciones_servicio', 'codigo_estacion')->where(function ($query) use ($activeContextIds) {
                    return $query
                        ->whereIn('id_contexto', $activeContextIds)
                        ->where('id_empresa_cliente', $this->input('id_empresa_cliente') ?: 0)
                        ->where('id_contexto', $this->resolveClienteContextId());
                }),
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

    private function resolveClienteContextId(): int
    {
        $clienteId = (int) $this->input('id_empresa_cliente', 0);

        if ($clienteId <= 0) {
            return 0;
        }

        return (int) (Empresa::withoutGlobalScopes()
            ->where('id_empresa', $clienteId)
            ->value('id_contexto') ?? 0);
    }
}
