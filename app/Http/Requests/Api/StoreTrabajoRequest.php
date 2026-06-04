<?php

namespace App\Http\Requests\Api;

use App\Models\EstacionServicio;
use App\Models\Tarifario;
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

        if ($this->input('numero_trabajo') === '') {
            $this->merge(['numero_trabajo' => null]);
        }

        if (trim((string) $this->input('numero_trabajo_operativo', '')) === '') {
            $this->merge(['numero_trabajo_operativo' => null]);
        }

        if (! $this->filled('estado')) {
            $this->merge(['estado' => 'en_curso']);
        }
    }

    public function rules(): array
    {
        return [
            'id_contexto'           => ['nullable', 'integer'],
            'numero_trabajo'       => ['nullable', 'integer'],
            'numero_trabajo_operativo' => ['nullable', 'string', 'max:100'],
            'descripcion_trabajo'  => ['required', 'string', 'max:150'],
            'id_estacion_servicio' => ['required', 'exists:estaciones_servicio,id_estacion_servicio'],
            'fecha_encargo'        => ['required', 'date'],
            'fecha_terminacion'    => ['nullable', 'date'],
            'estado'               => ['required', Rule::in(Trabajo::ESTADOS_MANUALES)],
            'observaciones'        => ['nullable', 'string'],
            'id_responsable_ciete' => [
                'nullable',
                'integer',
                Rule::exists('usuarios', 'id_usuario')->where(fn ($query) => $query->where('activo', true)),
            ],
            'id_tarifario' => ['nullable', 'integer'],

            // MOEVE exige contrato salvo que ya venga el tarifario; REPSOL y OTROS no.
            'id_contrato' => [
                Rule::requiredIf(fn () => $this->esContexto('moeve') && ! $this->filled('id_tarifario')),
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

            if (! $this->filled('id_tarifario')) {
                return;
            }

            $tarifario = Tarifario::withoutGlobalScopes()
                ->with('contrato:id_contrato,id_contexto,id_empresa_cliente,activo')
                ->find($this->input('id_tarifario'));

            if (! $tarifario || ! $tarifario->activo || ! $tarifario->contrato || ! $tarifario->contrato->activo) {
                $validator->errors()->add('id_tarifario', 'El contrato/tarifa seleccionado no está disponible.');
                return;
            }

            if (! in_array((int) $tarifario->id_contexto, $accessibleContextIds, true)) {
                $validator->errors()->add('id_tarifario', 'El contrato/tarifa seleccionado no está disponible para tu usuario.');
            }

            if ($selectedContextId > 0 && (int) $tarifario->id_contexto !== $selectedContextId) {
                $validator->errors()->add('id_tarifario', 'El contrato/tarifa no pertenece al cliente seleccionado.');
            }

            if ((int) $tarifario->contrato->id_empresa_cliente !== (int) $estacion->id_empresa_cliente) {
                $validator->errors()->add('id_tarifario', 'El contrato/tarifa no pertenece a la empresa de la estación seleccionada.');
            }

            if ($this->filled('id_contrato') && (int) $this->input('id_contrato') !== (int) $tarifario->id_contrato) {
                $validator->errors()->add('id_contrato', 'El contrato no coincide con el contrato/tarifa seleccionado.');
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
