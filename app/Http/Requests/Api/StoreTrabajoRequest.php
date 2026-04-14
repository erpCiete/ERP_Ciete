<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class StoreTrabajoRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'id_estacion_servicio' => ['required', 'integer'],
            'fecha_programada' => ['nullable', 'date'],
            'prioridad' => ['nullable', Rule::in(['baja', 'media', 'alta'])],
            'estado' => ['nullable', Rule::in(['pendiente', 'en_proceso', 'cerrado'])],

            // Validacion condicional por contexto
            'cod_repsol' => [
                Rule::requiredIf(fn () => $this->isRepsolContext()),
                'nullable',
                'string',
                'max:80',
                Rule::prohibitedIf(fn () => $this->isCepsaContext()),
            ],
            'cod_cepsa' => [
                Rule::requiredIf(fn () => $this->isCepsaContext()),
                'nullable',
                'string',
                'max:80',
                Rule::prohibitedIf(fn () => $this->isRepsolContext()),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'titulo' => is_string($this->titulo) ? trim($this->titulo) : $this->titulo,
            'descripcion' => is_string($this->descripcion) ? trim($this->descripcion) : $this->descripcion,
            'cod_repsol' => is_string($this->cod_repsol) ? trim($this->cod_repsol) : $this->cod_repsol,
            'cod_cepsa' => is_string($this->cod_cepsa) ? trim($this->cod_cepsa) : $this->cod_cepsa,
        ]);
    }

    private function isRepsolContext(): bool
    {
        $codigo = strtoupper((string) optional($this->user()?->contexto)->codigo);

        return $codigo === 'REPSOL';
    }

    private function isCepsaContext(): bool
    {
        $codigo = strtoupper((string) optional($this->user()?->contexto)->codigo);

        return $codigo === 'CEPSA';
    }
}
