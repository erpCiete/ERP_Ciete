<?php

namespace App\Http\Requests\Api;

use App\Models\EstacionServicio;
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
        return [
            'id_contexto' => ['nullable', 'integer'],
            'numero_trabajo' => ['required', 'string', 'max:50'],
            'descripcion_trabajo' => ['required', 'string', 'max:150'],
            'id_estacion_servicio' => ['required', 'exists:estaciones_servicio,id_estacion_servicio'],
            'fecha_encargo' => ['required', 'date'],
            'fecha_terminacion' => ['nullable', 'date'],
            'estado' => ['required', Rule::in(['borrador', 'en_curso', 'terminado', 'cerrado', 'cancelado'])],
            'observaciones' => ['nullable', 'string'],
            'id_contrato' => [
                Rule::requiredIf(fn () => $this->esCliente(1)),
                'nullable',
                'integer',
            ],
            'categoria' => ['nullable', 'string', 'max:100'],
            'id_tipo_documento' => [
                Rule::requiredIf(fn () => $this->esCliente(2)),
                'nullable',
                'integer',
            ],
            'id_tipo_trabajo' => [
                Rule::requiredIf(fn () => $this->esCliente(2)),
                'nullable',
                'integer',
            ],
            'numero_aviso' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $user = $this->user();
            $selectedContextId = (int) ($this->input('id_contexto') ?: 0);
            $accessibleContextIds = $user?->getAccessibleContextIds() ?? [];

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

    private function esCliente(int $idContexto): bool
    {
        if (! $this->id_estacion_servicio) {
            return false;
        }

        $estacion = EstacionServicio::withoutGlobalScopes()->find($this->id_estacion_servicio);

        return $estacion && (int) $estacion->id_contexto === $idContexto;
    }
}
