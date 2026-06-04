<?php

namespace App\Http\Requests\Api;

use App\Models\EstacionServicio;
use App\Models\Trabajo;
use App\Support\ContextGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTrabajoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('fecha_terminado') && ! $this->has('fecha_terminacion')) {
            $this->merge([
                'fecha_terminacion' => $this->input('fecha_terminado'),
            ]);
        }
    }

    public function rules(): array
    {
        $trabajo = $this->route('trabajo');
        $allowedStatuses = Trabajo::ESTADOS_MANUALES;

        if ($trabajo instanceof Trabajo && filled($trabajo->estado)) {
            $allowedStatuses = array_values(array_unique([...$allowedStatuses, (string) $trabajo->estado]));
        }

        return [
            'id_contexto' => ['nullable', 'integer'],
            'numero_trabajo' => ['sometimes', 'required', 'integer'],
            'numero_trabajo_operativo' => ['nullable', 'string', 'max:100'],
            'descripcion_trabajo' => ['sometimes', 'required', 'string', 'max:150'],
            'id_estacion_servicio' => ['sometimes', 'required', 'exists:estaciones_servicio,id_estacion_servicio'],
            'fecha_encargo' => ['sometimes', 'required', 'date'],
            'fecha_terminacion' => ['nullable', 'date'],
            'estado' => ['sometimes', 'required', Rule::in($allowedStatuses)],
            'observaciones' => ['nullable', 'string'],
            'id_responsable_ciete' => [
                'nullable',
                'integer',
                Rule::exists('usuarios', 'id_usuario')->where(fn ($query) => $query->where('activo', true)),
            ],
            'id_contrato' => [
                Rule::requiredIf(fn () => $this->esContexto('moeve')),
                'nullable',
                'integer',
            ],
            'categoria' => ['nullable', 'string', 'max:100'],
            'id_tipo_documento' => [
                Rule::requiredIf(fn () => $this->esContexto('repsol')),
                'nullable',
                'integer',
            ],
            'id_tipo_trabajo' => [
                Rule::requiredIf(fn () => $this->esContexto('repsol')),
                'nullable',
                'integer',
            ],
            'numero_aviso' => ['nullable', 'string', 'max:80'],
            'updated_at'   => ['nullable', 'string'],
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
        if (! $this->id_estacion_servicio) {
            return false;
        }

        $estacion = EstacionServicio::withoutGlobalScopes()->find($this->id_estacion_servicio);

        return $estacion && ContextGuard::workspaceKeyForContextId((int) $estacion->id_contexto) === $workspaceKey;
    }
}
