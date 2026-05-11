<?php

namespace App\Http\Requests\Api;

use App\Models\EstacionServicio;
use App\Models\Trabajo;
use App\Support\ContextGuard;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTrabajoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ContextGuard::canCreateInActiveContext($this->user());
    }

    protected function failedAuthorization(): void
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => ContextGuard::CREATE_FROM_ALL_MESSAGE,
                'error_code' => 'AUTHORIZATION_ERROR',
                'errors' => [],
            ], 403));
        }

        throw new AuthorizationException(ContextGuard::CREATE_FROM_ALL_MESSAGE);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('fecha_terminado') && ! $this->has('fecha_terminacion')) {
            $this->merge([
                'fecha_terminacion' => $this->input('fecha_terminado'),
            ]);
        }

        if (! $this->filled('estado')) {
            $this->merge(['estado' => 'en_curso']);
        }
    }

    public function rules(): array
    {
        return [
            'id_contexto'           => ['nullable', 'integer'],
            'numero_trabajo'       => ['required', 'integer'],
            'numero_trabajo_operativo' => ['nullable', 'string', 'max:100'],
            'descripcion_trabajo'  => ['required', 'string', 'max:150'], // Sincronizado con React
            'id_estacion_servicio' => ['required', 'exists:estaciones_servicio,id_estacion_servicio'],
            'fecha_encargo'        => ['required', 'date'],
            'fecha_terminacion'    => ['nullable', 'date'],
            'estado'               => ['required', Rule::in(Trabajo::ESTADOS_FUNCIONALES)],
            'observaciones'        => ['nullable', 'string'],
            'id_responsable_ciete' => [
                'nullable',
                'integer',
                Rule::exists('usuarios', 'id_usuario')->where(fn ($query) => $query->where('activo', true)),
            ],

            // Validacion contextual: OTROS CLIENTES no hereda obligatorios especificos de MOEVE/REPSOL.
            'id_contrato' => [
                Rule::requiredIf(fn () => $this->esContexto('moeve')),
                'nullable', 'integer'
            ],
            'id_tipo_documento' => [
                Rule::requiredIf(fn () => $this->esContexto('repsol')),
                'nullable', 'integer'
            ],
            'id_tipo_trabajo' => [
                Rule::requiredIf(fn () => $this->esContexto('repsol')),
                'nullable', 'integer'
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $user = $this->user();
            $selectedContextId = (int) ($this->input('id_contexto') ?: 0);
            $accessibleContextIds = $user?->getActiveContextIds() ?? [];

            if ($selectedContextId > 0 && ! in_array($selectedContextId, $accessibleContextIds, true)) {
                $validator->errors()->add('id_contexto', 'El contexto seleccionado no está disponible para tu usuario.');
            }

            if (! $this->id_estacion_servicio) {
                return;
            }

            $estacion = EstacionServicio::withoutGlobalScopes()->find($this->id_estacion_servicio);

            if (! $estacion) {
                return;
            }

            if (! in_array((int) $estacion->id_contexto, $accessibleContextIds, true)) {
                $validator->errors()->add('id_estacion_servicio', 'La estación seleccionada no está disponible para tu usuario.');
            }

            if ($selectedContextId > 0 && (int) $estacion->id_contexto !== $selectedContextId) {
                $validator->errors()->add('id_estacion_servicio', 'La estación no pertenece al cliente seleccionado.');
            }
        });
    }

    private function esContexto(string $workspaceKey): bool
    {
        if (!$this->id_estacion_servicio) return false;
        $estacion = EstacionServicio::withoutGlobalScopes()->find($this->id_estacion_servicio);
        return $estacion && ContextGuard::workspaceKeyForContextId((int) $estacion->id_contexto) === $workspaceKey;
    }
}
